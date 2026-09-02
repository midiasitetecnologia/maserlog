<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Coleta;
use App\ApiGeral;
use App\ApiIntegracao;
use DB;

class ApiColeta extends Model
{

    public function Local_GetDadosColeta($coleta_id)
    {

        $continuar = true;
        $retorno   = array();
        $dados     = array();
        $tipos_veiculo = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $motorista = DB::table('motorista')->where('user_id', '=', auth()->user()->id)->first();

            if (empty($motorista) == false) {
                $motorista_id = $motorista->id;
            } else {

                $continuar = false;
                $retorno['cod_retorno'] = 'B203';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$email', auth()->user()->email, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }
        }

        if ($continuar) {

            // Pegar Somente a o campo PLACA para melhor perfomance
            $veiculo = DB::table('veiculo')
                ->select('veiculo.placa', 'veiculo.geo_lat', 'veiculo.geo_lng')
                ->where('motorista_id', '=', $motorista_id)
                ->first();

            if (empty($veiculo)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'B201';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } else {

                $coleta = DB::table('coleta')
                    ->select(
                        'coleta.*',
                        'coleta.id as coleta_id',
                        'cliente.nome as nome_cliente',
                        'cli_coleta.nome as local_coleta',
                        'cli_coleta.endereco as endereco_coleta',
                        'cli_coleta.bairro as bairro_coleta',
                        'cli_coleta.cidade as cidade_coleta',
                        'cli_coleta.uf as uf_coleta',
                        'cli_coleta.geo_lat as geo_lat_coleta',
                        'cli_coleta.geo_lng as geo_lng_coleta',

                        'cli_entrega.nome as local_entrega',
                        'cli_entrega.endereco as endereco_entrega',
                        'cli_entrega.bairro as bairro_entrega',
                        'cli_entrega.cidade as cidade_entrega',
                        'cli_entrega.uf as uf_entrega',
                        'cli_entrega.geo_lat as geo_lat_entrega',
                        'cli_entrega.geo_lng as geo_lng_entrega',
                        'cli_entrega.local_distrib',

                        'mot_coleta.nome as motorista_coleta',
                        'mot_entrega.nome as motorista_entrega'
                    )
                    ->leftjoin('cliente', function ($join) {
                        $join->on('cliente.codigo', '=', 'coleta.cod_cliente')
                            ->on('cliente.empresa', '=', 'coleta.empresa');
                    })
                    ->leftjoin('cliente as cli_coleta', function ($join) {
                        $join->on('cli_coleta.codigo', '=', 'coleta.cod_loc_coleta')
                            ->on('cli_coleta.empresa', '=', 'coleta.empresa');
                    })
                    ->leftjoin('cliente as cli_entrega', function ($join) {
                        $join->on('cli_entrega.codigo', '=', 'coleta.cod_loc_entrega')
                            ->on('cli_entrega.empresa', '=', 'coleta.empresa');
                    })
                    ->leftjoin('motorista as mot_coleta', function ($join) {
                        $join->on('mot_coleta.id', '=', 'coleta.motor_coleta_id');
                    })
                    ->leftjoin('motorista as mot_entrega', function ($join) {
                        $join->on('mot_entrega.id', '=', 'coleta.motor_entrega_id');
                    })
                    ->where('coleta.id', '=', $coleta_id)
                    ->first();

                if (empty($coleta)) {

                    $retorno['cod_retorno'] = 'E200';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                    $retorno['msg_retorno'] = $msg_erro;
                } else {

                    // Devolvemos a placa de baldeação SE ainda não foi realizada
                    if (rgDifTrimNull($coleta->placa_baldeacao) && ($coleta->baldeada <> 'S')) {
                        $placa_baldeacao = $coleta->placa_baldeacao;
                    } else {
                        $placa_baldeacao = null;
                    }

                    $distancia_destino    = 'indefinida';
                    $prev_chegada_destino = 'indefinida';

                    if ((rgDifZeroNull($veiculo->geo_lat)) && (rgDifZeroNull($veiculo->geo_lng))) {

                        // Solicitação está na fase de COLETA ou ENTREGA?
                        if (substr($coleta->status, 0, 1) == 'C') {
                            $geo_lat_destino = $coleta->geo_lat_coleta;
                            $geo_lng_destino = $coleta->geo_lng_coleta;
                        } else {
                            $geo_lat_destino = $coleta->geo_lat_entrega;
                            $geo_lng_destino = $coleta->geo_lng_entrega;
                        }

                        if ((rgDifZeroNull($geo_lat_destino)) && (rgDifZeroNull($geo_lng_destino))) {

                            $api = new ApiIntegracao();
                            $array_tempo_dist = $api->CalcularDistanciaOrigem_Destino(
                                $veiculo->geo_lat,
                                $veiculo->geo_lng,
                                $geo_lat_destino,
                                $geo_lng_destino
                            );

                            $distancia_destino = rgFormataDistancia($array_tempo_dist['distance']);
                            $tempo_estimado_google = $array_tempo_dist['duration'];

                            // Somamos o tempo retornado do google com a data e hora atual para ficar
                            // mais claro que é a data de previsao de chegada que estamos retornando
                            $timezone_app = date_default_timezone_get();
                            $data_hora_atual = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

                            $tempo_estimado = strtotime($data_hora_atual) + $tempo_estimado_google;

                            //O tempo estimado está calculado em segundos. Fazemos a conversão para "H:i". Visualizar segundos não importa.
                            $prev_chegada_destino = date('H:i', $tempo_estimado);
                        }
                    }

                    // SE estiver como COLETA INICIADA... retornaremos a lista de tipos de veículo 
                    // para o usuário selecionar quando for finalizar a coleta
                    if ($coleta->status == 'C4') {

                        $tipo_veic = DB::table('tipo_veiculo')
                            ->select('codigo', 'descricao')
                            ->whereIn('classe', ['M', 'R'])
                            ->orderBy('descricao', 'asc')
                            ->get();

                        if (count($tipo_veic) > 0) {

                            $idx = 0;

                            foreach ($tipo_veic as $tp_veic) {

                                // Adicionar o primeiro elemento como vazio
                                $tipos_veiculo[$idx]['codigo']    = $tp_veic->codigo;
                                $tipos_veiculo[$idx]['descricao'] = $tp_veic->descricao;

                                $idx++;
                            }
                        }
                    }
                    $dados = $this->CarregaArrayDadosColeta($coleta, $distancia_destino, $prev_chegada_destino, $placa_baldeacao);

                    $retorno['cod_retorno'] = 'Z100';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];
        $resultado['dados'] = $dados;
        $resultado['tipos_veiculo'] = $tipos_veiculo;

        return $resultado;
    }


    public function CarregaArrayDadosColeta($coleta, $distancia_destino, $prev_chegada_destino, $placa_baldeacao)
    {

        $info_coleta['coleta_id']      = $coleta->coleta_id;
        $info_coleta['numero']         = $coleta->numero;
        $info_coleta['dt_prev_coleta'] = $coleta->dt_prev_coleta;
        $info_coleta['hr_prev_coleta'] = $coleta->hr_prev_coleta;

        $info_coleta['nome_cliente'] = $coleta->nome_cliente;

        // Quando é comanda... atribuimos o valor do local de coleta e entrega informado 
        // pelo usuáro.. .ao invés dos campos da tabela CLIENTE.

        if (($coleta->coleta_fixa == 'C') && (rgDifZeroNull($coleta->solic_origem_id))) {

            $info_coleta['local_coleta'] = $coleta->local_coleta_cmd;

            //Zeramos os endereços de coleta quando for comanda
            $info_coleta['endereco_coleta'] = null;
            $info_coleta['cidade_coleta']   = null;
            $info_coleta['bairro_coleta']   = null;
            $info_coleta['uf_coleta']       = null;
            $info_coleta['geo_lat_coleta']  = null;
            $info_coleta['geo_lng_coleta']  = null;

            $info_coleta['local_entrega'] = $coleta->local_entrega_cmd;

            //Zeramos os endereços de coleta quando for comanda
            $info_coleta['endereco_entrega'] = null;
            $info_coleta['bairro_entrega']   = null;
            $info_coleta['cidade_entrega']   = null;
            $info_coleta['uf_entrega']       = null;
            $info_coleta['geo_lat_entrega']  = null;
            $info_coleta['geo_lng_entrega']  = null;
            $info_coleta['local_distrib']    = null;
        } else {

            $info_coleta['local_coleta']    = $coleta->local_coleta;
            $info_coleta['endereco_coleta'] = $coleta->endereco_coleta;
            $info_coleta['cidade_coleta']   = $coleta->cidade_coleta;
            $info_coleta['bairro_coleta']   = $coleta->bairro_coleta;
            $info_coleta['uf_coleta']       = $coleta->uf_coleta;
            $info_coleta['geo_lat_coleta']  = $coleta->geo_lat_coleta;
            $info_coleta['geo_lng_coleta']  = $coleta->geo_lng_coleta;

            $info_coleta['local_entrega']    = $coleta->local_entrega;
            $info_coleta['endereco_entrega'] = $coleta->endereco_entrega;
            $info_coleta['bairro_entrega']   = $coleta->bairro_entrega;
            $info_coleta['cidade_entrega']   = $coleta->cidade_entrega;
            $info_coleta['uf_entrega']       = $coleta->uf_entrega;
            $info_coleta['geo_lat_entrega']  = $coleta->geo_lat_entrega;
            $info_coleta['geo_lng_entrega']  = $coleta->geo_lng_entrega;
            $info_coleta['local_distrib']    = $coleta->local_distrib;
        }

        $info_coleta['solicitante'] = $coleta->solicitante;

        $info_coleta['obs_coleta']  = $coleta->obs_coleta;
        $info_coleta['coleta_fixa'] = $coleta->coleta_fixa;

        $info_coleta['placa_coleta']     = $coleta->placa_coleta;
        $info_coleta['motorista_coleta'] = $coleta->motorista_coleta;
        $info_coleta['placa_baldeacao']  = $placa_baldeacao;

        $info_coleta['caract_coleta'] = $coleta->caract_coleta;

        $info_coleta['peso']       = rgFormataPesoVeiculo($coleta->peso);
        $info_coleta['volumes']    = $coleta->volumes;
        $info_coleta['especie']    = $coleta->especie;
        $info_coleta['sis_carga']  = $coleta->sis_carga;
        $info_coleta['larg_carga'] = $coleta->larg_carga;
        $info_coleta['alt_carga']  = $coleta->alt_carga;
        $info_coleta['comp_carga'] = $coleta->comp_carga;

        $info_coleta['dt_prev_entrega'] = $coleta->dt_prev_entrega;
        $info_coleta['hr_prev_entrega'] = $coleta->hr_prev_entrega;
        $info_coleta['entrega_urgente'] = $coleta->entrega_urgente;

        $info_coleta['recebedor'] = $coleta->recebedor;

        $info_coleta['dt_efet_coleta']  = $coleta->dt_efet_coleta;
        $info_coleta['hr_partida_coleta'] = $coleta->hr_partida_coleta;
        $info_coleta['hr_cheg_coleta']  = $coleta->hr_cheg_coleta;
        $info_coleta['hr_atend_coleta'] = $coleta->hr_atend_coleta;
        $info_coleta['hr_sai_coleta']   = $coleta->hr_sai_coleta;

        $info_coleta['placa_entrega']     = $coleta->placa_entrega;
        $info_coleta['motorista_entrega'] = $coleta->motorista_entrega;

        $info_coleta['dt_efet_entrega']  = $coleta->dt_efet_entrega;
        $info_coleta['hr_partida_entrega'] = $coleta->hr_partida_entrega;
        $info_coleta['hr_cheg_entrega']  = $coleta->hr_cheg_entrega;
        $info_coleta['hr_atend_entrega'] = $coleta->hr_atend_entrega;
        $info_coleta['hr_sai_entrega']   = $coleta->hr_sai_entrega;

        $info_coleta['dur_prev_coleta']  = $coleta->dur_prev_coleta;
        $info_coleta['dur_prev_entrega'] = $coleta->dur_prev_entrega;

        $info_coleta['receber_nf_frete'] = $coleta->receber_nf_frete;
        $info_coleta['aceitar_foto_rom'] = $coleta->aceitar_foto_rom;

        $info_coleta['instrucao'] = trim($coleta->txt_instrucao);

        $info_coleta['status'] = $coleta->status;

        $info_coleta['distancia_destino']    = $distancia_destino;
        $info_coleta['prev_chegada_destino'] = $prev_chegada_destino;

        // Devolvemos apenas a parte do status depois do hifen
        $txt_status = explode('-', $this->RetornarDescrStatusColeta($coleta->status));

        $info_coleta['txt_status'] = $txt_status[1];

        $info_coleta['obs_nao_entrega'] = rgGetMsgMotNaoEntregaColeta($coleta->mot_nao_entrega, $coleta->obs_nao_entrega);

        $info_coleta['reentrega'] = $coleta->reentrega;

        $info_coleta['solic_origem_id'] = $coleta->solic_origem_id;

        return $info_coleta;
    }


    public function Local_GetColetasPendentes()
    {

        $continuar = true;
        $retorno   = array();
        $dados     = array();
        $dados['contrato'] = array();
        $dados['rota']     = array();
        $dados['carga']    = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $motorista = DB::table('motorista')->where('user_id', '=', auth()->user()->id)->first();

            if (empty($motorista) == false) {
                $motorista_id = $motorista->id;
            } else {

                $continuar = false;
                $retorno['cod_retorno'] = 'B203';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$email', auth()->user()->email, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }
        }

        if ($continuar) {

            // Pegar Somente a o campo PLACA para melhor perfomance
            $veiculo = DB::table('veiculo')
                ->select('veiculo.placa')
                ->where('motorista_id', '=', $motorista_id)
                ->first();

            if (empty($veiculo)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'B201';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }

            if ($continuar) {

                $timezone_app = date_default_timezone_get();
                $data_atual_serv = Carbon::now($timezone_app)->format('Y-m-d');

                $tem_solic_cont = false;
                $solic_cont_id  = null;

                // -----------------------------------------------------------------------------
                // Quadro do MAPA -> Solicitação coleta fixa(Contrato: coleta_fixa = 'C')
                // -----------------------------------------------------------------------------

                // Aqui retornamos as solicitações de COLETA FIXA = 'CONTRATO
                $solic_cont =  DB::table('coleta as col')
                    ->select(
                        'col.id',
                        'col.id as coleta_id',
                        'col.numero',
                        'col.dt_prev_coleta as dt_prev_col_ent',
                        'col.hr_prev_coleta as hr_prev_col_ent',
                        'col.dt_prev_coleta',
                        'col.hr_prev_coleta',
                        'col.obs_coleta',
                        'col.coleta_fixa',
                        'col.caract_coleta',
                        'col.dt_prev_entrega',
                        'col.hr_prev_entrega',
                        'col.entrega_urgente',
                        'col.dt_efet_coleta',
                        'col.dur_prev_coleta',
                        'col.dur_prev_entrega',
                        'col.receber_nf_frete',
                        'col.aceitar_foto_rom',
                        'col.instrucao',
                        'col.status',
                        'col.txt_instrucao',
                        'col.solic_origem_id',
                        'col.local_coleta_cmd',
                        'col.local_entrega_cmd',
                        'col.placa_baldeacao',
                        'col.baldeada',
                        'cliente.nome as nome_cliente',
                        'cli_coleta.nome as local_coleta',
                        'cli_entrega.nome as local_entrega',
                        'cli_coleta.geo_lat as geo_lat_coleta',
                        'cli_coleta.geo_lng as geo_lng_coleta',
                        'cli_entrega.geo_lat as geo_lat_entrega',
                        'cli_entrega.geo_lng as geo_lng_entrega',
                        'cli_entrega.local_distrib as local_distrib_entrega'

                    )

                    ->join('cliente', function ($join) {
                        $join->on('cliente.codigo', '=', 'col.cod_cliente')
                            ->on('cliente.empresa', '=', 'col.empresa');
                    })
                    ->leftjoin('cliente as cli_coleta', function ($join) {
                        $join->on('cli_coleta.codigo', '=', 'col.cod_loc_coleta')
                            ->on('cli_coleta.empresa', '=', 'col.empresa');
                    })
                    ->leftjoin('cliente as cli_entrega', function ($join) {
                        $join->on('cli_entrega.codigo', '=', 'col.cod_loc_entrega')
                            ->on('cli_entrega.empresa', '=', 'col.empresa');
                    })

                    // Não pegamos solicitações com DATA DE COLETA futura
                    ->where('col.dt_prev_coleta', '<=', $data_atual_serv)

                    // Aqui pegamos somente solicitações na fase de COLETA.
                    // Consideramos APENAS solicitações tipo 'CONTRATO' com status 'C4' (Atend. Iniciado)
                    ->where('col.coleta_fixa', '=', 'C')
                    ->where(function ($query) {
                        $query->whereNull('col.solic_origem_id')
                            ->orWhere('col.solic_origem_id', '=', '0');
                    })

                    ->where('col.status', '=', 'C4')
                    ->where('col.placa_coleta', '=', $veiculo->placa)
                    ->orderBy('dt_prev_coleta', 'asc')
                    ->orderBy('hr_prev_coleta', 'asc')
                    ->first();

                if (empty($solic_cont) == false) {
                    $tem_solic_cont = true;
                    $solic_cont_id  = $solic_cont->coleta_id;

                    $dados['contrato'] = $this->MontaArrayUnicaSolicPendente($solic_cont);
                }
            }

            if ($continuar) {

                // -----------------------------------------------------------------------------
                // Quadro do MAPA -> Solicitações Pendentes(Coleta e Entrega)
                // -----------------------------------------------------------------------------

                // Aqui retornamos as solicitações de COLETA e ENTREGA alocadas para o veículo. 

                $solic_pend = DB::table('coleta as col')
                    ->select(
                        'col.id',
                        'col.id as coleta_id',
                        'col.numero',
                        'col.dt_prev_coleta as dt_prev_col_ent',
                        'col.hr_prev_coleta as hr_prev_col_ent',
                        'col.dt_prev_coleta',
                        'col.hr_prev_coleta',
                        'col.obs_coleta',
                        'col.coleta_fixa',
                        'col.caract_coleta',
                        'col.dt_prev_entrega',
                        'col.hr_prev_entrega',
                        'col.entrega_urgente',
                        'col.dt_efet_coleta',
                        'col.dur_prev_coleta',
                        'col.dur_prev_entrega',
                        'col.receber_nf_frete',
                        'col.aceitar_foto_rom',
                        'col.instrucao',
                        'col.status',
                        'col.txt_instrucao',
                        'col.solic_origem_id',
                        'col.local_coleta_cmd',
                        'col.local_entrega_cmd',
                        'col.seq_atend AS seq_atend',
                        'col.placa_baldeacao',
                        'col.baldeada',
                        'cliente.nome AS nome_cliente',
                        'cli_coleta.nome AS local_coleta',
                        'cli_entrega.nome AS local_entrega',
                        'cli_coleta.geo_lat as geo_lat_coleta',
                        'cli_coleta.geo_lng as geo_lng_coleta',
                        'cli_entrega.geo_lat as geo_lat_entrega',
                        'cli_entrega.geo_lng as geo_lng_entrega',
                        'cli_entrega.local_distrib as local_distrib_entrega'
                    )

                    ->join('cliente', function ($join) {
                        $join->on('cliente.codigo', '=', 'col.cod_cliente')
                            ->on('cliente.empresa', '=', 'col.empresa');
                    })
                    ->leftjoin('cliente as cli_coleta', function ($join) {
                        $join->on('cli_coleta.codigo', '=', 'col.cod_loc_coleta')
                            ->on('cli_coleta.empresa', '=', 'col.empresa');
                    })
                    ->leftjoin('cliente as cli_entrega', function ($join) {
                        $join->on('cli_entrega.codigo', '=', 'col.cod_loc_entrega')
                            ->on('cli_entrega.empresa', '=', 'col.empresa');
                    })

                    // Não pegamos solicitações com DATA DE COLETA futura
                    ->where('col.dt_prev_coleta', '<=', $data_atual_serv)

                    // Aqui pegamos somente solicitações na fase de COLETA.
                    //
                    // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
                    // Desconsideramos as COMANDAS (coleta_fixa = 'C' e solic_origem_id <> zero/null)
                    //
                    // Desconsideramos solicitações tipo 'CONTRATO' com status 'C4' (Atend. Iniciado)
                    // porque esse tipo deve entrar no SELECT específico dos contratos => $solic_cont
                    //
                    ->where(function ($query) use ($tem_solic_cont, $solic_cont_id) {

                        if ($tem_solic_cont == false) {   // -> IF do "empty($solic_cont) == true" no MAPA

                            $query->where(function ($query1) {
                                $query1->where('col.coleta_fixa', '!=', 'C')
                                    ->whereIn('col.status', ['C1', 'C2', 'C3', 'C4']);
                            })->orWhere(function ($query2) {
                                $query2->where('col.coleta_fixa', '=', 'C')
                                    ->whereIn('col.status', ['C1', 'C2', 'C3'])
                                    ->where(function ($query3) {
                                        $query3->whereNull('col.solic_origem_id')
                                            ->orWhere('col.solic_origem_id', '=', '0');
                                    });
                            });
                        } else {         // ELSE do "empty($solic_cont) == true" no MAPA
                            $query->where('col.coleta_fixa', '=', 'C')
                                ->whereIn('col.status', ['C1', 'C2', 'C3', 'C4'])
                                // Aqui... pegamos somente COMANDAS da solicitação CONTRATO em andamento
                                ->where('col.solic_origem_id', '=', $solic_cont_id);
                        }
                    })

                    // Somente COLETAS alocadas para o veículo
                    ->where('col.placa_coleta', '=', $veiculo->placa);

                //  ------------------------------
                //       U N I O N    A L L  -->  com Entregas carga do veículo)
                //  -------------------------------

                // Aqui retornamos as solicitações de ENTREGA alocadas para o veículo. 
                $solic_pend_union = DB::table('coleta as col')
                    ->select(
                        'col.id',
                        'col.id as coleta_id',
                        'col.numero',
                        'col.dt_prev_entrega as dt_prev_col_ent',
                        'col.hr_prev_entrega as hr_prev_col_ent',
                        'col.dt_prev_coleta',
                        'col.hr_prev_coleta',
                        'col.obs_coleta',
                        'col.coleta_fixa',
                        'col.caract_coleta',
                        'col.dt_prev_entrega',
                        'col.hr_prev_entrega',
                        'col.entrega_urgente',
                        'col.dt_efet_coleta',
                        'col.dur_prev_coleta',
                        'col.dur_prev_entrega',
                        'col.receber_nf_frete',
                        'col.aceitar_foto_rom',
                        'col.instrucao',
                        'col.status',
                        'col.txt_instrucao',
                        'col.solic_origem_id',
                        'col.local_coleta_cmd',
                        'col.local_entrega_cmd',
                        'col.seq_atend AS seq_atend',
                        'col.placa_baldeacao',
                        'col.baldeada',
                        'cliente.nome AS nome_cliente',
                        'cli_coleta.nome AS local_coleta',
                        'cli_entrega.nome AS local_entrega',
                        'cli_coleta.geo_lat as geo_lat_coleta',
                        'cli_coleta.geo_lng as geo_lng_coleta',
                        'cli_entrega.geo_lat as geo_lat_entrega',
                        'cli_entrega.geo_lng as geo_lng_entrega',
                        'cli_entrega.local_distrib as local_distrib_entrega'
                    )

                    ->join('cliente', function ($join) {
                        $join->on('cliente.codigo', '=', 'col.cod_cliente')
                            ->on('cliente.empresa', '=', 'col.empresa');
                    })
                    ->leftjoin('cliente as cli_coleta', function ($join) {
                        $join->on('cli_coleta.codigo', '=', 'col.cod_loc_coleta')
                            ->on('cli_coleta.empresa', '=', 'col.empresa');
                    })
                    ->leftjoin('cliente as cli_entrega', function ($join) {
                        $join->on('cli_entrega.codigo', '=', 'col.cod_loc_entrega')
                            ->on('cli_entrega.empresa', '=', 'col.empresa');
                    })

                    // Não pegamos solicitações com DATA DE COLETA futura
                    ->where('col.dt_prev_coleta', '<=', $data_atual_serv)

                    // Aqui pegamos somente solicitações na fase de ENTREGA. Não testamos o 
                    // campo coleta_fixa = 'C'... porque NÃO existirão solicitações do tipo CONTRATO
                    // em fase de ENTREGA. As 'comandas' criadas pelo motorista a partir de uma 
                    // solicitação origem CONTRATO são gravadas com coleta_fixa = 'N'.
                    ->whereIn('col.status', ['E1', 'E2', 'E3', 'E4'])

                    ->where(function ($query) use ($tem_solic_cont, $solic_cont_id) {

                        if ($tem_solic_cont == false) {  // -> IF do "empty($solic_cont) == true" no MAPA                            

                            // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
                            // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
                            //
                            $query->where('col.coleta_fixa', '!=', 'C')
                                ->orWhere(function ($query1) {
                                    $query1->where('col.coleta_fixa', '=', 'C')
                                        ->where(function ($query2) {
                                            $query2->whereNull('col.solic_origem_id')
                                                ->orWhere('col.solic_origem_id', '=', '0');
                                        });
                                });
                        } else {   // -> ELSE do "empty($solic_cont) == true" no MAPA
                            // Aqui... pegamos somente COMANDAS da solicitação CONTRATO em andamento
                            $query->where('col.solic_origem_id', '=', $solic_cont_id);
                        }
                    })

                    // Somente ENTREGAS alocadas para o veículo
                    ->where('col.placa_entrega', '=', $veiculo->placa)

                    // Faz o segundo select nas entregas UNINDO com as coletas e ordenando o resultado
                    ->union($solic_pend)
                    ->orderBy('seq_atend', 'asc')
                    ->orderBy('dt_prev_col_ent', 'asc')
                    ->orderBy('hr_prev_col_ent', 'asc')
                    ->get();

                // Atenção: Quando a leitura é por Get tem que testar com "Count"    
                if (count($solic_pend_union) > 0) {
                    // Montamos as solicitações pendentes na rota
                    $dados['rota'] = $this->MontaArraySolicPendentes($solic_pend_union);
                }

                // -----------------------------------------------------------------------------
                // Quadro do MAPA -> Coletas Realizadas E Entregas(carga do veículo)
                // -----------------------------------------------------------------------------

                // Aqui retornamos os registros de COLETA REALIZADAS e ainda alocadas para o veículo... 
                // ou seja.. é o que ainda está carregado no veículo => CARGA.
                $dados['carga'] = $this->MontarSolicCargaVeiculo($veiculo->placa, $tem_solic_cont, $solic_cont_id);
            }  // fim do if encontrou motorista

        }  // fim do if $continuar

        if ($continuar) {

            if ((empty($dados['contrato'])) && (empty($dados['rota'])) && (empty($dados['carga']))
            ) {
                $retorno['cod_retorno'] = 'Z101';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } else {
                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];
        $resultado['dados'] = $dados;

        return $resultado;
    }



    public function MontarSolicCargaVeiculo($veiculo_placa, $tem_solic_cont, $solic_cont_id)
    {

        $array_carga = array();

        // Aqui retornamos os registros de COLETA REALIZADAS e ainda alocadas para o veículo 
        // E as ENTREGAS, ou seja.. é o que está carregado no veículo => CARGA.
        $solic_carga = DB::table('coleta as col')
            ->select(
                'col.id',
                'col.id as coleta_id',
                'col.numero',
                'col.dt_prev_coleta as dt_prev_col_ent',
                'col.hr_prev_coleta as hr_prev_col_ent',
                'col.dt_prev_coleta',
                'col.hr_prev_coleta',
                'col.obs_coleta',
                'col.coleta_fixa',
                'col.caract_coleta',
                'col.dt_prev_entrega',
                'col.hr_prev_entrega',
                'col.entrega_urgente',
                'col.dt_efet_coleta',
                'col.dur_prev_coleta',
                'col.dur_prev_entrega',
                'col.receber_nf_frete',
                'col.aceitar_foto_rom',
                'col.instrucao',
                'col.status',
                'col.txt_instrucao',
                'col.solic_origem_id',
                'col.local_coleta_cmd',
                'col.local_entrega_cmd',
                'col.seq_atend AS seq_atend',
                'col.placa_baldeacao',
                'col.baldeada',
                'cliente.nome as nome_cliente',
                'cli_coleta.nome as local_coleta',
                'cli_entrega.nome as local_entrega',
                'cli_coleta.geo_lat as geo_lat_coleta',
                'cli_coleta.geo_lng as geo_lng_coleta',
                'cli_entrega.geo_lat as geo_lat_entrega',
                'cli_entrega.geo_lng as geo_lng_entrega',
                'cli_entrega.local_distrib as local_distrib_entrega'
            )

            ->join('cliente', function ($join) {
                $join->on('cliente.codigo', '=', 'col.cod_cliente')
                    ->on('cliente.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as cli_coleta', function ($join) {
                $join->on('cli_coleta.codigo', '=', 'col.cod_loc_coleta')
                    ->on('cli_coleta.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as cli_entrega', function ($join) {
                $join->on('cli_entrega.codigo', '=', 'col.cod_loc_entrega')
                    ->on('cli_entrega.empresa', '=', 'col.empresa');
            })

            // Somente status 'CR' - Coleta Realizada E NÃO foi descarregada no pavilhão
            ->where('col.status', '=', 'CR')
            ->where(function ($query) {
                $query->whereNull('col.carga_pavilhao')
                    ->orWhere('col.carga_pavilhao', '!=', 'S');
            })

            // Somente COLETAS alocadas para o veículo
            ->where('col.placa_coleta', '=', $veiculo_placa)

            ->where(function ($query) use ($tem_solic_cont, $solic_cont_id) {

                if ($tem_solic_cont == false) {  // -> IF do "empty($solic_cont) == true" no MAPA

                    // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
                    // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
                    //
                    $query->where('col.coleta_fixa', '!=', 'C')
                        ->orWhere(function ($query1) {
                            $query1->where('col.coleta_fixa', '=', 'C')
                                ->where(function ($query2) {
                                    $query2->whereNull('col.solic_origem_id')
                                        ->orWhere('col.solic_origem_id', '=', '0');
                                });
                        });
                } else {
                    // Aqui... pegamos somente COMANDAS da solicitação CONTRATO em andamento
                    $query->where('col.solic_origem_id', '=', $solic_cont_id);
                }
            });

        //  ------------------------------
        //       U N I O N    A L L
        //  -------------------------------

        $solic_carga_union = DB::table('coleta as col')
            ->select(
                'col.id',
                'col.id as coleta_id',
                'col.numero',
                'col.dt_prev_entrega as dt_prev_col_ent',
                'col.hr_prev_entrega as hr_prev_col_ent',
                'col.dt_prev_coleta',
                'col.hr_prev_coleta',
                'col.obs_coleta',
                'col.coleta_fixa',
                'col.caract_coleta',
                'col.dt_prev_entrega',
                'col.hr_prev_entrega',
                'col.entrega_urgente',
                'col.dt_efet_coleta',
                'col.dur_prev_coleta',
                'col.dur_prev_entrega',
                'col.receber_nf_frete',
                'col.aceitar_foto_rom',
                'col.instrucao',
                'col.status',
                'col.txt_instrucao',
                'col.solic_origem_id',
                'col.local_coleta_cmd',
                'col.local_entrega_cmd',
                'col.seq_atend AS seq_atend',
                'col.placa_baldeacao',
                'col.baldeada',
                'cliente.nome as nome_cliente',
                'cli_coleta.nome as local_coleta',
                'cli_entrega.nome as local_entrega',
                'cli_coleta.geo_lat as geo_lat_coleta',
                'cli_coleta.geo_lng as geo_lng_coleta',
                'cli_entrega.geo_lat as geo_lat_entrega',
                'cli_entrega.geo_lng as geo_lng_entrega',
                'cli_entrega.local_distrib as local_distrib_entrega'
            )

            ->join('cliente', function ($join) {
                $join->on('cliente.codigo', '=', 'col.cod_cliente')
                    ->on('cliente.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as cli_coleta', function ($join) {
                $join->on('cli_coleta.codigo', '=', 'col.cod_loc_coleta')
                    ->on('cli_coleta.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as cli_entrega', function ($join) {
                $join->on('cli_entrega.codigo', '=', 'col.cod_loc_entrega')
                    ->on('cli_entrega.empresa', '=', 'col.empresa');
            })

            // Aqui pegamos somente solicitações na fase de ENTREGA. Não  testamos o 
            // campo coleta_fixa = 'C'... porque NÃO existirão solicitações do tipo CONTRATO
            // em fase de ENTREGA. As 'comandas' criadas pelo motorista a partir de uma 
            // solicitação origem CONTRATO são gravadas com coleta_fixa = 'N'.
            //
            // Consideramos todos os status de ENTREGA que indicam que a carga AINDA está
            // com o veículo => 'E1', 'E2', 'E3', 'E4'
            // 
            // Status 'EN' ou 'EP': carga no veículo se não tem descarga no pavilhão 
            // 
            // Desconsideramos status 'E0' pode porque a entrega NÃO está autorizada e 
            // NÃO pode aparecer no aplicativo para o motorista
            //           

            ->where(function ($query) {
                $query->whereIn('col.status', ['E1', 'E2', 'E3', 'E4'])
                    ->orWhere(function ($query1) {
                        $query1->whereIn('col.status', ['EN', 'EP'])
                            ->where(function ($query) {
                                $query->whereNull('col.carga_pavilhao')
                                    ->orWhere('col.carga_pavilhao', '!=', 'S');
                            })
                            ->where(function ($query) {
                                $query->whereNull('col.reentrega_gerada')
                                    ->orWhere('col.reentrega_gerada', '!=', 'S');
                            });
                    });
            })

            // Somente ENTREGAS alocadas para o veículo
            ->where('col.placa_entrega', '=', $veiculo_placa)

            ->where(function ($query) use ($tem_solic_cont, $solic_cont_id) {

                if ($tem_solic_cont == false) {  // -> IF do "empty($solic_cont) == true" no MAPA                   

                    // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
                    // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
                    //
                    $query->where('col.coleta_fixa', '!=', 'C')
                        ->orWhere(function ($query1) {
                            $query1->where('col.coleta_fixa', '=', 'C')
                                ->where(function ($query2) {
                                    $query2->whereNull('col.solic_origem_id')
                                        ->orWhere('col.solic_origem_id', '=', '0');
                                });
                        });
                } else {
                    // Aqui... pegamos somente COMANDAS da solicitação CONTRATO em andamento
                    $query->where('col.solic_origem_id', '=', $solic_cont_id);
                }
            })

            // Abaixo o comando "union" fará a junção dos dois selects que estará mo objeto "$solic_carga_union"
            ->union($solic_carga)
            ->orderBy('seq_atend', 'asc')
            ->orderBy('dt_prev_col_ent', 'asc')
            ->orderBy('hr_prev_col_ent', 'asc')
            ->get();


        if (count($solic_carga_union) > 0) {

            $ind = 0;

            foreach ($solic_carga_union as $solic_carga) {

                $array_carga[$ind] = $this->MontaArrayUnicaSolicPendente($solic_carga);

                $notas_fiscais = DB::table('coleta_nf')
                    ->select(DB::raw('count(id) AS qtde_notas, 
                                    SUM(volumes) AS qtde_volumes'))
                    ->where('coleta_id', '=', $solic_carga->coleta_id)
                    ->first();

                $array_carga[$ind]['qtde_notas']   = $notas_fiscais->qtde_notas;
                $array_carga[$ind]['qtde_volumes'] = IntVal($notas_fiscais->qtde_volumes);

                $ind++;
            }
        }

        return $array_carga;
    }


    public function MontaArraySolicPendentes($solic_pendentes)
    {

        $info_coleta = array();
        $ind = 0;

        foreach ($solic_pendentes as $solic_pend) {

            $info_coleta[$ind] = $this->MontaArrayUnicaSolicPendente($solic_pend);

            $notas_fiscais = DB::table('coleta_nf')
                ->select(DB::raw('count(id) AS qtde_notas, 
                            SUM(volumes) AS qtde_volumes'))
                ->where('coleta_id', '=', $solic_pend->coleta_id)
                ->first();

            $info_coleta[$ind]['qtde_notas']   = $notas_fiscais->qtde_notas;
            $info_coleta[$ind]['qtde_volumes'] = $notas_fiscais->qtde_volumes;

            $ind++;
        }

        return $info_coleta;
    }


    public function MontaArrayUnicaSolicPendente($solic_pend)
    {

        $info_coleta['coleta_id']       = $solic_pend->coleta_id;
        $info_coleta['numero']          = $solic_pend->numero;

        $info_coleta['dt_prev_coleta']  = $solic_pend->dt_prev_coleta;
        $info_coleta['hr_prev_coleta']  = $solic_pend->hr_prev_coleta;

        $info_coleta['nome_cliente']    = $solic_pend->nome_cliente;

        // Testamos se é uma 'comanda' de um CONTRATO
        if (($solic_pend->coleta_fixa == 'C') && (rgDifZeroNull($solic_pend->solic_origem_id))) {

            // Quando é comanda... atribuimos o valor do local de coleta e entrega informado 
            // pelo usuário.. .ao invés dos campos da tabela CLIENTE.
            $solic_pend->local_coleta  = $solic_pend->local_coleta_cmd;
            $solic_pend->local_entrega = $solic_pend->local_entrega_cmd;
        }

        $info_coleta['local_coleta']    = $solic_pend->local_coleta;
        $info_coleta['local_entrega']   = $solic_pend->local_entrega;

        $info_coleta['local_distrib']   =  $solic_pend->local_distrib_entrega;

        if (substr($solic_pend->status, 0, 1) == 'C') {
            $info_coleta['geo_lat_destino']  = $solic_pend->geo_lat_coleta;
            $info_coleta['geo_lng_destino']  = $solic_pend->geo_lng_coleta;
        } else {
            $info_coleta['geo_lat_destino']  = $solic_pend->geo_lat_entrega;
            $info_coleta['geo_lng_destino']  = $solic_pend->geo_lng_entrega;
        }

        $info_coleta['obs_coleta']      = $solic_pend->obs_coleta;
        $info_coleta['coleta_fixa']     = $solic_pend->coleta_fixa;

        $info_coleta['caract_coleta']   = $solic_pend->caract_coleta;
        $info_coleta['dt_prev_entrega'] = $solic_pend->dt_prev_entrega;
        $info_coleta['hr_prev_entrega'] = $solic_pend->hr_prev_entrega;
        $info_coleta['entrega_urgente'] = $solic_pend->entrega_urgente;

        $info_coleta['dt_efet_coleta']  = $solic_pend->dt_efet_coleta;

        $info_coleta['dur_prev_coleta']  = $solic_pend->dur_prev_coleta;
        $info_coleta['dur_prev_entrega'] = $solic_pend->dur_prev_entrega;


        // Devolvemos a placa de baldeação SE ainda não foi realizada
        if (rgDifTrimNull($solic_pend->placa_baldeacao) && ($solic_pend->baldeada <> 'S')) {
            $placa_baldeacao = $solic_pend->placa_baldeacao;
        } else {
            $placa_baldeacao = null;
        }

        $info_coleta['placa_baldeacao']  = $placa_baldeacao;

        $info_coleta['receber_nf_frete'] = $solic_pend->receber_nf_frete;
        $info_coleta['aceitar_foto_rom'] = $solic_pend->aceitar_foto_rom;

        $info_coleta['instrucao']        = trim($solic_pend->txt_instrucao);

        $info_coleta['status']           = $solic_pend->status;
        $info_coleta['txt_status']       = $this->RetornarDescrStatusColeta($solic_pend->status);
        $info_coleta['solic_origem_id']  = $solic_pend->solic_origem_id;

        return $info_coleta;
    }


    public function RetornarDescrStatusColeta($status_coleta)
    {
        return match ($status_coleta) {
            'C0' => 'Coleta - Solicitada',
            'C1' => 'Coleta - Autorizada',
            'C2' => 'Coleta - Deslocamento',
            'C3' => 'Coleta - Chegada',
            'C4' => 'Coleta - Iniciada',
            'CN' => 'Coleta - Não realizada',
            'CR' => 'Coleta - Realizada',
            'E0' => 'Entrega - Carga definida',
            'E1' => 'Entrega - Autorizada',
            'E2' => 'Entrega - Deslocamento',
            'E3' => 'Entrega - Chegada',
            'E4' => 'Entrega - Iniciada',
            'EN' => 'Entrega - Não Realizada',
            'EP' => 'Entrega - Parcial',
            'ER' => 'Entrega - Realizada',
            default => '',
        };
    }


    public function Local_SetarInicioAtendColeta($coleta_id)
    {

        $continuar  = true;
        $retorno    = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $motorista = DB::table('motorista')->where('user_id', '=', auth()->user()->id)->first();

            if (empty($motorista) == false) {
                $motorista_id = $motorista->id;
            } else {

                $continuar = false;
                $retorno['cod_retorno'] = 'B203';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$email', auth()->user()->email, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }

            if ($continuar) {

                $veiculo = DB::table('veiculo')
                    ->select('placa')
                    ->where('motorista_id', '=', $motorista_id)
                    ->first();

                if (empty($veiculo)) {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'B201';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } else {

                    $veiculo_placa = $veiculo->placa;

                    $coleta = DB::table('coleta')
                        ->select('status', 'id', 'coleta_fixa', 'solic_origem_id')
                        ->where('id', '=', $coleta_id)
                        ->first();

                    if (empty($coleta)) {
                        $continuar = false;
                        $retorno['cod_retorno'] = 'E200';
                        $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                        $retorno['msg_retorno'] = $msg_erro;
                    }
                }
            }
        }

        if ($continuar) {

            // É uma COMANDA de CONTRATO?
            if (($coleta->coleta_fixa == 'C') && (rgDifZeroNull($coleta->solic_origem_id))) {

                // Para comandas... verificamos se existem outras solicitações em atendimento...
                // DESCONSIDERANDO a solicitação ATUAL e a solicitação ORIGEM (que estará
                // em ATENDIMENTO ao mesmo tempo que as comandas criadas a partir dela).

                $outras_solic = DB::table('coleta')
                    ->select('id')
                    ->where(function ($query) use ($veiculo_placa) {
                        $query->where(function ($query1) use ($veiculo_placa) {
                            $query1->where('status', '=', 'C4')
                                ->where('placa_coleta', '=', $veiculo_placa);
                        })->orWhere(function ($query2) use ($veiculo_placa) {
                            $query2->where('status', '=', 'E4')
                                ->where('placa_entrega', '=', $veiculo_placa);
                        });
                    })
                    ->where('id', '<>', $coleta_id)
                    ->where('id', '<>', $coleta->solic_origem_id)
                    ->first();

                if (empty($outras_solic) == false) {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E233';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $msg_erro = str_replace('$coleta_id', $outras_solic->id, $msg_erro);
                    $retorno['msg_retorno'] = $msg_erro;
                }
            } else {

                // Para qualquer outro tipo de solicitação... verificamos se existem outras 
                // solicitações em andamento... DESCONSIDERANDO a solicitação atual.

                $outras_solic = DB::table('coleta')
                    ->select('id', 'numero')
                    ->where(function ($query) use ($veiculo_placa) {
                        $query->where(function ($query1) use ($veiculo_placa) {
                            $query1->where('status', '=', 'C4')
                                ->where('placa_coleta', '=', $veiculo_placa);
                        })->orWhere(function ($query2) use ($veiculo_placa) {
                            $query2->where('status', '=', 'E4')
                                ->where('placa_entrega', '=', $veiculo_placa);
                        });
                    })
                    ->where('id', '<>', $coleta_id)
                    ->first();

                if (empty($outras_solic) == false) {

                    if (rgDifZeroNull($outras_solic->numero)) {

                        $continuar = false;
                        $retorno['cod_retorno'] = 'E208';
                        $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        $msg_erro = str_replace('$numero', $outras_solic->numero, $msg_erro);
                        $retorno['msg_retorno'] = $msg_erro;
                    } else {

                        $continuar = false;
                        $retorno['cod_retorno'] = 'E208';
                        $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        $msg_erro = str_replace('$numero', $outras_solic->id, $msg_erro);
                        $retorno['msg_retorno'] = $msg_erro;
                    }
                }
            }
        }

        if ($continuar) {

            if ($coleta->status == 'C3') {
                $continuar = true;  // Ja é true, apenas para continuar o fluxo do processo
            } else {

                if ($coleta->status == 'C4') {
                    // Vamos tolerar esta situação para não travar o processo... 
                    // caso tenha acontecido algum 'sinistro' e o status já esteja 'C4'
                    $continuar = false;
                    $retorno['cod_retorno'] = 'Z103';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } else {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E213';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            }
        }

        // Atualizar Registro
        if ($continuar) {

            try {

                $timezone_app = date_default_timezone_get();

                // Tem que atualizar desta forma, usando o "Find". Tem eventos a serem
                // disparados no model. Se atualizar com o "Update" estes eventos não serão
                // executados. Isto está na documentação do Laravel.
                if ($coletaaux = Coleta::find($coleta->id)) {

                    $coletaaux['status']          = 'C4';     // Coleta Iniciada
                    $coletaaux['hr_atend_coleta'] = Carbon::now($timezone_app)->format('H:i:s');
                    $coletaaux['ass_user_id']     = auth()->user()->id;

                    $coletaaux->save();
                }

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {
                $retorno['cod_retorno'] = 'E206';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function Local_SetarDeslocaColeta($coleta_id)
    {
        $continuar  = true;
        $retorno    = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $motorista = DB::table('motorista')->where('user_id', '=', auth()->user()->id)->first();

            if (empty($motorista) == false) {
                $motorista_id = $motorista->id;
            } else {
                $continuar = false;
                $retorno['cod_retorno'] = 'B203';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$email', auth()->user()->email, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }
        }

        if ($continuar) {

            // Pegar Somente a o campo PLACA para melhor perfomance
            $veiculo = DB::table('veiculo')
                ->select('veiculo.placa')
                ->where('motorista_id', '=', $motorista_id)
                ->first();

            if (empty($veiculo)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'B201';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } else {

                $coleta = DB::table('coleta as col')
                    ->select('col.id', 'col.status')
                    ->where('id', '=', $coleta_id)
                    ->first();

                if (empty($coleta)) {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E200';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                    $retorno['msg_retorno'] = $msg_erro;
                }
            }
        }

        if ($continuar) {

            if ($coleta->status == 'C1') {
                $continuar = true;  // Ja é true, apenas para continuar o fluxo do processo
            } else {

                if ($coleta->status == 'C2') {
                    // Vamos tolerar esta situação para não travar o processo... 
                    // caso tenha acontecido algum 'sinistro' e o status já esteja 'C2'
                    $continuar = false;

                    $retorno['cod_retorno'] = 'Z103';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } else {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E205';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $msg_erro = str_replace('$status', $this->RetornarDescrStatusColeta($coleta->status), $msg_erro);
                    $retorno['msg_retorno'] = $msg_erro;
                }
            }
        }

        if ($continuar) {

            $timezone_app = date_default_timezone_get();

            try {

                DB::beginTransaction();

                // Tem que atualizar desta forma, usando o "Find". Tem eventos a serem
                // disparados no model. Se atualizar com o "Update" estes eventos não serão
                // executados. Isto está na documentação do Laravel.
                if ($coletaaux = Coleta::find($coleta->id)) {

                    $data_hora_serv = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
                    $data_serv = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_serv)->format('Y-m-d');
                    $hora_serv = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_serv)->format('H:i:s');

                    $coletaaux['status'] = 'C2';     // Coleta Deslocamento

                    $coletaaux['dt_efet_coleta']    = $data_serv;
                    $coletaaux['hr_partida_coleta'] = $hora_serv;

                    //Atualizar motorista...para garantir que é o motorista atualizado do veículo
                    //..para casos de troca de motorista(Ex: 2 turnos Marcopolo)
                    $coletaaux['motor_coleta_id'] = $motorista_id;

                    // Limpamos os campos de NÃO COLETA porque esta coleta pode ter
                    // sido devolvida 'SEM ATENDIMENTO'
                    $coletaaux['mot_nao_coleta'] = null;
                    $coletaaux['obs_nao_coleta'] = null;

                    $coletaaux['ass_user_id'] = auth()->user()->id;

                    $coletaaux->save();

                    $this->RegistrarGeoPosVeiculoColeta(
                        $coleta_id,
                        'C2',    // Coleta - Deslocamento
                        $coletaaux->placa_coleta,
                        $coletaaux->motor_coleta_id,
                        auth()->user()->id
                    );
                }

                DB::commit();

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {

                $retorno['cod_retorno'] = 'E206';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());

                DB::rollback();
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function Local_SetarChegadaColeta($coleta_id, $dur_prev_coleta)
    {

        $continuar  = true;
        $retorno    = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if (rgIgualTrimNull($dur_prev_coleta)) {
            $continuar = false;
            $retorno['cod_retorno'] = 'E244';
            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
        }

        if ($continuar) {

            $motorista = DB::table('motorista')->where('user_id', '=', auth()->user()->id)->first();

            if (empty($motorista) == false) {
                $motorista_id = $motorista->id;
            } else {

                $continuar = false;
                $retorno['cod_retorno'] = 'B203';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$email', auth()->user()->email, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }
        }

        if ($continuar) {

            $veiculo = DB::table('veiculo')
                ->select('veiculo.placa', 'veiculo.geo_lat', 'veiculo.geo_lng')
                ->where('motorista_id', '=', $motorista_id)
                ->first();

            if (empty($veiculo)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'B201';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        if ($continuar) {

            $coleta = DB::table('coleta as col')
                ->leftJoin('cliente as lc', function ($join) {
                    $join->on('lc.codigo', '=', 'col.cod_loc_coleta')
                        ->on('lc.empresa', '=', 'col.empresa');
                })
                ->select(
                    'col.id',
                    'col.id as coleta_id',
                    'col.placa_coleta',
                    'col.motor_coleta_id',
                    'col.coleta_fixa',
                    'col.solic_origem_id',
                    'col.status',
                    'col.ass_user_id',
                    'lc.codigo AS cod_local_atual',
                    'lc.nome AS local_atual',
                    'lc.geo_lat AS geo_lat_atual',
                    'lc.geo_lng AS geo_lng_atual'
                )
                ->where('col.id', '=', $coleta_id)
                ->first();

            if (empty($coleta)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E200';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            } else {

                // A solicitação É uma COMANDA de CONTRATO?
                if (($coleta->coleta_fixa == 'C') && (rgDifZeroNull($coleta->solic_origem_id))) {
                    $continuar = true;  // Já é true - apenas para facilitar o fluxo de acordo com o Mapa
                } else {

                    $placa = $veiculo->placa;

                    $geo_lat_atual = 0;
                    $geo_lng_atual = 0;

                    if (rgDifZeroNull($coleta->geo_lat_atual)) {
                        $geo_lat_atual = $coleta->geo_lat_atual;
                    }
                    if (rgDifZeroNull($coleta->geo_lng_atual)) {
                        $geo_lng_atual = $coleta->geo_lng_atual;
                    }

                    // Verificamos se existem outras solicitações onde o usuário setou a CHEGADA... para um 
                    // local diferente da solicitação atual. Na prática... um VEÍCULO não pode estar em mais 
                    // de um lugar ao mesmo tempo (seria bilocação? Autobots, vamos rodar!). 
                    //
                    // Aqui todos os registros terão um 'cod_local_coleta'
                    //
                    $outras_solic = DB::table('coleta as col')
                        ->select(
                            'col.numero',
                            'col.status',
                            'lc.nome AS local_coleta',
                            'lc.geo_lat AS geo_lat_coleta',
                            'lc.geo_lng AS geo_lng_coleta',
                            'le.nome AS local_entrega',
                            'le.geo_lat AS geo_lat_entrega',
                            'le.geo_lng AS geo_lng_entrega'
                        )
                        ->leftjoin('cliente as lc', function ($join) {
                            $join->on('lc.codigo', '=', 'col.cod_loc_coleta')
                                ->on('lc.empresa', '=', 'col.empresa');
                        })
                        ->leftjoin('cliente as le', function ($join) {
                            $join->on('le.codigo', '=', 'col.cod_loc_entrega')
                                ->on('le.empresa', '=', 'col.empresa');
                        })

                        // 'C3' e 'E3' - Chegada   |   'C4' e 'E4' - Atendimento iniciado
                        //
                        ->where(function ($query) use ($placa, $geo_lat_atual, $geo_lng_atual) {
                            $query->where(function ($query1) use ($placa, $geo_lat_atual, $geo_lng_atual) {
                                $query1->whereIn('col.status', ['C3', 'C4'])
                                    ->where('col.placa_coleta', '=', $placa)
                                    ->whereRaw(DB::raw('(IFNULL(lc.geo_lat, 0) <> ' . $geo_lat_atual . ' or IFNULL(lc.geo_lng, 0) <> ' . $geo_lng_atual . ')'));
                            })->orWhere(function ($query2) use ($placa, $geo_lat_atual, $geo_lng_atual) {
                                $query2->whereIn('col.status', ['E3', 'E4'])
                                    ->where('col.placa_entrega', '=', $placa)
                                    ->whereRaw(DB::raw('(IFNULL(le.geo_lat, 0) <> ' . $geo_lat_atual . ' or IFNULL(le.geo_lng, 0) <> ' . $geo_lng_atual . ')'));
                            });
                        })

                        // Desconsideramos a solicitação atual
                        ->where('col.id', '<>', $coleta->coleta_id)
                        ->first();

                    if (empty($outras_solic) == false) {

                        // Verificamos a etapa da OUTRA solicitação encontrada
                        if (substr($outras_solic->status, 0, 1) == 'C') {
                            $continuar = false;
                            $retorno['cod_retorno'] = 'E259';
                            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                            $msg_erro = str_replace('$outro_local', $outras_solic->local_coleta, $msg_erro);
                            $msg_erro = str_replace('$local_atual', $coleta->local_atual, $msg_erro);
                            $retorno['msg_retorno'] = $msg_erro;
                        } else {
                            $continuar = false;
                            $retorno['cod_retorno'] = 'E259';
                            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                            $msg_erro = str_replace('$outro_local', $outras_solic->local_entrega, $msg_erro);
                            $msg_erro = str_replace('$local_atual', $coleta->local_atual, $msg_erro);
                            $retorno['msg_retorno'] = $msg_erro;
                        }
                    }
                }
            }
        }

        if ($continuar) {

            if ($coleta->status == 'C2') {
                $continuar = true; //Já é true, apenas para clareza no fluxo
            } else {

                if ($coleta->status == 'C3') {
                    // Vamos tolerar esta situação para não travar o processo... 
                    // caso tenha acontecido algum 'sinistro' e o status já esteja 'C2'
                    $continuar = false;
                    $retorno['cod_retorno'] = 'Z103';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } else {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E212';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            }
        }

        if ($continuar) {

            // Não exigiremos duração prevista de atendimento para coletas fixas do tipo CONTRATO
            if (($coleta->coleta_fixa == 'C') && (rgIgualZeroNull($coleta->solic_origem_id))) {
                //Setamos ZERO para a duração para evitar erros de cálculo
                $dur_prev_coleta = '00:00:00';
                $dur_prev_coleta = Carbon::createFromFormat('H:i:s', $dur_prev_coleta)->format('H:i:s');
            } else {
                if (strtotime($dur_prev_coleta) == false) {   // Se for uma hora inválida "StrToTime" retorna false
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E244';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            }
        }

        if ($continuar) {

            $timezone_app = date_default_timezone_get();

            try {

                DB::beginTransaction();

                // Tem que atualizar desta forma, usando o "Find". Tem eventos a serem
                // disparados no model. Se atualizar com o "Update" estes eventos não serão
                // executados. Isto está na documentação do Laravel.
                if ($coletaaux = Coleta::find($coleta->coleta_id)) {

                    $data_hora_serv = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
                    $hora_serv = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_serv)->format('H:i:s');

                    $coletaaux['status'] = 'C3';      // Coleta - chegada                    

                    $coletaaux['hr_cheg_coleta']  = $hora_serv;
                    $coletaaux['dur_prev_coleta'] = $dur_prev_coleta;

                    $coletaaux['ass_user_id']     = auth()->user()->id;

                    $coletaaux->save();

                    // Atualizamos a duração de atendimento padrão no veículo
                    $Veiculo = Veiculo::where('placa', '=', $coleta->placa_coleta)
                        ->update([
                            'dur_atend_atual' => $dur_prev_coleta,
                            'ass_user_id'     => auth()->user()->id
                        ]);

                    $this->RegistrarGeoPosVeiculoColeta(
                        $coleta_id,
                        'C3',     // Coleta Chegada
                        $coleta->placa_coleta,
                        $coleta->motor_coleta_id,
                        auth()->user()->id
                    );
                }

                DB::commit();

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {

                $retorno['cod_retorno'] = 'E206';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());

                DB::rollback();
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function Local_AtualizarNotaFiscalColeta($coleta_nf_id, $coleta_id, $cod_barras, $valor, $volumes)
    {

        $continuar  = true;
        $retorno    = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        // Validar Parâmetros 
        // O campo 'volumes' pode aceitar o valor '0'
        if ((rgIgualZeroNull($valor)) || ($volumes == '') || ($volumes == null)) {
            $continuar = false;
            $retorno['cod_retorno'] = 'E222';
            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
        }

        if ($continuar) {

            $coleta_nf = DB::table('coleta_nf')
                ->select('id')
                ->where('id', '=', $coleta_nf_id)
                ->where('coleta_id', '=', $coleta_id)
                ->where('cod_barras', '=', $cod_barras)
                ->first();

            if (empty($coleta_nf)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E225';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } else {

                try {

                    $coleta_nfaux = ColetaNf::where('id', '=', $coleta_nf_id)
                        ->update([
                            'valor'       => $valor,
                            'volumes'     => $volumes,
                            'ass_user_id' => auth()->user()->id
                        ]);

                    $retorno['cod_retorno'] = 'Z100';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } catch (\Exception $e) {

                    $retorno['cod_retorno'] = 'E224';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
                }
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function Local_ExcluirNotaFiscalColeta($coleta_nf_id, $coleta_id, $cod_barras)
    {

        $continuar  = true;
        $retorno    = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $coleta_nf = DB::table('coleta_nf')
                ->select('id')
                ->where('id', '=', $coleta_nf_id)
                ->where('coleta_id', '=', $coleta_id)
                ->where('cod_barras', '=', $cod_barras)
                ->first();

            if (empty($coleta_nf)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E225';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } else {

                try {

                    if ($coleta_nf_atlz = ColetaNf::find($coleta_nf_id)) {
                        // Atualizamos a assinatura do usuário antes de deletar para ficar correto no log
                        $coleta_nf_atlz['ass_user_id'] = Auth()->user()->id;
                        $coleta_nf_atlz->save();
                        // Depois deletamos efetivamente o registro
                        $coleta_nf_atlz->delete();
                    }

                    $retorno['cod_retorno'] = 'Z100';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } catch (\Exception $e) {

                    $retorno['cod_retorno'] = 'E226';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
                }
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }

    public function Local_IncluirNotaFiscalColeta($coleta_id, $cod_barras, $serie, $numero, $valor, $volumes, $dig_cnpj, $observ, $origem_reg, $img_base64)
    {
        $continuar = true;
        $retorno   = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        // Ajustar parâmetros
        // Utilizamos esta API no aplicativo do motorista e também na interface web
        //
        // O app do motorista não envia os parâmetros '$observ' e '$origem_reg' por isso 
        // setamos valores default
        $img_recibo = null;

        if (rgIgualTrimNull($observ)) {
            $observ = null;
        }

        if (rgIgualTrimNull($origem_reg)) {
            // Assumimos o default => "A2" - Aplicativo do motorista
            $origem_reg = 'A2';
        }

        // Se for uma inclusão pela plataforma Maser, precisamos tratar o tipo de campo por causa da máscara do input.
        if ($origem_reg == 'A4') {

            // Estamos vendo se existe a virgula como separador de decimal.
            if (strpos($valor, ',') > 0) {
                $valor = rgStringToFloat($valor);
            }
        }

        // Validar Parâmetros 
        // O campo 'volumes' pode aceitar o valor '0'
        if ((($cod_barras == '') || ($cod_barras == null)) || ($volumes == '') || ($volumes == null) || (rgIgualZeroNull($valor))) {
            $continuar = false;
            $retorno['cod_retorno'] = 'E222';
            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
        }

        if ($continuar) {

            // Chamar função que válida a chave de acesso
            if (testaChaveNFe($cod_barras) == false) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E258';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        if ($continuar) {

            //Verificar se a NF já foi incluída na coleta
            $coleta_nf = DB::table('coleta_nf')
                ->select('coleta_nf.id')
                ->where('coleta_nf.coleta_id', '=', $coleta_id)
                ->where('coleta_nf.cod_barras', '=', $cod_barras)
                ->first();

            if (!empty($coleta_nf)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E223';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        if ($continuar) {

            $coleta = DB::table('coleta')
                ->select('coleta.coleta_fixa', 'coleta.solic_origem_id', 'le.cpf_cnpj', 'le.nome', 'le.local_distrib')
                ->leftjoin('cliente as le', function ($join) {
                    $join->on('le.codigo', '=', 'coleta.cod_loc_entrega')
                        ->on('le.empresa', '=', 'coleta.empresa');
                })
                ->where('coleta.id', '=', $coleta_id)
                ->first();

            if (empty($coleta)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E200';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        if ($continuar) {

            // NÃO validaremos os dígitos do CNPJ para comandas de CONTRATOS => 
            // (coleta_fixa == "C"  E  solic_origem_id <> 0)   
            // OU SE o local de entrega for um LOCAL DE DISTRIBUIÇÃO => neste caso... 
            // serão incluidas notas fiscais para vários destinatários... e diferentes do local de entrega.

            if ((($coleta->coleta_fixa == 'C') && (rgDifZeroNull($coleta->solic_origem_id))) || ($coleta->local_distrib == 'S')) {
                $continuar = true;   // Já é true - apenas para facilitar o fluxo e ficar de acordo com o Mapa
            } else {
                //substr($coleta->cpf_cnpj, -2) => Irá retornar os 2 últimos caracteres.
                $dig_entrega = substr(trim($coleta->cpf_cnpj), -2);

                //Queremos uma comparação de string, por isso usar !==
                if ($dig_entrega !== trim($dig_cnpj)) {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E248';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $msg_erro = str_replace('$nome', $coleta->nome, $msg_erro);
                    $retorno['msg_retorno'] = $msg_erro;
                }
            }
        }

        if ($continuar) {

            // Se for uma inclusão pela plataforma Maser, vamos exigir a foto do recibo.
            if ($origem_reg == 'A4') {

                if (rgIgualTrimNull($img_base64)) {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E242';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } else {

                    try {

                        $timezone_app = date_default_timezone_get();
                        $data_hora_atual = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

                        $data_str = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_atual)->format('Ymd');
                        $hora_str = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_atual)->format('His');

                        $coleta_id_str = str_pad($coleta_id, 8, '0', STR_PAD_LEFT);

                        //Monta o nome do arquivo. Ex: 'recibo-coleta-nf-00000001-202000401-143345.jpg'
                        //e já concatena o nome da subpasta de imagens de coletas => 'coletas/'
                        $img_recibo = 'coletas/' . 'recibo-coleta-nf-' .
                            $coleta_id_str . '-' . $data_str . '-' . $hora_str . '.jpg';

                        //Monta o nome do arquivo de destino (caminho completo)         
                        $image_path_file = rgRetornarPastaRaizImagens() . '/' . $img_recibo;
                        Storage::put($image_path_file, base64_decode($img_base64));
                    } catch (\Exception $e) {
                        $img_recibo = null;
                    }
                }
            }
        }

        if ($continuar) {

            try {

                $new_coleta_nf = new ColetaNf();

                $new_coleta_nf['coleta_id']   = $coleta_id;
                $new_coleta_nf['cod_barras']  = $cod_barras;
                $new_coleta_nf['serie']       = $serie;
                $new_coleta_nf['numero']      = $numero;
                $new_coleta_nf['valor']       = $valor;
                $new_coleta_nf['volumes']     = $volumes;
                $new_coleta_nf['dig_cnpj']    = $dig_cnpj;
                $new_coleta_nf['img_recibo']  = $img_recibo;
                $new_coleta_nf['observ']      = $observ;
                $new_coleta_nf['origem_reg']  = $origem_reg;
                $new_coleta_nf['ass_user_id'] = auth()->user()->id;

                $new_coleta_nf->save();

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {
                $retorno['cod_retorno'] = 'E224';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }

    public function Local_GetNotasFiscaisColeta($coleta_id)
    {

        $continuar  = true;
        $retorno    = array();
        $dados      = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $coleta = DB::table('coleta')
                ->select('id')
                ->where('id', '=', $coleta_id)
                ->first();

            if (empty($coleta)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E200';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            } else {

                $notas_coleta = DB::table('coleta_nf')
                    ->select('id', 'coleta_id', 'cod_barras', 'serie', 'numero', 'valor', 'volumes', 'dig_cnpj', 'img_recibo', 'mot_nao_entrega')
                    ->where('coleta_id', '=', $coleta_id)
                    ->get();

                // A leitura é com Get. No teste tem que usar "count". O "empty" não funciona
                if (count($notas_coleta) == 0) {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'Z101';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } else {

                    $ind = 0;

                    foreach ($notas_coleta as $regnf_col) {

                        $dados[$ind]['coleta_nf_id']    = $regnf_col->id;
                        $dados[$ind]['coleta_id']       = $coleta->id;
                        $dados[$ind]['cod_barras']      = $regnf_col->cod_barras;
                        $dados[$ind]['serie']           = $regnf_col->serie;
                        $dados[$ind]['numero']          = $regnf_col->numero;
                        $dados[$ind]['valor']           = $regnf_col->valor;
                        $dados[$ind]['volumes']         = $regnf_col->volumes;
                        $dados[$ind]['dig_cnpj']        = $regnf_col->dig_cnpj;
                        $dados[$ind]['mot_nao_entrega'] = $regnf_col->mot_nao_entrega;

                        if (rgIgualTrimNull($regnf_col->img_recibo) == false) {
                            $dados[$ind]['url_recibo'] = rgRetornarUrlImagens($regnf_col->img_recibo);
                        } else {
                            $dados[$ind]['url_recibo'] = null;
                        }

                        $ind++;
                    }

                    $retorno['cod_retorno'] = 'Z100';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];
        $resultado['dados'] =  $dados;

        return $resultado;
    }


    public function RegistrarGeoPosVeiculoColeta($coleta_id, $status, $placa, $motorista_id, $ass_user_id)
    {

        //Inicializar Variáveis
        $geo_lat = 0;
        $geo_lng = 0;

        $placa_track = null;

        $veiculo = DB::table('veiculo')
            ->select('usar_gps', 'geo_lat', 'geo_lng', 'placa', 'placa_cavalo')
            ->where('placa', '=', $placa)
            ->first();

        if (!empty($veiculo)) {
            $geo_lat = $veiculo->geo_lat;
            $geo_lng = $veiculo->geo_lng;

            // A PLACA SOLICITADA é a padrão para o rastreador 
            $placa_track = $veiculo->placa;

            if (rgDifTrimNull($veiculo->placa_cavalo) == true) {
                // Um veículo que tem "placa_cavalo" informada... é um veículo do tipo 'Carreta' então... 
                // buscamos as coordenadas pela PLACA DO CAVALO, porque a carreta não tem rastreador. 
                $placa_track = $veiculo->placa_cavalo;
            }

            // Testar Coordenadas
            if (($veiculo->usar_gps == 'V') && (rgIgualZeroNull($geo_lat) || rgIgualZeroNull($geo_lng))) {

                $retorno = $this->RetornarGeoPosVeiculoRastreador($placa_track);

                $geo_lat = $retorno['geo_lat'];
                $geo_lng = $retorno['geo_lng'];
                $ignicao = $retorno['ignicao'];

                if (rgDifZeroNull($geo_lat) && rgDifZeroNull($geo_lng)) {

                    $timezone_app = date_default_timezone_get();
                    $data_hora_atual = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

                    // Atualizamos a posição do veículo quando conseguimos obter 
                    // as coordenadas do rastreador para a PLACA SOLICITADA => $placa
                    $veiculoAux = Veiculo::where('placa', '=', $placa)
                        ->update([
                            'geo_lat'     => $geo_lat,
                            'geo_lng'     => $geo_lng,
                            'dt_geopos'   => $data_hora_atual,
                            'ignicao'     => $ignicao,
                            'ass_user_id' => $ass_user_id
                        ]);

                    if (rgIgualTrimNull($veiculo->placa_cavalo) == false) {

                        // Atualizamos a posição do veículo para a PLACA DO CAVALO => $placa_track
                        $veiculoAux = Veiculo::where('placa', '=', $placa_track)
                            ->update([
                                'geo_lat'     => $geo_lat,
                                'geo_lng'     => $geo_lng,
                                'dt_geopos'   => $data_hora_atual,
                                'ignicao'     => $ignicao,
                                'ass_user_id' => $ass_user_id
                            ]);
                    }
                }
            }

            // Inserimos o registro da posição mesmo que seja ZERO, assim saberemos os casos em que
            // ocorreram falhas... mas não travaremos o processo quando não houver esses dados.

            $new_coleta_pos = new ColetaPos();

            $new_coleta_pos['coleta_id']    = $coleta_id;
            $new_coleta_pos['status']       = $status;
            $new_coleta_pos['placa']        = $placa;
            $new_coleta_pos['motorista_id'] = $motorista_id;
            $new_coleta_pos['geo_lat']      = $geo_lat;
            $new_coleta_pos['geo_lng']      = $geo_lng;

            $new_coleta_pos->save();
        }
    }

    public function RetornarGeoPosVeiculoRastreador($placa)
    {

        $geo_lat = 0;
        $geo_lng = 0;
        $ignicao = null;

        if (!rgIgualTrimNull($placa)) {

            $app = new ApiUsoComum();
            $veiculos = $app->RetornarVeiculosRastreaveis();

            if (!empty($veiculos)) {

                foreach ($veiculos['data'] as $regveiculos) {

                    if (isset($regveiculos['rotulo'])) {

                        if (rgRegexAlfaNum($regveiculos['rotulo']) == rgRegexAlfaNum($placa)) {

                            $geo_lat = $regveiculos['latitude'];
                            $geo_lng = $regveiculos['longitude'];
                            $ignicao = $regveiculos['ignicao'] == '1' ? 'S' : 'N';

                            break;
                        }
                    }
                }
            }
        }

        $retorno['geo_lat'] = $geo_lat;
        $retorno['geo_lng'] = $geo_lng;
        $retorno['ignicao'] = $ignicao;

        return $retorno;
    }


    public function Local_SetarDeslocaEntrega($coleta_id)
    {

        $continuar  = true;
        $retorno    = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $motorista = DB::table('motorista')->where('user_id', '=', auth()->user()->id)->first();

            if (empty($motorista) == false) {
                $motorista_id = $motorista->id;
            } else {
                $continuar = false;
                $retorno['cod_retorno'] = 'B203';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$email', auth()->user()->email, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }
        }

        if ($continuar) {

            // Pegar Somente a o campo PLACA para melhor perfomance
            $veiculo = DB::table('veiculo')
                ->select('veiculo.placa')
                ->where('motorista_id', '=', $motorista_id)
                ->first();

            if (empty($veiculo)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'B201';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } else {

                $coleta = DB::table('coleta')
                    ->select(
                        'coleta.id',
                        'coleta.id as coleta_id',
                        'coleta.status',
                        'coleta.coleta_fixa',
                        'coleta.solic_origem_id',
                        'coleta.placa_entrega',
                        'coleta.motor_entrega_id'
                    )
                    ->where('coleta.id', '=', $coleta_id)
                    ->first();

                if (empty($coleta)) {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E200';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                    $retorno['msg_retorno'] = $msg_erro;
                }
            }
        }

        if ($continuar) {

            if ($coleta->status == 'E1') {
                $continuar = true;  // Ja é true, apenas para continuar o fluxo do processo
            } else {

                if ($coleta->status == 'E2') {
                    // Vamos tolerar esta situação para não travar o processo... 
                    // caso tenha acontecido algum 'sinistro' e o status já esteja 'C2'
                    $continuar = false;

                    $retorno['cod_retorno'] = 'Z103';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } else {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E207';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $msg_erro = str_replace('$status', $this->RetornarDescrStatusColeta($coleta->status), $msg_erro);
                    $retorno['msg_retorno'] = $msg_erro;
                }
            }
        }

        if ($continuar) {

            $timezone_app = date_default_timezone_get();

            try {

                DB::beginTransaction();

                // Tem que atualizar desta forma, usando o "Find". Tem eventos a serem
                // disparados no model. Se atualizar com o "Update" estes eventos não serão
                // executados. Isto está na documentação do Laravel.
                if ($coletaaux = Coleta::find($coleta->coleta_id)) {

                    $data_hora_serv = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
                    $data_serv = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_serv)->format('Y-m-d');
                    $hora_serv = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_serv)->format('H:i:s');

                    $coletaaux['status'] = 'E2'; // Entrega - Deslocamento

                    $coletaaux['dt_efet_entrega']    = $data_serv;
                    $coletaaux['hr_partida_entrega'] = $hora_serv;

                    //Atualizamos o motorista, para garantir que é o motorista do veiculo...
                    //para casos de troca de motorista(Ex: 2 turnos Marcopolo)
                    $coletaaux['motor_entrega_id'] = $motorista_id;

                    $coletaaux['ass_user_id'] = auth()->user()->id;

                    $coletaaux->save();

                    $this->RegistrarGeoPosVeiculoColeta(
                        $coleta_id,
                        'E2',    // Coleta - Deslocamento
                        $coletaaux->placa_entrega,
                        $coletaaux->motor_entrega_id,
                        auth()->user()->id
                    );
                }

                DB::commit();

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {

                $retorno['cod_retorno'] = 'E206';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());

                DB::rollback();
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function Local_SetarChegadaEntrega($coleta_id, $dur_prev_entrega)
    {

        $continuar  = true;
        $retorno    = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if (rgIgualTrimNull($dur_prev_entrega)) {
            $continuar = false;
            $retorno['cod_retorno'] = 'E245';
            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
        }

        if ($continuar) {

            $motorista = DB::table('motorista')->where('user_id', '=', auth()->user()->id)->first();

            if (empty($motorista) == false) {
                $motorista_id = $motorista->id;
            } else {

                $continuar = false;
                $retorno['cod_retorno'] = 'B203';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$email', auth()->user()->email, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }
        }

        if ($continuar) {

            $veiculo = DB::table('veiculo')
                ->select('veiculo.placa', 'veiculo.geo_lat', 'veiculo.geo_lng')
                ->where('motorista_id', '=', $motorista_id)
                ->first();

            if (empty($veiculo)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'B201';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        if ($continuar) {

            $coleta = DB::table('coleta as col')
                ->leftJoin('cliente as le', function ($join) {
                    $join->on('le.codigo', '=', 'col.cod_loc_entrega')
                        ->on('le.empresa', '=', 'col.empresa');
                })
                ->select(
                    'col.id',
                    'col.id as coleta_id',
                    'col.placa_entrega',
                    'col.motor_entrega_id',
                    'col.solic_origem_id',
                    'col.status',
                    'col.ass_user_id',
                    'col.coleta_fixa',
                    'le.codigo AS cod_local_atual',
                    'le.nome AS local_atual',
                    'le.geo_lat AS geo_lat_atual',
                    'le.geo_lng AS geo_lng_atual'
                )
                ->where('col.id', '=', $coleta_id)
                ->first();

            if (empty($coleta)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E200';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            } else {

                // A solicitação É uma COMANDA de CONTRATO?
                if (($coleta->coleta_fixa == 'C') && (rgDifZeroNull($coleta->solic_origem_id))) {
                    $continuar = true;  // Já é true - apenas para facilitar o fluxo de acordo com o Mapa
                } else {

                    $placa = $veiculo->placa;

                    $geo_lat_atual = 0;
                    $geo_lng_atual = 0;

                    if (rgDifZeroNull($coleta->geo_lat_atual)) {
                        $geo_lat_atual = $coleta->geo_lat_atual;
                    }
                    if (rgDifZeroNull($coleta->geo_lng_atual)) {
                        $geo_lng_atual = $coleta->geo_lng_atual;
                    }

                    // Verificamos se existem outras solicitações onde o usuário setou a CHEGADA... para um 
                    // local diferente da solicitação atual. Na prática... um VEÍCULO não pode estar em mais 
                    // de um lugar ao mesmo tempo (seria bilocação? Autobots, vamos rodar!). 
                    //
                    // Aqui todos os registros terão um 'cod_local_coleta'
                    //
                    $outras_solic = DB::table('coleta as col')
                        ->select(
                            'col.numero',
                            'col.status',
                            'lc.nome AS local_coleta',
                            'lc.geo_lat AS geo_lat_coleta',
                            'lc.geo_lng AS geo_lng_coleta',
                            'le.nome AS local_entrega',
                            'le.geo_lat AS geo_lat_entrega',
                            'le.geo_lng AS geo_lng_entrega'
                        )
                        ->leftjoin('cliente as lc', function ($join) {
                            $join->on('lc.codigo', '=', 'col.cod_loc_coleta')
                                ->on('lc.empresa', '=', 'col.empresa');
                        })
                        ->leftjoin('cliente as le', function ($join) {
                            $join->on('le.codigo', '=', 'col.cod_loc_entrega')
                                ->on('le.empresa', '=', 'col.empresa');
                        })

                        // 'C3' e 'E3' - Chegada   |   'C4' e 'E4' - Atendimento iniciado
                        //
                        ->where(function ($query) use ($placa, $geo_lat_atual, $geo_lng_atual) {
                            $query->where(function ($query1) use ($placa, $geo_lat_atual, $geo_lng_atual) {
                                $query1->whereIn('col.status', ['C3', 'C4'])
                                    ->where('col.placa_coleta', '=', $placa)
                                    ->whereRaw(DB::raw('(IFNULL(lc.geo_lat, 0) <> ' . $geo_lat_atual . ' or IFNULL(lc.geo_lng, 0) <> ' . $geo_lng_atual . ')'));
                            })->orWhere(function ($query2) use ($placa, $geo_lat_atual, $geo_lng_atual) {
                                $query2->whereIn('col.status', ['E3', 'E4'])
                                    ->where('col.placa_entrega', '=', $placa)
                                    ->whereRaw(DB::raw('(IFNULL(le.geo_lat, 0) <> ' . $geo_lat_atual . ' or IFNULL(le.geo_lng, 0) <> ' . $geo_lng_atual . ')'));
                            });
                        })

                        // Desconsideramos a solicitação atual
                        ->where('col.id', '<>',  $coleta->coleta_id)
                        ->first();

                    if (empty($outras_solic) == false) {

                        // Verificamos a etapa da OUTRA solicitação encontrada
                        if (substr($outras_solic->status, 0, 1) == 'C') {
                            $continuar = false;
                            $retorno['cod_retorno'] = 'E259';
                            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                            $msg_erro = str_replace('$outro_local', $outras_solic->local_coleta, $msg_erro);
                            $msg_erro = str_replace('$local_atual', $coleta->local_atual, $msg_erro);
                            $retorno['msg_retorno'] = $msg_erro;
                        } else {
                            $continuar = false;
                            $retorno['cod_retorno'] = 'E259';
                            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                            $msg_erro = str_replace('$outro_local', $outras_solic->local_entrega, $msg_erro);
                            $msg_erro = str_replace('$local_atual', $coleta->local_atual, $msg_erro);
                            $retorno['msg_retorno'] = $msg_erro;
                        }
                    }
                }
            }
        }

        if ($continuar) {

            if ($coleta->status == 'E2') {
                $continuar = true; //Já é true, apenas para clareza no fluxo
            } else {

                if ($coleta->status == 'E3') {
                    // Vamos tolerar esta situação para não travar o processo... 
                    // caso tenha acontecido algum 'sinistro' e o status já esteja 'C2'
                    $retorno['cod_retorno'] = 'Z103';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } else {
                    $retorno['cod_retorno'] = 'E218';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }

                $continuar = false;
            }
        }

        if ($continuar) {

            $timezone_app = date_default_timezone_get();

            try {

                DB::beginTransaction();

                // Tem que atualizar desta forma, usando o "Find". Tem eventos a serem
                // disparados no model. Se atualizar com o "Update" estes eventos não serão
                // executados. Isto está na documentação do Laravel.
                if ($coletaaux = Coleta::find($coleta->coleta_id)) {

                    $data_hora_serv = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
                    $hora_serv = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_serv)->format('H:i:s');

                    $coletaaux['status'] = 'E3';  // Entrega - Chegada

                    $coletaaux['hr_cheg_entrega']  = $hora_serv;
                    $coletaaux['dur_prev_entrega'] = $dur_prev_entrega;

                    $coletaaux['ass_user_id'] = auth()->user()->id;

                    $coletaaux->save();

                    // Atualizamos a duração de atendimento padrão no veículo
                    $Veiculo = Veiculo::where('placa', '=', $coleta->placa_entrega)
                        ->update([
                            'dur_atend_atual' => $dur_prev_entrega,
                            'ass_user_id'     => auth()->user()->id
                        ]);

                    $this->RegistrarGeoPosVeiculoColeta(
                        $coleta_id,
                        'E3',     // Entrega Chegada
                        $coletaaux->placa_entrega,
                        $coletaaux->motor_entrega_id,
                        auth()->user()->id
                    );
                }

                DB::commit();

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {

                $retorno['cod_retorno'] = 'E206';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());

                DB::rollback();
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function Local_SetarInicioAtendEntrega($coleta_id)
    {

        $continuar  = true;
        $retorno    = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $motorista = DB::table('motorista')->where('user_id', '=', auth()->user()->id)->first();

            if (empty($motorista) == false) {
                $motorista_id = $motorista->id;
            } else {

                $continuar = false;
                $retorno['cod_retorno'] = 'B203';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$email', auth()->user()->email, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }

            if ($continuar) {

                $veiculo = DB::table('veiculo')
                    ->select('placa')
                    ->where('motorista_id', '=', $motorista_id)
                    ->first();

                if (empty($veiculo)) {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'B201';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } else {

                    $veiculo_placa = $veiculo->placa;

                    $coleta = DB::table('coleta')
                        ->select('status', 'id', 'coleta_fixa', 'solic_origem_id')
                        ->where('id', '=', $coleta_id)
                        ->first();

                    if (empty($coleta)) {
                        $continuar = false;
                        $retorno['cod_retorno'] = 'E200';
                        $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                        $retorno['msg_retorno'] = $msg_erro;
                    }
                }
            }
        }

        if ($continuar) {

            // É uma COMANDA de CONTRATO?
            if (($coleta->coleta_fixa == 'C') && (rgDifZeroNull($coleta->solic_origem_id))) {

                // Para comandas... verificamos se existem outras solicitações em atendimento...
                // DESCONSIDERANDO a solicitação ATUAL e a solicitação ORIGEM (que estará
                // em ATENDIMENTO ao mesmo tempo que as comandas criadas a partir dela).

                $outras_solic = DB::table('coleta')
                    ->select('id')
                    ->where(function ($query) use ($veiculo_placa) {
                        $query->where(function ($query1) use ($veiculo_placa) {
                            $query1->where('status', '=', 'C4')
                                ->where('placa_coleta', '=', $veiculo_placa);
                        })->orWhere(function ($query2) use ($veiculo_placa) {
                            $query2->where('status', '=', 'E4')
                                ->where('placa_entrega', '=', $veiculo_placa);
                        });
                    })
                    ->where('id', '<>', $coleta_id)
                    ->where('id', '<>', $coleta->solic_origem_id)
                    ->first();

                if (empty($outras_solic) == false) {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E233';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $msg_erro = str_replace('$coleta_id', $outras_solic->id, $msg_erro);
                    $retorno['msg_retorno'] = $msg_erro;
                }
            } else {

                // Para qualquer outro tipo de solicitação... verificamos se existem outras 
                // solicitações em andamento... DESCONSIDERANDO a solicitação atual.

                $outras_solic = DB::table('coleta')
                    ->select('id', 'numero')
                    ->where(function ($query) use ($veiculo_placa) {
                        $query->where(function ($query1) use ($veiculo_placa) {
                            $query1->where('status', '=', 'C4')
                                ->where('placa_coleta', '=', $veiculo_placa);
                        })->orWhere(function ($query2) use ($veiculo_placa) {
                            $query2->where('status', '=', 'E4')
                                ->where('placa_entrega', '=', $veiculo_placa);
                        });
                    })
                    ->where('id', '<>', $coleta_id)
                    ->first();

                if (empty($outras_solic) == false) {

                    if (rgDifZeroNull($outras_solic->numero)) {

                        $continuar = false;
                        $retorno['cod_retorno'] = 'E208';
                        $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        $msg_erro = str_replace('$numero', $outras_solic->numero, $msg_erro);
                        $retorno['msg_retorno'] = $msg_erro;
                    } else {

                        $continuar = false;
                        $retorno['cod_retorno'] = 'E208';
                        $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        $msg_erro = str_replace('$numero', $outras_solic->id, $msg_erro);
                        $retorno['msg_retorno'] = $msg_erro;
                    }
                }
            }
        }

        if ($continuar) {

            if ($coleta->status == 'E3') {
                $continuar = true;  // Ja é true, apenas para continuar o fluxo do processo
            } else {

                if ($coleta->status == 'E4') {
                    // Vamos tolerar esta situação para não travar o processo... 
                    // caso tenha acontecido algum 'sinistro' e o status já esteja 'C4'
                    $retorno['cod_retorno'] = 'Z103';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } else {
                    $retorno['cod_retorno'] = 'E219';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }

                $continuar = false;
            }
        }

        // Atualizar Registro
        if ($continuar) {

            try {

                $timezone_app = date_default_timezone_get();

                // Tem que atualizar desta forma, usando o "Find". Tem eventos a serem
                // disparados no model. Se atualizar com o "Update" estes eventos não serão
                // executados. Isto está na documentação do Laravel.
                if ($coletaaux = Coleta::find($coleta_id)) {

                    $coletaaux['status']  = 'E4';     // Entrega - Iniciada
                    $coletaaux['hr_atend_entrega'] = Carbon::now($timezone_app)->format('H:i:s');
                    $coletaaux['ass_user_id']      = auth()->user()->id;

                    $coletaaux->save();
                }

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {
                $retorno['cod_retorno'] = 'E206';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }

    public function Local_FinalizarColeta(
        $coleta_id,
        $realizada,
        $obs_nao_coleta,
        $nfs_comerciais,
        $img_carga_base64,
        $ocup_veiculo,
        $cod_tipo_veiculo_nec,
        $img_rom_base64
    ) {

        $continuar  = true;
        $retorno    = array();
        $img_carga  = null;

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $timezone_app = date_default_timezone_get();
            $data_hora_atual = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

            if ($realizada == 'S') {
                // Coleta realizada
                $new_status = 'CR';
                $mot_nao_coleta = null;
                $obs_nao_coleta = null;
            } else {

                // Coleta NÃO realizada
                $new_status = 'CN';

                // Não precisamos deste valor
                $nfs_comerciais = null;

                // Não precisamos do tipo de veículo necessário
                $cod_tipo_veiculo_nec = null;

                // Motivo: "01" - Cancelada: com deslocamento
                $mot_nao_coleta = '01';

                // Coleta NÃO realizada: a imagem da carga e o % de ocupação não são utilizados.
                // Atribuindo null para '$img_base64' neste ponto... evitamos que esta rotina 
                // tente gravar um arquivo .JPG com o conteúdo da variável.
                $img_carga_base64 = null;
                $ocup_veiculo = 0;

                if ((($mot_nao_coleta <> '01') && ($mot_nao_coleta <> '02')) || (rgIgualTrimNull($obs_nao_coleta))) {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E215';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } else {

                    // Por segurança: não permitimos notas fiscais para coletas NÃO REALIZADAS. 
                    $coleta_nf = DB::table('coleta_nf')
                        ->select('id')
                        ->where('coleta_id', '=', $coleta_id)
                        ->first();

                    if (empty($coleta_nf) == false) {

                        $continuar = false;
                        $retorno['cod_retorno'] = 'E250';
                        $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    }
                }
            }
        }

        if ($continuar) {

            $coleta = DB::table('coleta')
                ->select(
                    'id',
                    'coleta_fixa',
                    'id as coleta_id',
                    'status',
                    'motor_coleta_id',
                    'placa_coleta',
                    'solic_origem_id',
                    'aceitar_foto_rom',
                    'receber_nf_frete',
                    'reentrega'
                )
                ->where('id', '=', $coleta_id)
                ->first();

            if (empty($coleta)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E200';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            } else {

                if ($coleta->status == 'C4') {
                    $continuar = true;    // Já é true - só para clareza e facilitar o fluxo   
                } else {

                    $continuar = false;

                    if (($coleta->status == 'CR') || ($coleta->status == 'CN')) {
                        // Vamos tolerar esta situação para não travar o processo... 
                        // caso tenha acontecido algum 'sinistro' e o status já esteja 'CR' ou 'CN'
                        $retorno['cod_retorno'] = 'Z103';
                        $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    } else {
                        $retorno['cod_retorno'] = 'E214';
                        $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    }
                }
            }
        }

        if ($continuar) {

            // Coleta foi realizada?
            if ($new_status == 'CR') {

                // É uma COMANDA de CONTRATO?
                if (($coleta->coleta_fixa == 'C') && (rgDifZeroNull($coleta->solic_origem_id))) {

                    // Não precisamos do tipo de veículo necessário
                    $cod_tipo_veiculo_nec = null;
                } else {

                    // Coleta Realizada E NÃO é comanda: Exigimos o tipo de veículo necessário
                    $tipo_veiculo = DB::table('tipo_veiculo')
                        ->select('codigo')
                        ->where('codigo', '=', $cod_tipo_veiculo_nec)
                        ->first();

                    if (empty($tipo_veiculo)) {
                        $continuar = false;
                        $retorno['cod_retorno'] = 'E251';
                        $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    }
                }

                // Exigimos informações de ocupação para todos os tipos de solicitação                    
                if ($continuar) {

                    if ((rgIgualZeroNull($ocup_veiculo)) || (rgIgualTrimNull($img_carga_base64))) {
                        $continuar = false;
                        $retorno['cod_retorno'] = 'E240';
                        $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    }
                }

                //Notas comerciais
                if ($continuar) {

                    // Quando tem que receber a NF de frete... exigimos que o usuário informe se tem ou não NFs com fins comerciais
                    if (($coleta->receber_nf_frete == 'S') && (rgIgualTrimNull($nfs_comerciais))) {
                        $continuar = false;
                        $retorno['cod_retorno'] = 'E270';
                        $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    }
                }

                if ($continuar) {

                    // Verificamos se tem notas fiscais:
                    //
                    // SE for solicitação MULTI-DESTINOS ORIGEM... não permitimos a
                    // finalização da coleta SEM NOTAS FISCAIS. SE for outro tipo de 
                    // solicitação... exigimos uma foto do romaneio como documento 
                    // de carga SE estiver configurado para aceitar foto do romaneio.
                    $coleta_nf = DB::table('coleta_nf')
                        ->select('id')
                        ->where('coleta_id', '=', $coleta_id)
                        ->first();

                    if (empty($coleta_nf) == false) {   // achou
                        $img_rom_base64 = null;
                    } else {

                        // Se for uma solicitação MULTI-DESTINOS origem... exigimos notas fiscais.
                        if (($coleta->coleta_fixa == 'M') && (rgIgualZeroNull($coleta->solic_origem_id))) {
                            $continuar = false;
                            $retorno['cod_retorno'] = 'E269';
                            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        } else {

                            if (($coleta->aceitar_foto_rom == 'S') && (rgIgualTrimNull($img_rom_base64))) {
                                $continuar = false;
                                $retorno['cod_retorno'] = 'E267';
                                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                            }
                        }
                    }
                }
            }

            // Não permitimos o cancelamento da COLETA para solicitações de REENTREGA.
            // O motorista deve cancelar os passsos: Atendimento -> Chegada -> Deslocamento e solicitar 
            // para o "CONTROLE" a definição sobre oque fazer com a solicitação.
            if (($new_status == 'CN') && (rgDifTrimNull($coleta->reentrega))) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E292';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        if ($continuar) {

            $data_str = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_atual)->format('Ymd');
            $hora_str = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_atual)->format('His');
            $coleta_id_str = str_pad($coleta->id, 8, '0', STR_PAD_LEFT);

            // Imagem CARGA base64
            try {

                if (rgIgualTrimNull($img_carga_base64) == true) {
                    $img_carga = null;
                } else {

                    // Monta o nome do arquivo. Ex: 'carga-coleta-00000001-20200401-143345.jpg' 
                    // e já concatena o nome da subpasta de imagens de coletas => "coletas/".
                    $img_carga = 'coletas/' . 'carga-coleta-' .
                        $coleta_id_str . '-' . $data_str . '-' . $hora_str . '.jpg';

                    $image_path_file = rgRetornarPastaRaizImagens() . '/' . $img_carga;
                    Storage::put($image_path_file, base64_decode($img_carga_base64));
                }
            } catch (\Exception $e) {
                $img_carga = null;
            }

            // Imagem ROMANEIO base64
            try {

                if (rgIgualTrimNull($img_rom_base64) == true) {
                    $img_rom_coleta = null;
                } else {

                    // Monta o nome do arquivo. Ex: 'romaneio-coleta-00000001-20200401-143345.jpg' 
                    // e já concatena o nome da subpasta de imagens de coletas => "coletas/".
                    $img_rom_coleta = 'coletas/' . 'romaneio-coleta-' .
                        $coleta_id_str . '-' . $data_str . '-' . $hora_str . '.jpg';

                    $image_path_file = rgRetornarPastaRaizImagens() . '/' . $img_rom_coleta;
                    Storage::put($image_path_file, base64_decode($img_rom_base64));
                }
            } catch (\Exception $e) {
                $img_rom_coleta = null;
            }
        }

        if ($continuar) {

            $hora_serv_atual = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_atual)->format('H:i:s');

            try {

                DB::beginTransaction();

                // Tem que atualizar desta forma usando o "Find". Tem eventos a serem
                // disparados no model. Se atualizar com o "Update" estes eventos não serão
                // executados. Isto está na documentação do Laravel.
                if ($coletaaux = Coleta::find($coleta_id)) {

                    $coletaaux['status']        = $new_status;
                    $coletaaux['hr_sai_coleta'] = $hora_serv_atual;

                    if ($realizada != 'S') {
                        $coletaaux['mot_nao_coleta'] = $mot_nao_coleta;
                        $coletaaux['obs_nao_coleta'] = $obs_nao_coleta;
                    }

                    $coletaaux['nfs_comerciais']       = $nfs_comerciais;
                    $coletaaux['img_carga']            = $img_carga;
                    $coletaaux['ocup_veiculo']         = $ocup_veiculo;
                    $coletaaux['cod_tipo_veiculo_nec'] = $cod_tipo_veiculo_nec;
                    $coletaaux['img_rom_coleta']       = $img_rom_coleta;

                    // Limpamos o campo que sequencia os atendimentos e o campo
                    // que indica que a rota foi calculada... porque na fase de ENTREGA 
                    // terão novos valores conforme a rota que será definida.
                    $coletaaux['seq_atend'] = null;
                    $coletaaux['rota_calc'] = null;

                    $coletaaux['ass_user_id'] = auth()->user()->id;

                    $coletaaux->save();
                }

                // SE a coleta foi realizada... atualizamos a ocupação do veículo. 
                if ($new_status == 'CR') {
                    // SE coleta foi REALIZADA e conseguimos armazenar a imagem da ocupação 
                    // do veículo na COLETA... atualizaremos também no registro do VEICULO.
                    // Neste caso... passamos 'null' para o parâmetro 'img_base64'.
                    //
                    // Para 'comandas': $ocup_veiculo, $img_carga e $img_base64 não terão valor,
                    // mesmo assim atualizaremos o registro do veículo para limpar os campos.
                    $img_carga_base64 = null;

                    $api = new ApiGeral();
                    $resultado = $api->GravarOcupacaoVeiculo(
                        $coleta->placa_coleta,
                        $img_carga,
                        $ocup_veiculo,
                        $img_carga_base64
                    );

                    // Se for 'comanda'... vamos liberar a entrega automaticamente.
                    if (($coleta->coleta_fixa == 'C') && (rgDifZeroNull($coleta->solic_origem_id))) {
                        // Fizemos um procedimento de atualização específico 
                        // para o status 'E1'... para registrarmos duas vezes a 
                        // alteração do status na tabela COLETA_LOG

                        if ($coletaaux = Coleta::find($coleta_id)) {

                            $coletaaux['status']      = 'E1';   // (Entrega - Autorizada)
                            $coletaaux['ass_user_id'] = auth()->user()->id;

                            $coletaaux->save();
                        }
                    }
                }

                DB::commit();

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {
                DB::rollback();
                $retorno['cod_retorno'] = 'E206';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function Local_FinalizarEntrega($coleta_id, $realizada, $mot_nao_entrega, $obs_nao_entrega, $recebedor, $img_rom_base64)
    {
        $continuar = true;
        $retorno = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        // Para manter compatibilidade com versão anterior
        if (isset($realizada) == false) {
            $realizada = 'S';
        }

        if (isset($mot_nao_entrega) == false) {
            $mot_nao_entrega = null;
        }

        if (isset($obs_nao_entrega) == false) {
            $obs_nao_entrega = null;
        }

        //Validar parâmetros
        if ($continuar) {

            if ($realizada == 'S') {
                // Entrega realizada
                $new_status = "ER";

                $mot_nao_entrega = null;
                $obs_nao_entrega = null;

                if (rgIgualTrimNull($recebedor)) {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E221';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $retorno['msg_retorno'] = $msg_erro;
                }
            } else {

                if ($realizada == 'P') {

                    if (rgIgualTrimNull($recebedor)) {
                        $continuar = false;
                        $retorno['cod_retorno'] = 'E221';
                        $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        $retorno['msg_retorno'] = $msg_erro;
                    } else {
                        // Entrega Parcial
                        $new_status = "EP";

                        $mot_nao_entrega = null;
                        $obs_nao_entrega = null;
                    }
                } else {
                    // Entrega NÃO realizada
                    $new_status = "EN";

                    $recebedor = null;
                    $img_rom_base64 = null;

                    if (rgIgualTrimNull($mot_nao_entrega) || rgIgualTrimNull($obs_nao_entrega)) {
                        $continuar = false;
                        $retorno['cod_retorno'] = 'E274';
                        $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        $retorno['msg_retorno'] = $msg_erro;
                    }
                }
            }
        }

        if ($continuar) {

            $coleta = DB::table('coleta')
                ->select('id', 'status', 'aceitar_foto_rom', 'coleta_fixa', 'solic_origem_id')
                ->where('id', '=', $coleta_id)
                ->first();

            if (empty($coleta)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E200';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            } else {

                // Por enquanto... não permitimos entrega parcial... nem 'entrega não realizada' para comandas dos contratos
                if ((($coleta->coleta_fixa == 'C') && rgDifZeroNull($coleta->solic_origem_id)) && in_array($new_status, ['EN', 'EP'])) {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E291';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } else {

                    if ($coleta->status == 'E4') {
                        $continuar = true;  // Já é true apenas para clareza do processo
                    } else {

                        if (in_array($coleta->status, ['EN', 'EP', 'ER'])) {
                            // Vamos tolerar esta situação para não travar o processo... 
                            // caso tenha acontecido algum 'sinistro' e a solicitação esteja finalizada
                            $retorno['cod_retorno'] = 'Z103';
                            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        } else {
                            $retorno['cod_retorno'] = 'E220';
                            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        }

                        $continuar = false;
                    }
                }
            }

            if ($continuar) {

                $total_nfs = DB::table('coleta_nf')
                    ->select('id')
                    ->where('coleta_id', '=', $coleta_id)
                    ->count();

                $nfs_entregues = DB::table('coleta_nf')
                    ->select('id')
                    ->where('coleta_id', '=', $coleta_id)
                    ->where(function ($query) {
                        $query->whereNotNull('img_recibo')
                            ->orWhere('img_recibo', '!=', '');
                    })
                    ->where(function ($query) {
                        $query->whereNull('mot_nao_entrega')
                            ->orWhere('mot_nao_entrega', '=', '');
                    })
                    ->count();

                $nfs_nao_entregues = DB::table('coleta_nf')
                    ->select('id')
                    ->where('coleta_id', '=', $coleta_id)
                    ->where(function ($query) {
                        $query->whereNull('img_recibo')
                            ->orWhere('img_recibo', '=', '');
                    })
                    ->where(function ($query) {
                        $query->whereNotNull('mot_nao_entrega')
                            ->orWhere('mot_nao_entrega', '!=', '');
                    })
                    ->count();

                $nfs_sem_resposta = DB::table('coleta_nf')
                    ->select('id')
                    ->where('coleta_id', '=', $coleta_id)
                    ->where(function ($query) {
                        $query->whereNull('img_recibo')
                            ->orWhere('img_recibo', '=', '');
                    })
                    ->where(function ($query) {
                        $query->whereNull('mot_nao_entrega')
                            ->orWhere('mot_nao_entrega', '=', '');
                    })
                    ->count();

                // Tem notas fiscais?
                if ($total_nfs > 0) {

                    // Quando tem NF´s garantimos que não terá imagem do romaneio
                    $img_rom_base64 = null;

                    // Entrega REALIZADA
                    if ($new_status == 'ER') {

                        // Entrega Total: todas as notas devem ter recibo de entrega
                        if ($nfs_entregues < $total_nfs) {
                            $continuar = false;
                            $retorno['cod_retorno'] = 'E247';
                            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        }
                    } else {

                        // Entrega PARCIAL
                        if ($new_status == 'EP') {

                            // Entrega Parcial: somente algumas notas devem ter motivo de não entrega
                            if (($nfs_nao_entregues == $total_nfs) || ($nfs_nao_entregues == 0) || ($nfs_sem_resposta > 0)) {
                                $continuar = false;
                                $retorno['cod_retorno'] = 'E283';
                                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                            }
                        } else {

                            // Entrega NÃO REALIZADA: todas as notas devem ter motivo de não entrega SE o motivo definido na SOLICITAÇÃO for 
                            // '50' => Informado nas notas
                            if (($mot_nao_entrega == '50') && ($nfs_nao_entregues < $total_nfs)) {
                                $continuar = false;
                                $retorno['cod_retorno'] = 'E275';
                                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                            }
                        }
                    }
                } else {

                    // SE NÃO tem notas fiscais... exigimos uma foto do romaneio como documento de carga... SE estiver configurado para aceitar foto do romaneio. 
                    // Para ENTREGAS NÃO REALIZADAS não vamos exigir foto do romaneio.
                    if (($new_status != 'EN') && ($coleta->aceitar_foto_rom == 'S')) {

                        // Imagem do romaneio obrigatória
                        if (rgIgualTrimNull($img_rom_base64)) {
                            $continuar = false;
                            $retorno['cod_retorno'] = 'E268';
                            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        }
                    }
                }
            }

            if ($continuar) {

                $timezone_app = date_default_timezone_get();

                try {

                    $data_hora_atual = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
                    $data_str = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_atual)->format('Ymd');
                    $hora_str = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_atual)->format('His');
                    $coleta_id_str = str_pad($coleta->id, 8, '0', STR_PAD_LEFT);

                    if (rgIgualTrimNull($img_rom_base64) == true) {
                        $img_rom_entrega = null;
                    } else {

                        // Monta o nome do arquivo. Ex: 'carga-coleta-00000001-20200401-143345.jpg' 
                        // e já concatena o nome da subpasta de imagens de coletas => "coletas/".
                        $img_rom_entrega = 'coletas/' . 'romaneio-entrega-' .
                            $coleta_id_str . '-' . $data_str . '-' . $hora_str . '.jpg';

                        $image_path_file = rgRetornarPastaRaizImagens() . '/' . $img_rom_entrega;
                        Storage::put($image_path_file, base64_decode($img_rom_base64));
                    }
                } catch (\Exception $e) {
                    $img_rom_entrega = null;
                }

                // Atualiza o registro da coleta
                try {

                    DB::beginTransaction();

                    $dt_entrega = Carbon::now($timezone_app)->format('Y-m-d');
                    $hr_entrega = Carbon::now($timezone_app)->format('H:i:s');

                    if ($coletaaux = Coleta::find($coleta_id)) {

                        $coletaaux['status']          = $new_status;
                        $coletaaux['hr_sai_entrega']  = $hr_entrega;
                        $coletaaux['recebedor']       = $recebedor;
                        $coletaaux['img_rom_entrega'] = $img_rom_entrega;
                        $coletaaux['mot_nao_entrega'] = $mot_nao_entrega;
                        $coletaaux['obs_nao_entrega'] = $obs_nao_entrega;

                        $coletaaux['ass_user_id'] = auth()->user()->id;

                        $coletaaux->save();

                        // SE entrega não realizada: limpamos todos os recibos que o usuário possa ter informado. 
                        if ($coletaaux->status == 'EN') {

                            $coleta_nf = ColetaNf::where('coleta_id', '=', $coleta_id)
                                ->update([
                                    'img_recibo' => null,
                                    'ass_user_id' => auth()->user()->id
                                ]);
                        }

                        // SE entrega REALIZADA ou NÃO REALIZADA com motivo DIFERENTE de '50' => Informado nas notas... 
                        // limpamos os motivos que o usuário possa ter informado nas notas
                        if (($coletaaux->status == 'ER') || ($coletaaux->status == 'EN' && $coletaaux->mot_nao_entrega != '50')) {

                            $coleta_nf = ColetaNf::where('coleta_id', '=', $coleta_id)
                                ->update([
                                    'mot_nao_entrega' => null,
                                    'ass_user_id' => auth()->user()->id
                                ]);
                        }

                        // Atualizamos a ocupação Veículo. 
                        // No update não tem atualização de placa, então é passar a placa da coleta
                        $this->TratarOcupacaoVeiculo($coletaaux->placa_entrega);

                        // É uma Solicitação Auxiliar Multi-destinos?
                        if (($coletaaux->coleta_fixa == 'M') && (rgDifZeroNull($coletaaux->solic_origem_id))) {

                            // Se for REENTREGA ou DEVOLUÇÃO não podemos atualizar a solicitação Multidestinos ORIGEM, 
                            // PORQUE a atualização é feita quando a solicitação distribuida (que não é reentrega) é 
                            // finalizada.  
                            //
                            if (rgIgualTrimNull($coletaaux->reentrega)) {
                                $this->FinalizarSolicOrigemMultiDestinos($coletaaux->solic_origem_id, $dt_entrega, $hr_entrega);
                            }
                        }
                    }

                    DB::commit();

                    $retorno['cod_retorno'] = 'Z100';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } catch (\Exception $e) {
                    DB::rollback();
                    $retorno['cod_retorno'] = 'E206';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                    $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
                }
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function Local_IncluirComanda($solic_origem_id, $local_coleta, $local_entrega, $obs_coleta)
    {
        $continuar  = true;
        $retorno    = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        // Validar Parâmetros 
        if (rgIgualTrimNull($local_coleta) || rgIgualTrimNull($local_entrega)) {
            $continuar = false;
            $retorno['cod_retorno'] = 'E227';
            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
        }

        if ($continuar) {

            $solic_origem = DB::table('coleta')
                ->select(
                    'coleta_fixa',
                    'status',
                    'empresa',
                    'cod_cliente',
                    'cod_tipo_veiculo',
                    'placa_coleta',
                    'motor_coleta_id',
                    'aceitar_foto_rom'
                )
                ->where('id', '=', $solic_origem_id)
                ->first();

            if (empty($solic_origem)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E200';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $solic_origem_id, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            } else {

                if ($solic_origem->coleta_fixa == "C") {

                    //Coleta - Realizada: Para solicitaçõess com campo "coleta_fixa = C (contrato)"
                    //consideramos o status "C4" como "Ínicio de Expediente"
                    if ($solic_origem->status != "C4") {
                        $continuar = false;
                        $retorno['cod_retorno'] = 'E229';
                        $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        $msg_erro = str_replace('$coleta_id', $solic_origem_id, $msg_erro);
                        $msg_erro = str_replace('$status', $this->RetornarDescrStatusColeta($solic_origem->status), $msg_erro);
                        $retorno['msg_retorno'] = $msg_erro;
                    }
                } else {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E228';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $msg_erro = str_replace('$coleta_id', $solic_origem_id, $msg_erro);
                    $retorno['msg_retorno'] = $msg_erro;
                }
            }
        }

        if ($continuar) {

            try {

                $coleta = new Coleta();

                $coleta['empresa'] = $solic_origem->empresa;
                $coleta['numero']  = null;

                $timezone_app = date_default_timezone_get();

                $data_atual_servidor = Carbon::now($timezone_app)->format('Y-m-d');
                $hora_atual_servidor = Carbon::now($timezone_app)->format('H:i:s');

                $coleta['data_cad'] = $data_atual_servidor;
                $coleta['hora_cad'] = $hora_atual_servidor;

                $coleta['cod_cliente'] = $solic_origem->cod_cliente;

                $coleta['dt_prev_coleta'] = $data_atual_servidor;
                $coleta['hr_prev_coleta'] = $hora_atual_servidor;

                //Para comandas...data+hora ENTREGA podem ser iguais às da COLETA
                $coleta['dt_prev_entrega'] = $data_atual_servidor;
                $coleta['hr_prev_entrega'] = $hora_atual_servidor;

                $coleta['entrega_urgente'] = 'N';

                $coleta['cod_loc_coleta']   = null;
                $coleta['local_coleta_cmd'] = $local_coleta;

                $coleta['cod_loc_entrega']   = null;
                $coleta['local_entrega_cmd'] = $local_entrega;

                //Para comandas... não interessa o sistema de carga                
                $coleta['sis_carga']  = 'N';
                $coleta['obs_coleta'] = $obs_coleta;

                $coleta['cod_tipo_veiculo'] = $solic_origem->cod_tipo_veiculo;
                $coleta['placa_coleta']     = $solic_origem->placa_coleta;

                //Motorista da COLETA será gravado quando o status da solicitação
                //mudar para "C2"=>Coleta-Deslocamento. Fica mais seguro...nos
                //casos de troca de motorista(Ex: 2 turnos da Marcopolo)
                $coleta['motor_coleta_id']  = null;

                //Para comandas... o veículo da ENTREGA é o mesmo da COLETA
                $coleta['placa_entrega']    = $solic_origem->placa_coleta;

                //Motorista da ENTREGA será gravado quando o status da solicitação
                //mudar para "E2"=>Coleta-Deslocamento. Fica mais seguro...nos
                //casos de troca de motorista(Ex: 2 turnos da Marcopolo)
                $coleta['motor_entrega_id']  = null;

                // 'coleta_fixa' = 'C' => Contrato
                $coleta['coleta_fixa']       = 'C';
                $coleta['receber_nf_frete']  = 'N';

                $coleta['aceitar_foto_rom'] = $solic_origem->aceitar_foto_rom;

                //Para comandas.. não precisa calcular distancia e tempo
                $coleta['distancia_km']   = 0;
                $coleta['tempo_estimado'] = 0;

                //Não tem sentido setar instrução para comanda
                $coleta['instrucao'] = null;

                //Para comandas... a coleta é liberada automaticamente
                $coleta['status']    = 'C1'; //Coleta - Autorizada

                //Vinculamos esta comanda ao registro da solicitação da coleta
                $coleta['solic_origem_id'] = $solic_origem_id;
                $coleta['origem_reg']      = 'A2'; //Criado pelo aplicativo do motorista

                $coleta['ass_user_id'] = auth()->user()->id;

                $coleta->save();

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {
                $retorno['cod_retorno'] = 'E230';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }

    public function Local_AtualizarComanda($solic_origem_id, $coleta_id, $local_coleta, $local_entrega, $obs_coleta)
    {
        $continuar = true;
        $retorno   = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        // Validar Parâmetros 
        if (rgIgualTrimNull($local_coleta) || rgIgualTrimNull($local_entrega)) {
            $continuar = false;
            $retorno['cod_retorno'] = 'E227';
            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
        }

        if ($continuar) {

            $coleta = Coleta::select('id', 'solic_origem_id', 'local_coleta_cmd', 'local_entrega_cmd', 'obs_coleta')
                ->where('id', '=', $coleta_id)
                ->where('solic_origem_id', '=', $solic_origem_id)
                ->first();

            if (empty($coleta)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E200';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            } else {

                try {

                    $coletaaux = Coleta::where('id', '=', $coleta_id)
                        ->update([
                            'local_coleta_cmd'       => $local_coleta,
                            'local_entrega_cmd'     => $local_entrega,
                            'obs_coleta' => $obs_coleta,
                            'ass_user_id' => auth()->user()->id
                        ]);

                    $retorno['cod_retorno'] = 'Z100';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } catch (\Exception $e) {
                    $retorno['cod_retorno'] = 'E231';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                    $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
                }
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }

    public function Local_ExcluirComanda($solic_origem_id, $coleta_id)
    {
        $continuar  = true;
        $retorno    = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $coleta = Coleta::select('id', 'solic_origem_id')
                ->where('id', '=', $coleta_id)
                ->where('solic_origem_id', '=', $solic_origem_id)
                ->first();

            if (empty($coleta)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E200';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            } else {

                try {

                    // Atualizamos a assinatura do usuário antes de deletar para ficar correto no log
                    $coletaaux = Coleta::where('id', '=', $coleta_id)
                        ->update([
                            'ass_user_id' => auth()->user()->id
                        ]);

                    // Depois deletamos efetivamente o registro
                    $coleta->delete();

                    $retorno['cod_retorno'] = 'Z100';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } catch (\Exception $e) {

                    if ($e instanceof \PDOException) {
                        $retorno['cod_retorno'] = 'E246';
                    } else {
                        $retorno['cod_retorno'] = 'E232';
                    }

                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                    $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
                }
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }

    public function Local_IniciarExpediente($coleta_id)
    {

        $continuar   = true;
        $lercomandas = false;
        $retorno     = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $timezone_app = date_default_timezone_get();
            $data_atual_serv = Carbon::now($timezone_app)->format('Y-m-d');
            $hora_atual_serv = Carbon::now($timezone_app)->format('H:i:s');

            $coleta = DB::table('coleta')
                ->select('id', 'coleta_fixa', 'coleta_fixa_id', 'solic_origem_id', 'placa_coleta', 'status')
                ->where('id', '=', $coleta_id)
                ->first();

            if (empty($coleta)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E200';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            } else {

                // Aceita iniciar o expediente apenas para 
                // solicitações de coleta fixa do tipo 'CONTRATO'
                if (($coleta->coleta_fixa == 'C') && rgIgualZeroNull($coleta->solic_origem_id)) {
                    $continuar = true;   // Já é true - apenas para facilitar o fluxo do processo
                } else {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E236';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            }

            if ($continuar) {

                if ($coleta->status == 'C3') {

                    // Teremos 'coleta_fixa_id' somente quando a solicitação CONTRATO
                    // for gerada pelas Coletas Fixas. SE for criada manualmente pela
                    // plataforma ou Sistema de Gestão coleta_fixa_id será NULL.. neste
                    // caso NÃO temos como identificar os 2 TURNOS.
                    if (rgDifZeroNull($coleta->coleta_fixa_id)) {
                        $lercomandas = true;
                    }
                } else {

                    if ($coleta->status == 'C4') {
                        // Vamos tolerar esta situação para não travar o processo... 
                        // caso tenha acontecido algum 'sinistro' e o status já esteja 'C4'
                        $continuar = false;
                        $retorno['cod_retorno'] = 'Z103';
                        $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    } else {
                        $continuar = false;
                        $retorno['cod_retorno'] = 'E234';
                        $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    }
                }
            }
        }

        if ($continuar) {

            if ($lercomandas) {

                // Verificamos se restaram comandas em aberto do turno anterior. Vamos gravar o ID da
                // solicitação atual como 'solic_origem' dessas comandas restantes.
                $placa_coleta  = $coleta->placa_coleta;

                $comandas = DB::table('coleta as cmd')
                    ->select('cmd.id')
                    // Não pegamos solicitações com DATA DE COLETA futura
                    ->where('cmd.dt_prev_coleta', '<=', $data_atual_serv)

                    // Comandas vinculadas ao veículo onde a COLETA ainda não foi realizada ('C1')  
                    // ou ... a carga está no veículo para realizar a ENTREGA ('E1'). Consideramos apenas
                    // os status 'C1 e 'E1' porque as comandas NÃO passam pelos status 'C0', 'CR' e 'E0'.

                    // ATENÇÃO: comparamos a placa de COLETA e de ENTREGA da COMANDA somente com a
                    // PLACA COLETA da solicitação origem... porque gravamos somente a PLACA DE COLETA 
                    // nas solicitações CONTRATO.
                    //
                    // Quando uma solicitação CONTRATO é transferida para outro veículo... atualizamos
                    // somente a placa de COLETA, porque o status da solicitação CONTRATO fica
                    // sempre no fase 'C' (coleta) enquanto está em andamento. 
                    //
                    ->where(function ($query) use ($placa_coleta) {
                        $query->where(function ($query1) use ($placa_coleta) {
                            $query1->where('cmd.placa_coleta', '=', $placa_coleta)
                                ->where('cmd.status', '=', 'C1');
                        })->orWhere(function ($query2) use ($placa_coleta) {
                            $query2->where('cmd.placa_entrega', '=', $placa_coleta)
                                ->where('cmd.status', '=', 'E1');
                        });
                    })

                    // Comandas com o mesmo ID de contrato que a solicitação atual 
                    ->whereRaw(DB::raw('( (SELECT so.coleta_fixa_id  
                            FROM coleta so 
                            WHERE so.id = cmd.solic_origem_id ) = ' . $coleta->coleta_fixa_id . ')'))

                    // Desconsideramos a solicitação atual (para não retornar ela mesma)  
                    ->where('cmd.id', '<>', $coleta->id)

                    // Deve ser uma COMANDA de contrato
                    ->where(function ($query) {
                        $query->where(function ($query1) {
                            $query1->where('cmd.coleta_fixa', '=', 'C');
                        })->where(function ($query2) {
                            $query2->whereNotNull('cmd.solic_origem_id')
                                ->orWhere('cmd.solic_origem_id', '!=', '0');
                        });
                    })
                    ->get();

                if (count($comandas) > 0) {

                    foreach ($comandas as $com) {

                        try {

                            // Atualizar o registro usando o MODEL para registrar o log da coleta
                            // Vinculamos a comanda à solicitação atual
                            if ($coletacom = Coleta::find($com->id)) {

                                $coletacom['solic_origem_id'] = $coleta->id;     // Coleta Iniciada
                                $coletacom['ass_user_id']     = auth()->user()->id;

                                $coletacom->save();
                            }
                        } catch (\Exception $e) {
                            $retorno['cod_retorno'] = 'E206';
                            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                            $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                            $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
                        }
                    }
                }
            }
        }

        // Atualizar Registro
        if ($continuar) {

            try {

                $timezone_app = date_default_timezone_get();

                // Tem que atualizar desta forma, usando o "Find". Tem eventos a serem
                // disparados no model. Se atualizar com o "Update" estes eventos não serão
                // executados. Isto está na documentação do Laravel.
                if ($coletaaux = Coleta::find($coleta->id)) {

                    // Para início de expediente...setamos a hora da saída da coleta
                    // com a mesma hora de início de atendimento.

                    $coletaaux['status']          = 'C4';     // Coleta Iniciada
                    $coletaaux['hr_atend_coleta'] = $hora_atual_serv;
                    $coletaaux['hr_sai_coleta']   = $hora_atual_serv;
                    $coletaaux['ass_user_id']     = auth()->user()->id;

                    $coletaaux->save();
                }

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {
                $retorno['cod_retorno'] = 'E206';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function Local_FinalizarExpediente($coleta_id)
    {

        $continuar = true;
        $retorno   = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $coleta = DB::table('coleta')
                ->select('id', 'coleta_fixa', 'solic_origem_id', 'placa_coleta', 'status')
                ->where('id', '=', $coleta_id)
                ->first();

            if (empty($coleta)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E200';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            } else {

                // Aceita finalizar o expediente apenas para 
                // solicitações de coleta fixa do tipo 'CONTRATO'
                if (($coleta->coleta_fixa == 'C') && (rgIgualZeroNull($coleta->solic_origem_id))) {
                    $continuar = true; // Já é true - apenas para facilitar o fluxo do processo
                } else {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E237';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            }

            if ($continuar) {

                // Para finalizar o expediente... a solicitação deve estar com status = 'C4' 
                // (Coleta - Iniciada)... que significa 'Expediente iniciado' para solicitações 
                // do tipo 'CONTRATO'.
                if ($coleta->status == 'C4') {
                    $continuar = true;  // Ja é true, apenas para continuar o fluxo do processo
                } else {

                    if ($coleta->status == 'ER') {
                        // Vamos tolerar esta situação para não travar o processo... caso tenha acontecido 
                        // algum 'sinistro' e o status já esteja 'ER'
                        $continuar = false;
                        $retorno['cod_retorno'] = 'Z103';
                        $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    } else {
                        $continuar = false;
                        $retorno['cod_retorno'] = 'E238';
                        $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    }
                }
            }
        }

        if ($continuar) {

            // Para finalizar o expediente... não pode haver solicitações do tipo 'comanda' (solic_origem_id <> zero) 
            // em andamento E que estejam vinculadas ao veículo E à solicitação origem atual.             
            $placa_coleta = $coleta->placa_coleta;

            $outras_solic = DB::table('coleta')
                ->select('id')

                // ATENÇÃO: comparamos a placa de COLETA e de ENTREGA da COMANDA somente com a
                // PLACA COLETA da solicitação origem... porque gravamos somente a PLACA DE COLETA 
                // nas solicitações CONTRATO.
                //
                // Quando uma solicitação CONTRATO é transferida para outro veículo... atualizamos
                // somente a placa de COLETA, porque o status da solicitação CONTRATO fica
                // sempre na fase 'C' (coleta) enquanto está em andamento. 
                //
                ->where(function ($query) use ($placa_coleta) {
                    $query->where(function ($query1) use ($placa_coleta) {
                        $query1->where('placa_coleta', '=', $placa_coleta)
                            ->whereIn('status', ['C2', 'C3', 'C4']);
                    })->orWhere(function ($query2) use ($placa_coleta) {
                        $query2->where('placa_entrega', '=', $placa_coleta)
                            ->whereIn('status', ['E2', 'E3', 'E4']);
                    });
                })
                ->where('id', '<>', $coleta_id)
                ->where('solic_origem_id', '=', $coleta_id)
                ->first();

            if (empty($outras_solic) == false) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E235';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        if ($continuar) {

            try {

                $timezone_app = date_default_timezone_get();

                // Tem que atualizar desta forma, usando o "Find". Tem eventos a serem
                // disparados no model. Se atualizar com o "Update" estes eventos não serão
                // executados. Isto está na documentação do Laravel.
                if ($coletaaux = Coleta::find($coleta->id)) {

                    $data_hora_serv = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
                    $data_serv = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_serv)->format('Y-m-d');
                    $hora_serv = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_serv)->format('H:i:s');

                    $coletaaux['status']           = 'ER';  // Entrega Realizada
                    $coletaaux['dt_efet_entrega']  = $data_serv;
                    $coletaaux['hr_partida_entrega'] = $hora_serv;
                    $coletaaux['hr_cheg_entrega']  = $hora_serv;
                    $coletaaux['hr_atend_entrega'] = $hora_serv;
                    $coletaaux['hr_sai_entrega']   = $hora_serv;

                    $coletaaux['ass_user_id'] = auth()->user()->id;

                    $coletaaux->save();
                }

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {
                $retorno['cod_retorno'] = 'E206';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function Local_SetarDescargaColeta($coleta_id)
    {
        $continuar = true;
        $retorno   = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $coleta = DB::table('coleta')
                ->select('id', 'status', 'carga_pavilhao', 'placa_coleta', 'motor_coleta_id', 'cod_tipo_veiculo')
                ->where('id', '=', $coleta_id)
                ->first();

            if (empty($coleta)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E200';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            } else {

                // A fase de coleta ou de entrega devem estar finalizadas E a carga precisa ainda estar com o veículo
                if (in_array($coleta->status, ['CR', 'EN', 'EP']) && ($coleta->carga_pavilhao != 'S')) {

                    $tempo_desloc_pavilhao = null;

                    $tipo_veiculo = DB::table('tipo_veiculo')
                        ->select('tempo_desloc_pavilhao')
                        ->where('codigo', '=', $coleta->cod_tipo_veiculo)
                        ->first();

                    if (!empty($tipo_veiculo)) {
                        $tempo_desloc_pavilhao = $tipo_veiculo->tempo_desloc_pavilhao;
                    }
                } else {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E239';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            }
        }

        if ($continuar) {

            try {

                DB::beginTransaction();

                // Tem que atualizar desta forma, usando o "Find". Tem eventos a serem
                // disparados no model. Se atualizar com o "Update" estes eventos não serão
                // executados. Isto está na documentação do Laravel.
                if ($coletaaux = Coleta::find($coleta->id)) {

                    $coletaaux['carga_pavilhao'] = 'S'; // Carga descarregada no pavilhao

                    // Quando a descarga foi realizada, carregamos o tempo padrão
                    // de deslocamento até o pavilhão
                    //
                    $coletaaux['tempo_desloc_pavilhao'] = $tempo_desloc_pavilhao;

                    // Limpamos o campo que sequencia os atendimentos e o campo
                    // que indica que a rota foi calculada... porque na fase de ENTREGA 
                    // terão novos valores conforme a rota que será definida.
                    $coletaaux['seq_atend'] = null;
                    $coletaaux['rota_calc'] = null;

                    $coletaaux['ass_user_id'] = auth()->user()->id;

                    $coletaaux->save();

                    // Atualizamos a ocupação Veículo. 
                    // No update não tem atualização de placa, então é passar a placa da coleta direto
                    $this->TratarOcupacaoVeiculo($coletaaux->placa_coleta);
                }

                DB::commit();

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {
                DB::rollback();
                $retorno['cod_retorno'] = 'E206';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function Local_AtualizarReciboNotaFiscalColeta($coleta_nf_id, $coleta_id, $img_base64, $observ, $img_recibo, $mot_nao_entrega)
    {

        $continuar  = true;
        $retorno    = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        // Ajustar parâmetros

        // O aplicativo não envia o parâmetro '$observ'
        if (rgIgualTrimNull($observ)) {
            $observ = null;
        }

        // O aplicativo não envia o parâmetro '$img_recibo'... somente '$img_base64'
        if (rgIgualTrimNull($img_recibo)) {
            $img_recibo = null;
        }

        if (rgIgualTrimNull($mot_nao_entrega)) {
            $mot_nao_entrega = null;
        }

        if (rgDifTrimNull($mot_nao_entrega) && !in_array($mot_nao_entrega, ['51', '52'])) {
            $continuar = false;
            $retorno['cod_retorno'] = 'E288';
            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            $msg_erro = str_replace('$mot_nao_entrega', $mot_nao_entrega, $msg_erro);
            $retorno['msg_retorno'] = $msg_erro;
        }

        if ($continuar) {

            // Verificar se a NF pertence à coleta atual
            $coleta_nf = DB::table('coleta_nf')
                ->where('id', '=', $coleta_nf_id)
                ->where('coleta_id', '=', $coleta_id)
                ->first();

            if (empty($coleta_nf)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E225';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $retorno['msg_retorno'] = $msg_erro;
            }
        }

        //Verificar alteração de imagem
        if ($continuar) {

            // SE o parâmetro '$img_recibo' tiver conteúdo... indica que a imagem do recibo NÃO foi alterada.  
            // SE for vazio... o usuário deverá enviar uma imagem base64
            if (rgDifTrimNull($img_recibo)) {

                // Quando essa API for acionada pela interface web... e o usuário NÃO alterou a imagem do recibo, o parâmetro '$img_recibo' 
                // deve ser enviado com o mesmo valor gravado no campo 'img_recibo' da tabela coleta_nf.

                if ($img_recibo != $coleta_nf->img_recibo) {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E271';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            } else {
                if (rgIgualTrimNull($img_base64)) {

                    if (rgIgualTrimNull($mot_nao_entrega)) {
                        $continuar = false;
                        $retorno['cod_retorno'] = 'E242';
                        $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    } else {
                        $img_base64 = null;
                        $img_recibo = null;
                    }
                } else {

                    try {

                        $timezone_app = date_default_timezone_get();
                        $data_hora_atual = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

                        $data_str = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_atual)->format('Ymd');
                        $hora_str = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_atual)->format('His');

                        $coleta_id_str = str_pad($coleta_id, 8, '0', STR_PAD_LEFT);

                        //Monta o nome do arquivo. Ex: 'recibo-coleta-nf-00000001-202000401-143345.jpg'
                        //e já concatena o nome da subpasta de imagens de coletas => 'coletas/'
                        $img_recibo = 'coletas/' . 'recibo-coleta-nf-' .
                            $coleta_id_str . '-' . $data_str . '-' . $hora_str . '.jpg';

                        //Monta o nome do arquivo de destino (caminho completo)         
                        $image_path_file = rgRetornarPastaRaizImagens() . '/' . $img_recibo;

                        Storage::put($image_path_file, base64_decode($img_base64));
                    } catch (\Exception $e) {
                        $img_recibo = null;
                    }
                }
            }
        }

        if ($continuar) {

            if (rgDifTrimNull($img_recibo) || rgDifTrimNull($mot_nao_entrega)) {

                try {

                    if ($coleta_nf = ColetaNf::where('id', '=', $coleta_nf_id)
                        ->where('coleta_id', '=', $coleta_id)
                        ->first()
                    ) {

                        $coleta_nf['img_recibo']      = $img_recibo;
                        $coleta_nf['observ']          = $observ;
                        $coleta_nf['mot_nao_entrega'] = $mot_nao_entrega;
                        $coleta_nf['ass_user_id'] = auth()->user()->id;

                        $coleta_nf->save();
                    }

                    $retorno['cod_retorno'] = 'Z100';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } catch (\Exception $e) {
                    $retorno['cod_retorno'] = 'E243';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($retorno['msg_retorno'], $e->getMessage());
                }
            } else {
                $retorno['cod_retorno'] = 'E243';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function Local_BaldearColeta($placa_destino, $transfer_code, $coleta_id)
    {
        $continuar = true;
        $retorno   = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $motorista = Motorista::where('user_id', '=', auth()->user()->id)->first();

            if (empty($motorista) == false) {
                $motorista_id = $motorista->id;
            } else {
                $continuar = false;
                $retorno['cod_retorno'] = 'B203';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$email', auth()->user()->email, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }
        }

        if ($continuar) {
            // Pegar somente o campo PLACA para melhor performance
            // Ler o PRIMEIRO registro do veículo => $vei_orig... onde:
            $vei_orig = DB::table('veiculo')
                ->select('placa')
                ->where('motorista_id', '=', $motorista_id)
                ->first();

            if (empty($vei_orig)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'B201';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } else {

                $placa_origem = $vei_orig->placa;

                if ($placa_origem <> $placa_destino) {
                    $continuar = true;   // Já é true - Apenas para facilitar o fluxo e a clareza
                } else {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'B221';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            }
        }

        if ($continuar) {
            // Ler o PRIMEIRO registro do veículo => $vei_dest... onde:
            $vei_dest = DB::table('veiculo')
                ->select('transfer_code', 'motorista_id', 'dt_transfer_code')
                ->where('placa', '=', $placa_destino)
                ->first();

            if (empty($vei_dest)) {

                $continuar = false;
                $retorno['cod_retorno'] = 'B211';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$placa', $placa_destino, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }
        }

        if ($continuar) {

            $timezone_app = date_default_timezone_get();
            $data_hora_atual = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

            if (($transfer_code == $vei_dest->transfer_code) && ($data_hora_atual <= $vei_dest->dt_transfer_code)) {
                $continuar = true;   // Já é true, apenas para clareza no processo
            } else {
                $continuar = false;
                $retorno['cod_retorno'] = 'B220';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$placa', $placa_destino, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }

            if ($continuar) {

                if (!($coleta = Coleta::where('id', '=', $coleta_id)->first())) {
                    $retorno['cod_retorno'] = 'E200';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                    $retorno['msg_retorno'] = $msg_erro;
                } else {

                    // Consideramos somente os status que indicam que a carga está com o veículo e que pode occorer uma baldeação 
                    // até por problemas no veículo mesmo com uma solicitação de entrega 'em andamento'. 

                    // 'E0' - Entrega - Carga definida: não consideramos como carga do veículo enquanto não estiver autorizada. 

                    // 'E3' - Entrega - Chegada: quando o veículo chegou e o atendimento vai demorar para iniciar... 
                    // a carga pode ser transferida para outro veículo menor (por exemplo)... para liberar o veículo atual.

                    // 'CR' - Coleta - Realizada / 'EN' - Entrega - Não Realizada / 'EP' - Entrega - Parcial: 
                    // quando 'carga_pavilhao' <> 'S'... indica que a carga está com o veículo.

                    if (
                        in_array($coleta->status, ['E1', 'E3']) || (in_array($coleta->status, ['CR', 'EN', 'EP']) && $coleta->carga_pavilhao != 'S')
                    ) {

                        if ($placa_destino != $coleta->placa_baldeacao) {
                            $retorno['cod_retorno'] = 'E249';
                            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        } else {

                            try {

                                // Testamos a fase da solicitação para saber se atualizamos a placa de COLETA ou de ENTREGA
                                // Utilizar o model para atualizar a coleta
                                if (substr($coleta->status, 0, 1) == 'C') {

                                    // Se a etapa for uma coleta guardamos a placa da coleta antes da atualização da coleta
                                    // para atualizarmos posteriormente a ocupação do do veiculo
                                    $placa_anterior = $coleta->placa_coleta;

                                    $coleta['placa_coleta']    = $placa_destino;
                                    $coleta['motor_coleta_id'] = $vei_dest->motorista_id;
                                } else {
                                    // Se a etapa for uma entrega, guardamos a placa da entrega antes da atualização da coleta
                                    // para atualizarmos posteriormente a ocupação do do veiculo
                                    $placa_anterior = $coleta->placa_entrega;

                                    $coleta['placa_entrega']    = $placa_destino;
                                    $coleta['motor_entrega_id'] = $vei_dest->motorista_id;
                                }

                                $coleta['baldeada']    = 'S';
                                $coleta['ass_user_id'] = auth()->user()->id;

                                $coleta->save();

                                // Placa coleta/entrega lida... ANTES de atualizar a nova placa
                                $this->TratarOcupacaoVeiculo($placa_anterior);

                                $retorno['cod_retorno'] = 'Z100';
                                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                            } catch (\Exception $e) {
                                $retorno['cod_retorno'] = 'Z200';
                                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
                            }
                        }
                    } else {
                        $retorno['cod_retorno'] = 'E211';
                        $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        $msg_erro = str_replace('$status', $coleta->status, $msg_erro);
                        $retorno['msg_retorno'] = $msg_erro;
                    }
                }
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function Local_DevolverSemAtendColeta($coleta_id, $obs_nao_coleta)
    {

        $continuar  = true;
        $retorno    = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if (rgIgualTrimNull($obs_nao_coleta)) {
            $continuar = false;
            $retorno['cod_retorno'] = 'E253';
            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
        }

        if ($continuar) {

            $coleta = DB::table('coleta')
                ->select('coleta.status', 'coleta.id', 'coleta.id as coleta_id', 'coleta.placa_coleta', 'coleta.motor_coleta_id')
                ->where('id', '=', $coleta_id)
                ->first();

            if (empty($coleta)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E200';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            } else {

                // Aceita devolver a coleta 'SEM ATENDIMENTO" somente quando a chegada foi informada
                if ($coleta->status == 'C3') {
                    $continuar = true; //Já é true, apenas para clareza no fluxo
                } else {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E252';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            }
        }

        if ($continuar) {

            // Tem que atualizar desta forma, usando o "Find". Tem eventos a serem
            // disparados no model. Se atualizar com o "Update" estes eventos não serão
            // executados. Isto está na documentação do Laravel.
            if ($coletaaux = Coleta::find($coleta->coleta_id)) {

                // Quando é REENTREGA ou DEVOLUÇÃO... 
                // assumimos que a carga estava no pavilhão, assim como quando geramos a solicitação de reentrega
                if (rgDifTrimNull($coletaaux->reentrega)) {
                    $carga_pavilhao = 'S';
                } else {
                    $carga_pavilhao = null;
                }

                try {
                    $coletaaux['status']          = 'C0';   // Coleta - chegada

                    $coletaaux['instrucao']       = null;   // Nenhuma
                    $coletaaux['txt_instrucao']   = null;

                    $coletaaux['dt_efet_coleta']  = null;
                    $coletaaux['hr_partida_coleta'] = null;
                    $coletaaux['hr_cheg_coleta']  = null;
                    $coletaaux['dur_prev_coleta'] = null;

                    // Desvinculamos o veículo e o motorista da coleta
                    $coletaaux['placa_coleta']    = null;
                    $coletaaux['motor_coleta_id'] = null;

                    $coletaaux['mot_nao_coleta'] = '03';    // Sem atendimento
                    $coletaaux['obs_nao_coleta'] = $obs_nao_coleta;

                    $coletaaux['carga_pavilhao'] = $carga_pavilhao;

                    $coletaaux['ass_user_id'] = auth()->user()->id;

                    $coletaaux->save();

                    $retorno['cod_retorno'] = 'Z100';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } catch (\Exception $e) {

                    $retorno['cod_retorno'] = 'E206';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                    $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
                }
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function Local_DesfazerStatusAtualColeta($coleta_id)
    {

        $continuar  = true;
        $retorno    = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            // Tem que atualizar desta forma, usando o "Find". Tem eventos a serem
            // disparados no model. Se atualizar com o "Update" estes eventos não serão
            // executados. Isto está na documentação do Laravel.
            if ($coleta = Coleta::find($coleta_id)) {

                // Quem aciona esta rotina é o app do motorista. 
                // Uma solicitação DESCARREGADA no pavilhão NÃO deveria aparecer na carga do veículo, logo... 
                // o motorista não teria como acessar a opção 'Desfazer'. Mesmo assim... 
                // fizemos este teste para impedir qualquer tentativa de desfazer a operação SE descarga já foi realizada - tanto na coleta.. quanto na entrega.
                if ($coleta->carga_pavilhao == 'S') {
                    $retorno['cod_retorno'] = 'E285';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } else {

                    // Guardamos o status atual, ANTES da alteração. Isto é importante, pois este é o status que deve ser testado
                    // nos demais processos desta rotina
                    $coleta_status_atual = $coleta->status;

                    if (substr($coleta_status_atual, 0, 1) == 'C') {
                        $arr_result = $this->DesfazerStatusAtualEtapaColeta($coleta, $coleta_id, $coleta_status_atual);
                        $retorno['cod_retorno'] = $arr_result['cod_retorno'];
                        $retorno['msg_retorno'] = $arr_result['msg_retorno'];
                    } else {   // Etapa atual é ENTREGA
                        $arr_result = $this->DesfazerStatusAtualEtapaEntrega($coleta, $coleta_id, $coleta_status_atual);
                        $retorno['cod_retorno'] = $arr_result['cod_retorno'];
                        $retorno['msg_retorno'] = $arr_result['msg_retorno'];
                    }
                }
            } else {
                $retorno['cod_retorno'] = 'E200';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function DesfazerStatusAtualEtapaColeta($coleta, $coleta_id, $coleta_status_atual)
    {

        $result = true;
        $retorno = array();

        try {

            DB::beginTransaction();

            // 'C2' => Coleta - Deslocamento
            if ($coleta_status_atual == 'C2') {

                $coleta['status']      = 'C1';    // "C1" => Coleta - Autorizada
                $coleta['dt_efet_coleta'] = null;
                $coleta['hr_partida_coleta'] = null;
                $coleta['ass_user_id'] = auth()->user()->id;

                $coleta->save();

                // Pegamos o último registro de 'Início de Deslocamento' conforme a etapa em que a solicitação estava: 'C2'
                $coleta_pos_max_id = DB::table('coleta_pos')
                    ->where('coleta_id', '=', $coleta_id)
                    ->where('status', '=', $coleta_status_atual)
                    ->max('id');

                // Deletamos o último registro de 'Início de Deslocamento' conforme a etapa em que a solicitação estava: 'C2'
                $coleta_pos = DB::table('coleta_pos')
                    ->where('id', '=', $coleta_pos_max_id)
                    ->delete();
            } else {

                // 'C3' => Coleta - Chegada
                if ($coleta_status_atual == 'C3') {

                    $coleta['status']         = 'C2';    // "C2" => Coleta - Deslocamento                    
                    $coleta['hr_cheg_coleta'] = null;
                    $coleta['ass_user_id']    = auth()->user()->id;

                    $coleta->save();

                    // Pegamos o último registro de 'Chegada' conforme a etapa em que a solicitação estava: 'C3'
                    $coleta_pos_max_id = DB::table('coleta_pos')
                        ->where('coleta_id', '=', $coleta_id)
                        ->where('status', '=', $coleta_status_atual)
                        ->max('id');

                    // Deletamos o último registro de 'Chegada' conforme a etapa em que a solicitação estava: 'C3'
                    $coleta_pos = DB::table('coleta_pos')
                        ->where('id', '=', $coleta_pos_max_id)
                        ->delete();
                } else {

                    // 'C4' => Coleta - Início atendimento
                    if ($coleta_status_atual == 'C4') {

                        if ($coleta->coleta_fixa == 'C') {

                            // SE o contrato já tiver COMANDAS... NÃO permitiremos o cancelamento 
                            // do atendimento (Início de Expediente) para não dar problema nos 
                            // horários. Assim evitamos comandas com horáros anteriores ao Início 
                            // de Expediente do contrato.
                            $coleta_aux = DB::table('coleta')
                                ->where('solic_origem_id', '=', $coleta_id)
                                ->first();

                            if (empty($coleta_aux) == false) {
                                $result = false;
                                $retorno['cod_retorno'] = 'E263';
                                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                            }
                        }

                        if ($result) {

                            $coleta['status']          = 'C3';    // "C3" => Coleta - Chegada
                            $coleta['hr_atend_coleta'] = null;
                            $coleta['ass_user_id']     = auth()->user()->id;

                            $coleta->save();
                        }
                    } else {

                        // 'CR' => Coleta - Realizada
                        if ($coleta_status_atual == 'CR') {

                            $timezone_app = date_default_timezone_get();
                            $data_hora_atual = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

                            $dt_hr_sai_coleta = $coleta->dt_efet_coleta . ' ' . $coleta->hr_sai_coleta;
                            $dt_hr_sai_coleta = Carbon::createFromFormat('Y-m-d H:i:s', $dt_hr_sai_coleta)->format('Y-m-d H:i:s');

                            // Pode reabrir uma coleta realizada até 3 horas depois
                            // A funcao "gIntervaloDatasSemFormatacao" retorna na propriedade "h" o nro. de horas

                            $tempo = rgIntervaloDatasSemFormatacao($data_hora_atual, $dt_hr_sai_coleta);

                            if ($tempo->h < 3) {
                                $coleta['status']        = 'C4';    // "C4" => Coleta - Início atendimento
                                $coleta['hr_sai_coleta'] = null;
                                $coleta['ass_user_id']   = auth()->user()->id;

                                $coleta->save();
                            } else {
                                $result = false;
                                $retorno['cod_retorno'] = 'E262';
                                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                            }
                        } else {

                            $result = false;

                            $retorno['cod_retorno'] = 'E261';
                            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                            $msg_erro = str_replace('$status', $coleta_status_atual, $msg_erro);
                            $retorno['msg_retorno'] = $msg_erro;
                        }
                    }
                }
            }

            DB::commit();

            // Se chegou até aqui e continuar é "true", é porque alterou com sucesso o Status da coleta
            if ($result) {
                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        } catch (\Exception $e) {

            $retorno['cod_retorno'] = 'E206';
            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
            $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());

            DB::rollback();
        }

        return $retorno;
    }


    public function DesfazerStatusAtualEtapaEntrega($coleta, $coleta_id, $coleta_status_atual)
    {
        $result = true;
        $retorno = array();

        try {

            DB::beginTransaction();

            // Na etapa de ENTREGA não permitimos desfazer o status 'ER' - Entrega - Realizada... 
            // porque a solicitação NÃO aparece mais no aplicativo

            // 'E2' => Entrega - Deslocamento
            if ($coleta_status_atual == 'E2') {

                $coleta['status']      = 'E1';    // "E1" => Entrega - Autorizada
                $coleta['dt_efet_entrega'] = null;
                $coleta['hr_partida_entrega'] = null;
                $coleta['ass_user_id'] = auth()->user()->id;

                $coleta->save();

                // Pegamos o último registro de 'Início de Deslocamento' conforme
                // a etapa em que a solicitação estava: 'E2'
                $coleta_pos_max_id = DB::table('coleta_pos')
                    ->where('coleta_id', '=', $coleta_id)
                    ->where('status', '=', $coleta_status_atual)
                    ->max('id');

                // Deletamos o último registro de 'Início de Deslocamento' conforme
                // a etapa em que a solicitação estava: 'E2'
                $coleta_pos = DB::table('coleta_pos')
                    ->where('id', '=', $coleta_pos_max_id)
                    ->delete();
            } else {

                // 'E3' => Entrega - Chegada
                if ($coleta_status_atual == 'E3') {

                    $coleta['status']          = 'E2';  // "E2" => Entrega - Deslocamento
                    $coleta['hr_cheg_entrega'] = null;
                    $coleta['ass_user_id']     = auth()->user()->id;

                    $coleta->save();

                    // Pegamos o último registro de 'Chegada' conforme a etapa em que a solicitação estava: 'E3'    
                    $coleta_pos_max_id = DB::table('coleta_pos')
                        ->where('coleta_id', '=', $coleta_id)
                        ->where('status', '=', $coleta_status_atual)
                        ->max('id');

                    // Deletamos o último registro de 'Chegada' conforme a etapa em que a solicitação estava: 'E3'    
                    $coleta_pos = DB::table('coleta_pos')
                        ->where('id', '=', $coleta_pos_max_id)
                        ->delete();
                } else {

                    // 'E4' => Entrega - Início atendimento
                    if ($coleta_status_atual == 'E4') {

                        $coleta['status']           = 'E3';    // "E3" => Entrega - Chegada
                        $coleta['hr_atend_entrega'] = null;
                        $coleta['ass_user_id']      = auth()->user()->id;

                        $coleta->save();
                    } else {

                        // 'EN' => Entrega - Não realizada
                        // 'EP' => Entrega - Parcial
                        if (($coleta_status_atual == 'EN') || ($coleta_status_atual == 'EP')) {

                            // Somente se a solicitação de REENTREGA não foi gerada
                            if ($coleta->reentrega_gerada == 'S') {
                                $result = false;
                                $retorno['cod_retorno'] = 'E282';
                                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                            } else {

                                $timezone_app = date_default_timezone_get();
                                $data_hora_atual = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

                                $dt_hr_sai_entrega = $coleta->dt_efet_entrega . ' ' . $coleta->hr_sai_entrega;
                                $dt_hr_sai_entrega = Carbon::createFromFormat('Y-m-d H:i:s', $dt_hr_sai_entrega)->format('Y-m-d H:i:s');

                                // Pode reabrir uma entrega até 3 horas depois
                                // A funcao "gIntervaloDatasSemFormatacao" retorna na propriedade "h" o nro. de horas

                                $tempo = rgIntervaloDatasSemFormatacao($data_hora_atual, $dt_hr_sai_entrega);

                                if ($tempo->h < 3) {
                                    $coleta['status']         = 'E4'; // "E4" => Entrega - Início atendimento
                                    $coleta['hr_sai_entrega'] = null;
                                    $coleta['ass_user_id']    = auth()->user()->id;

                                    $coleta->save();
                                } else {
                                    $result = false;
                                    $retorno['cod_retorno'] = 'E262';
                                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                                }
                            }
                        } else {
                            $result = false;
                            $retorno['cod_retorno'] = 'E260';
                            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        }
                    }
                }
            }

            DB::commit();

            // Se chegou até aqui e result é "true", é porque alterou com sucesso o Status da coleta
            if ($result) {
                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        } catch (\Exception $e) {
            DB::rollback();

            $retorno['cod_retorno'] = 'E206';
            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
            $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
        }

        return $retorno;
    }


    public function TratarOcupacaoVeiculo($placa)
    {

        $retorno = array();

        // Aqui retornamos os registros de COLETA REALIZADAS e ainda alocadas para o veículo 
        // E as ENTREGAS, ou seja.. é o que está carregado no veículo => CARGA.
        $carga = DB::table('coleta')
            ->select('id')
            // Somente status 'CR' - Coleta Realizada: a carga está com o veículo
            //
            // Consideramos todos os status de ENTREGA que indicam que a carga AINDA está
            // com o veículo => 'E1', 'E2', 'E3', 'E4', 'EN', 'EP'
            // 
            // Desconsideramos status 'E0' pode porque a entrega NÃO está autorizada e 
            // logo... NÃO é considerada como carga do veículo 
            //
            ->where(function ($query) use ($placa) {
                $query->where(function ($query1) use ($placa) {
                    $query1->where('status', '=', 'CR')
                        ->where('placa_coleta', '=', $placa);
                })->orWhere(function ($query2) use ($placa) {
                    $query2->whereIn('status', ['E1', 'E2', 'E3', 'E4', 'EN', 'EP'])
                        ->where('placa_entrega', '=', $placa);
                });
            })

            // DESCONSIDERAMOS solicitações de coleta fixa 'CONTRATO'... porque ficam sempre
            // em aberto até a finalização do expediente e não pode ser considerada como CARGA.
            //
            // CONSIDERAMOS as COMANDAS dos contratos.
            //             
            ->where(function ($query) {
                $query->where('coleta_fixa', '!=', 'C')
                    ->orWhere(function ($query1) {
                        $query1->where('coleta_fixa', '=', 'C')
                            ->where(function ($query2) {
                                $query2->whereNotNull('solic_origem_id')
                                    ->orWhere('solic_origem_id', '!=', '0');
                            });
                    });
            })

            // Para qualquer situação, a carga NÃO pode ter sido descarregada            
            ->where(function ($query) {
                $query->whereNull('carga_pavilhao')
                    ->orWhere('carga_pavilhao', '!=', 'S');
            })

            ->first();

        // SE tem carga... não podemos ZERAR a ocupação
        if (empty($carga) == false) {
            $retorno['cod_retorno'] = 'Z100';
            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
        } else {

            try {

                //Zerar ocupação
                $img_carga = null;
                $ocup_veiculo = 0;
                $img_base64 = null;

                $api = new ApiGeral();
                $resultado = $api->GravarOcupacaoVeiculo($placa, $img_carga, $ocup_veiculo, $img_base64);

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {
                $retorno['cod_retorno'] = 'B219';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function FinalizarSolicOrigemMultiDestinos($solic_origem_id, $dt_entrega, $hr_entrega)
    {

        $retorno = array();

        // Verificação da solicitações filhas em aberto

        // Verificamos se existe alguma Solicitação Auxiliar Multi-destinos EM ABERTO
        // e que seja filha da solicitação ORIGEM informada

        $solic_aux = DB::table('coleta')
            ->select('id')

            // Consideramos apenas solicitações do tipo "M" e que sejam filhas da 
            // solicitação que desejamos finalizar
            // 
            ->where('coleta_fixa', '=', 'M')
            ->where('solic_origem_id', '=', $solic_origem_id)

            // Desconsideramos os status de solicitação finalizada
            //
            ->whereNotIn('status', ['CN', 'EN', 'EP', 'ER'])
            ->first();

        if (empty($solic_aux) == false) {
            $retorno['cod_retorno'] = 'E284';
            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
        } else {

            // Finalizar solicitação 

            try {

                if ($coleta = Coleta::where('id', '=', $solic_origem_id)
                    // Para garantir que esta função será utilizada APENAS para finalizar solicitações Multi-Destinos ORIGEM 
                    ->where('coleta_fixa', '=', 'M')
                    ->where(function ($query1) {
                        $query1->whereNull('solic_origem_id')
                            ->orWhere('solic_origem_id', '=', '0');
                    })
                    ->first()
                ) {

                    $coleta['status']             = 'ER'; // Entrega Realizada
                    $coleta['dt_efet_entrega']    = $dt_entrega;
                    $coleta['hr_partida_entrega'] = $hr_entrega;
                    $coleta['hr_cheg_entrega']    = $hr_entrega;
                    $coleta['hr_atend_entrega']   = $hr_entrega;
                    $coleta['hr_sai_entrega']     = $hr_entrega;

                    $coleta['ass_user_id'] = auth()->user()->id;

                    $coleta->save();
                }

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {

                $retorno['cod_retorno'] = 'E206';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $solic_origem_id, $msg_erro);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
            }
        }

        return $retorno;
    }

    public function Local_GerarSolicAuxiliarMultiDestinos($solic_origem_id, $lista_notas)
    {
        $continuar = true;
        $erros     = array();
        $retorno   = array();

        // Verificar solicitação Multidestinos de origem
        $solic_origem = DB::table('coleta')
            ->select('coleta.*')
            ->where('id', '=', $solic_origem_id)
            ->first();

        if (empty($solic_origem)) {
            $continuar = false;
            $retorno['cod_retorno'] = 'E200';
            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            $msg_erro = str_replace('$coleta_id', $solic_origem_id, $msg_erro);
            $retorno['msg_retorno'] = $msg_erro;
        } else {

            // É uma solicitação Multi-destinos mãe?
            if (($solic_origem->coleta_fixa == 'M') && (rgIgualZeroNull($solic_origem->solic_origem_id))) {
                $continuar = true;   //Já é true - apenas para facilitar o fluxo e ficar de acordo com o Mapa
            } else {
                $continuar = false;
                $retorno['cod_retorno'] = 'E264';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $solic_origem->id, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }
        }

        if ($continuar) {

            if ((isset($lista_notas) == false) || (empty($lista_notas) == true)) {
                $retorno['cod_retorno'] = 'E228';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } else {

                foreach ($lista_notas as $nota) {

                    // Processamos a NF se o local de entrega foi informado
                    if (rgDifZeroNull($nota['cod_loc_entrega'])) {

                        // A nota deve pertencer à coleta informada (solic_origem_id)  
                        // E não deve ter sido distribuida (solic_destino_id)
                        $coleta_nf = DB::table('coleta_nf')
                            ->where('id', '=', $nota['coleta_nf_id'])
                            ->where('coleta_id', '=', $solic_origem->id)
                            ->where(function ($query) {
                                $query->whereNull('solic_destino_id')
                                    ->orWhere('solic_destino_id', '=', '0');
                            })
                            ->first();

                        if (empty($coleta_nf) == false) {

                            // Verificamos se o local de entrega existe para a mesma empresa da solicitação de coleta (origem)
                            $cliente = DB::table('cliente as cli')
                                ->select('nome as local_entrega')
                                ->where('cli.empresa', '=', $solic_origem->empresa)
                                ->where('codigo', '=', $nota['cod_loc_entrega'])
                                ->first();

                            if (empty($cliente) == true) {

                                $retorno['cod_retorno'] = 'E266';
                                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                                $msg_erro = str_replace('$nro_nota', $coleta_nf->numero, $msg_erro);
                                $msg_erro = str_replace('$empresa', $solic_origem->empresa, $msg_erro);
                                $msg_erro = str_replace('$cod_local_entrega', $nota['cod_loc_entrega'], $msg_erro);
                                $retorno['msg_retorno'] = $msg_erro;

                                $idx = count($erros);
                                $erros[$idx]['cod_retorno'] = $retorno['cod_retorno'];
                                $erros[$idx]['msg_retorno'] = $msg_erro;
                            } else {

                                $arr_erros = $this->GravarSolicitacaoAuxiliar(
                                    $solic_origem,
                                    $nota,
                                    $coleta_nf,
                                    $cliente->local_entrega
                                );

                                if (empty($arr_erros) == false) {

                                    $idx = count($erros);
                                    $erros[$idx]['cod_retorno'] = $arr_erros['cod_retorno'];
                                    $erros[$idx]['msg_retorno'] = $arr_erros['msg_retorno'];
                                }
                            }
                        }
                    }
                }

                // Esta API verifica se sobrou alguma nota sem ser distribuida, SE NÃO SOBROU
                // vai setar a solicitação origem com 'carga_pavilhao' = 'S'.
                $arr_result = $this->SetarDescargaSolicOrigemMultiDestinos($solic_origem_id);

                if ($arr_result['cod_retorno'] != 'Z100' && $arr_result['cod_retorno'] != 'Z103') {
                    $idx = count($erros);
                    $erros[$idx]['cod_retorno'] = $arr_result['cod_retorno'];
                    $erros[$idx]['msg_retorno'] = $arr_result['msg_retorno'];
                }

                if (empty($erros)) {
                    $retorno['cod_retorno'] = 'Z100';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } else {
                    $retorno['cod_retorno'] = 'Z200';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];
        $resultado['erros'] = $erros;

        return $resultado;
    }


    public function GravarSolicitacaoAuxiliar($solic_origem, $nota, $coleta_nf, $local_entrega)
    {

        $erros = array();

        try {

            DB::beginTransaction();

            $coleta = new Coleta();

            $coleta['empresa'] = $solic_origem->empresa;
            $coleta['numero']  = null;

            $timezone_app = date_default_timezone_get();
            $data_atual_servidor = Carbon::now($timezone_app)->format('Y-m-d');

            $coleta['data_cad'] = $solic_origem->data_cad;
            $coleta['hora_cad'] = $solic_origem->hora_cad;

            $coleta['cod_cliente']     = $solic_origem->cod_cliente;

            $coleta['dt_prev_coleta']  = $solic_origem->dt_prev_coleta;
            $coleta['hr_prev_coleta']  = $solic_origem->hr_prev_coleta;

            //Para comandas...data+hora ENTREGA podem ser iguais às da COLETA
            $coleta['dt_prev_entrega'] = $data_atual_servidor;
            $coleta['hr_prev_entrega'] = $nota['hr_prev_entrega'];

            $coleta['entrega_urgente'] = $nota['entrega_urgente'];

            $coleta['cod_loc_coleta']  = $solic_origem->cod_loc_coleta;
            $coleta['cod_loc_entrega'] = $nota['cod_loc_entrega'];

            $coleta['solicitante']     = $solic_origem->solicitante;

            // Aproveitamos os volumes informados na nota fiscal
            $coleta['volumes']         = $coleta_nf->volumes;
            $coleta['especie']         = 'vol';

            // Solicitação auxiliar: não interessa o sistema de carga
            $coleta['sis_carga']        = 'N';

            $coleta['cod_tipo_veiculo'] = $solic_origem->cod_tipo_veiculo;
            $coleta['placa_coleta']     = $solic_origem->placa_coleta;
            $coleta['motor_coleta_id']  = $solic_origem->motor_coleta_id;

            // 'coleta_fixa' = 'M' => Multi-destinos
            $coleta['coleta_fixa']      = 'M';

            $coleta['cod_tipo_veiculo_nec'] = $solic_origem->cod_tipo_veiculo_nec;

            // Solicitação auxiliar: o veículo da ENTREGA é o mesmo da COLETA
            $coleta['placa_entrega']    = $solic_origem->placa_coleta;

            // Motorista da ENTREGA será gravado quando o status da solicitação
            // mudar para 'E2' => Entrega - Deslocamento. Fica mais seguro... nos 
            // casos de troca de motorista. 
            //
            $coleta['motor_entrega_id'] = null;

            $coleta['receber_nf_frete'] = 'N';

            // 'ocultar_resumo' deve ser igual solicitação origem
            $coleta['ocultar_resumo'] = $solic_origem->ocultar_resumo;

            // Distância e tempo entre local COLETA e local ENTREGA não estamos 
            // utilizando. Decidimos gravar ZERO aqui para economizar consumo
            // das API´s do Google. Se mais adiante for necessário... temos que 
            // calcular aqui e gravar.
            //
            $coleta['distancia_km']     = 0;
            $coleta['tempo_estimado']   = 0;

            // Solicitação auxiliar: Gravamos inicialmente SEM instrução e com 
            // status 'E0' - Carga definida. Depois da gravação do registro alteramos 
            // para 'E1' para que uma notificação seja enviada para o motorista 
            // quando o campo 'txt_instrucao'  for alterado.
            //
            $coleta['instrucao']        = null;     // Nenhuma 
            $coleta['txt_instrucao']    = null;     // Nenhuma
            $coleta['status']           = 'E0';     // Entrega - carga definida

            // Vinculamos esta 'solicitação auxiliar' ao registro da solicitação de coleta
            $coleta['solic_origem_id']  = $solic_origem->id;

            $coleta['origem_reg']       = 'A4';     // Criado pelos usuários da Maser na plataforma

            $coleta['ass_user_id']      = auth()->user()->id;

            $coleta->save();

            // ------------------------------------------------------------------------------
            // Atualizamos a coleta novamente alterando o status para gerar notificações e log
            // necessários 
            // -------------------------------------------------------------------------------

            $instrucao = '06';   // '06' - Fazer entrega
            $api = new Coleta();
            $txt_instrucao = $api->RetornarDescrInstrucaoColeta($instrucao);

            $coleta['instrucao']     = $instrucao;
            $coleta['txt_instrucao'] = $txt_instrucao;
            $coleta['status']        = 'E1';    // Entrega - autorizada;

            $coleta['ass_user_id']      = auth()->user()->id;

            $coleta->save();

            // -------------------------------------------------------------------------------
            // Atualizamos a tabela ColetaNf com número da coleta distribuida
            // -------------------------------------------------------------------------------

            ColetaNf::where('id', '=', $coleta_nf->id)
                ->update([
                    'solic_destino_id' => $coleta->id,   // -->id_registro_inserido
                    'ass_user_id'      => auth()->user()->id
                ]);

            // -------------------------------------------------------------------------------
            // Inserimos o registro na tabela de coleta NF para nova solicitação criada
            // -------------------------------------------------------------------------------

            $coleta_nf_new = new ColetaNf();

            $coleta_nf_new['coleta_id']  = $coleta->id;
            $coleta_nf_new['cod_barras'] = $coleta_nf->cod_barras;
            $coleta_nf_new['serie']      = $coleta_nf->serie;
            $coleta_nf_new['numero']     = $coleta_nf->numero;
            $coleta_nf_new['valor']      = $coleta_nf->valor;
            $coleta_nf_new['volumes']    = $coleta_nf->volumes;

            $coleta_nf_new['ass_user_id'] = auth()->user()->id;

            $coleta_nf_new->save();

            DB::commit();
        } catch (\Exception $e) {

            DB::rollback();

            $erros['cod_retorno'] = 'E265';
            $msg_erro = rgGetMsgRetornoAPI($erros['cod_retorno']);
            $msg_erro = str_replace('$nro_nota', $coleta_nf->numero, $msg_erro);
            $msg_erro = str_replace('$local_entrega', $local_entrega, $msg_erro);
            $erros['msg_retorno'] = $msg_erro . ' -> ' . $e->getMessage();
        }

        return $erros;
    }


    public function SetarDescargaSolicOrigemMultiDestinos($solic_origem_id)
    {

        $retorno = array();

        // Verificamos se existe alguma nota fiscal ainda NÃO DISTRIBUIDA Multi-destinos 
        // e que seja filha da solicitação ORIGEM informada
        $notas = DB::table('coleta_nf as nf')
            ->select('nf.id')
            ->where('nf.coleta_id', '=', $solic_origem_id)
            // Somente notas não vinculadas a nenhuma solicitação auxiliar
            ->where(function ($query) {
                $query->whereNull('nf.solic_destino_id')
                    ->orWhere('nf.solic_destino_id', '=', '0');
            })
            ->first();

        if (empty($notas) == false) {
            $retorno['cod_retorno'] = 'Z103';
            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
        } else {

            // Atualizar registro

            try {

                // Na API 'SetarDescargaColeta' fazemos duas chamadas a outras rotinas:
                // 'RegistrarGeoPosVeiculoColeta' e 'TratarOcupacaoVeiculo'. Para uma
                // solicitação 'multi-destinos' essas chamadas NÃO é necessárias. 
                //
                // Setamos 'S' para 'carga_pavilhao' desta solicitação MULTI-DESTINOS para 
                // tirar a carga do veículo que fez a coleta, pois teremos as solicitações Auxiliares 
                // de entrega e que serão alocadas para mesmo veículo da coleta ou para outros
                // veículos e são essas solicitações que valem como carga para cada veículo.
                //
                // Aqui NÃO precisamos gravar o campo 'tempo_desloc_pavilhao'. Gravamos este
                // campo somente na rotina 'SetarDescargaColeta'.
                //

                if ($coleta = Coleta::find($solic_origem_id)) {
                    $coleta['carga_pavilhao'] = 'S';
                    $coleta['ass_user_id'] = auth()->user()->id;
                    $coleta->save();
                }

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {
                $retorno['cod_retorno'] = 'E206';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $solic_origem_id, $msg_erro);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
            }
        }

        return $retorno;
    }


    public function Local_GerarSolicReentrega(
        $coleta_id,
        $cod_loc_coleta,
        $dt_prev_coleta,
        $hr_prev_coleta,
        $cod_loc_entrega,
        $dt_prev_entrega,
        $hr_prev_entrega,
        $entrega_urgente,
        $solicitante,
        $peso,
        $volumes,
        $especie,
        $sis_carga,
        $alt_carga,
        $larg_carga,
        $comp_carga,
        $tipo_frete,
        $cod_tipo_veiculo,
        $caract_coleta,
        $obs_coleta,
        $reentrega,
        $lista_notas
    ) {
        $continuar = true;
        $erros     = array();
        $retorno   = array();

        $notas_reentrega = array();

        // Verificar solicitação origem
        $origem = DB::table('coleta')
            ->where('id', '=', $coleta_id)
            ->first();

        if (empty($origem)) {
            $continuar = false;
            $retorno['cod_retorno'] = 'E200';
            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
            $retorno['msg_retorno'] = $msg_erro;

            $idx = count($erros);
            $erros[$idx]['cod_retorno'] = $retorno['cod_retorno'];
            $erros[$idx]['msg_retorno'] = $retorno['msg_retorno'];
        }

        if ($continuar) {
            // Por enquanto: Reentrega permitida apenas para solic. Diárias ou Auxiliares Multi-destinos 
            if (($origem->coleta_fixa == 'D') || (($origem->coleta_fixa == 'M') && (rgDifZeroNull($origem->solic_origem_id)))) {
                $continuar = true;   //Já é true - apenas para facilitar o fluxo e ficar de acordo com o Mapa
            } else {
                $continuar = false;
                $retorno['cod_retorno'] = 'E277';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $retorno['msg_retorno'] = $msg_erro;

                $idx = count($erros);
                $erros[$idx]['cod_retorno'] = $retorno['cod_retorno'];
                $erros[$idx]['msg_retorno'] = $retorno['msg_retorno'];
            }
        }

        if ($continuar) {
            // Permitirmos gerar reentrega se a solicitação origem não foi entregue total ou parcialmente
            if ($origem->status == 'EN' || $origem->status == 'EP') {
                $continuar = true;   //Já é true - apenas para facilitar o fluxo e ficar de acordo com o Mapa
            } else {
                $continuar = false;
                $retorno['cod_retorno'] = 'E278';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $retorno['msg_retorno'] = $msg_erro;

                $idx = count($erros);
                $erros[$idx]['cod_retorno'] = $retorno['cod_retorno'];
                $erros[$idx]['msg_retorno'] = $retorno['msg_retorno'];
            }
        }

        if ($continuar) {
            // Permitirmos gerar reentrega somente se a carga foi descarregada no pavilhão
            if ($origem->carga_pavilhao == 'S') {
                $continuar = true;   //Já é true - apenas para facilitar o fluxo e ficar de acordo com o Mapa
            } else {
                $continuar = false;
                $retorno['cod_retorno'] = 'E287';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $retorno['msg_retorno'] = $msg_erro;

                $idx = count($erros);
                $erros[$idx]['cod_retorno'] = $retorno['cod_retorno'];
                $erros[$idx]['msg_retorno'] = $retorno['msg_retorno'];
            }
        }

        if ($continuar) {
            // 'reentrega_gerada' = 'S' => indica que a solicitação de reentrega já foi feita para a solicitação origem. 
            // Se a solicitação origem tiver várias notas, permitirmos a geração de uma ou mais solicitações de reentrega, 
            // neste caso... o atribuimos 'S' para campo 'reentrega_gerada' ... quando todas as NF foram atribuidas a uma solicitação de reentrega.            
            if ($origem->reentrega_gerada == 'S') {
                $continuar = false;
                $retorno['cod_retorno'] = 'E279';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $retorno['msg_retorno'] = $msg_erro;

                $idx = count($erros);
                $erros[$idx]['cod_retorno'] = $retorno['cod_retorno'];
                $erros[$idx]['msg_retorno'] = $retorno['msg_retorno'];
            }
        }

        if ($continuar) {
            // Se a lista de notas estiver vazia E existirem notas não entregues... exigimos que as notas sejam informadas
            if (empty($lista_notas) && $this->ExistemNotasFiscaisParaReentrega($origem->id)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E290';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $retorno['msg_retorno'] = $msg_erro;

                $idx = count($erros);
                $erros[$idx]['cod_retorno'] = $retorno['cod_retorno'];
                $erros[$idx]['msg_retorno'] = $retorno['msg_retorno'];
            }
        }

        if ($continuar) {

            if (!empty($lista_notas)) {

                $idxNota = 0;

                foreach ($lista_notas as $nota) {

                    // Adicionar esta NF à solicitação destino ou marcar como substituida?                            
                    if (($nota['acao'] == 'A') || ($nota['acao'] == 'S')) {

                        // A nota deve pertencer à coleta informada (solic_origem_id)  
                        // E não deve ter sido atribuida a outra solicitação (solic_destino_id)
                        $coleta_nf = DB::table('coleta_nf')
                            ->where('id', '=', $nota['coleta_nf_id'])
                            ->where('coleta_id', '=', $origem->id)
                            ->where(function ($query) {
                                $query->whereNull('solic_destino_id')
                                    ->orWhere('solic_destino_id', '=', '0');
                            })
                            ->first();

                        if (empty($coleta_nf) == false) {

                            // O teste abaixo funciona para contar as notas definidas como não entregues (mot_nao_entrega <> null) e 
                            // também para status = 'EN', porque neste caso, as notas não terão nenhuma resposta (img_recibo == null  E  mot_nao_entrega == null)                            
                            if (
                                rgIgualZeroNull($coleta_nf->solic_destino_id) && ($coleta_nf->substituida != 'S') && (rgDifTrimNull($coleta_nf->mot_nao_entrega) || (rgIgualTrimNull($coleta_nf->img_recibo) && rgIgualTrimNull($coleta_nf->mot_nao_entrega)))
                            ) {
                                $notas_reentrega[$idxNota]['coleta_nf_id'] = $coleta_nf->id;
                                $notas_reentrega[$idxNota]['acao'] = $nota['acao'];
                            }
                        }
                    }

                    $idxNota++;
                }

                // SE tiver notas fiscais o usuário precisa marcar alguma nota fiscal para ser substituida ou para adicionada na solicitação destino
                if (count($notas_reentrega) > 0) {
                    $continuar = true;   //Já é true - apenas para facilitar o fluxo e ficar de acordo com o Mapa
                } else {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E289';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);

                    $idx = count($erros);
                    $erros[$idx]['cod_retorno'] = $retorno['cod_retorno'];
                    $erros[$idx]['msg_retorno'] = $retorno['msg_retorno'];
                }
            }
        }

        if ($continuar) {

            try {
                DB::beginTransaction();

                $coleta_id = $this->GravarSolicReentrega(
                    $origem,
                    $cod_loc_coleta,
                    $dt_prev_coleta,
                    $hr_prev_coleta,
                    $cod_loc_entrega,
                    $dt_prev_entrega,
                    $hr_prev_entrega,
                    $entrega_urgente,
                    $solicitante,
                    $peso,
                    $volumes,
                    $especie,
                    $sis_carga,
                    $alt_carga,
                    $larg_carga,
                    $comp_carga,
                    $tipo_frete,
                    $cod_tipo_veiculo,
                    $caract_coleta,
                    $obs_coleta,
                    $reentrega
                );

                if (!empty($notas_reentrega)) {

                    foreach ($notas_reentrega as $nota) {

                        // A nota deve pertencer à coleta informada (solic_origem_id)  
                        // E não deve ter sido atribuida a outra solicitação (solic_destino_id)
                        $coleta_nf = DB::table('coleta_nf')
                            ->where('id', '=', $nota['coleta_nf_id'])
                            ->first();

                        if (empty($coleta_nf) == false) {

                            // Adicionar esta NF à solicitação destino?
                            if ($nota['acao'] == 'A') {

                                // -------------------------------------------------------------------------------
                                // Inserimos o registro na tabela de coleta NF para nova solicitação criada
                                // -------------------------------------------------------------------------------

                                $coleta_nf_new = new ColetaNf();

                                $coleta_nf_new['coleta_id']   = $coleta_id; // -->id_registro_inserido
                                $coleta_nf_new['cod_barras']  = $coleta_nf->cod_barras;
                                $coleta_nf_new['serie']       = $coleta_nf->serie;
                                $coleta_nf_new['numero']      = $coleta_nf->numero;
                                $coleta_nf_new['valor']       = $coleta_nf->valor;
                                $coleta_nf_new['volumes']     = $coleta_nf->volumes;
                                $coleta_nf_new['ass_user_id'] = auth()->user()->id;

                                $coleta_nf_new->save();

                                // Gravamos o ID da solicitação de reentrega gerada em 'solic_destino_id' 
                                // do registro da NF da solicitação origem, para indicar que a NF foi adicionada
                                // à solicitação de reentrega gerada. O motorista não precisará fazer nova leitura.
                                ColetaNf::where('id', '=', $coleta_nf->id)
                                    ->update([
                                        'solic_destino_id' => $coleta_id, // -->id_registro_inserido                                                
                                        'ass_user_id' => auth()->user()->id
                                    ]);
                            } else {
                                // Gravamos 'S' no campo 'substituida' da NF da solicitação origem para
                                // indicar que a NF será substituida, ou seja, a NF não será vinculada à
                                // solicitação de reentrega gerada - o motorista terá que fazer nova leitura.
                                ColetaNf::where('id', '=', $coleta_nf->id)
                                    ->update([
                                        'substituida' => 'S',
                                        'ass_user_id' => auth()->user()->id
                                    ]);
                            }
                        }
                    }
                }

                // Esta API verifica se sobrou alguma nota sem ser distribuida, SE NÃO SOBROU
                // vai setar o campo 'reentrega_gerada' com 'S'.
                $arr_result = $this->SetarReentregaGeradaSolicOrigem($origem->id);

                if ($arr_result['cod_retorno'] != 'Z100' && $arr_result['cod_retorno'] != 'Z103') {
                    $idx = count($erros);
                    $erros[$idx]['cod_retorno'] = $arr_result['cod_retorno'];
                    $erros[$idx]['msg_retorno'] = $arr_result['msg_retorno'];
                }

                if (empty($erros)) {
                    DB::commit();
                    $retorno['cod_retorno'] = 'Z100';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } else {
                    DB::rollback();
                    $retorno['cod_retorno'] = 'Z200';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            } catch (\Exception $e) {
                DB::rollback();
                $retorno['cod_retorno'] = 'E280';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
                $erros[0]['cod_retorno'] = $retorno['cod_retorno'];
                $erros[0]['msg_retorno'] = $e->getMessage();
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];
        $resultado['erros'] = $erros;

        return $resultado;
    }


    public function GravarSolicReentrega(
        $origem,
        $cod_loc_coleta,
        $dt_prev_coleta,
        $hr_prev_coleta,
        $cod_loc_entrega,
        $dt_prev_entrega,
        $hr_prev_entrega,
        $entrega_urgente,
        $solicitante,
        $peso,
        $volumes,
        $especie,
        $sis_carga,
        $alt_carga,
        $larg_carga,
        $comp_carga,
        $tipo_frete,
        $cod_tipo_veiculo,
        $caract_coleta,
        $obs_coleta,
        $reentrega
    ) {

        $coleta = new Coleta();

        $coleta['empresa'] = $origem->empresa;
        $coleta['numero']  = null;

        $timezone_app = date_default_timezone_get();
        $data_atual_servidor = Carbon::now($timezone_app)->format('Y-m-d');
        $hora_atual_servidor = Carbon::now($timezone_app)->format('H:i:s');

        $coleta['data_cad'] = $data_atual_servidor;
        $coleta['hora_cad'] = $hora_atual_servidor;

        $coleta['cod_cliente'] = $origem->cod_cliente;

        $coleta['dt_prev_coleta']  = $dt_prev_coleta;
        $coleta['hr_prev_coleta']  = $hr_prev_coleta;

        $coleta['dt_prev_entrega'] = $dt_prev_entrega;
        $coleta['hr_prev_entrega'] = $hr_prev_entrega;
        $coleta['entrega_urgente'] = $entrega_urgente;

        $coleta['cod_loc_coleta']  = $cod_loc_coleta;
        $coleta['cod_loc_entrega'] = $cod_loc_entrega;

        $coleta['solicitante'] = $solicitante;

        $coleta['caract_coleta'] = $caract_coleta;
        $coleta['obs_coleta'] = $obs_coleta;

        /* ATENÇÃO - 
            O teste "strpos" existe para os campos, pois eles não são carregados na interface ou foram carregados e alterados 
            para um tipo de coleta que não exigia, logo não passaram pelo tratamento de diretivas que está formando a máscara dos dados (input da tela) 
            e irá gerar uma exceção de violação do tipo de campo no MySql.
        */

        if (isset($peso)) {
            if (strpos($peso, ',') > 0) {
                $coleta['peso'] = rgStringToFloat($peso);
            }
        }

        if (isset($comp_carga)) {
            if (strpos($comp_carga, ',') > 0) {
                $coleta['comp_carga'] = rgStringToFloat($comp_carga);
            }
        }

        if (isset($larg_carga)) {
            if (strpos($larg_carga, ',') > 0) {
                $coleta['larg_carga'] = rgStringToFloat($larg_carga);
            }
        }

        if (isset($alt_carga)) {
            if (strpos($alt_carga, ',') > 0) {
                $coleta['alt_carga'] = rgStringToFloat($alt_carga);
            }
        }

        $coleta['volumes'] = $volumes;
        $coleta['especie'] = $especie;

        $coleta['sis_carga'] = $sis_carga;
        $coleta['tipo_frete'] = $tipo_frete;
        $coleta['cod_tipo_veiculo'] = $cod_tipo_veiculo;

        $coleta['coleta_fixa'] = $origem->coleta_fixa;

        $coleta['placa_coleta'] = null;

        // Motorista da COLETA será gravado quando o status da solicitação
        // mudar para 'C2' => Coleta - Deslocamento. Fica mais seguro... nos 
        // casos de troca de motorista. 
        $coleta['motor_coleta_id'] = null;

        $coleta['placa_entrega'] = null;

        // Motorista da ENTREGA será gravado quando o status da solicitação
        // mudar para 'E2' => Entrega - Deslocamento. Fica mais seguro... nos 
        // casos de troca de motorista. 
        $coleta['motor_entrega_id'] = null;

        $coleta['receber_nf_frete'] = 'N';

        // 'aceitar_foto_rom' deve ser igual solicitação origem
        $coleta['aceitar_foto_rom'] = $origem->aceitar_foto_rom;

        // 'ocultar_resumo' deve ser igual solicitação origem
        $coleta['ocultar_resumo'] = $origem->ocultar_resumo;

        // Distância e tempo entre local COLETA e local ENTREGA não estamos 
        // utilizando. Decidimos gravar ZERO aqui para economizar consumo
        // das API´s do Google. Se mais adiante for necessário... temos que 
        // calcular aqui e gravar.
        $coleta['distancia_km']   = 0;
        $coleta['tempo_estimado'] = 0;

        // Gravamos SEM instrução e com status 'C0' - Coleta Solicitada. 
        $coleta['instrucao']     = null;     // Nenhuma 
        $coleta['txt_instrucao'] = null;     // Nenhuma
        $coleta['status']        = 'C0';     // Coleta - solicitada

        // Apontamos para a solicitação que está sendo reentregue
        $coleta['solic_reentrega_id'] = $origem->id;

        // Esta solicitação de reentrega ficará vinculada à mesma solicitação
        // de origem da solicitação que está sendo reentregue. 
        $coleta['solic_origem_id'] = $origem->solic_origem_id;

        // Reentrega sempre partirá do pavilhão
        $coleta['carga_pavilhao'] = 'S';
        $coleta['reentrega'] = $reentrega;

        $coleta['origem_reg']  = 'A4';     // Criado pelos usuários da Maser na plataforma
        $coleta['ass_user_id'] = auth()->user()->id;

        $coleta->save();

        return $coleta->id; // -->id_registro_inserido
    }

    public function SetarReentregaGeradaSolicOrigem($coleta_id)
    {
        $retorno = array();

        // Não pode existir notas fiscais pendentes para reentrega... 
        // para que a solicitação origem seja marcada com 'S' no campo 'reentrega_gerada'.

        if ($this->ExistemNotasFiscaisParaReentrega($coleta_id)) {
            $retorno['cod_retorno'] = 'Z103';
            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
        } else {

            // Atualizar registro
            try {

                if ($coleta = Coleta::find($coleta_id)) {

                    // Tiramos a carga do pavilhão para a solicitação que será reentregue.
                    // As novas solicitações é que apareceção no pavilhão.  
                    $coleta['carga_pavilhao'] = null;

                    // O campo 'reentrega_gerada' = 'S' indica que as solicitações de reentrega para esta   
                    // solicitação já foram geradas. Normalmente é gerada uma única solicitação de  
                    // reentrega, em alguns casos, mais de uma pode ser gerada.
                    $coleta['reentrega_gerada'] = 'S';

                    $coleta['ass_user_id'] = auth()->user()->id;
                    $coleta->save();
                }

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {
                $retorno['cod_retorno'] = 'E281';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
            }
        }

        return $retorno;
    }

    public function ExistemNotasFiscaisParaReentrega($coleta_id)
    {
        // Verificamos se existe alguma nota fiscal ainda NÃO ATRIBUIDA (solic_destino_id = null), 
        // que não esteja marcada como SUBSTITUIDA, que não tenha sido entregue E que seja filha da solicitação informada.

        $notas = DB::table('coleta_nf as nf')
            ->select('nf.id')
            ->where('nf.coleta_id', '=', $coleta_id)
            // Somente notas não vinculadas a nenhuma solicitação auxiliar
            ->where(function ($query) {
                $query->whereNull('nf.solic_destino_id')
                    ->orWhere('nf.solic_destino_id', '=', '0');
            })
            // Notas que não serão substituidas na solicitação destino
            ->where(function ($query) {
                $query->whereNull('nf.substituida')
                    ->orWhere('nf.substituida', 'N');
            })

            // Quando tem motivo não foi entregue. 
            // Sem recibo e sem motivo... indica NF sem resposta.
            ->where(function ($query) {
                $query->where(function ($query1) {
                    $query1->whereNotNull('nf.mot_nao_entrega')
                        ->orWhere('nf.mot_nao_entrega', '!=', '');
                })->orWhere(function ($query2) {
                    $query2->whereNull('nf.img_recibo')
                        ->whereNull('nf.mot_nao_entrega');
                });
            })
            ->first();

        if (empty($notas) == false) {
            $retorno = true;
        } else {
            $retorno = false;
        }

        return $retorno;
    }
}
