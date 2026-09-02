<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\ApiUsoComum;
use Carbon\Carbon;
use DB;

class Controle extends Model
{
    public function Local_RetornarVeiculosColeta($coleta_id, $com_motorista)
    {

        $dados_coleta = array();
        $veiculos = array();

        $coleta = $this->LerDadosColeta($coleta_id);

        if (empty($coleta) == false) {
            //Monta o array com os dados da coleta solicitada
            $dados_coleta = $this->MontaArrayDadosColeta($coleta);

            //Monta o array de veículos com suas respectivas coletas
            $veiculos = $this->MontaArrayVeiculos($coleta, $com_motorista);

            // Ordenar o array $veiculos em ordem decrescente de pontos_veiculo
            $veiculos = $this->OrdenaArrayVeiculoPorPontuacao($veiculos);
        }

        $dados['dados']['dados_coleta'] = $dados_coleta;
        $dados['dados']['veiculos']     = $veiculos;

        return $dados;
    }

    public function LerDadosColeta($coleta_id)
    {

        $coleta = DB::table('coleta')
            ->select(
                'coleta.*',
                'coleta.id as coleta_id',
                'cli_coleta.geo_lat AS geo_lat_coleta',
                'cli_coleta.geo_lng AS geo_lng_coleta',

                'cli_coleta.hr_ini_coleta_man',
                'cli_coleta.hr_fim_coleta_man',
                'cli_coleta.hr_ini_coleta_tar',
                'cli_coleta.hr_fim_coleta_tar',

                'cli_entrega.hr_ini_entrega_man',
                'cli_entrega.hr_fim_entrega_man',
                'cli_entrega.hr_ini_entrega_tar',
                'cli_entrega.hr_fim_entrega_tar',

                'cliente.nome AS nome_cliente',
                'cli_coleta.nome AS local_coleta',
                'cli_entrega.nome AS local_entrega',

                'tipo_veiculo.descricao AS tipo_veiculo'
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
            ->leftJoin('tipo_veiculo', 'tipo_veiculo.codigo', '=', 'coleta.cod_tipo_veiculo')
            ->where('coleta.id', '=', $coleta_id)
            ->first();

        return $coleta;
    }



    public function MontaArrayDadosColeta($coleta)
    {

        // Variáveis para retornar dados da coleta
        $dados_coleta['coleta_id']      = $coleta->coleta_id;
        $dados_coleta['numero']         = $coleta->numero;
        $dados_coleta['coleta_fixa']    = $coleta->coleta_fixa;
        $dados_coleta['coleta_fixa_id'] = $coleta->coleta_fixa_id;
        $dados_coleta['sis_carga']      = $coleta->sis_carga;
        $dados_coleta['data_cad']       = $coleta->data_cad;
        $dados_coleta['hora_cad']       = $coleta->hora_cad;
        $dados_coleta['nome_cliente']   = $coleta->nome_cliente;
        $dados_coleta['placa_coleta']   = $coleta->placa_coleta;
        $dados_coleta['local_coleta']   = $coleta->local_coleta;
        $dados_coleta['local_entrega']  = $coleta->local_entrega;

        $dados_coleta['dt_prev_coleta']  = $coleta->dt_prev_coleta;
        $dados_coleta['hr_prev_coleta']  = $coleta->hr_prev_coleta;
        $dados_coleta['dt_prev_entrega'] = $coleta->dt_prev_entrega;
        $dados_coleta['hr_prev_entrega'] = $coleta->hr_prev_entrega;

        $dados_coleta['entrega_urgente'] = $coleta->entrega_urgente;

        $dados_coleta['hr_ini_coleta_man'] = $coleta->hr_ini_coleta_man;
        $dados_coleta['hr_fim_coleta_man'] = $coleta->hr_fim_coleta_man;
        $dados_coleta['hr_ini_coleta_tar'] = $coleta->hr_ini_coleta_tar;
        $dados_coleta['hr_fim_coleta_tar'] = $coleta->hr_fim_coleta_tar;

        $dados_coleta['hr_ini_entrega_man'] = $coleta->hr_ini_entrega_man;
        $dados_coleta['hr_fim_entrega_man'] = $coleta->hr_fim_entrega_man;
        $dados_coleta['hr_ini_entrega_tar'] = $coleta->hr_ini_entrega_tar;
        $dados_coleta['hr_fim_entrega_tar'] = $coleta->hr_fim_entrega_tar;

        $dados_coleta['vol_carga'] = $coleta->volumes . ' ' .
            $coleta->especie . ' ' . rgFormataPesoVeiculo($coleta->peso);

        $dados_coleta['dim_carga'] = rgFormataDimensoes($coleta->comp_carga, $coleta->larg_carga, $coleta->alt_carga);

        $dados_coleta['caract'] = $coleta->caract_coleta;
        $dados_coleta['tipo_veiculo'] = $coleta->tipo_veiculo;

        return $dados_coleta;
    }


    public function MontaArrayVeiculos($coleta, $com_motorista)
    {
        $separador = ' ';

        $veiculo_arr_final = array();

        $timezone_app = date_default_timezone_get();
        $data_hora_atual = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
        $data_serv_atual = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_atual)->format('Y-m-d');

        // Selecionamos os veículos ativos e que não estejam atendendo à uma 
        // solicitação de coleta fixa do tipo 'CONTRATO' (desconsideramos os 
        // registros com data futura).
        $veiculo = DB::table('veiculo as v')
            ->select(
                'v.placa',
                'v.motorista_id',
                'v.comprimento',
                'v.largura',
                'v.altura',
                'v.sis_carga_empilha',
                'v.sis_carga_ponte',
                'v.sis_carga_manual',
                'v.cap_kg',
                'v.ocup_veiculo',
                'v.img_carga',
                'v.geo_lat',
                'v.geo_lng',
                'v.ignicao',
                'v.nivel_cons',
                'v.dt_geopos',
                'v.dur_atend_atual',
                'tv.codigo AS cod_tipo_veiculo',
                'tv.descricao as descr_tipo_veiculo',
                'm.nome as nome_motorista'
            )
            ->Join('tipo_veiculo as tv', 'tv.codigo', '=', 'v.cod_tipo_veiculo')
            ->leftJoin('motorista as m', 'm.id', '=', 'v.motorista_id')
            ->where('v.ativo', '=', 'S')

            // Consideramos apenas veículos: "M" => Monobloco ou "R" => Carrega/Reboque
            ->whereIn('tv.classe', ['M', 'R'])

            // Caso seja solicitado, selecionamos somente veículos com motorista
            ->where(function ($query) use ($com_motorista) {
                if ($com_motorista == 'S') {
                    $query->whereNotNull('v.motorista_id')
                        ->where('v.motorista_id', '<>', '0')
                        ->where('v.motorista_id', '<>', '');
                }
            })

            ->whereRaw('(SELECT Count(1)
                        FROM coleta col
                        WHERE 
                            ( col.status IN ("C1", "C2", "C3", "C4") AND col.placa_coleta = v.placa ) AND 
                            ( col.coleta_fixa = "C" ) AND  
                            ( col.solic_origem_id = 0 OR col.solic_origem_id IS NULL) AND
                            ( col.dt_prev_coleta <= ' . $data_serv_atual . ') ) = 0')
            ->get();

        $menor_tempo = 0;
        $idx_menor_tempo = null;

        $idx = 0;

        if (count($veiculo) > 0) {

            foreach ($veiculo as $vei) {

                // Pontuação do veículo
                $pontos_veiculo = 0;
                $det_pontos = '';

                //Tipo de Veiculo
                $veiculo_arr['tipo_veiculo'] = $vei->descr_tipo_veiculo;

                if ($vei->cod_tipo_veiculo == $coleta->cod_tipo_veiculo) {
                    $veiculo_arr['tipo_veiculo_ok'] = 'S';
                    $pontos_veiculo = $pontos_veiculo + 1000;
                    // Concatenar DOIS espaços 
                    $det_pontos = $det_pontos . '  ' . 'Tipo:1000';
                } else {
                    $veiculo_arr['tipo_veiculo_ok'] = 'A';
                    // Concatenar DOIS espaços 
                    $det_pontos = 'Tipo:0';
                }

                //Localização do veículo
                $loc = new ApiUsoComum();
                $localizacao = $loc->RetornarAreaLocalVeiculo($vei->placa);
                $veiculo_arr['local_veiculo'] = $localizacao['local_veiculo'];

                //Sistema de carga
                if (($coleta->sis_carga == 'E' && $vei->sis_carga_empilha == 'S') || ($coleta->sis_carga == 'P' && $vei->sis_carga_ponte == 'S')  || ($coleta->sis_carga == 'M' && $vei->sis_carga_manual == 'S')
                ) {
                    $veiculo_arr['sis_carga_ok'] = 'S';
                    $pontos_veiculo = $pontos_veiculo + 3000;
                    // Concatenar DOIS espaços 
                    $det_pontos = $det_pontos . $separador . 'Sis.Carga:3000';
                } else {
                    $veiculo_arr['sis_carga_ok'] = 'N';
                    // Concatenar DOIS espaços 
                    $det_pontos = $det_pontos . $separador . 'Sis.Carga:0';
                }

                // Dimensões da carga X veículo
                if (($coleta->comp_carga <= $vei->comprimento) && ($coleta->larg_carga <= $vei->largura) && ($coleta->alt_carga <= $vei->altura)
                ) {
                    $veiculo_arr['dimensoes_ok'] = 'S';
                    $pontos_veiculo = $pontos_veiculo + 3000;
                    // Concatenar DOIS espaços 
                    $det_pontos = $det_pontos . $separador . 'Dim:3000';
                } else {
                    $veiculo_arr['dimensoes_ok'] = 'A';
                    // Concatenar DOIS espaços 
                    $det_pontos = $det_pontos . $separador . 'Dim:0';
                }

                // Montar string das dimensões. Ex: "3,50 x 2 x 1,80"
                $veiculo_arr['dimensoes'] = rgFormataDimensoes($vei->comprimento, $vei->largura, $vei->altura);

                //Ocupação do veículo
                $veiculo_arr['ocup_veiculo'] = floatval($vei->ocup_veiculo);

                if (rgIgualTrimNull($vei->img_carga) == false) {
                    $veiculo_arr['img_carga'] = $vei->img_carga;
                } else {
                    $veiculo_arr['img_carga'] = '';
                }

                // Cálculo da ocupação da carga para saber se tem espaço no veículo.
                // Volume cúbico da carga
                $veiculo_arr['vol_carga'] = $coleta->comp_carga * $coleta->larg_carga * $coleta->alt_carga;

                // Capacidade cúbica do veículo
                $veiculo_arr['cap_veiculo'] = $vei->comprimento * $vei->largura * $vei->altura;

                // Percentual de ocupação da carga no veículo (sem decimais)
                if (rgIgualZeroNull($veiculo_arr['cap_veiculo'])) {
                    $veiculo_arr['ocup_carga'] = 0;
                } else {
                    $veiculo_arr['ocup_carga'] = round($veiculo_arr['vol_carga'] / $veiculo_arr['cap_veiculo'] * 100, 0);
                }

                // Espaço disponível no veículo
                $veiculo_arr['espaco_disp_veiculo'] = 100 - $vei->ocup_veiculo;

                // Carga cabe no espaço disponível do veículo?
                if ($veiculo_arr['ocup_carga'] <= $veiculo_arr['espaco_disp_veiculo']) {
                    $veiculo_arr['ocup_ok'] = 'S';
                    $pontos_veiculo = $pontos_veiculo + 1000;
                    // Concatenar DOIS espaços 
                    $det_pontos = $det_pontos . $separador . 'Ocup:1000';
                } else {
                    $veiculo_arr['ocup_ok'] = 'A';
                    // Concatenar DOIS espaços 
                    $det_pontos = $det_pontos . $separador . 'Ocup:0';
                }

                // Distância e tempo até local coleta
                // Distância e tempo da posição do veículo até o local da coleta
                $veiculo_arr['distancia']    = 0;
                $veiculo_arr['tempo_viagem'] = 0;
                $tempo_estimado = 0;

                if ((rgDifZeroNull($vei->geo_lat)) && (rgDifZeroNull($vei->geo_lng)) && (rgDifZeroNull($coleta->geo_lat_coleta)) && (rgDifZeroNull($coleta->geo_lng_coleta))
                ) {

                    $api = new ApiIntegracao();
                    $retorno = $api->CalcularDistanciaOrigem_Destino(
                        $vei->geo_lat,
                        $vei->geo_lng,
                        $coleta->geo_lat_coleta,
                        $coleta->geo_lng_coleta
                    );

                    $veiculo_arr['distancia'] = $retorno['distance'];
                    $tempo_estimado = $retorno['duration'];

                    // O tempo estimado está calculado em segundos. Fazemos a conversão para "H:i:s" 
                    $veiculo_arr['tempo_viagem'] = rgSecondsToTime($tempo_estimado);
                }

                // Nível de consumo x Distância
                if ((intval($vei->nivel_cons) <> 0) && ($veiculo_arr['distancia'] > 0)) {

                    // O nível de consumo indica a relação Consumo por litro x Preço do combustível
                    // Então... multiplicamos pela distância que o veículo está do local da coleta para
                    // obtermos o veículo mais econômico para atender à solicitação

                    // O fator é para dar o 'peso' adequado para cada nível de consumo.
                    // 6 é o nível máximo de consumo que temos no cadastro do veículo.
                    //
                    // Ex: Para nivel de consumo = 1... o fator será 6, para o nivel = 2... o fator será 5, ...
                    //
                    $fator_nivel_cons = (6 - intval($vei->nivel_cons) + 1);

                    // Aqui os pontos terão 3 (três) casas decimais
                    //
                    $pontos_calc = round(1 / $veiculo_arr['distancia'] * $fator_nivel_cons, 3);
                    $pontos_veiculo = $pontos_veiculo + $pontos_calc;

                    // Concatenar DOIS espaços 
                    $det_pontos = $det_pontos . $separador . 'Niv.Cons:' . $pontos_calc;
                } else {

                    // Nível de consumo "0" - Não definido => não ganha pontos
                    // Concatenar DOIS espaços 
                    $det_pontos = $det_pontos . $separador . 'Niv.Cons:0';
                }

                // Armazenamos no array de veículos a pontuação e o detalhamento da pontuacao 
                // obtida até este ponto da rotina. Nos calculos a seguir estas informações devem
                // ser somadas a partir do array e não mais das variáveis "$pontos_veiculo" e "$detpontos'
                $veiculo_arr['pontos_veiculo'] = $pontos_veiculo;
                $veiculo_arr['det_pontos']     = $det_pontos;

                // A rotina "RetSolicitacoesAlocadasVeiculoAtual" é utilizada em três funções: 
                // "RetornarVeiculosFrota", "RetornarVeiculosColeta" e ""RetornarVeiculosBaldeacao"
                $coletas_veiculo = $this->RetSolicitacoesAlocadasVeiculoAtual($vei, $data_serv_atual);

                // Monta o array das coletas para este veículo da frota
                // A rotina "MontaArrayColetasVeiculoColeta_Frota" é utilizada em duas funções: "RetornarVeiculosFrota" e "RetornarVeiculosColeta"
                $retorno = $this->MontaArrayColetasVeiculoColeta_Frota($coletas_veiculo, $vei);

                // Atribuir para o array Veículos os valores modificados pela rotina "MontaArrayColetasVeiculo"      
                $veiculo_arr['peso_total_coletas'] = $retorno['peso_total_coletas'];
                $veiculo_arr['hr_prev_saida']      = $retorno['hr_prev_saida'];
                $veiculo_arr['qtde_coletas']       = $retorno['qtde_coletas'];
                $veiculo_arr['qtde_solic_and']     = $retorno['qtde_solic_and'];

                $veiculo_arr = $this->MontaInfExpedienteLocColeta(
                    $veiculo_arr,
                    $data_hora_atual,
                    $coleta
                );

                // Capacidade veículo: KG
                // Peso da solicitação atual + Peso das coletas alocadas para o veículo  x Capacidade do veículo em KG
                if (($coleta->peso + $veiculo_arr['peso_total_coletas']) <= $vei->cap_kg) {
                    $veiculo_arr['capacid_peso_ok'] = 'S';
                    $veiculo_arr['pontos_veiculo']  = $veiculo_arr['pontos_veiculo'] + 3000;
                    // Concatenar DOIS espaços 
                    $veiculo_arr['det_pontos']      =  $veiculo_arr['det_pontos'] . $separador . 'Cap.KG:3000';
                } else {
                    $veiculo_arr['capacid_peso_ok'] = 'N';
                    $veiculo_arr['det_pontos']      =  $veiculo_arr['det_pontos'] . $separador . 'Cap.KG:0';
                }

                // Contabilizamos os pontos para MENOR TEMPO... 
                // somente para os veículos que atendem aos requisitos: 
                // sistema de carga, capacid. peso e dimensões
                if (($veiculo_arr['sis_carga_ok'] == 'S') && ($veiculo_arr['dimensoes_ok'] == 'S') && ($veiculo_arr['capacid_peso_ok'] == 'S')) {

                    if ($tempo_estimado > 0) {
                        if (($tempo_estimado <= $menor_tempo) || ($menor_tempo == 0)) {
                            // Armazenar o tempo em segundos
                            $menor_tempo     = $tempo_estimado;
                            $idx_menor_tempo = $idx;
                        }
                    }
                }

                // Agora que o array de veículos está com todas as informações, acrescentamos
                // no final o "subarray" das Coletas do veículo
                $veiculo_arr['coletas_veiculo'] = $retorno['array_coletas'];

                // Setamos o menor tempo para todos os veículos. Posteriormente setaremos o mais rápido
                $veiculo_arr['menor_tempo'] = 'N';

                $veiculo_arr_final[$idx] = $this->MontaArrayVeicFinalSemOrdenacao($veiculo_arr, $vei, $data_hora_atual);

                $idx++;
            }
        }

        // ANTES de devolver o array de veículos para ordenação... vamos setar o veículo mais rápido. 
        if ($idx_menor_tempo <> null) {
            // Setamos o veículo que vai demorar menos tempo de viagem
            $veiculo_arr_final[$idx_menor_tempo]['menor_tempo'] = 'S';
        }

        return $veiculo_arr_final;
    }

    public function RetSolicitacoesAlocadasVeiculoAtual($vei, $data_serv_atual)
    {

        $placa = $vei->placa;

        // Verificamos se existem solicitações alocadas para o veículo atual
        $coletas_veiculo = DB::table('coleta as col')
            ->select(
                'col.id',
                'col.numero',
                'col.dt_efet_coleta',
                'col.hr_atend_coleta',
                'col.dur_prev_coleta',
                'col.hr_cheg_coleta',
                'col.dt_efet_entrega',
                'col.hr_atend_entrega',
                'col.hr_cheg_entrega',
                'col.dur_prev_entrega',
                'col.status',
                'col.peso',
                'col.data_cad',
                'col.dt_prev_coleta',
                'col.hr_prev_coleta',
                'col.dt_prev_entrega',
                'col.hr_prev_entrega',
                'col.dt_efet_coleta',
                'col.hr_sai_coleta',
                'col.coleta_fixa',
                'cli_coleta.geo_lat AS geo_lat_coleta',
                'cli_coleta.geo_lng AS geo_lng_coleta',
                'cli_entrega.geo_lat AS geo_lat_entrega',
                'cli_entrega.geo_lng AS geo_lng_entrega',
                'cli.nome AS nome_cliente',
                'cli_coleta.nome AS local_coleta',
                'cli_entrega.nome AS local_entrega'
            )
            ->leftjoin('cliente as cli', function ($join) {
                $join->on('cli.codigo', '=', 'col.cod_cliente')
                    ->on('cli.empresa', '=', 'col.empresa');
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
            ->where('col.dt_prev_coleta', '<=', $data_serv_atual)

            // Solicitações na etapa COLETA com qualquer status... EXCETO: 'CN' - Coleta - Não Realizada 
            // Solicitações na etapa ENTREGA com qualquer status... EXCETO: 'ER' - Entrega - Realizada 
            //            
            ->where(function ($query) use ($placa) {
                $query->where(function ($query1) use ($placa) {
                    $query1->where('col.placa_coleta', '=', $placa)
                        ->whereIn('col.status', ['C0', 'C1', 'C2', 'C3', 'C4', 'CR']);
                })->orWhere(function ($query2) use ($placa) {
                    $query2->where('col.placa_entrega', '=', $placa)
                        ->whereIn('col.status', ['E0', 'E1', 'E2', 'E3', 'E4', 'EN', 'EP']);
                });
            })

            // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
            // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
            //
            ->where(function ($query) {
                $query->where('col.coleta_fixa', '!=', 'C')
                    ->orWhere(function ($query1) {
                        $query1->where('col.coleta_fixa', '=', 'C')
                            ->where(function ($query2) {
                                $query2->whereNull('col.solic_origem_id')
                                    ->orWhere('col.solic_origem_id', '=', '0');
                            });
                    });
            })

            // Para qualquer situação, a carga NÃO pode ter sido descarregada            
            ->where(function ($query) {
                $query->whereNull('col.carga_pavilhao')
                    ->orWhere('col.carga_pavilhao', '!=', 'S');
            })

            // Em qualquer situação mostramos apenas se a solicitação de reentrega não foi gerada.
            // 
            ->where(function ($query) {
                $query->whereNull('col.reentrega_gerada')
                    ->orWhere('col.reentrega_gerada', '!=', 'S');
            })

            ->orderBy('col.seq_atend', 'asc')
            ->orderBy('col.numero', 'asc')
            ->get();

        return $coletas_veiculo;
    }


    public function MontaArrayColetasVeiculo($coletas_veiculo, $vei)
    {
        //Solicitações alocadas para o veículo
        $hr_prev_saida  = '';

        $qtde_coletas   = 0;
        $array_coletas  = array();
        $peso_total_coletas = 0;

        $qtde_solic_and = 0;

        // As coordenadas de ORIGEM iniciam com a posição 
        // do veículo que está sendo processado
        $geo_lat_origem = $vei->geo_lat;
        $geo_lng_origem = $vei->geo_lng;

        $qtde_coletas = count($coletas_veiculo);

        if ($qtde_coletas > 0) {

            $ind = 0;

            foreach ($coletas_veiculo as $col_vei) {

                // 'C3', 'C4' ou 'E3','E4':  veículo já chegou ao local de destino
                if (in_array($col_vei->status, ['C3', 'C4', 'E3', 'E4']) == true) {

                    // Etapa da solicitação
                    // Define a etapa em que a solicitação está
                    if (substr($col_vei->status, 0, 1) == 'C') {
                        $geo_lat_destino = $col_vei->geo_lat_coleta;
                        $geo_lng_destino = $col_vei->geo_lng_coleta;
                    } else {
                        $geo_lat_destino = $col_vei->geo_lat_entrega;
                        $geo_lng_destino = $col_vei->geo_lng_entrega;
                    }

                    // Coordenadas de ORIGEM e DESTINO iguais... indicam que a solicitação ANTERIOR 
                    // e a solicitação ATUAL se destinam ao MESMO LOCAL.
                    // Calculamos a hora de saída prevista somente se a ORIGEM e o DESTINO forem diferentes. 
                    if (($geo_lat_origem <> $geo_lat_destino) || ($geo_lng_origem <> $geo_lng_destino)) {

                        // Guardamos a posição do destino atual como origem para o próximo destino. 
                        //
                        $geo_lat_origem = $geo_lat_destino;
                        $geo_lng_origem = $geo_lng_destino;

                        $timezone_app = date_default_timezone_get();
                        $data_hora_atual_serv = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
                        $data_atual_serv      = Carbon::now($timezone_app)->format('Y-m-d');
                        $hora_serv_atual      = Carbon::now($timezone_app)->format('H:i:s');

                        if (($col_vei->status == 'C3') || ($col_vei->status == 'C4')) {

                            // Solicitação com status 'COLETA - Chegada ou Iniciada'.
                            //
                            // Calculamos a hora prevista da saída do local a partir da HORA DE CHEGADA 
                            // e com a duração do atendimento atual... que está gravada no veículo
                            //
                            $hr_prev_saida_sec = strtotime($col_vei->hr_cheg_coleta) + rgTimeToSeconds($vei->dur_atend_atual);
                            $hr_prev_saida     = date('H:i:s', $hr_prev_saida_sec);

                            if (($col_vei->dt_efet_coleta < $data_atual_serv) || ($hr_prev_saida < $hora_serv_atual)) {
                                // Se a previsão de saída está atrasada... colocamos a hora atual
                                $hr_prev_saida = $hora_serv_atual;
                            }
                        } else {

                            // Solicitação com status 'ENTREGA - Chegada ou Iniciada'.
                            //
                            // Calculamos a hora prevista da saída do local a partir da HORA DE CHEGADA 
                            // e com a duração do atendimento atual... que está gravada no veículo
                            //
                            $hr_prev_saida_sec = strtotime($col_vei->hr_cheg_entrega) + rgTimeToSeconds($vei->dur_atend_atual);
                            $hr_prev_saida = date('H:i:s', $hr_prev_saida_sec);

                            if (($col_vei->dt_efet_entrega < $data_atual_serv) || ($hr_prev_saida < $hora_serv_atual)) {
                                // Se a previsão de saída está atrasada... colocamos a hora atual
                                $hr_prev_saida = $hora_serv_atual;
                            }
                        }
                    }
                }

                // Contamos as solicitacoes em andamento
                if (in_array($col_vei->status, ['C2', 'C3', 'C4', 'E2', 'E3', 'E4']) == true) {
                    $qtde_solic_and = $qtde_solic_and + 1;
                }

                // Acumulamos o peso das coletas do veículo
                $peso_total_coletas = $peso_total_coletas + $col_vei->peso;

                // Adicionar a solicitação no array de coletas do veículo
                $array_coletas[$ind]['coleta_id']     = $col_vei->id;
                $array_coletas[$ind]['coleta_fixa']   = $col_vei->coleta_fixa;
                $array_coletas[$ind]['numero']        = $col_vei->numero;
                $array_coletas[$ind]['cliente']       = $col_vei->nome_cliente;
                $array_coletas[$ind]['local_coleta']  = $col_vei->local_coleta;
                $array_coletas[$ind]['local_entrega'] = $col_vei->local_entrega;
                $array_coletas[$ind]['status']        = $col_vei->status;

                $array_coletas[$ind]['peso']             = rgFormataPesoVeiculo($col_vei->peso);
                $array_coletas[$ind]['data_cad']         = $col_vei->data_cad;
                $array_coletas[$ind]['dt_prev_coleta']   = $col_vei->dt_prev_coleta;
                $array_coletas[$ind]['hr_prev_coleta']   = $col_vei->hr_prev_coleta;
                $array_coletas[$ind]['dt_prev_entrega']  = $col_vei->dt_prev_entrega;
                $array_coletas[$ind]['hr_prev_entrega']  = $col_vei->hr_prev_entrega;
                $array_coletas[$ind]['dt_efet_coleta']   = $col_vei->dt_efet_coleta;
                $array_coletas[$ind]['hr_sai_coleta']    = $col_vei->hr_sai_coleta;

                $ind++;
            }
        }

        $retorno['hr_prev_saida']      = $hr_prev_saida;
        $retorno['peso_total_coletas'] = $peso_total_coletas;
        $retorno['qtde_coletas']       = $qtde_coletas;
        $retorno['qtde_solic_and']     = $qtde_solic_and;

        $retorno['array_coletas']      = $array_coletas;

        return $retorno;
    }


    public function MontaArrayColetasVeiculoColeta_Frota($coletas_veiculo, $vei)
    {

        $array_coletas = array();

        $hr_prev_saida = '';

        $peso_total_coletas = 0;
        $qtde_coletas = 0;
        $qtde_solic_and = 0;

        // As coordenadas de ORIGEM iniciam com a posição 
        // do veículo que está sendo processado
        //
        $geo_lat_origem = $vei->geo_lat;
        $geo_lng_origem = $vei->geo_lng;

        $qtde_coletas = count($coletas_veiculo);

        if ($qtde_coletas > 0) {

            $ind = 0;

            foreach ($coletas_veiculo as $col_vei) {

                // 'C3', 'C4' ou 'E3','E4':  veículo já chegou ao local de destino
                if (in_array($col_vei->status, ['C3', 'C4', 'E3', 'E4'])) {

                    // Etapa da solicitação
                    // Define a etapa em que a solicitação está
                    if (substr($col_vei->status, 0, 1) == 'C') {
                        $geo_lat_destino = $col_vei->geo_lat_coleta;
                        $geo_lng_destino = $col_vei->geo_lng_coleta;
                    } else {
                        $geo_lat_destino = $col_vei->geo_lat_entrega;
                        $geo_lng_destino = $col_vei->geo_lng_entrega;
                    }

                    // Coordenadas de ORIGEM e DESTINO iguais... indicam que a solicitação ANTERIOR e a solicitação ATUAL se destinam ao MESMO LOCAL.
                    // Calculamos a hora de saída prevista somente se a ORIGEM e o DESTINO forem diferentes. 
                    if (($geo_lat_origem <> $geo_lat_destino) || ($geo_lng_origem <> $geo_lng_destino)) {

                        // Guardamos a posição do destino atual como origem para o próximo destino. 
                        //
                        $geo_lat_origem = $geo_lat_destino;
                        $geo_lng_origem = $geo_lng_destino;

                        $timezone_app = date_default_timezone_get();
                        $data_atual_serv = Carbon::now($timezone_app)->format('Y-m-d');
                        $hora_serv_atual = Carbon::now($timezone_app)->format('H:i:s');

                        if (($col_vei->status == 'C3') || ($col_vei->status == 'C4')) {

                            // Solicitação com status 'COLETA - Chegada ou Iniciada'.
                            //
                            // Calculamos a hora prevista da saída do local a partir da HORA DE CHEGADA 
                            // e com a duração do atendimento atual... que está gravada no veículo
                            //
                            $hr_prev_saida_sec = strtotime($col_vei->hr_cheg_coleta) + rgTimeToSeconds($vei->dur_atend_atual);
                            $hr_prev_saida     = date('H:i:s', $hr_prev_saida_sec);

                            if (($col_vei->dt_efet_coleta < $data_atual_serv) || ($hr_prev_saida < $hora_serv_atual)) {
                                // Se a previsão de saída está atrasada... colocamos a hora atual
                                $hr_prev_saida = $hora_serv_atual;
                            }
                        } else {

                            // Solicitação com status 'ENTREGA - Chegada ou Iniciada'.
                            //
                            // Calculamos a hora prevista da saída do local a partir da HORA DE CHEGADA 
                            // e com a duração do atendimento atual... que está gravada no veículo
                            //
                            $hr_prev_saida_sec = strtotime($col_vei->hr_cheg_entrega) + rgTimeToSeconds($vei->dur_atend_atual);
                            $hr_prev_saida     = date('H:i:s', $hr_prev_saida_sec);

                            if (($col_vei->dt_efet_entrega < $data_atual_serv) || ($hr_prev_saida < $hora_serv_atual)) {
                                // Se a previsão de saída está atrasada... colocamos a hora atual
                                $hr_prev_saida = $hora_serv_atual;
                            }
                        }
                    }
                }

                // Contamos as solicitacoes em andamento
                if (in_array($col_vei->status, ['C2', 'C3', 'C4', 'E2', 'E3', 'E4']) == true) {
                    $qtde_solic_and = $qtde_solic_and + 1;
                }

                // Acumulamos o peso das coletas do veículo
                $peso_total_coletas = $peso_total_coletas + $col_vei->peso;

                // Adicionar a solicitação no array de coletas do veículo
                $array_coletas[$ind]['coleta_id']     = $col_vei->id;
                $array_coletas[$ind]['coleta_fixa']   = $col_vei->coleta_fixa;
                $array_coletas[$ind]['numero']        = $col_vei->numero;
                $array_coletas[$ind]['cliente']       = $col_vei->nome_cliente;
                $array_coletas[$ind]['local_coleta']  = $col_vei->local_coleta;
                $array_coletas[$ind]['local_entrega'] = $col_vei->local_entrega;
                $array_coletas[$ind]['status']        = $col_vei->status;

                $array_coletas[$ind]['peso']             = rgFormataPesoVeiculo($col_vei->peso);
                $array_coletas[$ind]['data_cad']         = $col_vei->data_cad;
                $array_coletas[$ind]['dt_prev_coleta']   = $col_vei->dt_prev_coleta;
                $array_coletas[$ind]['hr_prev_coleta']   = $col_vei->hr_prev_coleta;
                $array_coletas[$ind]['dt_prev_entrega']  = $col_vei->dt_prev_entrega;
                $array_coletas[$ind]['hr_prev_entrega']  = $col_vei->hr_prev_entrega;
                $array_coletas[$ind]['dt_efet_coleta']   = $col_vei->dt_efet_coleta;
                $array_coletas[$ind]['hr_sai_coleta']    = $col_vei->hr_sai_coleta;

                $ind++;
            }
        }

        $retorno['hr_prev_saida']      = $hr_prev_saida;
        $retorno['peso_total_coletas'] = $peso_total_coletas;
        $retorno['qtde_coletas']       = $qtde_coletas;
        $retorno['qtde_solic_and']     = $qtde_solic_and;

        $retorno['array_coletas']      = $array_coletas;

        return $retorno;
    }


    public function MontaInfExpedienteLocColeta(
        $veiculo_arr,
        $data_hora_atual,
        $coleta
    ) {
        $separador = ' ';

        //Previsão chegada ao local de coleta

        // SE tiver previsão de saída... indica que o veículo tinha chegado em algum local.... 
        // SE NÃO TIVER... estava em trânsito
        if ($veiculo_arr['hr_prev_saida'] <> '') {
            // Previsão de chegada ao local da coleta... partindo da PREVISÃO DE SAÍDA 
            // da solicitação que está sendo atendida pelo veículo
            $seconds = strtotime($veiculo_arr['hr_prev_saida']) + rgTimeToSeconds($veiculo_arr['tempo_viagem']);
            $hr_prev_chegada = date('H:i:s', $seconds);
        } else {
            // Previsão de chegada ao local da coleta... partindo da HORA ATUAL... porque o veículo não está atendendo a uma solicitação no momento
            $seconds = strtotime($data_hora_atual) + rgTimeToSeconds($veiculo_arr['tempo_viagem']);
            $hr_prev_chegada = date('H:i:s', $seconds);
        }

        //Expediente do local de coleta
        // Vai chegar no período da manhã?
        if ($hr_prev_chegada <= '12:00:00') {

            // Expediente manhã informado?
            if ((rgDifZeroNull($coleta->hr_ini_coleta_man)) && (rgDifZeroNull($coleta->hr_fim_coleta_man))) {

                // Testamos expediente de coleta da MANHÃ
                if (($hr_prev_chegada >= $coleta->hr_ini_coleta_man)  && ($hr_prev_chegada <= $coleta->hr_fim_coleta_man)
                ) {
                    $veiculo_arr['tempo_ok'] = 'S';
                    $veiculo_arr['msg_tempo'] = '';
                    $veiculo_arr['pontos_veiculo'] =  $veiculo_arr['pontos_veiculo'] + 1000;
                    // Concatenar DOIS espaços 
                    $veiculo_arr['det_pontos'] = $veiculo_arr['det_pontos'] . $separador . 'Horário:1000';
                } else {
                    $veiculo_arr['tempo_ok'] = 'N';
                    $veiculo_arr['msg_tempo'] = 'Fora do expediente da manhã';
                    // Concatenar DOIS espaços 
                    $veiculo_arr['det_pontos'] = $veiculo_arr['det_pontos'] . $separador . 'Horário:0';
                }
            } else {
                $veiculo_arr['tempo_ok'] = 'A';
                $veiculo_arr['msg_tempo'] = 'Horários de atendimento da manhã não informados';
            }
        } else {

            // Expediente tarde informado?
            if ((rgDifZeroNull($coleta->hr_ini_coleta_tar)) && (rgDifZeroNull($coleta->hr_fim_coleta_tar))
            ) {

                // Testamos expediente de coleta da TARDE
                if (($hr_prev_chegada >= $coleta->hr_ini_coleta_tar) && ($hr_prev_chegada <= $coleta->hr_fim_coleta_tar)
                ) {

                    $veiculo_arr['tempo_ok'] = 'S';
                    $veiculo_arr['msg_tempo'] = '';

                    $veiculo_arr['pontos_veiculo'] = $veiculo_arr['pontos_veiculo'] + 1000;

                    // Concatenar DOIS espaços 
                    $veiculo_arr['det_pontos'] = $veiculo_arr['det_pontos'] . $separador . 'Horário:1000';
                } else {
                    $veiculo_arr['tempo_ok'] = 'N';
                    $veiculo_arr['msg_tempo'] = 'Fora expediente da tarde';

                    // Concatenar DOIS espaços 
                    $veiculo_arr['det_pontos'] = $veiculo_arr['det_pontos'] . $separador . 'Horário:0';
                }
            } else {
                $veiculo_arr['tempo_ok'] = 'A';
                $veiculo_arr['msg_tempo'] = 'Horários de atendimento da tarde não informados';
            }
        }

        //Guarda a hora da previsão de chegada para retornar no array de veículos
        $veiculo_arr['hr_prev_chegada'] = $hr_prev_chegada;

        return $veiculo_arr;
    }


    public function RetornarContadoresColeta($coleta_id)
    {

        $notas_fiscais = DB::table('coleta_nf')
            ->select('id')
            ->where('coleta_id', '=', $coleta_id)
            ->count();

        $qtde_notas_distrib = DB::table('coleta_nf')
            ->select('id')
            ->where('coleta_id', '=', $coleta_id)
            ->whereNull('solic_destino_id')
            ->count();

        $coleta_log = DB::table('coleta_log')
            ->select('id')
            ->where('coleta_id', '=', $coleta_id)
            ->count();

        $coleta_pos = DB::table('coleta_pos')
            ->select('id')
            ->where('coleta_id', '=', $coleta_id)
            ->count();

        $arr_contadores['notas_fiscais']      = $notas_fiscais;
        $arr_contadores['qtde_notas_distrib'] = $qtde_notas_distrib;
        $arr_contadores['coleta_log']         = $coleta_log;
        $arr_contadores['coleta_pos']         = $coleta_pos;

        return $arr_contadores;
    }


    public function OrdenaArrayVeiculoPorPontuacao($veiculos)
    {

        if (count($veiculos) > 0) {
            // Organizar o $array de veiculos pela pontuacao decrescente
            foreach ($veiculos as $key => $row) {
                $arr_pontos[$key] = $row['pontos_veiculo'];
            }

            array_multisort($arr_pontos, SORT_DESC, $veiculos);
        }

        return $veiculos;
    }


    public function MontaArrayVeicFinalSemOrdenacao($veiculo_arr, $vei, $data_hora_atual)
    {

        $arr_veic['placa']           = $vei->placa;
        $arr_veic['motorista_id']    = $vei->motorista_id;
        $arr_veic['nome_motorista']  = $vei->nome_motorista;

        $arr_veic['tipo_veiculo_ok'] = $veiculo_arr['tipo_veiculo_ok'];
        $arr_veic['tipo_veiculo']    = $veiculo_arr['tipo_veiculo'];

        $arr_veic['sis_carga_ok']    = $veiculo_arr['sis_carga_ok'];

        $arr_veic['dimensoes_ok']    = $veiculo_arr['dimensoes_ok'];
        $arr_veic['dimensoes']       = $veiculo_arr['dimensoes'];

        $arr_veic['capacid_peso_ok'] = $veiculo_arr['capacid_peso_ok'];

        $arr_veic['capacid_peso_kg'] = rgFormataPesoVeiculo($vei->cap_kg);
        $arr_veic['peso_total_coletas'] = rgFormataPesoVeiculo($veiculo_arr['peso_total_coletas']);

        $arr_veic['ocup_ok']         = $veiculo_arr['ocup_ok'];
        $arr_veic['ocup_veiculo']    = $veiculo_arr['ocup_veiculo'];
        $arr_veic['img_carga']       = $veiculo_arr['img_carga'];

        if (rgIgualTrimNull($veiculo_arr['img_carga']) == false) {
            $arr_veic['url_imagem']  = rgRetornarUrlImagens($veiculo_arr['img_carga']);
        } else {
            $arr_veic['url_imagem']  = '';
        }

        if ($veiculo_arr['distancia'] == 0) {
            $arr_veic['distancia']   = '-';
        } else {
            $arr_veic['distancia']   = rgFormataDistancia($veiculo_arr['distancia']);
        }

        $arr_veic['menor_tempo']     = 'N';
        $arr_veic['tempo_ok']        = $veiculo_arr['tempo_ok'];

        $arr_veic['tempo_viagem']    = rgRetornaFormataTempoExt($veiculo_arr['tempo_viagem']);
        $arr_veic['hr_prev_chegada'] = $veiculo_arr['hr_prev_chegada'];
        $arr_veic['msg_tempo']       = $veiculo_arr['msg_tempo'];

        $arr_veic['local_veiculo']   = $veiculo_arr['local_veiculo'];
        $arr_veic['geo_lat']         = $vei->geo_lat;
        $arr_veic['geo_lng']         = $vei->geo_lng;

        if (rgIgualTrimNull($vei->dt_geopos)) {
            $arr_veic['atlz_geopos'] = 'Sem informações';
        } else {
            $arr_veic['atlz_geopos'] = rgRetornaDiferencaDataExt($data_hora_atual, $vei->dt_geopos);
        }

        $arr_veic['ignicao']           = $vei->ignicao;
        $arr_veic['sis_carga_empilha'] = $vei->sis_carga_empilha;
        $arr_veic['sis_carga_ponte']   = $vei->sis_carga_ponte;
        $arr_veic['sis_carga_manual']  = $vei->sis_carga_manual;

        $arr_veic['hr_prev_saida']   = $veiculo_arr['hr_prev_saida'];

        $arr_veic['qtde_coletas']    = $veiculo_arr['qtde_coletas'];
        $arr_veic['qtde_solic_and']  = $veiculo_arr['qtde_solic_and'];
        // Para garantir 03 casas decimais 
        $arr_veic['pontos_veiculo']  = round($veiculo_arr['pontos_veiculo'], 3);
        $arr_veic['det_pontos']      = trim($veiculo_arr['det_pontos']);

        $arr_veic['coletas_veiculo'] = $veiculo_arr['coletas_veiculo'];

        return $arr_veic;
    }


    public function Local_DefinirVeiculoColeta($coleta_id, $placa, $autorizar)
    {

        $continuar = true;

        //SE ocorrer uma falha na gravação deste registro... apenas retornaremos FALSE,
        //para a rotina chamadora saber que a gravação não foi executada.
        try {

            $result = true;

            if ($autorizar == 'S') {

                $instrucao = '01';   // 01 - Fazer Coleta 
                $api = new Coleta();
                $txt_instrucao = $api->RetornarDescrInstrucaoColeta($instrucao);

                //Com o "status = C0" no WHERE garantimos que a solicitação DEVE estar "C0" => Solicitada
                $coleta = Coleta::where('id', '=', $coleta_id)->where('status', '=', 'C0')->first();

                if (empty($coleta)) {
                    $result = false;
                    $continuar = false;
                }

                if ($continuar) {
                    $coleta['placa_coleta']  = $placa;
                    $coleta['instrucao']     = $instrucao;
                    $coleta['txt_instrucao'] = $txt_instrucao;

                    // Limpamos o campo 'carga_pavilhao': quando foi atribuida 
                    // a um veículo, a carga foi tirada do pavilhão.
                    //
                    // Isso é necessário porque as solicitações de REENTREGA passam
                    // pelo pavilhão. Na autorização da COLETA precisamos limpar o
                    // campo 'carga_pavilhao'.                    
                    // 
                    $coleta['carga_pavilhao'] = null;

                    $coleta['status'] = 'C1'; // Coleta autorizada

                    $coleta['ass_user_id'] = auth()->user()->id;
                    $coleta->save();
                }
            } else {

                //Atualizamos somente a placa porque não precisa autorizar a solicitação
                $coleta = Coleta::where('id', '=', $coleta_id)->where('status', '=', 'C0')->first();

                if (empty($coleta)) {
                    $result = false;
                    $continuar = false;
                }

                if ($continuar) {

                    $coleta['placa_coleta'] = $placa;

                    // Limpamos os campos de não-coleta 
                    $coleta['mot_nao_coleta'] = null;
                    $coleta['obs_nao_coleta'] = null;

                    // Limpamos o campo 'carga_pavilhao': quando foi atribuida 
                    // a um veículo, a carga foi tirada do pavilhão.
                    //
                    // Isso é necessário porque as solicitações de REENTREGA passam
                    // pelo pavilhão.
                    // 
                    $coleta['carga_pavilhao'] = null;

                    $coleta['ass_user_id'] = auth()->user()->id;
                    $coleta->save();
                }
            }
        } catch (\Exception $e) {
            $result = false;
        }

        return ['status' => $result];
    }


    public function Local_DefinirVeiculoEntrega($coleta_id, $placa, $autorizar)
    {

        $continuar = true;
        $result    = false;
        $retorno   = array();

        if (!($coleta = Coleta::find($coleta_id))) {
            $continuar = false;
            $retorno['cod_retorno'] = 'E200';
            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
            $retorno['msg_retorno'] = $msg_erro;
        }

        if ($continuar) {

            if ($autorizar == 'S') {

                if (in_array($coleta->status, ['CR', 'E0', 'E1'])) {
                    $continuar = true;   // já é true... apenas para clareza no fluxo
                } else {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E241';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            } else {
                // Para atribuir uma placa na fase de ENTREGA a coleta precisa ter sido realizada ('CR'). 
                // Para a solicitação a outro veículo (alterar)... 
                // primeiro o usuário deve remover a solicitação do veículo (rotina: RemoverSolicitacaoVeiculo).. para depois atribuir a nova placa.
                if ($coleta->status == 'CR') {
                    $continuar = true;   // já é true... apenas para clareza no fluxo
                } else {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E286';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            }

            if ($continuar) {

                if (rgIgualTrimNull($placa)) {

                    if (rgIgualTrimNull($coleta->placa_entrega)) {
                        //Se a placa não for passada E a coleta NÃO tiver placa de entrega definida
                        //atribuimos a mesma placa da coleta
                        $placa = $coleta->placa_coleta;
                    } else {
                        //Se tiver veiculo de entrega definido...atribuimos o memo valor para atualização
                        $placa = $coleta->placa_entrega;
                    }
                }

                try {

                    // O registro da coleta já foi lido no início da rotina
                    // Utilizar o model para atualizar o registro para disparar os eventos Update
                    if ($autorizar == 'S') {

                        $instrucao = '06';   // 06 - Fazer entrega
                        $api = new Coleta();
                        $txt_instrucao = $api->RetornarDescrInstrucaoColeta($instrucao);

                        $coleta['placa_entrega'] = $placa;
                        $coleta['instrucao']     = $instrucao;
                        $coleta['txt_instrucao'] = $txt_instrucao;

                        // Limpamos campos de baldeacao para a etapa ENTREGA
                        $coleta['placa_baldeacao'] = null;
                        $coleta['baldeada']        = 'N';

                        // Limpamos o campo 'carga_pavilhao': quando foi atribuida 
                        // a um veículo, a carga foi tirada do pavilhão.
                        //
                        // Na autorização da ENTREGA precisamos limpar o
                        // campo 'carga_pavilhao'.
                        // 
                        $coleta['carga_pavilhao'] = null;

                        $coleta['status'] = 'E1'; // Entrega Autorizada

                    } else {
                        $coleta['placa_entrega'] = $placa;

                        // Limpamos os campos de não entrega, caso o motorista
                        // tenha finalizado como a entrega como 'não realizada e
                        // depois desfaça as operações..
                        //
                        $coleta['mot_nao_entrega'] = null;
                        $coleta['obs_nao_entrega'] = null;

                        // Limpamos o campo 'carga_pavilhao': quando foi atribuida 
                        // a um veículo, a carga foi tirada do pavilhão.
                        //
                        $coleta['carga_pavilhao'] = null;

                        $coleta['status'] = 'E0'; // Carga Definida
                    }

                    $coleta['ass_user_id'] = auth()->user()->id;
                    $coleta->save();

                    $result = true;
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

        $resultado['status'] = $result;

        $resultado['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function Local_RetornarVeiculosFrota($data)
    {

        if (isset($data['cliente_id'])) {
            $cliente_id = $data['cliente_id'];
        } else {
            $cliente_id = null;
        }

        if (isset($data['geo_lat_coleta'])) {
            $geo_lat_coleta = $data['geo_lat_coleta'];
        } else {
            $geo_lat_coleta = null;
        }

        if (isset($data['geo_lng_coleta'])) {
            $geo_lng_coleta = $data['geo_lng_coleta'];
        } else {
            $geo_lng_coleta = null;
        }

        if (isset($data['cod_tipo_veiculo'])) {
            $cod_tipo_veiculo = $data['cod_tipo_veiculo'];
        } else {
            $cod_tipo_veiculo = null;
        }

        if (isset($data['sis_carga'])) {
            $sis_carga = $data['sis_carga'];
        } else {
            $sis_carga = null;
        }

        if (isset($data['hr_prev_coleta'])) {
            $hr_prev_coleta = $data['hr_prev_coleta'];
        } else {
            $hr_prev_coleta = null;
        }

        if (isset($data['com_carga'])) {
            $com_carga = $data['com_carga'];
        } else {
            $com_carga = null;
        }

        if (isset($data['com_motorista'])) {
            $com_motorista = $data['com_motorista'];
        } else {
            $com_motorista = null;
        }

        $veiculos = array();

        $where = $this->MontaCondicaoWhereVeicFrota($cod_tipo_veiculo, $sis_carga, $com_motorista);

        //Monta o array de veículos da Frota com suas respectivas coletas
        $veiculos = $this->MontaArrayVeiculosFrota($where, $cliente_id, $geo_lat_coleta, $geo_lng_coleta, $hr_prev_coleta, $com_carga);

        // Ordenar o array de veiculos da frota em ordem crescente de distancia
        $veiculos =  $this->OrdenaArrayVeicFrotaPorDistancia($veiculos);

        $dados['dados']['veiculos'] = $veiculos;

        return $dados;
    }


    public function MontaCondicaoWhereVeicFrota($cod_tipo_veiculo, $sis_carga, $com_motorista)
    {

        $where = '(v.ativo = "S")';

        $where = $where . ' AND ' . '(tv.classe IN ("M", "R"))';

        if (rgDifZeroNull($cod_tipo_veiculo)) {
            $where = $where . ' AND ' . '(v.cod_tipo_veiculo = ' . $cod_tipo_veiculo . ')';
        }

        if ($sis_carga == 'M') {
            $where = $where . ' AND ' . '(v.sis_carga_manual = "S")';
        }

        if ($sis_carga == 'E') {
            $where = $where . ' AND ' . '(v.sis_carga_empilha = "S")';
        }

        if ($sis_carga == 'P') {
            $where = $where . ' AND ' . '(v.sis_carga_ponte = "S")';
        }

        if ($com_motorista == 'S') {
            $where = $where . ' AND ' . '(v.motorista_id <> "") AND (v.motorista_id <> "0") AND (v.motorista_id IS NOT NULL)';
        }

        return $where;
    }


    public function MontaArrayVeiculosFrota($where, $cliente_id, $geo_lat_coleta, $geo_lng_coleta, $hr_prev_coleta, $com_carga)
    {

        $veiculo_arr_final = array();

        $timezone_app = date_default_timezone_get();
        $data_hora_atual = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
        $data_serv_atual = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_atual)->format('Y-m-d');

        // Selecionamos os veículos ativos da frota E que a classe do tipo do 
        // veículo seja igual a 'M' - Monobloco ou 'R' - Carreta / Reboque
        $veiculo = DB::table('veiculo as v')
            ->select(
                'v.placa',
                'v.motorista_id',
                'v.comprimento',
                'v.largura',
                'v.altura',
                'v.sis_carga_empilha',
                'v.sis_carga_ponte',
                'v.sis_carga_manual',
                'v.cap_kg',
                'v.ocup_veiculo',
                'v.img_carga',
                'v.nivel_cons',
                'v.geo_lat',
                'v.geo_lng',
                'v.ignicao',
                'v.dt_geopos',
                'v.dur_atend_atual',
                'tv.codigo AS cod_tipo_veiculo',
                'tv.descricao as descr_tipo_veiculo',
                'm.nome as nome_motorista'
            )
            ->Join('tipo_veiculo as tv', 'tv.codigo', '=', 'v.cod_tipo_veiculo')
            ->leftJoin('motorista as m', 'm.id', '=', 'v.motorista_id')
            ->whereRaw($where)
            ->get();

        $menor_distancia = 0;
        $menor_tempo = 0;

        $idx_menor_distancia = null;
        $idx_menor_tempo = null;

        $idx = 0;

        if (count($veiculo) > 0) {

            foreach ($veiculo as $vei) {

                // A rotina "RetSolicitacoesAlocadasVeiculoAtual" é utilizada em duas funções: "RetornarVeiculosFrota",
                // "RetornarVeiculosBaldeacao" e "RetornarVeiculosColeta"
                $coletas_veiculo = $this->RetSolicitacoesAlocadasVeiculoAtual($vei, $data_serv_atual);

                $retornar_veiculo = true;

                if (count($coletas_veiculo) == 0) {

                    if ($com_carga == 'S') {
                        // Não retornamos este veículo porque NÃO tem carga... e o parâmetro '$com_carga' está == "S"
                        $retornar_veiculo = false;
                    } else {
                        // Retornamos este veículo mesmo SEM carga porque parâmetro com_carga NÃO é == "S"
                        $retornar_veiculo = true;
                    }
                }

                if ($retornar_veiculo) {

                    //Tipo de Veiculo
                    $veiculo_arr['tipo_veiculo'] = $vei->descr_tipo_veiculo;

                    //Localização do veículo
                    $loc = new ApiUsoComum();
                    $localizacao = $loc->RetornarAreaLocalVeiculo($vei->placa);
                    $veiculo_arr['local_veiculo'] = $localizacao['local_veiculo'];

                    //Dimensões do veículo
                    //Montar string das dimensões. Ex: "3,50 x 2 x 1,80"
                    $veiculo_arr['dimensoes'] = rgFormataDimensoes($vei->comprimento, $vei->largura, $vei->altura);

                    //Ocupação do veículo
                    $veiculo_arr['ocup_veiculo'] = floatval($vei->ocup_veiculo);

                    if (rgIgualTrimNull($vei->img_carga) == false) {
                        $veiculo_arr['img_carga'] = $vei->img_carga;
                    } else {
                        $veiculo_arr['img_carga'] = '';
                    }

                    // Distância e tempo até local coleta
                    if (rgDifZeroNull($cliente_id)) {
                        $cliente = DB::table('cliente')
                            ->where('id', '=', $cliente_id)
                            ->first();

                        if (!empty($cliente)) {
                            $geo_lat_coleta = $cliente->geo_lat;
                            $geo_lng_coleta = $cliente->geo_lng;
                        }
                    }

                    // Distância e tempo da posição do veículo até o local da coleta
                    $distancia    = 0;
                    $tempo_viagem = 0;

                    if ((rgDifZeroNull($vei->geo_lat)) && (rgDifZeroNull($vei->geo_lng))
                        && (rgDifZeroNull($geo_lat_coleta)) && (rgDifZeroNull($geo_lng_coleta))
                    ) {

                        $api = new ApiIntegracao();
                        $retorno = $api->CalcularDistanciaOrigem_Destino(
                            $vei->geo_lat,
                            $vei->geo_lng,
                            $geo_lat_coleta,
                            $geo_lng_coleta
                        );

                        $distancia    = $retorno['distance'];
                        $tempo_viagem = $retorno['duration'];
                    }

                    if ($distancia > 0) {
                        if (($distancia <= $menor_distancia) || ($menor_distancia == 0)) {
                            $menor_distancia = $distancia;
                            $idx_menor_distancia = $idx;
                        }
                        $veiculo_arr['distancia'] = $distancia;
                        $veiculo_arr['distancia_txt'] = rgFormataDistancia($distancia);
                    } else {
                        //Quando não tem distância setamos esta valor para ficar por último na ordenação.
                        $veiculo_arr['distancia'] = 999999999;
                        $veiculo_arr['distancia_txt'] = '-';
                    }

                    if ($tempo_viagem > 0) {
                        if (($tempo_viagem <= $menor_tempo) || ($menor_tempo == 0)) {
                            // Armazenar o tempo em segundos
                            $menor_tempo = $tempo_viagem;
                            $idx_menor_tempo = $idx;
                        }
                    }

                    // Solicitações alocadas para o veículo
                    // A rotina "MontaArrayColetasVeiculoColeta_Frota" é utilizada em duas funções: 
                    //"RetornarVeiculosFrota" e "RetornarVeiculosColeta"
                    $retorno = $this->MontaArrayColetasVeiculoColeta_Frota($coletas_veiculo, $vei);

                    $veiculo_arr['peso_total_coletas'] = $retorno['peso_total_coletas'];
                    $veiculo_arr['hr_prev_saida']      = $retorno['hr_prev_saida'];
                    $veiculo_arr['qtde_coletas']       = $retorno['qtde_coletas'];
                    $veiculo_arr['qtde_solic_and']     = $retorno['qtde_solic_and'];

                    // Agora que o array de veículos está com todas as informações, acrescentamos
                    // no final o "subarray" das Coletas do veículo
                    $veiculo_arr['coletas_veiculo'] = $retorno['array_coletas'];

                    // Previsão chegada ao local de coleta

                    // SE tiver previsão de saída... indica que o veículo tinha chegado em algum local.... SE NÃO TIVER... estava em trânsito

                    if ($veiculo_arr['hr_prev_saida'] <> '') {
                        // Previsão de chegada ao local da coleta... partindo da PREVISÃO DE SAÍDA 
                        // da solicitação que está sendo atendida pelo veículo
                        $hr_prev_chegada = strtotime($veiculo_arr['hr_prev_saida']) + $tempo_viagem;
                    } else {
                        // Previsão de chegada ao local da coleta... partindo da HORA ATUAL... 
                        //porque o veículo não está atendendo a uma solicitação no momento
                        $hr_prev_chegada = strtotime($data_hora_atual) + $tempo_viagem;
                    }

                    // Verificamos SE o veículo vai chegar em tempo no local de coleta
                    // A "$hr_prev_chegada" está em segundos, convertemos e extraimos somente a hora
                    if (date('H:i:s', $hr_prev_chegada) <= $hr_prev_coleta) {
                        $veiculo_arr['tempo_ok'] = 'S';
                    } else {
                        $veiculo_arr['tempo_ok'] = 'N';
                    }

                    // Adiciona no array de veículos as variavéis que serão devolvidas no final                
                    $veiculo_arr['tempo_viagem']    = rgSecondsToTime($tempo_viagem);
                    $veiculo_arr['hr_prev_chegada'] = date('H:i:s', $hr_prev_chegada);

                    $veiculo_arr['menor_distancia'] = $menor_distancia;
                    $veiculo_arr['menor_tempo']     = $menor_tempo;

                    $veiculo_arr_final[$idx] = $this->MontaArrayFinalVeiculosFrota($veiculo_arr, $vei, $data_hora_atual);

                    $idx++;
                }
            }
        }

        // ANTES de devolver o array de veículos para ordenação... vamos setar o veículo mais próximo e o mais rápido. 
        if ($idx_menor_distancia <> null) {
            // Setamos o veículo que está mais próximo do local de coleta
            $veiculo_arr_final[$idx_menor_distancia]['menor_distancia'] = 'S';
        }

        if ($idx_menor_tempo <> null) {
            // Setamos o veículo que vai demorar menos tempo de viagem
            $veiculo_arr_final[$idx_menor_tempo]['menor_tempo'] = 'S';
        }

        return $veiculo_arr_final;
    }


    public function MontaArrayFinalVeiculosFrota($veiculo_arr, $vei, $data_hora_atual)
    {

        $arr_veic['placa']           = $vei->placa;
        $arr_veic['motorista_id']    = $vei->motorista_id;
        $arr_veic['nome_motorista']  = $vei->nome_motorista;

        $arr_veic['tipo_veiculo']    = $veiculo_arr['tipo_veiculo'];
        $arr_veic['dimensoes']       = $veiculo_arr['dimensoes'];

        $arr_veic['consumo']         = $vei->nivel_cons;

        $arr_veic['capacid_peso_kg']    = rgFormataPesoVeiculo($vei->cap_kg);
        $arr_veic['peso_total_coletas'] = rgFormataPesoVeiculo($veiculo_arr['peso_total_coletas']);

        $arr_veic['ocup_veiculo']    = $veiculo_arr['ocup_veiculo'];
        $arr_veic['img_carga']       = $veiculo_arr['img_carga'];

        if (rgIgualTrimNull($veiculo_arr['img_carga']) == false) {
            $arr_veic['url_imagem']  = rgRetornarUrlImagens($veiculo_arr['img_carga']);
        } else {
            $arr_veic['url_imagem']  = '';
        }

        $arr_veic['menor_distancia'] = $veiculo_arr['menor_distancia'];
        $arr_veic['menor_tempo']     = $veiculo_arr['menor_tempo'];

        $arr_veic['distancia']       = $veiculo_arr['distancia'];
        $arr_veic['distancia_txt']   = $veiculo_arr['distancia_txt'];

        $arr_veic['tempo_ok']        = $veiculo_arr['tempo_ok'];

        $arr_veic['tempo_viagem']    = rgRetornaFormataTempoExt($veiculo_arr['tempo_viagem']);
        $arr_veic['hr_prev_chegada'] = $veiculo_arr['hr_prev_chegada'];

        $arr_veic['local_veiculo']   = $veiculo_arr['local_veiculo'];
        $arr_veic['geo_lat']         = $vei->geo_lat;
        $arr_veic['geo_lng']         = $vei->geo_lng;

        if (rgIgualTrimNull($vei->dt_geopos)) {
            $arr_veic['atlz_geopos'] = 'Sem informações';
        } else {
            $arr_veic['atlz_geopos'] = rgRetornaDiferencaDataExt($data_hora_atual, $vei->dt_geopos);
        }

        $arr_veic['ignicao']           = $vei->ignicao;
        $arr_veic['sis_carga_empilha'] = $vei->sis_carga_empilha;
        $arr_veic['sis_carga_ponte']   = $vei->sis_carga_ponte;
        $arr_veic['sis_carga_manual']  = $vei->sis_carga_manual;

        $arr_veic['hr_prev_saida']   = $veiculo_arr['hr_prev_saida'];
        $arr_veic['qtde_coletas']    = $veiculo_arr['qtde_coletas'];
        $arr_veic['qtde_solic_and']  = $veiculo_arr['qtde_solic_and'];
        $arr_veic['coletas_veiculo'] = $veiculo_arr['coletas_veiculo'];

        return $arr_veic;
    }


    public function OrdenaArrayVeicFrotaPorDistancia($veiculos)
    {

        if (count($veiculos) > 0) {

            // Organizar o $array de veiculos pela pontuacao descrecente
            foreach ($veiculos as $key => $row) {
                $arr_distancia[$key] = $row['distancia'];
            }

            array_multisort($arr_distancia, SORT_ASC, $veiculos);
        }

        return $veiculos;
    }


    public function Local_EnviarInstrucaoColeta($coleta_id, $instrucao, $txt_instrucao_info, $placa_baldeacao)
    {

        $retorno = array();
        $retorno['status'] = false;

        $txt_instrucao = $txt_instrucao_info;

        if (!($coleta = Coleta::find($coleta_id))) {
            $retorno['cod_retorno'] = 'E200';
            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            $retorno['msg_retorno']  = str_replace('$coleta_id', $coleta_id, $msg_erro);
        } else {

            if ($instrucao <> '99') {
                $col = new Coleta();
                $descr_instrucao = $col->RetornarDescrInstrucaoColeta($instrucao);

                // É baldeação?
                if ($instrucao == '05') {
                    // Concatenamos as instrucoes digitadas pelo usuário junto com a descricao padrão da
                    // instrução "05-Fazer Baldeação"
                    $txt_instrucao = $descr_instrucao . ' => ' . $placa_baldeacao . ' | ' . $txt_instrucao_info;

                    //Limitamos a instrucao a 255 caracteres no campo "txt_instrucao" para não estourar o campo
                    $txt_instrucao = mb_strimwidth($txt_instrucao, 0, 255);
                } else {
                    $txt_instrucao = $descr_instrucao;
                }
            }

            try {

                // Utilizar o 'model' para fazer a atualização do registro
                $coleta['instrucao']     = $instrucao;
                $coleta['txt_instrucao'] = $txt_instrucao;

                //Aqui atualizamos a placa de baldeacao quando for a instrução 
                if ($instrucao == '05') {
                    $coleta['placa_baldeacao'] = $placa_baldeacao;
                    $coleta['baldeada']        = 'N';
                }

                $coleta['ass_user_id'] = auth()->user()->id;

                $coleta->save();

                $retorno['status'] = true;
                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {
                $retorno['cod_retorno'] = 'E206';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
            }
        }

        return $retorno;
    }


    public function Local_RetornarDadosVeiculoCarga($placa)
    {

        $dados = array();
        $retorno['status'] = false;

        $veiculo = DB::table('veiculo as v')
            ->select(
                'v.placa',
                'v.cap_kg',
                'v.ocup_veiculo',
                'v.img_carga',
                'v.ignicao',
                'v.comprimento',
                'v.largura',
                'v.altura',
                'v.geo_lat',
                'v.geo_lng',
                'tv.descricao as tipo_veiculo',
                'm.nome as nome_motorista'
            )
            ->leftJoin('tipo_veiculo as tv', 'tv.codigo', '=', 'v.cod_tipo_veiculo')
            ->leftJoin('motorista as m', 'm.id', '=', 'v.motorista_id')
            ->where('v.placa', '=', $placa)
            ->first();

        if (empty($veiculo) == false) {

            $dados['placa']          = $veiculo->placa;
            $dados['tipo_veiculo']   = $veiculo->tipo_veiculo;
            $dados['motorista']      = $veiculo->nome_motorista;
            $dados['cap_kg']         = rgFormataPesoVeiculo($veiculo->cap_kg);
            $dados['dimensoes']      = rgFormataDimensoes($veiculo->comprimento, $veiculo->largura, $veiculo->altura);
            $dados['ocup_veiculo']   = $veiculo->ocup_veiculo;

            if (rgIgualTrimNull($veiculo->img_carga) == false) {
                $dados['url_img_carga'] = rgRetornarUrlImagens($veiculo->img_carga);
            } else {
                $dados['url_img_carga'] = '';
            }

            $dados['ignicao']        = $veiculo->ignicao;
            $dados['geo_lat']        = $veiculo->geo_lat;
            $dados['geo_lng']        = $veiculo->geo_lng;

            $arr_totais = $this->RetornarDadosOutrasInfVeiculoCarga($veiculo);

            $dados['qtde_coletas']       = $arr_totais['qtde_coletas'];
            $dados['peso_total_coletas'] = $arr_totais['peso_total_coletas'];
            $dados['peso_restante']      = $arr_totais['peso_restante'];
            $dados['capacid_peso_ok']    = $arr_totais['capacid_peso_ok'];

            $retorno['status']       = true;
        }

        $retorno['dados'] = $dados;

        return $retorno;
    }

    public function RetornarDadosOutrasInfVeiculoCarga($veiculo)
    {

        $veiculo_placa = $veiculo->placa;

        $timezone_app = date_default_timezone_get();
        $data_atual_serv = Carbon::now($timezone_app)->format('Y-m-d');

        $qtde_coletas       = 0;
        $peso_total_coletas = 0;

        // Totalizamos as solicitações alocadas para o veículo => $coletas
        // NÃO testar o campo 'carga_pavilhao'... precisamos somar as coletas ALOCADAS para o veículo.
        $coletas =  DB::table('coleta as col')
            ->select(
                DB::raw('COUNT(col.id) AS qtde_coletas, SUM(col.peso) AS peso_total_coletas')
            )

            // Não pegamos solicitações com DATA DE COLETA futura
            ->where('col.dt_prev_coleta', '<=', $data_atual_serv)

            // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
            // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
            //
            ->where(function ($query) {
                $query->where('col.coleta_fixa', '!=', 'C')
                    ->orWhere(function ($query1) {
                        $query1->where('col.coleta_fixa', '=', 'C')
                            ->where(function ($query2) {
                                $query2->whereNull('col.solic_origem_id')
                                    ->orWhere('col.solic_origem_id', '=', '0');
                            });
                    });
            })

            // COLETAS ou ENTREGAS atribuidas ao veículo
            //
            ->where(function ($query) use ($veiculo_placa) {
                $query->where(function ($query1) use ($veiculo_placa) {
                    $query1->whereIn('col.status', ['C0', 'C1', 'C2', 'C3', 'C4', 'CR'])
                        ->where('col.placa_coleta', '=', $veiculo_placa);
                })->orWhere(function ($query2) use ($veiculo_placa) {
                    $query2->whereIn('col.status', ['E0', 'E1', 'E2', 'E3', 'E4', 'EN', 'EP'])
                        ->where('col.placa_entrega', '=', $veiculo_placa);
                });
            })
            ->first();

        if (empty($coletas == false)) {
            $qtde_coletas  = $coletas->qtde_coletas;
            $peso_total_coletas = $coletas->peso_total_coletas;
        }

        $arr_totais = $this->MontaArrayTotaisSolicVeiculo($veiculo, $peso_total_coletas, $qtde_coletas);

        return $arr_totais;
    }


    public function Local_RetornarEntregasPendentesCarga($placa)
    {

        $array_retorno = array();

        $veiculo = DB::table('veiculo as v')
            ->select(
                'v.comprimento',
                'v.largura',
                'v.altura',
                'v.cap_kg'
            )
            ->where('v.placa', '=', $placa)
            ->first();

        if (empty($veiculo) == false) {
            $array_retorno = $this->ProcEntregasPendentesCarga($veiculo, $placa);
        }

        if (empty($array_retorno) == false) {
            $retorno['status'] = true;
        } else {
            $retorno['status'] = false;
        }

        $retorno['dados'] = $array_retorno;

        return $retorno;
    }


    public function ProcEntregasPendentesCarga($veiculo, $placa)
    {

        $array_col_pend = array();

        $timezone_app = date_default_timezone_get();
        $data_atual_serv = Carbon::now($timezone_app)->format('Y-m-d');

        // Acumular o peso das solicitações já alocadas para o veículo
        $peso_total_coletas = $this->RetornarPesoTotalColetas($placa, $data_atual_serv);

        // Selecionar entregas pendentes da carga
        $coletas = $this->SelecionaEntregasPendentesCarga($data_atual_serv);

        if (count($coletas) > 0) {

            $ind = 0;

            // Para cada elemento de $coletas => $col
            foreach ($coletas as $col) {

                // Dimensões da carga
                // Dimensões da carga X dimensões do veículo
                if (($col->comp_carga <= $veiculo->comprimento) && ($col->larg_carga <= $veiculo->largura) && ($col->alt_carga <= $veiculo->altura)
                ) {
                    $dimensoes_ok = 'S';
                } else {
                    $dimensoes_ok = 'A';
                }

                // Peso da solicitação atual + Peso das coletas alocadas para o veículo x Capacidade do veículo em KG
                if (($col->peso + $peso_total_coletas) <= $veiculo->cap_kg) {
                    $capacid_peso_ok = 'S';
                } else {
                    $capacid_peso_ok = 'N';
                }

                $array_col_pend[$ind] = $this->MontaArrayEntregasPendentesCarga($col, $dimensoes_ok, $capacid_peso_ok);

                $ind++;
            }
        }

        return $array_col_pend;
    }


    public function RetornarPesoTotalColetas($placa, $data_atual_serv)
    {
        $peso_total_coletas = 0;

        // Acumular o peso das solicitações já alocadas para o veículo. 
        // NÃO testar o campo 'carga_pavilhao'... precisamos somar as coletas ALOCADAS para o veículo.

        $coletas_veiculo = DB::table('coleta')
            ->select(DB::raw('sum(peso) AS peso_total'))
            // Não pegamos solicitações com DATA DE COLETA futura
            ->where('dt_prev_coleta', '<=', $data_atual_serv)

            // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
            // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
            //
            ->where(function ($query) {
                $query->where('coleta_fixa', '!=', 'C')
                    ->orWhere(function ($query1) {
                        $query1->where('coleta_fixa', '=', 'C')
                            ->where(function ($query2) {
                                $query2->whereNull('solic_origem_id')
                                    ->orWhere('solic_origem_id', '=', '0');
                            });
                    });
            })

            // Coletas e Entregas que ainda estão com o veículo. Desconsideramos: "CN" e "ER".
            ->where(function ($query) use ($placa) {
                $query->where(function ($query1) use ($placa) {
                    $query1->where('placa_coleta', '=', $placa)
                        ->whereIn('status', ['C0', 'C1', 'C2', 'C3', 'C4', 'CR']);
                })->orWhere(function ($query2) use ($placa) {
                    $query2->where('placa_entrega', '=', $placa)
                        ->whereIn('status', ['E0', 'E1', 'E2', 'E3', 'E4', 'EN', 'EP']);
                });
            })
            ->first();

        if (empty($coletas_veiculo) == false) {
            $peso_total_coletas = $coletas_veiculo->peso_total;
        }

        return $peso_total_coletas;
    }


    public function SelecionaEntregasPendentesCarga($data_atual_serv)
    {
        // Verificar as entregas pendentes com carga_pavilhao = 'S' (descarregadas no pavilhão).
        // Desconsideramos coletas fixas do tipo CONTRATO e COMANDAS.

        $coletas = DB::table('coleta as col')
            ->select(
                'col.id',
                'col.numero',
                'col.coleta_fixa',
                'col.placa_coleta',
                'col.dt_efet_coleta',
                'col.hr_sai_coleta',
                'col.placa_entrega',
                'col.dt_prev_entrega',
                'col.hr_prev_entrega',
                'col.dt_efet_entrega',
                'col.hr_sai_entrega',
                'col.entrega_urgente',
                'col.peso',
                'col.volumes',
                'col.especie',
                'col.comp_carga',
                'col.larg_carga',
                'col.alt_carga',
                'col.status',
                'col.data_cad',
                'col.reentrega',

                'cli.nome AS nome_cliente',
                'lc.nome AS local_coleta',

                'le.codigo AS cod_local_entrega',
                'le.nome AS local_entrega',
                'le.endereco AS ender_entrega',
                'le.bairro AS bairro_entrega',
                'le.cidade AS cidade_entrega',
                'le.cep AS cep_entrega',
                'le.uf AS uf_entrega',
                'le.fone AS fone_entrega',
                'le.nome AS local_entrega',
                'le.hr_ini_entrega_man',
                'le.hr_fim_entrega_man',
                'le.hr_ini_entrega_tar',
                'le.hr_fim_entrega_tar',
                'le.geo_lat AS geo_lat_entrega',
                'le.geo_lng AS geo_lng_entrega'
            )

            ->leftjoin('cliente as cli', function ($join) {
                $join->on('cli.codigo', '=', 'col.cod_cliente')
                    ->on('cli.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as lc', function ($join) {
                $join->on('lc.codigo', '=', 'col.cod_loc_coleta')
                    ->on('lc.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as le', function ($join) {
                $join->on('le.codigo', '=', 'col.cod_loc_entrega')
                    ->on('le.empresa', '=', 'col.empresa');
            })

            // Não pegamos solicitações com DATA DE COLETA futura
            ->where('col.dt_prev_coleta', '<=', $data_atual_serv)

            // Desconsideramos solicitações CONTRATO e COMANDAS ("C"):  este tipo de solicitação NÃO
            // fica com carga_pavilhao = 'S' => colocamos a condição para maior clareza. 
            //
            // Desconsideramos solicitações MULTI-DESTINOS ORIGEM:  não podemos mostrar a solicitação 
            // ORIGEM com carga_pavilhao = 'S' para não confundir o usuário... porque teremos as solicitações 
            // AUXILIARES em aberto. 
            // 
            // Consideramos as solicitações MULTI-DESTINOS AUXILIARES... porque quando são removidas 
            // da carga na fase de ENTREGA... ficam com carga_pavilhao = 'S' ... então o usuário pode atribuir 
            // para outro veículo. 
            //
            // Aqui... não interessa se a solicitação tem ou não uma placa_entrega definida, SE foi descarregada
            // no pavilhão... a solicitção deve ser considerada. 
            //
            ->where('col.carga_pavilhao', '=', 'S')
            ->where(function ($query) {
                $query->whereNotIn('col.coleta_fixa', ['C', 'M'])
                    ->orwhere(function ($query1) {
                        $query1->where('coleta_fixa', '=', 'M')
                            ->where(function ($query2) {
                                $query2->whereNotNull('col.solic_origem_id')
                                    ->where('col.solic_origem_id', '!=', '0');
                            });
                    });
            })
            ->orderBy('col.dt_prev_entrega', 'asc')
            ->orderBy('col.hr_prev_entrega', 'asc')
            ->get();

        return $coletas;
    }


    public function MontaArrayEntregasPendentesCarga($col, $dimensoes_ok, $capacid_peso_ok)
    {
        $dados['coleta_id']    = $col->id;
        $dados['numero']       = $col->numero;
        $dados['coleta_fixa']  = $col->coleta_fixa;
        $dados['nome_cliente'] = $col->nome_cliente;

        $dados['local_coleta'] = $col->local_coleta;

        $dados['cod_local_entrega'] = $col->cod_local_entrega;
        $dados['local_entrega']     = $col->local_entrega;
        $dados['ender_entrega']     = $col->ender_entrega;
        $dados['bairro_entrega']    = $col->bairro_entrega;
        $dados['cidade_entrega']    = $col->cidade_entrega;
        $dados['cep_entrega']       = $col->cep_entrega;
        $dados['uf_entrega']        = $col->uf_entrega;
        $dados['fone_entrega']      = $col->fone_entrega;

        $dados['geo_lat_entrega']   = $col->geo_lat_entrega;
        $dados['geo_lng_entrega']   = $col->geo_lng_entrega;

        $dados['exped_entrega_man'] =
            rgFormataHorarioExpediente($col->hr_ini_entrega_man, $col->hr_fim_entrega_man);
        $dados['exped_entrega_tar'] =
            rgFormataHorarioExpediente($col->hr_ini_entrega_tar, $col->hr_fim_entrega_tar);

        $dados['placa_coleta']   = $col->placa_coleta;
        $dados['dt_efet_coleta'] = $col->dt_efet_coleta;
        $dados['hr_sai_coleta']  = $col->hr_sai_coleta;

        $dados['placa_entrega']   = $col->placa_entrega;
        $dados['dt_efet_entrega'] = $col->dt_efet_entrega;
        $dados['hr_sai_entrega']  = $col->hr_sai_entrega;

        $dados['dt_prev_entrega'] = $col->dt_prev_entrega;
        $dados['hr_prev_entrega'] = $col->hr_prev_entrega;
        $dados['entrega_urgente'] = $col->entrega_urgente;

        $dados['data_cad']        = $col->data_cad;
        $dados['status']          = $col->status;
        $dados['reentrega']       = $col->reentrega;

        $dados['capacid_peso_ok'] = $capacid_peso_ok;

        $dados['vol_carga'] = $col->volumes . ' ' .
            $col->especie . ' ' . rgFormataPesoVeiculo($col->peso);
        $dados['dimensoes_ok'] = $dimensoes_ok;
        $dados['dim_carga']    = rgFormataDimensoes($col->comp_carga, $col->larg_carga, $col->alt_carga);

        return $dados;
    }


    public function Local_RetornarColetasVeiculoCarga($placa, $local_saida, $hora_saida)
    {

        $array_retorno['coletas'] = array();
        $array_retorno['carga']   = array();

        $timezone_app = date_default_timezone_get();
        $data_hora_atual_serv = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

        $data_atual_serv = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_atual_serv)->format('Y-m-d');

        // Deixar a hora com hh:mm:ss
        $hora_saida = Carbon::createFromFormat('H:i', $hora_saida)->format('H:i:s');

        // Montar data + hora:  Ex: '2020-05-07 16:45:00'
        $dt_hr_saida_inicial = $data_atual_serv . ' ' . $hora_saida;
        $dt_hr_saida_inicial = Carbon::createFromFormat('Y-m-d H:i:s', $dt_hr_saida_inicial);

        // A primeira vez atribuimos a data+hora saída inicial
        $dt_hr_prev_saida = $dt_hr_saida_inicial;

        $geo_lat_origem = 0;
        $geo_lng_origem = 0;

        // Ler registro de SYS_CFG  => $sys_cfg
        $sys_cfg = DB::table('sys_cfg')
            ->select('geo_lat_pavilion', 'geo_lng_pavilion')
            ->first();

        if (empty($sys_cfg) == false) {
            $geo_lat_origem = $sys_cfg->geo_lat_pavilion;
            $geo_lng_origem = $sys_cfg->geo_lng_pavilion;
        }

        $veiculo = DB::table('veiculo AS v')
            ->join('tipo_veiculo AS tv', 'tv.codigo', '=', 'v.cod_tipo_veiculo')
            ->select(
                'v.comprimento',
                'v.largura',
                'v.altura',
                'v.cap_kg',
                'v.placa',
                'v.geo_lat',
                'v.geo_lng',
                'v.dur_atend_atual',
                'tv.dur_prev_atend'
            )
            ->where('v.placa', '=', $placa)
            ->first();

        if (empty($veiculo) == false) {

            // Carregamos a duração padrão de atendimento para este TIPO DE VEÍCULO.
            // Serve para maior precisão no cálculo das previsões de saída dos pontos da rota.
            $dur_atend_padrao = $veiculo->dur_prev_atend;

            // "P" => Pavilhão   "V" => Veículo (posição)
            if ($local_saida <> 'P') {

                // Local de saída => Posição atual do veículo
                $geo_lat_origem = $veiculo->geo_lat;
                $geo_lng_origem = $veiculo->geo_lng;

                // Da posição do veículo assume a hora atual
                //
                $dt_hr_prev_saida = $data_hora_atual_serv;
            }

            $array_retorno = $this->ProcSolicEmAndamento(
                $geo_lat_origem,
                $geo_lng_origem,
                $veiculo,
                $placa,
                $dt_hr_saida_inicial,
                $dur_atend_padrao,
                $dt_hr_prev_saida,
                $data_atual_serv,
                $data_hora_atual_serv
            );
        }

        if (empty($array_retorno['coletas']) == false) {
            $retorno['status'] = true;
        } else {
            $retorno['status'] = false;
        }

        $retorno['dados']['coletas'] = $array_retorno['coletas'];
        $retorno['dados']['carga']   = $array_retorno['carga'];

        return $retorno;
    }


    public function ProcSolicEmAndamento(
        $geo_lat_origem,
        $geo_lng_origem,
        $veiculo,
        $placa,
        $dt_hr_saida_inicial,
        $dur_atend_padrao,
        $dt_hr_prev_saida,
        $data_atual_serv,
        $data_hora_atual_serv
    ) {

        $array_coletas = array();
        $array_carga   = array();

        // Inicializamos aqui para NÃO dar erro quando as coordenadas de destino da primeira 
        // solicitação do veículo for EXATAMENTE igual à posição atual do veículo (rastreador) 
        // 
        $dt_hr_prev_chegada = $dt_hr_prev_saida;

        // -----------------------------------------------------------------
        // Processamento da solicitacao Atual do veiculo
        // -----------------------------------------------------------------
        $ret_solic_atu = $this->ProcessarSolicitacoesEmAndamento(
            $geo_lat_origem,
            $geo_lng_origem,
            $veiculo,
            $placa,
            $data_hora_atual_serv,
            $dt_hr_prev_saida,
            $dur_atend_padrao,
            $dt_hr_saida_inicial,
            $dt_hr_prev_chegada
        );

        if (empty($ret_solic_atu['array_coletas']) == false) {

            // Atualizamos a previsão de saída, com a previsão de saída da solicitação atual
            $dt_hr_prev_saida = $ret_solic_atu['dt_hr_prev_saida'];
            $dt_hr_prev_chegada = $ret_solic_atu['dt_hr_prev_chegada'];

            $array_coletas = array_merge($array_coletas, $ret_solic_atu['array_coletas']);
        }

        // -----------------------------------------------------------------
        // Processamento das Solicitações pendentes do veículo (ROTA)
        // -----------------------------------------------------------------

        $ret_outras_solic = $this->ProcessaSolicPendentesVeiculoRota(
            $veiculo,
            $geo_lat_origem,
            $geo_lng_origem,
            $dt_hr_prev_saida,
            $data_atual_serv,
            $dur_atend_padrao,
            $dt_hr_saida_inicial,
            $dt_hr_prev_chegada
        );

        if (empty($ret_outras_solic['array_coletas']) == false) {
            $array_coletas = array_merge($array_coletas, $ret_outras_solic['array_coletas']);
        }

        // -----------------------------------------------------------------
        // Processamento das Coletas realizadas do veículo (CARGA)
        // -----------------------------------------------------------------

        $ret_solic_carga = $this->ProcessaColetasRealizadasVeiculoCarga($veiculo->placa);

        if (empty($ret_solic_carga['array_carga']) == false) {
            $array_carga = $ret_solic_carga['array_carga'];
        }

        $retorno['coletas'] = $array_coletas;
        $retorno['carga']   = $array_carga;

        return $retorno;
    }


    public function ProcessarSolicitacoesEmAndamento(
        $geo_lat_origem,
        $geo_lng_origem,
        $veiculo,
        $placa,
        $data_hora_atual_serv,
        $dt_hr_prev_saida,
        $dur_atend_padrao,
        $dt_hr_saida_inicial,
        $dt_hr_prev_chegada
    ) {

        $array_coletas = array();

        $solic_and = $this->SelecionarSolicitacoesEmAndamento($data_hora_atual_serv, $placa);

        if (count($solic_and) > 0) {

            $arr_retorno = $this->ProcessarColetasVeiculoCarga(
                $solic_and,
                $veiculo,
                $dt_hr_prev_saida,
                $geo_lat_origem,
                $geo_lng_origem,
                $dt_hr_saida_inicial,
                $dur_atend_padrao,
                $dt_hr_prev_chegada
            );

            $array_coletas = $arr_retorno['array_coletas'];

            // Retornarmos a data e a hora da saida calculada na solicitacao atual.
            // Esta é a nova previsão de saída que deverá ser considerada nas demais solicitacoes do veiculo        
            $dt_hr_prev_saida   = $arr_retorno['dt_hr_prev_saida'];
            $dt_hr_prev_chegada = $arr_retorno['dt_hr_prev_chegada'];
        }

        $arr_retorno['array_coletas']      = $array_coletas;
        $arr_retorno['dt_hr_prev_saida']   = $dt_hr_prev_saida;
        $arr_retorno['dt_hr_prev_chegada'] = $dt_hr_prev_chegada;

        return $arr_retorno;
    }


    public function SelecionarSolicitacoesEmAndamento($data_atual_serv, $placa)
    {

        // Solicitações em ANDAMENTO com status 'C3' / 'E3' (chegada) ou 'C4' e 'E4' (atendimento iniciado):
        //
        // - 'C3' e 'E3': Pode haver várias solicitações com esses status.. porém, para o MESMO LOCAL. 
        //
        // - 'C4' e 'E4': Pode haver APENAS UMA solicitação por vez. As API´s que setam o status para 'C4' ou 'E4' 
        //   devem garantir essa condição.
        //
        $solic_and = DB::table('coleta as col')
            ->select(
                'col.*',
                'col.id AS coleta_id',
                'lc.*',
                'le.*',
                'col.id AS coleta_id',
                'col.dt_prev_coleta AS dt_prev_col_ent',
                'col.hr_prev_coleta AS hr_prev_col_ent',

                'cli.nome AS nome_cliente',

                'lc.nome AS local_coleta',
                'lc.geo_lat AS geo_lat_coleta',
                'lc.geo_lng AS geo_lng_coleta',

                'lc.endereco AS endereco_coleta',
                'lc.bairro AS bairro_coleta',
                'lc.cidade AS cidade_coleta',
                'lc.cep AS cep_coleta',
                'lc.uf AS uf_coleta',
                'lc.fone AS fone_coleta',

                'lc.hr_ini_coleta_man As hr_ini_coleta_man_coleta',
                'lc.hr_fim_coleta_man As hr_fim_coleta_man_coleta',
                'lc.hr_ini_coleta_tar As hr_ini_coleta_tar_coleta',
                'lc.hr_fim_coleta_tar As hr_fim_coleta_tar_coleta',

                'le.nome as local_entrega',
                'le.geo_lat AS geo_lat_entrega',
                'le.geo_lng AS geo_lng_entrega',

                'le.endereco AS endereco_entrega',
                'le.bairro AS bairro_entrega',
                'le.cidade AS cidade_entrega',
                'le.cep AS cep_entrega',
                'le.uf AS uf_entrega',
                'le.fone AS fone_entrega',

                'le.hr_ini_entrega_man As hr_ini_entrega_man_entrega',
                'le.hr_fim_entrega_man As hr_fim_entrega_man_entrega',
                'le.hr_ini_entrega_tar As hr_ini_entrega_tar_entrega',
                'le.hr_fim_entrega_tar As hr_fim_entrega_tar_entrega'
            )

            ->leftjoin('cliente as cli', function ($join) {
                $join->on('cli.codigo', '=', 'col.cod_cliente')
                    ->on('cli.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as lc', function ($join) {
                $join->on('lc.codigo', '=', 'col.cod_loc_coleta')
                    ->on('lc.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as le', function ($join) {
                $join->on('le.codigo', '=', 'col.cod_loc_entrega')
                    ->on('le.empresa', '=', 'col.empresa');
            })

            // Não pegamos solicitações com DATA DE COLETA futura
            ->where('col.dt_prev_coleta', '<=', $data_atual_serv)

            // Aqui pegamos somente solicitações na fase de COLETA (inclusive 'CONTRATOS').
            //
            // Desconsideramos status 'CR' porque deve constar como CARGA... SE (carga_pavilhao <> 'S')
            // Desconsideramos status 'CN': porque a carga NÃO está com o veículo. 
            //
            ->whereIn('col.status', ['C3', 'C4'])

            // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
            // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
            //
            ->where(function ($query) {
                $query->where('col.coleta_fixa', '!=', 'C')
                    ->orWhere(function ($query1) {
                        $query1->where('col.coleta_fixa', '=', 'C')
                            ->where(function ($query2) {
                                $query2->whereNull('col.solic_origem_id')
                                    ->orWhere('col.solic_origem_id', '=', '0');
                            });
                    });
            })

            // Para qualquer situação, a carga NÃO pode ter sido descarregada            
            ->where(function ($query) {
                $query->whereNull('col.carga_pavilhao')
                    ->orWhere('col.carga_pavilhao', '!=', 'S');
            })

            // COLETAS atribuidas ao veículo
            ->where('col.placa_coleta', '=',  $placa);

        //  ------------------------------
        //       U N I O N    A L L
        //  -------------------------------

        $solic_and_union = DB::table('coleta as col')
            ->select(
                'col.*',
                'col.id AS coleta_id',
                'lc.*',
                'le.*',
                'col.id AS coleta_id',
                'col.dt_prev_entrega AS dt_prev_col_ent',
                'col.hr_prev_entrega AS hr_prev_col_ent',

                'cli.nome AS nome_cliente',

                'lc.nome AS local_coleta',
                'lc.geo_lat AS geo_lat_coleta',
                'lc.geo_lng AS geo_lng_coleta',

                'lc.endereco AS endereco_coleta',
                'lc.bairro AS bairro_coleta',
                'lc.cidade AS cidade_coleta',
                'lc.cep AS cep_coleta',
                'lc.uf AS uf_coleta',
                'lc.fone AS fone_coleta',

                'lc.hr_ini_coleta_man As hr_ini_coleta_man_coleta',
                'lc.hr_fim_coleta_man As hr_fim_coleta_man_coleta',
                'lc.hr_ini_coleta_tar As hr_ini_coleta_tar_coleta',
                'lc.hr_fim_coleta_tar As hr_fim_coleta_tar_coleta',

                'le.nome as local_entrega',
                'le.geo_lat AS geo_lat_entrega',
                'le.geo_lng AS geo_lng_entrega',

                'le.endereco AS endereco_entrega',
                'le.bairro AS bairro_entrega',
                'le.cidade AS cidade_entrega',
                'le.cep AS cep_entrega',
                'le.uf AS uf_entrega',
                'le.fone AS fone_entrega',

                'le.hr_ini_entrega_man As hr_ini_entrega_man_entrega',
                'le.hr_fim_entrega_man As hr_fim_entrega_man_entrega',
                'le.hr_ini_entrega_tar As hr_ini_entrega_tar_entrega',
                'le.hr_fim_entrega_tar As hr_fim_entrega_tar_entrega'

            )

            ->leftjoin('cliente as cli', function ($join) {
                $join->on('cli.codigo', '=', 'col.cod_cliente')
                    ->on('cli.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as lc', function ($join) {
                $join->on('lc.codigo', '=', 'col.cod_loc_coleta')
                    ->on('lc.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as le', function ($join) {
                $join->on('le.codigo', '=', 'col.cod_loc_entrega')
                    ->on('le.empresa', '=', 'col.empresa');
            })

            // Não pegamos solicitações com DATA DE COLETA futura
            ->where('col.dt_prev_coleta', '<=', $data_atual_serv)

            // Aqui pegamos somente solicitações na fase de ENTREGA. 
            // Desconsideramos o status de entrega 'ER' - Entrega realizada.
            //
            ->whereIn('col.status', ['E3', 'E4'])

            // NÃO existirão solicitações do tipo CONTRATO em fase de ENTREGA, mesmo assim, 
            // mantivemos o teste padrão para facilitar a manutenção do código.
            //
            // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
            // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
            //            
            ->where(function ($query) {
                $query->where('col.coleta_fixa', '!=', 'C')
                    ->orWhere(function ($query1) {
                        $query1->where('col.coleta_fixa', '=', 'C')
                            ->where(function ($query2) {
                                $query2->whereNull('col.solic_origem_id')
                                    ->orWhere('col.solic_origem_id', '=', '0');
                            });
                    });
            })

            // Para qualquer situação, a carga NÃO pode ter sido descarregada            
            ->where(function ($query) {
                $query->whereNull('col.carga_pavilhao')
                    ->orWhere('col.carga_pavilhao', '!=', 'S');
            })

            // ENTREGAS atribuidas ao veículo
            ->where('col.placa_entrega', '=',  $placa)

            // Abaixo o comando "union" fará a junção dos dois selects que estará no objeto "$solic_and_union"
            ->union($solic_and)

            ->orderBy('seq_atend', 'asc')
            ->orderBy('dt_prev_col_ent', 'asc')
            ->orderBy('hr_prev_col_ent', 'asc')
            ->get();

        return $solic_and_union;
    }


    public function ProcessaSolicPendentesVeiculoRota(
        $veiculo,
        $geo_lat_origem,
        $geo_lng_origem,
        $dt_hr_prev_saida,
        $data_atual_serv,
        $dur_atend_padrao,
        $dt_hr_saida_inicial,
        $dt_hr_prev_chegada
    ) {

        $array_coletas = array();

        $coletas = $this->SelOutrasSolicAlocadasVeiculo($data_atual_serv, $veiculo->placa);

        if (count($coletas) > 0) {

            $arr_retorno = $this->ProcessarColetasVeiculoCarga(
                $coletas,
                $veiculo,
                $dt_hr_prev_saida,
                $geo_lat_origem,
                $geo_lng_origem,
                $dt_hr_saida_inicial,
                $dur_atend_padrao,
                $dt_hr_prev_chegada
            );

            $array_coletas = $arr_retorno['array_coletas'];
        }

        $retorno['array_coletas'] = $array_coletas;

        return $retorno;
    }


    public function SelOutrasSolicAlocadasVeiculo($data_atual_serv, $veiculo_placa)
    {
        // Aqui pegamos as solicitações de COLETA e ENTREGA alocadas para o veículo

        // SELECT da primeira parte do UNION
        $coletas_aux = DB::table('coleta as col')
            ->select(
                'col.*',
                'lc.*',
                'le.*',
                'col.id AS coleta_id',
                'cli.nome AS nome_cliente',
                'col.dt_prev_coleta AS dt_prev_col_ent',
                'col.hr_prev_coleta AS hr_prev_col_ent',

                'lc.nome AS local_coleta',
                'lc.geo_lat AS geo_lat_coleta',
                'lc.geo_lng AS geo_lng_coleta',

                'lc.endereco AS endereco_coleta',
                'lc.bairro AS bairro_coleta',
                'lc.cidade AS cidade_coleta',
                'lc.cep AS cep_coleta',
                'lc.uf AS uf_coleta',
                'lc.fone AS fone_coleta',

                'lc.hr_ini_coleta_man As hr_ini_coleta_man_coleta',
                'lc.hr_fim_coleta_man As hr_fim_coleta_man_coleta',
                'lc.hr_ini_coleta_tar As hr_ini_coleta_tar_coleta',
                'lc.hr_fim_coleta_tar As hr_fim_coleta_tar_coleta',

                'le.nome as local_entrega',
                'le.geo_lat AS geo_lat_entrega',
                'le.geo_lng AS geo_lng_entrega',

                'le.endereco AS endereco_entrega',
                'le.bairro AS bairro_entrega',
                'le.cidade AS cidade_entrega',
                'le.cep AS cep_entrega',
                'le.uf AS uf_entrega',
                'le.fone AS fone_entrega',

                'le.hr_ini_entrega_man As hr_ini_entrega_man_entrega',
                'le.hr_fim_entrega_man As hr_fim_entrega_man_entrega',
                'le.hr_ini_entrega_tar As hr_ini_entrega_tar_entrega',
                'le.hr_fim_entrega_tar As hr_fim_entrega_tar_entrega'
            )

            ->leftjoin('cliente as cli', function ($join) {
                $join->on('cli.codigo', '=', 'col.cod_cliente')
                    ->on('cli.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as lc', function ($join) {
                $join->on('lc.codigo', '=', 'col.cod_loc_coleta')
                    ->on('lc.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as le', function ($join) {
                $join->on('le.codigo', '=', 'col.cod_loc_entrega')
                    ->on('le.empresa', '=', 'col.empresa');
            })

            // Não pegamos solicitações com DATA DE COLETA futura
            ->where('col.dt_prev_coleta', '<=', $data_atual_serv)

            // Aqui pegamos somente solicitações na fase de COLETA (inclusive 'CONTRATOS').
            //
            // Desconsideramos status 'CR' porque deve constar como CARGA... SE (carga_pavilhao <> 'S')
            // Desconsideramos status 'CN': porque a carga NÃO está com o veículo. 
            //
            ->whereIn('col.status', ['C0', 'C1', 'C2'])

            // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
            // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
            //
            ->where(function ($query) {
                $query->where('col.coleta_fixa', '!=', 'C')
                    ->orWhere(function ($query1) {
                        $query1->where('col.coleta_fixa', '=', 'C')
                            ->where(function ($query2) {
                                $query2->whereNull('col.solic_origem_id')
                                    ->orWhere('col.solic_origem_id', '=', '0');
                            });
                    });
            })

            // Para qualquer situação, a carga NÃO pode ter sido descarregada            
            ->where(function ($query) {
                $query->whereNull('col.carga_pavilhao')
                    ->orWhere('col.carga_pavilhao', '!=', 'S');
            })

            // COLETAS atribuidas ao veículo
            ->where('col.placa_coleta', '=', $veiculo_placa);

        // SELECT da segunda parte do UNION

        $coletas = DB::table('coleta as col')
            ->select(
                'col.*',
                'lc.*',
                'le.*',
                'col.id AS coleta_id',
                'cli.nome AS nome_cliente',
                'col.dt_prev_entrega AS dt_prev_col_ent',
                'col.hr_prev_entrega AS hr_prev_col_ent',

                'lc.nome AS local_coleta',
                'lc.geo_lat AS geo_lat_coleta',
                'lc.geo_lng AS geo_lng_coleta',

                'lc.endereco AS endereco_coleta',
                'lc.bairro AS bairro_coleta',
                'lc.cidade AS cidade_coleta',
                'lc.cep AS cep_coleta',
                'lc.uf AS uf_coleta',
                'lc.fone AS fone_coleta',

                'lc.hr_ini_coleta_man As hr_ini_coleta_man_coleta',
                'lc.hr_fim_coleta_man As hr_fim_coleta_man_coleta',
                'lc.hr_ini_coleta_tar As hr_ini_coleta_tar_coleta',
                'lc.hr_fim_coleta_tar As hr_fim_coleta_tar_coleta',

                'le.nome as local_entrega',
                'le.geo_lat AS geo_lat_entrega',
                'le.geo_lng AS geo_lng_entrega',

                'le.endereco AS endereco_entrega',
                'le.bairro AS bairro_entrega',
                'le.cidade AS cidade_entrega',
                'le.cep AS cep_entrega',
                'le.uf AS uf_entrega',
                'le.fone AS fone_entrega',

                'le.hr_ini_entrega_man As hr_ini_entrega_man_entrega',
                'le.hr_fim_entrega_man As hr_fim_entrega_man_entrega',
                'le.hr_ini_entrega_tar As hr_ini_entrega_tar_entrega',
                'le.hr_fim_entrega_tar As hr_fim_entrega_tar_entrega'
            )
            ->leftjoin('cliente as cli', function ($join) {
                $join->on('cli.codigo', '=', 'col.cod_cliente')
                    ->on('cli.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as lc', function ($join) {
                $join->on('lc.codigo', '=', 'col.cod_loc_coleta')
                    ->on('lc.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as le', function ($join) {
                $join->on('le.codigo', '=', 'col.cod_loc_entrega')
                    ->on('le.empresa', '=', 'col.empresa');
            })

            // Não pegamos solicitações com DATA DE COLETA futura
            ->where('col.dt_prev_coleta', '<=', $data_atual_serv)

            // Aqui pegamos somente solicitações na fase de ENTREGA. 
            // Desconsideramos o status de entrega 'ER' - Entrega realizada.
            //
            ->whereIn('col.status', ['E0', 'E1', 'E2'])

            // NÃO existirão solicitações do tipo CONTRATO em fase de ENTREGA, mesmo assim, 
            // mantivemos o teste padrão para facilitar a manutenção do código.
            //
            // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
            // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
            //
            ->where(function ($query) {
                $query->where('col.coleta_fixa', '!=', 'C')
                    ->orWhere(function ($query1) {
                        $query1->where('col.coleta_fixa', '=', 'C')
                            ->where(function ($query2) {
                                $query2->whereNull('col.solic_origem_id')
                                    ->orWhere('col.solic_origem_id', '=', '0');
                            });
                    });
            })

            // Para qualquer situação, a carga NÃO pode ter sido descarregada            
            ->where(function ($query) {
                $query->whereNull('col.carga_pavilhao')
                    ->orWhere('col.carga_pavilhao', '!=', 'S');
            })

            // ENTREGAS atribuidas ao veículo
            ->where('col.placa_entrega', '=', $veiculo_placa)

            // Faz o segundo select nas entregas UNINDO com as coletas e ordenando o resultado
            ->union($coletas_aux)
            ->orderBy('seq_atend', 'asc')
            ->orderBy('dt_prev_col_ent', 'asc')
            ->orderBy('hr_prev_col_ent', 'asc')
            ->get();

        return $coletas;
    }

    public function ProcessarColetasVeiculoCarga(
        $solic_and,
        $veiculo,
        $dt_hr_prev_saida,
        $geo_lat_origem,
        $geo_lng_origem,
        $dt_hr_saida_inicial,
        $dur_atend_padrao,
        $dt_hr_prev_chegada
    ) {

        $ind = 0;

        $timezone_app = date_default_timezone_get();
        $data_hora_atual_serv = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

        foreach ($solic_and as $col) {

            // Define a etapa em que a solicitação está
            if (substr($col->status, 0, 1) == 'C') {

                $etapa = 'Coleta';

                $cod_destino = $col->cod_loc_coleta;
                $destino     = $col->local_coleta;
                $placa       = $col->placa_coleta;

                $ender_destino  = $col->endereco_coleta;
                $bairro_destino = $col->bairro_coleta;
                $cidade_destino = $col->cidade_coleta;
                $cep_destino    = $col->cep_coleta;
                $uf_destino     = $col->uf_coleta;
                $fone_destino   = $col->fone_coleta;

                $geo_lat_destino = $col->geo_lat_coleta;
                $geo_lng_destino = $col->geo_lng_coleta;

                $hr_ini_manha = $col->hr_ini_coleta_man_coleta;
                $hr_fim_manha = $col->hr_fim_coleta_man_coleta;

                $hr_ini_tarde = $col->hr_ini_coleta_tar_coleta;
                $hr_fim_tarde = $col->hr_fim_coleta_tar_coleta;
            } else {

                $etapa = 'Entrega';

                $cod_destino = $col->cod_loc_entrega;
                $destino     = $col->local_entrega;
                $placa       = $col->placa_entrega;

                $ender_destino  = $col->endereco_entrega;
                $bairro_destino = $col->bairro_entrega;
                $cidade_destino = $col->cidade_entrega;
                $cep_destino    = $col->cep_entrega;
                $uf_destino     = $col->uf_entrega;
                $fone_destino   = $col->fone_entrega;

                $geo_lat_destino = $col->geo_lat_entrega;
                $geo_lng_destino = $col->geo_lng_entrega;

                $hr_ini_manha = $col->hr_ini_entrega_man_entrega;
                $hr_fim_manha = $col->hr_fim_entrega_man_entrega;

                $hr_ini_tarde = $col->hr_ini_entrega_tar_entrega;
                $hr_fim_tarde = $col->hr_fim_entrega_tar_entrega;
            }

            // Dimensões da carga X dimensões do veículo
            if (($col->comp_carga <= $veiculo->comprimento) && ($col->larg_carga <= $veiculo->largura) && ($col->alt_carga <= $veiculo->altura)
            ) {
                $dimensoes_ok = 'S';
            } else {
                $dimensoes_ok = 'A';
            }

            // Distância e tempo do local de saída (origem) 
            // até o local de destino (coleta ou entrega)

            $distancia_destino = 0;
            $tempo_viagem = 0;

            $dur_prev_atend = 0;

            // Inicializamos aqui para NÃO dar erro quando as coordenadas 
            // de destino da primeira solicitação do veículo for EXATAMENTE 
            // igual à posição atual do veículo (rastreador) 
            // 

            // 'C3', 'C4' ou 'E3','E4':  veículo já chegou ao local ao local de destino: 
            // já temos a duração prevista do atendimento
            if (in_array($col->status, ['C3', 'C4', 'E3', 'E4'])) {

                // Coordenadas de ORIGEM e DESTINO iguais... indicam que a solicitação ANTERIOR e 
                // a solicitação ATUAL se destinam ao MESMO LOCAL.
                // Calculamos a hora de saída prevista somente se a ORIGEM e o DESTINO forem diferentes. 
                if (($geo_lat_origem <> $geo_lat_destino) || ($geo_lng_origem <> $geo_lng_destino)) {
                    // Guardamos a posição do destino atual como origem para o próximo destino. 
                    $geo_lat_origem = $geo_lat_destino;
                    $geo_lng_origem = $geo_lng_destino;

                    if (($col->status == 'C3') || ($col->status == 'C4')) {

                        // 'C3' ou 'C4' => COLETA - Chegada ou Iniciada: a hora de previsão de chegada é a hora efetiva da chegada.
                        $hr_prev_chegada = $col->hr_cheg_coleta;
                        $dt_hr_prev_chegada = $col->dt_efet_coleta . ' ' . $hr_prev_chegada;
                        $dt_hr_prev_chegada = Carbon::createFromFormat('Y-m-d H:i:s',  $dt_hr_prev_chegada);

                        // Calculamos a hora prevista da saída do local a partir da HORA DE CHEGADA e com a duração do atendimento
                        // atual... que está gravada no veículo
                        $prev_saida_sec = strtotime($dt_hr_prev_chegada) + rgTimeToSeconds($veiculo->dur_atend_atual);
                        $dt_hr_prev_saida = date('Y-m-d H:i:s', $prev_saida_sec);

                        // Setamos a duração de atendimento utilizada para esta solicitação
                        $dur_prev_atend = $veiculo->dur_atend_atual;

                        if ($dt_hr_prev_saida < $data_hora_atual_serv) {
                            // Se a previsão de saída está atrasada... colocamos a hora atual
                            $dt_hr_prev_saida = $data_hora_atual_serv;
                        }
                    } else {

                        // 'E3' ou 'E4' => ENTREGA - Chegada ou Iniciada: a hora de previsão de chegada é a hora efetiva da chegada.
                        $hr_prev_chegada = $col->hr_cheg_entrega;
                        $dt_hr_prev_chegada = $col->dt_efet_entrega . ' ' . $hr_prev_chegada;
                        $dt_hr_prev_chegada = Carbon::createFromFormat('Y-m-d H:i:s', $dt_hr_prev_chegada);

                        // Calculamos a hora prevista da saída do local a partir da HORA DE CHEGADA e com a duração do atendimento
                        // atual... que está gravada no veículo
                        $prev_saida_sec   = strtotime($dt_hr_prev_chegada) + rgTimeToSeconds($veiculo->dur_atend_atual);
                        $dt_hr_prev_saida = date('Y-m-d H:i:s', $prev_saida_sec);

                        // Setamos a duração de atendimento utilizada para esta solicitação
                        $dur_prev_atend = $veiculo->dur_atend_atual;

                        if ($dt_hr_prev_saida < $data_hora_atual_serv) {
                            // Se a previsão de saída está atrasada... colocamos a hora atual
                            $dt_hr_prev_saida = $data_hora_atual_serv;
                        }
                    }
                }

                // Pegamos a data e a hora da CHEGADA... para adicionar no array de retorno
                $dt_prev_chegada = Carbon::createFromFormat('Y-m-d H:i:s', $dt_hr_prev_chegada)->format('Y-m-d');
                $hr_prev_chegada = Carbon::createFromFormat('Y-m-d H:i:s', $dt_hr_prev_chegada)->format('H:i:s');

                // Pegamos a data e a hora da SAÍDA... para adicionar no array de retorno
                $dt_prev_saida = Carbon::createFromFormat('Y-m-d H:i:s', $dt_hr_prev_saida)->format('Y-m-d');
                $hr_prev_saida = Carbon::createFromFormat('Y-m-d H:i:s', $dt_hr_prev_saida)->format('H:i:s');
            } else {

                if (($geo_lat_origem <> 0) && ($geo_lng_origem <> 0)
                    && (rgDifZeroNull($geo_lat_destino)) && (rgDifZeroNull($geo_lng_destino))
                ) {

                    // Coordenadas de ORIGEM e DESTINO iguais... indicam que a solicitação ANTERIOR e a 
                    // solicitação ATUAL se destinam ao MESMO LOCAL.
                    // Calculamos tempo e distância somente se a ORIGEM e o DESTINO forem diferentes. 
                    if (($geo_lat_origem <> $geo_lat_destino) || ($geo_lng_origem <> $geo_lng_destino)) {

                        $api = new ApiIntegracao();
                        $retorno = $api->CalcularDistanciaOrigem_Destino(
                            $geo_lat_origem,
                            $geo_lng_origem,
                            $geo_lat_destino,
                            $geo_lng_destino
                        );

                        $distancia_destino = $retorno['distance'];
                        $tempo_viagem = $retorno['duration'];

                        // Guardamos a posição do destino atual como origem para o próximo destino. 
                        // SE o cálculo não foi realizado por falta de coordenadas... o ponto de origem 
                        // será o último que tinha valor válido. (Fazê u quê? Chutá num dá... né 'zifio'?).
                        $geo_lat_origem = $geo_lat_destino;
                        $geo_lng_origem = $geo_lng_destino;

                        // Previsão de chegada no ponto atual: adicionamos o tempo de viagem e duração do atendimento
                        // SOMENTE quando o local (coordenadas) da solicitação atual forem diferentes da solicitação anterior
                        $dt_hr_prev_chegada_sec = strtotime($dt_hr_prev_saida) + $tempo_viagem;
                        $dt_hr_prev_chegada = date('Y-m-d H:i:s', $dt_hr_prev_chegada_sec);

                        // Previsão de saída do ponto atual
                        $dt_hr_prev_saida_sec = strtotime($dt_hr_prev_chegada) + rgTimetoSeconds($dur_atend_padrao);
                        $dt_hr_prev_saida = date('Y-m-d H:i:s', $dt_hr_prev_saida_sec);

                        // Atribuimos a duração de atendimento padrão... SOMENTE quando o local (coordenadas)
                        // da solicitação atual forem diferentes da solicitação anterior
                        $dur_prev_atend = $dur_atend_padrao;
                    }
                } else {

                    // Quando ocorrer várias solicitações de coleta/entrega no mesmo DESTINO (coordenadas)... as datas de
                    // previsão de CHEGADA e SAÍDA permanecerão iguais... até que as coordenadas mudem. Inicializamos 
                    //
                    // Atribuímos aqui também para NÃO dar erro quando as coordenadas forem ZERO.
                    //
                    // A Previsão de chegada no ponto atual... começar a partir da previsão de saída do ponto anterior
                    $dt_hr_prev_chegada = $dt_hr_prev_saida;

                    // Previsão de saída do ponto atual.... começa a partir da previsão de chegada no mesmo ponto
                    $dt_hr_prev_saida = $dt_hr_prev_chegada;
                }

                // Pegamos a data e a hora da CHEGADA... para adicionar no array de retorno
                $dt_prev_chegada = Carbon::createFromFormat('Y-m-d H:i:s', $dt_hr_prev_chegada)->format('Y-m-d');
                $hr_prev_chegada = Carbon::createFromFormat('Y-m-d H:i:s', $dt_hr_prev_chegada)->format('H:i:s');

                // Pegamos a data e a hora da SAÍDA... para adicionar no array de retorno
                $dt_prev_saida = Carbon::createFromFormat('Y-m-d H:i:s', $dt_hr_prev_saida)->format('Y-m-d');
                $hr_prev_saida = Carbon::createFromFormat('Y-m-d H:i:s', $dt_hr_prev_saida)->format('H:i:s');
            }

            // Expediente do local de destino
            $retorno = $this->VerificarHorariosExpedColetas(
                $hr_prev_chegada,
                $hr_ini_manha,
                $hr_fim_manha,
                $hr_ini_tarde,
                $hr_fim_tarde
            );

            $tempo_ok  = $retorno['tempo_ok'];
            $msg_tempo = $retorno['msg_tempo'];

            if ($dt_hr_prev_chegada <= $dt_hr_saida_inicial) {
                // Não temos tempo de rota se a saída do veículo for menor que a hora inicial da rota
                // Isso ocorre geralmente quando o veículo já está no destino e setou a chegada
                $tempo_rota = 0;
            } else {
                // Calculamos o tempo da rota para esta solicitação... que é a diferença entre
                // a hora da saída inicial (pavilhão ou veículo)  E  a hora de chegada do veículo
                // 
                $tempo_rota = strtotime($dt_hr_prev_chegada) - strtotime($dt_hr_saida_inicial);
            }

            // Converte o tempo da rota que está em segundos para hh:mm:ss
            $tempo_rota = rgSecondsToTime($tempo_rota);

            $atual_nao = 'N';

            $array_coletas[$ind] =
                $this->MontaArraySolicitacoesVeiculoCarga(
                    $col,
                    $placa,
                    $etapa,
                    $cod_destino,
                    $destino,
                    $geo_lat_destino,
                    $geo_lng_destino,
                    $ender_destino,
                    $bairro_destino,
                    $cidade_destino,
                    $cep_destino,
                    $uf_destino,
                    $fone_destino,
                    $hr_ini_manha,
                    $hr_fim_manha,
                    $hr_ini_tarde,
                    $hr_fim_tarde,
                    $dimensoes_ok,
                    $distancia_destino,
                    $dt_prev_chegada,
                    $hr_prev_chegada,
                    $dur_prev_atend,
                    $dt_prev_saida,
                    $hr_prev_saida,
                    $tempo_viagem,
                    $tempo_ok,
                    $msg_tempo,
                    $col->dt_prev_col_ent,
                    $col->hr_prev_col_ent,
                    $atual_nao,
                    $tempo_rota
                );

            // Chama a rotina que vai acumular os contadores da coleta
            $arr_contadores_coleta = $this->RetornarContadoresColeta($col->coleta_id);

            // Adiciona o array de contadores 
            $array_coletas[$ind]['notas_fiscais'] = $arr_contadores_coleta['notas_fiscais'];
            $array_coletas[$ind]['coleta_log']    = $arr_contadores_coleta['coleta_log'];
            $array_coletas[$ind]['coleta_pos']    = $arr_contadores_coleta['coleta_pos'];

            $ind++;
        }

        // Esta rotina será chamada em dois momentos(solicitacoes em andamento e nas solicitaçoes da rota) 
        // Temos que retornar a previsão de saída, pois a Previsão de chegada nas solicitações da rota
        // será a previsão de saída do ponto anterior (última solicitacao em andamento processada)
        $arr_retorno['dt_hr_prev_saida']   = $dt_hr_prev_saida;
        $arr_retorno['dt_hr_prev_chegada'] = $dt_hr_prev_chegada;
        $arr_retorno['array_coletas']      = $array_coletas;

        return $arr_retorno;
    }


    public function MontaArraySolicitacoesVeiculoCarga(
        $col,
        $placa,
        $etapa,
        $cod_destino,
        $destino,
        $geo_lat_destino,
        $geo_lng_destino,
        $ender_destino,
        $bairro_destino,
        $cidade_destino,
        $cep_destino,
        $uf_destino,
        $fone_destino,
        $hr_ini_manha,
        $hr_fim_manha,
        $hr_ini_tarde,
        $hr_fim_tarde,
        $dimensoes_ok,
        $distancia_destino,
        $dt_prev_chegada,
        $hr_prev_chegada,
        $dur_prev_atend,
        $dt_prev_saida,
        $hr_prev_saida,
        $tempo_viagem,
        $tempo_ok,
        $msg_tempo,
        $dt_prev_col_ent,
        $hr_prev_col_ent,
        $atual,
        $tempo_rota

    ) {

        $dados['coleta_id']        = $col->coleta_id;
        $dados['numero']           = $col->numero;
        $dados['data_cad']         = $col->data_cad;
        $dados['etapa']            = $etapa;
        $dados['atual']            = $atual;

        $dados['nome_cliente']     = $col->nome_cliente;
        $dados['coleta_fixa']      = $col->coleta_fixa;

        $dados['cod_destino']      = $cod_destino;
        $dados['destino']          = $destino;

        $dados['ender_destino']    = $ender_destino;
        $dados['bairro_destino']   = $bairro_destino;
        $dados['cidade_destino']   = $cidade_destino;
        $dados['cep_destino']      = $cep_destino;
        $dados['uf_destino']       = $uf_destino;
        $dados['fone_destino']     = $fone_destino;

        $dados['geo_lat_destino']  = $geo_lat_destino;
        $dados['geo_lng_destino']  = $geo_lng_destino;

        // Acrescentados estes campos ------------------------        
        $dados['cod_loc_coleta']  = $col->cod_loc_coleta;
        $dados['local_coleta']    = $col->local_coleta;
        $dados['dt_prev_coleta']  = $col->dt_prev_coleta;
        $dados['hr_prev_coleta']  = $col->hr_prev_coleta;
        $dados['placa_coleta']    = $col->placa_coleta;
        $dados['dt_efet_coleta']  = $col->dt_efet_coleta;
        $dados['hr_sai_coleta']   = $col->hr_sai_coleta;

        $dados['cod_loc_entrega'] = $col->cod_loc_entrega;
        $dados['local_entrega']   = $col->local_entrega;
        $dados['dt_prev_entrega'] = $col->dt_prev_entrega;
        $dados['hr_prev_entrega'] = $col->hr_prev_entrega;
        // ------------------------------

        $dados['exped_manha'] =
            rgFormataHorarioExpediente($hr_ini_manha, $hr_fim_manha);
        $dados['exped_tarde'] =
            rgFormataHorarioExpediente($hr_ini_tarde, $hr_fim_tarde);

        $dados['placa'] = $placa;

        $dados['dt_prev_col_ent'] = $dt_prev_col_ent;
        $dados['hr_prev_col_ent'] = $hr_prev_col_ent;
        $dados['entrega_urgente'] = $col->entrega_urgente;

        $dados['baldeada']        = $col->baldeada;
        $dados['placa_baldeacao'] = $col->placa_baldeacao;

        $dados['dimensoes_ok'] = $dimensoes_ok;
        $dados['dim_carga']    = rgFormataDimensoes($col->comp_carga, $col->larg_carga, $col->alt_carga);
        $dados['vol_carga']    = $col->volumes . ' ' . $col->especie . ' ' . rgFormataPesoVeiculo($col->peso);

        if ($distancia_destino == 0) {
            $dados['distancia_destino'] = '-';
        } else {
            $dados['distancia_destino'] = rgFormataDistancia($distancia_destino);
        }

        // O tempo de viagem passado como parâmetro está em segundos
        $dados['tempo_viagem']    = rgRetornaFormataTempoExt(rgSecondsToTime($tempo_viagem));

        $dados['tempo_rota']      = rgRetornaFormataTempoExt($tempo_rota);

        $dados['dt_prev_chegada'] = $dt_prev_chegada;
        $dados['hr_prev_chegada'] = $hr_prev_chegada;

        $dados['dur_prev_atend']  = $dur_prev_atend;

        $dados['dt_prev_saida']   = $dt_prev_saida;
        $dados['hr_prev_saida']   = $hr_prev_saida;

        $dados['tempo_ok']        = $tempo_ok;
        $dados['msg_tempo']       = $msg_tempo;

        $dados['status']          = $col->status;
        $dados['txt_instrucao']   = $col->txt_instrucao;

        $dados['img_rom_coleta']  = $col->img_rom_coleta;
        $dados['img_rom_entrega'] = $col->img_rom_entrega;

        if (rgDifTrimNull($dados['img_rom_coleta']) || rgDifTrimNull($dados['img_rom_entrega'])) {
            $dados['url_imagem'] = rgRetornarUrlImagens(''); //Precisamos apenas do caminho, vamos montar o arquivo com a url + a img_rom necessária.
        } else {
            $dados['url_imagem'] = null;
        }

        return $dados;
    }


    public function VerificarHorariosExpedColetas(
        $hr_prev_chegada,
        $hr_ini_manha,
        $hr_fim_manha,
        $hr_ini_tarde,
        $hr_fim_tarde
    ) {

        //Expediente do local de destino
        // Vai chegar no período da manhã?
        if ($hr_prev_chegada <= '12:00:00') {

            // Expediente manhã informado?

            if ((rgDifZeroNull($hr_ini_manha)) && (rgDifZeroNull($hr_fim_manha))) {

                // Testamos expediente da MANHÃ
                if (($hr_prev_chegada >= $hr_ini_manha)  && ($hr_prev_chegada <= $hr_fim_manha)
                ) {
                    $tempo_ok = 'S';
                    $msg_tempo = '';
                } else {
                    $tempo_ok = 'N';
                    $msg_tempo = 'Fora do expediente da manhã';
                }
            } else {
                $tempo_ok = 'A';
                $msg_tempo = 'horários de atendimento da manhã não informados';
            }
        } else {

            // Expediente tarde informado?
            if ((rgDifZeroNull($hr_ini_tarde)) && (rgDifZeroNull($hr_fim_tarde))) {

                // Testamos expediente da TARDE
                if (($hr_prev_chegada >= $hr_ini_tarde) && ($hr_prev_chegada <= $hr_fim_tarde)) {
                    $tempo_ok = 'S';
                    $msg_tempo = '';
                } else {
                    $tempo_ok = 'N';
                    $msg_tempo = 'Fora do expediente da tarde';
                }
            } else {
                $tempo_ok = 'A';
                $msg_tempo = 'Horários de atendimento da tarde não informados';
            }
        }

        return ['tempo_ok' => $tempo_ok, 'msg_tempo' => $msg_tempo];
    }


    public function ProcessaColetasRealizadasVeiculoCarga($veiculo_placa)
    {

        $array_carga = array();

        // Selecionamos as COLETAS REALIZADAS e as ENTREGAS alocadas para o veículo
        $coletas_realiz = DB::table('coleta as col')
            ->select(
                'col.*',
                'lc.*',
                'le.*',
                'col.id AS coleta_id',
                'cli.nome AS nome_cliente',
                'col.dt_prev_coleta AS dt_prev_col_ent',
                'col.hr_prev_coleta AS hr_prev_col_ent',
                'lc.nome AS local_coleta',
                'lc.geo_lat AS geo_lat_coleta',
                'lc.geo_lng AS geo_lng_coleta',
                'le.nome AS local_entrega',
                'le.geo_lat AS geo_lat_entrega',
                'le.geo_lng AS geo_lng_entrega'
            )

            ->leftjoin('cliente as cli', function ($join) {
                $join->on('cli.codigo', '=', 'col.cod_cliente')
                    ->on('cli.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as lc', function ($join) {
                $join->on('lc.codigo', '=', 'col.cod_loc_coleta')
                    ->on('lc.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as le', function ($join) {
                $join->on('le.codigo', '=', 'col.cod_loc_entrega')
                    ->on('le.empresa', '=', 'col.empresa');
            })

            // Somente status 'CR' - Coleta Realizada: a carga está com o veículo
            ->where('col.status', '=', 'CR')

            // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
            // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
            //
            ->where(function ($query) {
                $query->where('col.coleta_fixa', '!=', 'C')
                    ->orWhere(function ($query1) {
                        $query1->where('col.coleta_fixa', '=', 'C')
                            ->where(function ($query2) {
                                $query2->whereNull('col.solic_origem_id')
                                    ->orWhere('col.solic_origem_id', '=', '0');
                            });
                    });
            })

            // Para qualquer situação, a carga NÃO pode ter sido descarregada            
            ->where(function ($query) {
                $query->whereNull('col.carga_pavilhao')
                    ->orWhere('col.carga_pavilhao', '!=', 'S');
            })

            // COLETAS atribuidas ao veículo
            ->where('col.placa_coleta', '=', $veiculo_placa);

        //  ------------------------------
        //       U N I O N    A L L
        //  -------------------------------

        $coletas_realiz_union = DB::table('coleta as col')
            ->select(
                'col.*',
                'lc.*',
                'le.*',
                'col.id AS coleta_id',
                'cli.nome AS nome_cliente',
                'col.dt_prev_entrega AS dt_prev_col_ent',
                'col.hr_prev_entrega AS hr_prev_col_ent',
                'lc.nome AS local_coleta',
                'lc.geo_lat AS geo_lat_coleta',
                'lc.geo_lng AS geo_lng_coleta',
                'le.nome AS local_entrega',
                'le.geo_lat AS geo_lat_entrega',
                'le.geo_lng AS geo_lng_entrega'
            )

            ->leftjoin('cliente as cli', function ($join) {
                $join->on('cli.codigo', '=', 'col.cod_cliente')
                    ->on('cli.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as lc', function ($join) {
                $join->on('lc.codigo', '=', 'col.cod_loc_coleta')
                    ->on('lc.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as le', function ($join) {
                $join->on('le.codigo', '=', 'col.cod_loc_entrega')
                    ->on('le.empresa', '=', 'col.empresa');
            })

            // Aqui pegamos somente solicitações na fase de ENTREGA. 
            //
            // NÃO testamos o campo coleta_fixa = 'C'... porque NÃO existirão solicitações 
            // do tipo CONTRATO em fase de ENTREGA.
            //
            // Consideramos todos os status de ENTREGA que indicam que a carga AINDA está
            // com o veículo => 'E1', 'E2', 'E3', 'E4', 'EN' e 'EP'
            // 
            // Desconsideramos status 'E0' pode porque a entrega NÃO está autorizada
            //
            ->whereIn('col.status', ['E1', 'E2', 'E3', 'E4', 'EN', 'EP'])

            // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
            // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
            //
            ->where(function ($query) {
                $query->where('col.coleta_fixa', '!=', 'C')
                    ->orWhere(function ($query1) {
                        $query1->where('col.coleta_fixa', '=', 'C')
                            ->where(function ($query2) {
                                $query2->whereNull('col.solic_origem_id')
                                    ->orWhere('col.solic_origem_id', '=', '0');
                            });
                    });
            })

            // Para qualquer situação, a carga NÃO pode ter sido descarregada            
            ->where(function ($query) {
                $query->whereNull('col.carga_pavilhao')
                    ->orWhere('col.carga_pavilhao', '!=', 'S');
            })

            // Em qualquer situação mostramos apenas se a solicitação de reentrega não foi gerada.
            // 
            ->where(function ($query) {
                $query->whereNull('col.reentrega_gerada')
                    ->orWhere('col.reentrega_gerada', '!=', 'S');
            })

            // ENTREGAS atribuidas ao veículo
            ->where('col.placa_entrega', '=', $veiculo_placa)

            // Abaixo o comando "union" fará a junção dos dois selects que estará mo objeto "$coletas_realiz_union"
            ->union($coletas_realiz)

            ->orderBy('seq_atend', 'asc')
            ->orderBy('dt_prev_col_ent', 'asc')
            ->orderBy('hr_prev_col_ent', 'asc')
            ->get();

        if (count($coletas_realiz_union) > 0) {

            $ind = 0;

            foreach ($coletas_realiz_union as $col_real) {

                // Chama a rotina que vai acumular os contadores da coleta
                $arr_cont_carga = $this->RetornarContadoresColeta($col_real->coleta_id);

                // Monta o array de retorno da carga para a coleta corrente
                $array_carga[$ind] = $this->MontaArrayColetasRealizVeiculoCarga($col_real, $arr_cont_carga);

                $ind++;
            }
        }

        $retorno['array_carga'] = $array_carga;

        return $retorno;
    }


    public function MontaArrayColetasRealizVeiculoCarga($col, $arr_cont_carga)
    {

        $dados['coleta_id']       = $col->coleta_id;
        $dados['numero']          = $col->numero;
        $dados['coleta_fixa']     = $col->coleta_fixa;
        $dados['solic_origem_id'] = $col->solic_origem_id;

        $dados['cod_loc_coleta']  = $col->cod_loc_coleta;
        $dados['local_coleta']    = $col->local_coleta;
        $dados['geo_lat_coleta']  = $col->geo_lat_coleta;
        $dados['geo_lng_coleta']  = $col->geo_lng_coleta;

        $dados['placa_coleta']    = $col->placa_coleta;

        $dados['dt_efet_coleta']  = $col->dt_efet_coleta;
        $dados['hr_sai_coleta']   = $col->hr_sai_coleta;

        $dados['cod_loc_entrega'] = $col->cod_loc_entrega;
        $dados['local_entrega']   = $col->local_entrega;
        $dados['geo_lat_entrega'] = $col->geo_lat_entrega;
        $dados['geo_lng_entrega'] = $col->geo_lng_entrega;

        $dados['dt_prev_entrega'] = $col->dt_prev_entrega;
        $dados['hr_prev_entrega'] = $col->hr_prev_entrega;

        $dados['entrega_consolidada'] = $col->entrega_consolidada;
        $dados['entrega_urgente'] = $col->entrega_urgente;

        $dados['carga_pavilhao']  = $col->carga_pavilhao;

        $dados['baldeada']        = $col->baldeada;
        $dados['placa_baldeacao'] = $col->placa_baldeacao;

        $dados['dim_carga']       = rgFormataDimensoes($col->comp_carga, $col->larg_carga, $col->alt_carga);
        $dados['vol_carga']       = $col->volumes . ' ' . $col->especie . ' ' . rgFormataPesoVeiculo($col->peso);

        $dados['txt_instrucao']   = $col->txt_instrucao;
        $dados['status']          = $col->status;

        $dados['notas_fiscais']   = $arr_cont_carga['notas_fiscais'];

        $dados['img_rom_coleta']  = $col->img_rom_coleta;
        $dados['img_rom_entrega'] = $col->img_rom_entrega;

        if (rgDifTrimNull($dados['img_rom_coleta']) || rgDifTrimNull($dados['img_rom_entrega'])) {
            $dados['url_imagem'] = rgRetornarUrlImagens(''); //Precisamos apenas do caminho, vamos montar o arquivo com a url + a img_rom necessária.
        } else {
            $dados['url_imagem'] = null;
        }

        $dados['qtde_notas_distrib'] = $arr_cont_carga['qtde_notas_distrib'];
        $dados['coleta_log']         = $arr_cont_carga['coleta_log'];
        $dados['coleta_pos']         = $arr_cont_carga['coleta_pos'];

        return $dados;
    }



    public function MontaArrayTotaisSolicVeiculo($veiculo, $peso_total_coletas, $qtde_coletas)
    {

        // Calculamos a capacidade restante do veículo
        $peso_restante = $veiculo->cap_kg - $peso_total_coletas;

        if ($peso_restante >= 0) {
            $capacid_peso_ok = 'S';
            $peso_restante_abs = $peso_restante;
            if ($peso_restante == 0) {
                $sinal = '';
            } else {
                $sinal = '+';
            }
        } else {
            $capacid_peso_ok = 'N';
            $peso_restante_abs = abs($peso_restante);
            $sinal = '-';
        }

        // Montamos o retorno dos totais
        $totais['qtde_coletas']       = $qtde_coletas;
        $totais['peso_total_coletas'] = rgFormataPesoVeiculo($peso_total_coletas);

        $totais['peso_restante']      = $sinal . rgFormataPesoVeiculo($peso_restante_abs);
        $totais['capacid_peso_ok']    = $capacid_peso_ok;

        return $totais;
    }


    public function Local_DesvincularVeiculoSolicitacao($coleta_id, $placa)
    {

        if (!($coleta = Coleta::find($coleta_id))) {
            $result = false;
        } else {

            // Verificamos em qual fase a solicitação está?
            if (substr($coleta->status, 0, 1) == 'C') {

                // Desvincular da coleta
                // Fase de COLETA: a solicitação NÃO PODE estar em andamento ou encerrada  
                // E a placa solicitada deve ser a mesma que está gravada para a COLETA.
                $result = false;

                if (($coleta->placa_coleta == $placa) && (in_array($coleta->status, ['C0', 'C1']))) {

                    // SE a solicitação que está sendo removida do veículo for de REENTREGA ou DEVOLUÇÃO... 
                    // assumimos que a carga estava no pavilhão, assim como quando geramos a solicitação de reentrega
                    if (rgDifTrimNull($coleta->reentrega)) {
                        $carga_pavilhao = 'S';
                    } else {
                        $carga_pavilhao = null;
                    }

                    // Retornamos o status para 'C0' - Coleta solicitada.

                    try {

                        $coleta['placa_coleta']    = null;
                        $coleta['motor_coleta_id'] = null;

                        $coleta['placa_baldeacao'] = null;
                        $coleta['baldeada']        = 'N';

                        $coleta['carga_pavilhao'] = $carga_pavilhao;

                        $coleta['seq_atend'] = null;
                        $coleta['rota_calc'] = null;

                        $coleta['status'] = 'C0';   // Coleta solicitada

                        $coleta['ass_user_id'] = auth()->user()->id;

                        $coleta->save();

                        $result = true;
                    } catch (\Exception $e) {
                        $result = false;
                    }
                }
            } else {

                // Desvincular da entrega
                $result = false;

                if (($coleta->placa_entrega == $placa) && (in_array($coleta->status, ['E0', 'E1']))) {

                    // Fase de ENTREGA:  a solicitação NÃO PODE estar em andamento ou encerrada  
                    // E  a placa solicitada deve ser a mesma que está gravada para a ENTREGA. 
                    try {

                        $coleta['placa_entrega']    = null;
                        $coleta['motor_entrega_id'] = null;

                        $coleta['placa_baldeacao'] = null;
                        $coleta['baldeada']        = 'N';

                        $coleta['seq_atend'] = null;
                        $coleta['rota_calc'] = null;

                        // Limpamos os campos de instrução para que seja disparada uma notificação
                        // na próxima AUTORIZAÇÃO DE ENTREGA
                        //
                        $coleta['instrucao']     = null;
                        $coleta['txt_instrucao'] = null;

                        // Voltamos para 'CR - Coleta realizada porque é o status condizente com o fato:
                        // SE a solicitação foi removida da carga no estágio de ENTREGA... é porque a coleta
                        // foi realizada.  
                        //
                        $coleta['status'] = 'CR';

                        // Deixamos como 'Descarga Realizada'... porque foi removida da carga do veículo
                        $coleta['carga_pavilhao'] = 'S';

                        $coleta['ass_user_id'] = auth()->user()->id;

                        $coleta->save();

                        $result = true;
                    } catch (\Exception $e) {
                        $result = false;
                    }
                }
            }
        }

        return $result;
    }


    public function Local_RetornarInstrucoesColeta($coleta_id)
    {

        $dados = array();
        $result = false;

        $cont_coletas  = 0;
        $cont_entregas = 0;

        // Selecionamos as notificações das solicitações atribuidas ao veículo (coleta ou entrega) 
        $notif = DB::table('notif')
            ->select('notif.*')
            ->where('notif.reg_id', '=', $coleta_id)
            ->whereIn('notif.evento', ['C01', 'E01'])
            ->orderBy('notif.id', 'asc')
            ->get();

        if (count($notif) > 0) {

            $ind = 0;

            foreach ($notif as $not) {

                $dados[$ind]['evento']     = $not->evento;
                $dados[$ind]['notif_id']   = $not->id;
                $dados[$ind]['coleta_id']  = $not->reg_id;
                $dados[$ind]['titulo']     = $not->titulo;
                $dados[$ind]['texto']      = $not->texto;
                $dados[$ind]['lida']       = $not->lida;
                $dados[$ind]['created_at'] = $not->created_at;
                $dados[$ind]['updated_at'] = $not->updated_at;

                if ($not->evento == 'C01') {
                    $cont_coletas++;
                } else {
                    $cont_entregas++;
                }

                $ind++;
            }

            $result = true;
        }

        $retorno['status'] = $result;
        $retorno['dados']['cont_coletas']  = $cont_coletas;
        $retorno['dados']['cont_entregas'] = $cont_entregas;
        $retorno['dados']['instrucoes']    = $dados;

        return $retorno;
    }


    public function Local_RetornarVeiculosBaldeacaoSimples($coleta_id, $com_motorista)
    {

        $dados = array();

        $result = true;

        $coleta = DB::table('coleta')
            ->select('status', 'placa_coleta', 'placa_entrega')
            ->where('id', '=', $coleta_id)
            ->first();

        if (empty($coleta)) {

            $result = false;
        } else {
            //Testamos a fase da solicitação

            if (substr($coleta->status, 0, 1) == 'C') {
                // Fase de Coleta: pegamos a placa da coleta
                $placa_atual = $coleta->placa_coleta;
            } else {
                // Fase de entrega: pegamos a placa da entrega
                $placa_atual = $coleta->placa_entrega;
            }
        }

        if ($result) {

            //Retornamos um registro com o conteúdo nenhum independente se vai encontrar veículos ou não
            $ind = 0;

            // Selecionar registros da tabela VEICULO => $veiculo... que NÃO estejam relacionados a 
            // nenhum motorista... exceto o motorista atual ($motorista_id):
            $veiculo = DB::table('veiculo as v')
                ->select('v.placa', 'tv.descricao AS descr_tipo_veiculo', 'v.motorista_id')
                ->join('tipo_veiculo as tv', 'tv.codigo', '=', 'v.cod_tipo_veiculo')
                ->where('v.ativo', '=', 'S')
                // Consideramos apenas veículos: "M" => Monobloco ou "R" => Carrega/Reboque
                ->whereIn('tv.classe', ['M', 'R'])
                ->where('v.placa', '<>', $placa_atual)
                ->where(function ($query) use ($com_motorista) {
                    if ($com_motorista == 'S') {
                        $query->whereNotNull('v.motorista_id')
                            ->where('v.motorista_id', '<>', '0')
                            ->where('v.motorista_id', '<>', '');
                    }
                })
                ->orderby('v.placa', 'asc')
                ->get();

            foreach ($veiculo as $regveiculo) {

                $dados[$ind]['placa']              = $regveiculo->placa;
                $dados[$ind]['descr_tipo_veiculo'] = $regveiculo->descr_tipo_veiculo;
                $dados[$ind]['motorista_id']       = $regveiculo->motorista_id;

                $ind++;
            }
        }

        $retorno['status'] = $result;
        $retorno['dados']  = $dados;

        return $retorno;
    }


    public function Local_GravarSeqAtendRotaCarga($lista_coletas)
    {

        $result = true;

        if ((!isset($lista_coletas)) || ($lista_coletas == null) || (count($lista_coletas) == 0)) {
            $result = false;
        }

        if ($result) {

            $seq_atend = 0;

            foreach ($lista_coletas as $col) {

                try {

                    $seq_atend = $seq_atend + 1;

                    // Não estamos usando o model, pois NÃO temos que disparar nenhum evento 
                    // depois da gravação da tabela coleta nesta situação
                    $coleta = Coleta::where('id', '=', $col['coleta_id'])
                        ->update([
                            'seq_atend'   => $seq_atend,
                            'rota_calc'   => 'S',
                            'ass_user_id' => auth()->user()->id
                        ]);
                } catch (\Exception $e) {

                    $result = false;
                }
            }
        }

        $retorno['status'] = $result;
        return $retorno;
    }


    public function Local_RetornarVeiculosBaldeacao($coleta_id, $com_motorista)
    {

        $arr_dados_coleta   = array();
        $arr_veic_baldeacao = array();

        $timezone_app = date_default_timezone_get();
        $data_hora_atual = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
        $data_serv_atual = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_atual)->format('Y-m-d');

        $coleta = $this->LerDadosColetaBaldeacao($coleta_id);

        if (empty($coleta) == false) {

            $exped_entrega_man = rgFormataHorarioExpediente($coleta->hr_ini_entrega_man, $coleta->hr_fim_entrega_man);
            $exped_entrega_tar = rgFormataHorarioExpediente($coleta->hr_ini_entrega_tar, $coleta->hr_fim_entrega_tar);

            $dim_carga  = rgFormataDimensoes($coleta->comp_carga, $coleta->larg_carga, $coleta->alt_carga);
            $vol_carga  = $coleta->volumes . ' x ' . $coleta->especie . ' x ' . rgFormataPesoVeiculo($coleta->peso);

            // Define a etapa em que a solicitação está

            if (substr($coleta->status, 0, 1) == 'C') {
                $etapa = 'Coleta';
                $placa = $coleta->placa_coleta;
            } else {
                $etapa = 'Entrega';
                $placa = $coleta->placa_entrega;
            }

            // Ler o veículo da etapa da solicitação => $veiculo_solic
            $veiculo_solic = DB::table('veiculo as v')
                ->select('v.geo_lat', 'v.geo_lng')
                ->where('v.placa', '=', $placa)
                ->first();

            // Se tiver placa definida para a etapa... não pode baldear. 
            if (empty($veiculo_solic) == false) {

                //Monta o array com os dados da coleta solicitada
                $arr_dados_coleta = $this->MontaArrayColetaBaldeacao(
                    $coleta,
                    $placa,
                    $etapa,
                    $exped_entrega_man,
                    $exped_entrega_tar,
                    $vol_carga,
                    $dim_carga
                );

                $veiculos = $this->SelecionarVeiculosBaldeacaoColeta($placa, $com_motorista, $data_serv_atual);

                if (count($veiculos) > 0) {

                    //Monta o array de veículos para a baldeacao da coleta
                    $arr_veic_baldeacao = $this->MontaArrayVeiculosBaldeacaoColeta(
                        $veiculos,
                        $coleta,
                        $veiculo_solic,
                        $data_hora_atual,
                        $data_serv_atual
                    );

                    //Ordenar o array $veiculos em ordem decrescente de pontos_veiculo
                    $arr_veic_baldeacao = $this->OrdenaArrayVeiculoPorPontuacao($arr_veic_baldeacao);
                }
            }
        }

        $dados['dados']['dados_coleta'] = $arr_dados_coleta;
        $dados['dados']['veiculos']     = $arr_veic_baldeacao;

        return $dados;
    }


    public function LerDadosColetaBaldeacao($coleta_id)
    {

        $coleta = DB::table('coleta')
            ->select(
                'coleta.*',
                'coleta.id as coleta_id',
                'cli.nome AS nome_cliente',
                'lc.nome AS local_coleta',
                'le.nome AS local_entrega',
                'le.hr_ini_entrega_man',
                'le.hr_fim_entrega_man',
                'le.hr_ini_entrega_tar',
                'le.hr_fim_entrega_tar',
                'tipo_veiculo.descricao AS tipo_veiculo'
            )
            ->leftjoin('cliente as cli', function ($join) {
                $join->on('cli.codigo', '=', 'coleta.cod_cliente')
                    ->on('cli.empresa', '=', 'coleta.empresa');
            })
            ->leftjoin('cliente as lc', function ($join) {
                $join->on('lc.codigo', '=', 'coleta.cod_loc_coleta')
                    ->on('lc.empresa', '=', 'coleta.empresa');
            })
            ->leftjoin('cliente as le', function ($join) {
                $join->on('le.codigo', '=', 'coleta.cod_loc_entrega')
                    ->on('le.empresa', '=', 'coleta.empresa');
            })

            ->leftJoin('tipo_veiculo', 'tipo_veiculo.codigo', '=', 'coleta.cod_tipo_veiculo')
            ->where('coleta.id', '=', $coleta_id)
            ->first();

        return $coleta;
    }


    public function SelecionarVeiculosBaldeacaoColeta($placa, $com_motorista, $data_atual_serv)
    {

        $veiculos = DB::table('veiculo as v')
            ->select(
                'v.placa',
                'v.motorista_id',
                'v.comprimento',
                'v.largura',
                'v.altura',
                'v.sis_carga_empilha',
                'v.sis_carga_ponte',
                'v.sis_carga_manual',
                'v.cap_kg',
                'v.ocup_veiculo',
                'v.img_carga',
                'v.geo_lat',
                'v.geo_lng',
                'v.ignicao',
                'v.nivel_cons',
                'v.dt_geopos',
                'v.dur_atend_atual',
                'tv.codigo AS cod_tipo_veiculo',
                'tv.descricao as descr_tipo_veiculo',
                'm.nome as nome_motorista'
            )
            ->Join('tipo_veiculo as tv', 'tv.codigo', '=', 'v.cod_tipo_veiculo')
            ->leftJoin('motorista as m', 'm.id', '=', 'v.motorista_id')
            ->where('v.ativo', '=', 'S')

            // Desconsideramos o veículo da etapa atual da solicitação que será baldeada
            ->where('v.placa', '<>', $placa)

            // Consideramos apenas veículos: "M" => Monobloco ou "R" => Carrega/Reboque
            ->whereIn('tv.classe', ['M', 'R'])

            // Caso seja solicitado, selecionamos somente veículos com motorista
            ->where(function ($query) use ($com_motorista) {
                if ($com_motorista == 'S') {
                    $query->whereNotNull('v.motorista_id')
                        ->where('v.motorista_id', '<>', '0')
                        ->where('v.motorista_id', '<>', '');
                }
            })

            ->whereRaw(DB::raw('(SELECT Count(1)
                    FROM coleta col
                    WHERE 
                        ( col.status IN ("C1", "C2", "C3", "C4") AND col.placa_coleta = v.placa ) AND 
                        ( col.coleta_fixa = "C" ) AND  
                        ( col.solic_origem_id = 0 OR col.solic_origem_id IS NULL) AND
                        ( col.dt_prev_coleta <= ' . $data_atual_serv . ') ) =  0'))
            ->get();

        return $veiculos;
    }


    public function MontaArrayVeiculosBaldeacaoColeta(
        $veiculos,
        $coleta,
        $veiculo_solic,
        $data_hora_atual,
        $data_serv_atual
    ) {

        $idx = 0;
        $separador = ' ';

        $menor_tempo = 0;
        $idx_menor_tempo = null;

        foreach ($veiculos as $vei) {

            // Pontuação do veículo
            $pontos_veiculo = 0;
            $det_pontos = '';

            //Tipo de Veiculo
            $veiculo_arr['tipo_veiculo'] = $vei->descr_tipo_veiculo;

            //Localização do veículo
            $loc = new ApiUsoComum();
            $localizacao = $loc->RetornarAreaLocalVeiculo($vei->placa);
            $veiculo_arr['local_veiculo'] = $localizacao['local_veiculo'];

            // Dimensões da carga X veículo
            if (($coleta->comp_carga <= $vei->comprimento) && ($coleta->larg_carga <= $vei->largura) && ($coleta->alt_carga <= $vei->altura)
            ) {
                $veiculo_arr['dimensoes_ok'] = 'S';
                $pontos_veiculo = $pontos_veiculo + 3000;
                // Concatenar DOIS espaços 
                $det_pontos = $det_pontos . $separador . 'Dim:3000';
            } else {
                $veiculo_arr['dimensoes_ok'] = 'A';
                // Concatenar DOIS espaços 
                $det_pontos = $det_pontos . $separador . 'Dim:0';
            }

            // Montar string das dimensões. Ex: "3,50 x 2 x 1,80"
            $veiculo_arr['dimensoes'] = rgFormataDimensoes($vei->comprimento, $vei->largura, $vei->altura);

            //Ocupação do veículo
            $veiculo_arr['ocup_veiculo'] = floatval($vei->ocup_veiculo);

            if (rgIgualTrimNull($vei->img_carga) == false) {
                $veiculo_arr['img_carga'] = $vei->img_carga;
            } else {
                $veiculo_arr['img_carga'] = '';
            }

            // Cálculo da ocupação da carga para saber se tem espaço no veículo.
            // Volume cúbico da carga
            $veiculo_arr['vol_carga'] = $coleta->comp_carga * $coleta->larg_carga * $coleta->alt_carga;

            // Capacidade cúbica do veículo
            $veiculo_arr['cap_veiculo'] = $vei->comprimento * $vei->largura * $vei->altura;

            // Percentual de ocupação da carga no veículo (sem decimais)
            if (rgIgualZeroNull($veiculo_arr['cap_veiculo'])) {
                $veiculo_arr['ocup_carga'] = 0;
            } else {
                $veiculo_arr['ocup_carga'] = round($veiculo_arr['vol_carga'] / $veiculo_arr['cap_veiculo'] * 100, 0);
            }

            // Espaço disponível no veículo
            $veiculo_arr['espaco_disp_veiculo'] = 100 - $vei->ocup_veiculo;

            // Carga cabe no espaço disponível do veículo?
            if ($veiculo_arr['ocup_carga'] <= $veiculo_arr['espaco_disp_veiculo']) {
                $veiculo_arr['ocup_ok'] = 'S';
                $pontos_veiculo = $pontos_veiculo + 1000;
                // Concatenar DOIS espaços 
                $det_pontos = $det_pontos . $separador . 'Ocup:1000';
            } else {
                $veiculo_arr['ocup_ok'] = 'A';
                // Concatenar DOIS espaços 
                $det_pontos = $det_pontos . $separador . 'Ocup:0';
            }

            // Distância e tempo da posição do veículo até o local 
            // onde está o veículo atual que quer fazer a baldeação
            $veiculo_arr['distancia']    = 0;
            $veiculo_arr['tempo_viagem'] = 0;
            $tempo_estimado = 0;

            if ((rgDifZeroNull($vei->geo_lat)) && (rgDifZeroNull($vei->geo_lng))
                && (rgDifZeroNull($veiculo_solic->geo_lat)) && (rgDifZeroNull($veiculo_solic->geo_lng))
            ) {

                $api = new ApiIntegracao();
                $retorno = $api->CalcularDistanciaOrigem_Destino(
                    $vei->geo_lat,
                    $vei->geo_lng,
                    $veiculo_solic->geo_lat,
                    $veiculo_solic->geo_lng
                );

                $veiculo_arr['distancia'] = $retorno['distance'];
                $tempo_estimado = $retorno['duration'];

                // O tempo estimado está calculado em segundos. Fazemos a conversão para "H:i:s" 
                $veiculo_arr['tempo_viagem'] = rgSecondsToTime($tempo_estimado);
            }

            // Nível de consumo x Distância
            if ((intval($vei->nivel_cons) <> 0) && ($veiculo_arr['distancia'] > 0)) {

                // O nível de consumo indica a relação Consumo por litro x Preço do combustível
                // Então... multiplicamos pela distância que o veículo está do local da coleta para
                // obtermos o veículo mais econômico para atender à solicitação

                // O fator é para dar o 'peso' adequado para cada nível de consumo.
                // 6 é o nível máximo de consumo que temos no cadastro do veículo.
                //
                // Ex: Para nivel de consumo = 1... o fator será 6, para o nivel = 2... o fator será 5, ...
                //
                $fator_nivel_cons = (6 - intval($vei->nivel_cons) + 1);

                // Aqui os pontos terão 3 (três) casas decimais
                //
                $pontos_calc = round(1 / $veiculo_arr['distancia'] * $fator_nivel_cons, 3);
                $pontos_veiculo = $pontos_veiculo + $pontos_calc;

                // Concatenar DOIS espaços 
                $det_pontos = $det_pontos . $separador . 'Niv.Cons:' . $pontos_calc;
            } else {

                // Nível de consumo "0" - Não definido => não ganha pontos
                // Concatenar DOIS espaços 
                $det_pontos = $det_pontos . $separador . 'Niv.Cons:0';
            }

            // Armazenamos no array de veículos a pontuação e o detalhamento da pontuacao 
            // obtida até este ponto da rotina. Nos calculos a seguir estas informações devem
            // ser somadas a partir do array e não mais das variáveis "$pontos_veiculo" e "$detpontos'
            $veiculo_arr['pontos_veiculo'] = $pontos_veiculo;
            $veiculo_arr['det_pontos']     = $det_pontos;

            // A rotina "RetSolicitacoesAlocadasVeiculoAtual" é utilizada em três funções: "RetornarVeiculosFrota",
            // "RetornarVeiculosBaldeacao" e "RetornarVeiculosColeta"
            $coletas_veiculo = $this->RetSolicitacoesAlocadasVeiculoAtual($vei, $data_serv_atual);

            $retorno = $this->MontaArrayColetasVeiculo($coletas_veiculo, $vei);

            // Atribuir para o array Veículos os valores modificados pela rotina "MontaArrayColetasVeiculo"      
            $veiculo_arr['peso_total_coletas'] = $retorno['peso_total_coletas'];

            $veiculo_arr['hr_prev_saida']      = $retorno['hr_prev_saida'];
            $veiculo_arr['qtde_coletas']       = $retorno['qtde_coletas'];
            $veiculo_arr['qtde_solic_and']     = $retorno['qtde_solic_and'];

            // Previsão chegada ao local de coleta
            // Verificamos SE o veículo tem uma solicitação em andamento E está como 'Coleta / Entrega - Iniciada" 
            // (teremos uma previsão de saída)
            if ($veiculo_arr['hr_prev_saida'] <> '') {
                // Previsão de chegada ao local da coleta... partindo da PREVISÃO DE SAÍDA 
                // da solicitação que está sendo atendida pelo veículo
                $seconds = strtotime($veiculo_arr['hr_prev_saida']) + rgTimeToSeconds($veiculo_arr['tempo_viagem']);
                $hr_prev_chegada = date('H:i:s', $seconds);
            } else {
                // Previsão de chegada ao local da coleta... partindo da HORA ATUAL... porque o veículo não está atendendo a uma solicitação no momento
                $seconds = strtotime($data_hora_atual) + rgTimeToSeconds($veiculo_arr['tempo_viagem']);
                $hr_prev_chegada = date('H:i:s', $seconds);
            }

            // Capacidade veículo: KG
            // Peso da solicitação atual + Peso das coletas alocadas para o veículo  x Capacidade do veículo em KG
            if (($coleta->peso + $veiculo_arr['peso_total_coletas']) <= $vei->cap_kg) {
                $veiculo_arr['capacid_peso_ok'] = 'S';
                $veiculo_arr['pontos_veiculo']  = $veiculo_arr['pontos_veiculo'] + 3000;
                // Concatenar DOIS espaços 
                $veiculo_arr['det_pontos']      =  $veiculo_arr['det_pontos'] . $separador . 'Cap.KG:3000';
            } else {
                $veiculo_arr['capacid_peso_ok'] = 'N';
                $veiculo_arr['det_pontos']      =  $veiculo_arr['det_pontos'] . $separador . 'Cap.KG:0';
            }

            // Menor distância e menor tempo
            // Contabilizamos os pontos para MENOR TEMPO... 
            // somente para os veículos que atendem aos requisitos: 
            // sistema de carga, capacid. peso e dimensões

            if (($veiculo_arr['dimensoes_ok'] == 'S') && ($veiculo_arr['capacid_peso_ok'] == 'S')) {

                if ($tempo_estimado > 0) {
                    if (($tempo_estimado <= $menor_tempo) || ($menor_tempo == 0)) {
                        // Armazenar o tempo em segundos
                        $menor_tempo     = $tempo_estimado;
                        $idx_menor_tempo = $idx;
                    }
                }
            }

            // Agora que o array de veículos está com todas as informações, acrescentamos
            // no final o "subarray" das Coletas do veículo
            $veiculo_arr['coletas_veiculo'] = $retorno['array_coletas'];

            //Guarda a hora da previsão de chegada para retornar no array de veículos da baldeacao
            $veiculo_arr['hr_prev_chegada'] = $hr_prev_chegada;

            // Setamos o menor tempo para todos os veículos. Posteriormente setaremos o mais rápido
            $veiculo_arr['menor_tempo'] = 'N';

            $veiculo_arr_final[$idx] = $this->MontaArrayVeicBaldeacaoSemOrdenacao($veiculo_arr, $vei, $data_hora_atual);

            $idx++;
        }

        // ANTES de devolver o array de veículos para ordenação... vamos setar o veículo mais rápido. 
        if ($idx_menor_tempo <> null) {
            // Setamos o veículo que vai demorar menos tempo de viagem
            $veiculo_arr_final[$idx_menor_tempo]['menor_tempo'] = 'S';
        }

        return $veiculo_arr_final;
    }


    public function MontaArrayColetaBaldeacao(
        $coleta,
        $placa,
        $etapa,
        $exped_entrega_man,
        $exped_entrega_tar,
        $vol_carga,
        $dim_carga
    ) {

        // Variáveis para retornar dados da coleta da baldeacao

        $dados_coleta['coleta_id']     = $coleta->id;
        $dados_coleta['numero']        = $coleta->numero;
        $dados_coleta['etapa']         = $etapa;

        $dados_coleta['data_cad']      = $coleta->data_cad;
        $dados_coleta['hora_cad']      = $coleta->hora_cad;

        $dados_coleta['placa_atual']   = $placa;

        $dados_coleta['nome_cliente']  = $coleta->nome_cliente;
        $dados_coleta['local_coleta']  = $coleta->local_coleta;
        $dados_coleta['local_entrega'] = $coleta->local_entrega;
        $dados_coleta['placa_coleta']  = $coleta->placa_coleta;

        $dados_coleta['dt_efet_coleta'] = $coleta->dt_efet_coleta;
        $dados_coleta['hr_sai_coleta']  = $coleta->hr_sai_coleta;

        $dados_coleta['coleta_fixa']    = $coleta->coleta_fixa;
        $dados_coleta['coleta_fixa_id'] = $coleta->coleta_fixa_id;
        $dados_coleta['tipo_veiculo']   = $coleta->tipo_veiculo;

        $dados_coleta['placa_entrega']   = $coleta->placa_entrega;
        $dados_coleta['dt_prev_entrega'] = $coleta->dt_prev_entrega;
        $dados_coleta['hr_prev_entrega'] = $coleta->hr_prev_entrega;
        $dados_coleta['entrega_urgente'] = $coleta->entrega_urgente;

        $dados_coleta['exped_entrega_man'] = $exped_entrega_man;
        $dados_coleta['exped_entrega_tar'] = $exped_entrega_tar;

        $dados_coleta['vol_carg']      = $vol_carga;
        $dados_coleta['dim_carga']     = $dim_carga;

        $dados_coleta['caract_coleta'] = $coleta->caract_coleta;
        $dados_coleta['txt_instrucao'] = $coleta->txt_instrucao;

        return $dados_coleta;
    }


    public function MontaArrayVeicBaldeacaoSemOrdenacao($veiculo_arr, $vei, $data_hora_atual)
    {

        $arr_veic['placa']           = $vei->placa;
        $arr_veic['motorista_id']    = $vei->motorista_id;
        $arr_veic['nome_motorista']  = $vei->nome_motorista;

        $arr_veic['tipo_veiculo']    = $veiculo_arr['tipo_veiculo'];
        $arr_veic['dimensoes_ok']    = $veiculo_arr['dimensoes_ok'];
        $arr_veic['dimensoes']       = $veiculo_arr['dimensoes'];
        $arr_veic['capacid_peso_ok'] = $veiculo_arr['capacid_peso_ok'];
        $arr_veic['capacid_peso_kg'] = rgFormataPesoVeiculo($vei->cap_kg);
        $arr_veic['peso_total_coletas'] = rgFormataPesoVeiculo($veiculo_arr['peso_total_coletas']);

        $arr_veic['ocup_ok']         = $veiculo_arr['ocup_ok'];
        $arr_veic['ocup_veiculo']    = $veiculo_arr['ocup_veiculo'];
        $arr_veic['img_carga']       = $veiculo_arr['img_carga'];

        if (rgIgualTrimNull($veiculo_arr['img_carga']) == false) {
            $arr_veic['url_imagem']  = rgRetornarUrlImagens($veiculo_arr['img_carga']);
        } else {
            $arr_veic['url_imagem']  = '';
        }

        if ($veiculo_arr['distancia'] == 0) {
            $arr_veic['distancia']   = '-';
        } else {
            $arr_veic['distancia']   = rgFormataDistancia($veiculo_arr['distancia']);
        }

        $arr_veic['menor_tempo']     = 'N';
        $arr_veic['tempo_viagem']    = rgRetornaFormataTempoExt($veiculo_arr['tempo_viagem']);

        $arr_veic['hr_prev_chegada'] = $veiculo_arr['hr_prev_chegada'];
        $arr_veic['local_veiculo']   = $veiculo_arr['local_veiculo'];
        $arr_veic['geo_lat']         = $vei->geo_lat;
        $arr_veic['geo_lng']         = $vei->geo_lng;

        if (rgIgualTrimNull($vei->dt_geopos)) {
            $arr_veic['atlz_geopos'] = 'Sem informações';
        } else {
            $arr_veic['atlz_geopos'] = rgRetornaDiferencaDataExt($data_hora_atual, $vei->dt_geopos);
        }

        $arr_veic['ignicao']         = $vei->ignicao;

        $arr_veic['hr_prev_saida']   = $veiculo_arr['hr_prev_saida'];

        $arr_veic['qtde_coletas']    = $veiculo_arr['qtde_coletas'];
        $arr_veic['qtde_solic_and']  = $veiculo_arr['qtde_solic_and'];
        // Para garantir 03 casas decimais 
        $arr_veic['pontos_veiculo']  = round($veiculo_arr['pontos_veiculo'], 3);
        $arr_veic['det_pontos']      = trim($veiculo_arr['det_pontos']);

        $arr_veic['coletas_veiculo'] = $veiculo_arr['coletas_veiculo'];

        return $arr_veic;
    }


    public function Local_ExecutarBaldeacaoPatio($coleta_id, $placa_destino)
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

            // Validamos a coleta utilizando o model. Desta forma já temos o registro lido
            // para gravação no final do processo, caso tudo esteja correto
            if (!($coleta = Coleta::where('id', '=', $coleta_id)->first())) {
                $continuar = false;
                $retorno['cod_retorno'] = 'E200';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            } else {

                // Consideramos somente os status que indicam que a carga está com o veículo e que pode occorer uma baldeação até por problemas no veículo 
                // mesmo com uma solicitação de entrega 'em andamento'. 

                // 'E0' - Entrega - Carga definida: NÃO consideramos como carga do veículo enquanto NÃO estiver autorizada. 

                // 'E1' - Entrega - Autorizada: Carga está com o veículo (SE carga_pavilhao <> 'S')

                // 'E3' - Entrega - Chegada: quando o veículo chegou e o atendimento vai demorar para iniciar... 
                // a carga pode ser transferida para outro veículo menor (por exemplo)... para liberar o veículo atual

                // 'EN': Entrega - Não Realizada
                // 'EP': Entrega - Parcial

                // Para os status permitidos, a carga NÃO pode ter sido descarregada => ( carga_pavilhao <> 'S' )
                // Permitimos que CONTRATOS com status "C4" (Expediente Iniciado) sejam transferidos para outro veículo
                if ((in_array($coleta->status, ['CR', 'E1', 'E3', 'EN', 'EP']) && ($coleta->carga_pavilhao != 'S')) || (($coleta->coleta_fixa == 'C') && ($coleta->status == 'C4'))
                ) {

                    $etapa = substr($coleta->status, 0, 1);

                    // Testamos a fase da solicitação para saber qual é a placa de origem
                    if ($etapa == 'C') {
                        // Baldeação de coleta
                        $placa_origem = $coleta->placa_coleta;
                    } else {
                        // Baldeação de entrega
                        $placa_origem = $coleta->placa_entrega;
                    }

                    // Não permite baldear para a mesma placa
                    if ($placa_origem == $coleta->destino) {
                        $continuar = false;
                        $retorno['cod_retorno'] = 'B221';
                        $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    } else {

                        // Instrução especial para baldeação no pátio
                        $instrucao = '07';     /// Baldeação no pátio
                        $api = new Coleta();
                        $txt_instrucao = $api->RetornarDescrInstrucaoColeta($instrucao) . ' => ' . $placa_destino;

                        $vei_dest = DB::table('veiculo')
                            ->select('motorista_id')
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
                } else {

                    $continuar = false;
                    $retorno['cod_retorno'] = 'E211';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $msg_erro = str_replace('$status', $coleta->status, $msg_erro);
                    $retorno['msg_retorno'] = $msg_erro;
                }
            }
        }

        if ($continuar) {

            // SE a solicitação a ser baldeada for CONTRATO... verificamos as COMANDAS.
            if ($coleta->coleta_fixa == 'C') {

                // NÃO permitimos a baldeação de uma solicitação CONTRATO... SE tiver
                // alguma COMANDA com status indicando DESLOCAMENTO - neste caso 
                // o motorista deve cancelar o deslocamento das comandas pelo aplicativo.
                //
                // É uma segurança para que os cálculos de kilometragem fiquem corretos e 
                // não ocorra um registro de deslocamento "C2 / E2" para o veículo "A" 
                // e um registro de chegada "C3 / E3" para o veículo "B" - por exemplo.
                //
                $comandas = DB::table('coleta')
                    ->select('id')
                    ->where('solic_origem_id', '=', $coleta_id)
                    ->whereIn('status', ['C2', 'E2'])
                    ->first();

                if (empty($comandas) == false) {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E272';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $retorno['msg_retorno'] = $msg_erro;
                }
            }
        }

        if ($continuar) {

            try {

                DB::beginTransaction();

                // Testamos a fase da solicitação para saber se atualizamos a placa de COLETA ou de ENTREGA
                // Utilizar o model para atualizar a coleta
                if ($etapa == 'C') {

                    // Se a etapa for uma coleta, guardamos a placa da coleta antes da atualização da coleta 
                    // para atualizarmos posteriormente a ocupação do do veiculo;
                    $placa_anterior = $coleta->placa_coleta;

                    $coleta['placa_coleta']     = $placa_destino;
                    $coleta['motor_coleta_id']  = $vei_dest->motorista_id;
                } else {

                    // Se a etapa for uma entrega, guardamos a placa da entrega antes da atualização da coleta
                    // para atualizarmos posteriormente a ocupação do do veiculo;
                    $placa_anterior = $coleta->placa_entrega;

                    $coleta['placa_entrega']    = $placa_destino;
                    $coleta['motor_entrega_id'] = $vei_dest->motorista_id;
                }

                $coleta['instrucao']       = $instrucao;
                $coleta['txt_instrucao']   = $txt_instrucao;
                $coleta['placa_baldeacao'] = $placa_destino;
                $coleta['baldeada']        = 'S';
                $coleta['ass_user_id']     = auth()->user()->id;

                // Utilizar o model para fazer esta atualização
                $coleta->save();

                // Atualizamos a ocupação do veículo da placa da coleta ou entrega (de acordo com a etapa da coleta)
                // Placa coleta/entrega lida... ANTES de atualizar a nova placa
                $api = new ApiColeta();
                $api->TratarOcupacaoVeiculo($placa_anterior);

                // Atualizar comandas
                // Se for CONTRATO... vamos transferir também as comandas EM ABERTO para o novo veículo
                if ($coleta->coleta_fixa == 'C') {

                    // Transferimos as COMANDAS que estão na fase de COLETA                   
                    // Comandas pertencentes à solicitação CONTRATO em questão

                    // Utilizar o model para fazer esta atualização
                    $comandas = Coleta::where('solic_origem_id', '=', $coleta_id)
                        // Consideramos todos os status da fase de COLETA... EXCETO:
                        //
                        // "C0" - porque as comandas NÃO passam por este status
                        // "C2" - indica DESLOCAMENTO para coleta
                        //
                        ->whereIn('status', ['C1', 'C3', 'C4', 'CR'])
                        ->get();

                    if (count($comandas) > 0) {

                        foreach ($comandas as $cmd) {

                            $cmd->placa_coleta    = $placa_destino;
                            $cmd->motor_coleta_id = $vei_dest->motorista_id;

                            // Comanda na fase de COLETA gravamos o veículo
                            // e motorista da ENTREGA
                            //                            
                            $cmd->placa_entrega    = $placa_destino;
                            $cmd->motor_entrega_id = $vei_dest->motorista_id;

                            $cmd->ass_user_id     = auth()->user()->id;
                            $cmd->save();
                        }
                    }

                    // Transferimos as COMANDAS que estão na fase de ENTREGA
                    // Comandas pertencentes à solicitação CONTRATO em questão

                    // Utilizar o model para fazer esta atualização
                    $comandas = Coleta::where('solic_origem_id', '=', $coleta_id)
                        // Consideramos todos os status da fase de ENTREGA... EXCETO:
                        //
                        // "E2" - indica DESLOCAMENTO para entrega
                        // "ER" (entrega realizada): a carga NÃO está mais com o veículo 
                        //
                        ->whereIn('status', ['E0', 'E1', 'E3', 'E4'])
                        ->get();

                    if (count($comandas) > 0) {

                        foreach ($comandas as $cmd) {

                            // Comanda na fase de ENTREGA: gravamos SOMENTE o veículo
                            // e o motorista da ENTREGA.
                            //
                            $cmd->placa_entrega    = $placa_destino;
                            $cmd->motor_entrega_id = $vei_dest->motorista_id;
                            $cmd->ass_user_id      = auth()->user()->id;
                            $cmd->save();
                        }
                    }
                }

                DB::commit();

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {
                $retorno['cod_retorno'] = 'E273';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());

                DB::rollback();
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }

    public function Local_SetarEntregaConsolidada($coleta_id, $consolidada)
    {
        $continuar = true;
        $retorno = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $coleta = DB::table('coleta')
                ->select('id', 'status')
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

        if ($continuar) {

            // Validação
            // Somente solicitações em aberto na etapa de ENTREGA (Exceto 'E0' - que ainda não foi autorizada) 
            if (in_array($coleta->status, ['E1', 'E2', 'E3', 'E4'])) {

                if ($consolidada != 'S') {
                    $consolidada = 'N';
                }

                try {

                    DB::beginTransaction();

                    // Utilizar o 'model' para fazer a atualização do registro
                    if ($coletaaux = Coleta::find($coleta_id)) {

                        $coletaaux['entrega_consolidada'] = $consolidada;
                        $coletaaux['ass_user_id'] = auth()->user()->id;

                        $coletaaux->save();
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
            } else {
                $retorno['cod_retorno'] = 'E294';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }

    public function Local_RetornarColetasResumoDia($data_prevista)
    {

        $dados = array();

        $data_prevista = substr($data_prevista, 0, 10);

        // Solicitações em andamento
        $coletas = DB::table('coleta as col')
            ->select(
                'col.*',
                'col.id AS coleta_id',

                DB::raw('IF(col.status IN ("C0", "C1", "C2", "C3", "C4"), col.dt_prev_coleta, col.dt_prev_entrega) AS dt_prev_col_ent'),
                DB::raw('IF(col.status IN ("C0", "C1", "C2", "C3", "C4"), col.hr_prev_coleta, col.hr_prev_entrega) AS hr_prev_col_ent'),
                DB::raw('IF(col.status IN ("C0", "C1", "C2", "C3", "C4"), col.placa_coleta, col.placa_entrega) AS placa'),

                'lc.*',
                'le.*',

                'lc.nome AS local_coleta',
                'lc.geo_lat AS geo_lat_coleta',
                'lc.geo_lng AS geo_lng_coleta',

                'lc.endereco AS endereco_coleta',
                'lc.bairro AS bairro_coleta',
                'lc.cidade AS cidade_coleta',
                'lc.cep AS cep_coleta',
                'lc.uf AS uf_coleta',
                'lc.fone AS fone_coleta',

                'le.nome as local_entrega',
                'le.geo_lat AS geo_lat_entrega',
                'le.geo_lng AS geo_lng_entrega',

                'le.endereco AS endereco_entrega',
                'le.bairro AS bairro_entrega',
                'le.cidade AS cidade_entrega',
                'le.cep AS cep_entrega',
                'le.uf AS uf_entrega',
                'le.fone AS fone_entrega',

                'tv.descricao as descricao_tipo_veiculo',
                'tvn.descricao as descricao_tipo_veiculo_nec',

                DB::raw('IF(col.status IN ("C0", "C1", "C2", "C3", "C4"), mot_coleta.nome, mot_entrega.nome) AS nome_motorista'),
                DB::raw('IF(col.status IN ("C0", "C1", "C2", "C3", "C4"), mot_coleta.hr_ini_exped, mot_entrega.hr_ini_exped) AS hr_ini_exped'),
                DB::raw('IF(col.status IN ("C0", "C1", "C2", "C3", "C4"), mot_coleta.hr_fim_exped, mot_entrega.hr_fim_exped) AS hr_fim_exped'),

                DB::raw('(select count(cnf.id) from coleta_nf as cnf where (cnf.coleta_id = col.id) AND (cnf.solic_destino_id IS NULL)) as qtde_notas_distrib')
            )
            ->leftjoin('cliente as lc', function ($join) {
                $join->on('lc.codigo', '=', 'col.cod_loc_coleta')
                    ->on('lc.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as le', function ($join) {
                $join->on('le.codigo', '=', 'col.cod_loc_entrega')
                    ->on('le.empresa', '=', 'col.empresa');
            })
            ->leftJoin('tipo_veiculo as tv', 'tv.codigo', '=', 'col.cod_tipo_veiculo')
            ->leftJoin('tipo_veiculo as tvn', 'tvn.codigo', '=', 'col.cod_tipo_veiculo_nec')
            ->leftJoin('motorista as mot_coleta', 'mot_coleta.id', '=', 'col.motor_coleta_id')
            ->leftJoin('motorista as mot_entrega', 'mot_entrega.id', '=', 'col.motor_entrega_id')

            // Não pegamos solicitações com DATA maior que a data PREVISTA                        
            ->where(function ($query) use ($data_prevista) {

                // Novas Coletas e Coletas em Andamento

                $query->where('col.dt_prev_coleta', '<=', $data_prevista);
                $query->whereIn('col.status', ['C0', 'C1', 'C2', 'C3', 'C4']);

                // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
                // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
                $query->where(function ($query) {
                    $query->where('col.coleta_fixa', '!=', 'C')
                        ->orWhere(function ($query) {
                            $query->where('col.coleta_fixa', '=', 'C')
                                ->whereNull('col.solic_origem_id');
                        });
                });

                $query->where(function ($query) {
                    $query->whereNull('col.ocultar_resumo')
                        ->orWhere('col.ocultar_resumo', '!=', 'S');
                });
            })->orWhere(function ($query) use ($data_prevista) {

                // Entregas Pendentes

                $query->where('col.dt_prev_entrega', '<=', $data_prevista);

                // Desconsideramos as COMANDAS dos contratos => (coleta_fixa = "C"  E  solic_origem_id <> zero/null)
                // com qualquer status. Os contratos => (coleta_fixa = "C"  E  solic_origem_id == zero/null) são desconsiderados
                // porque não ficam nenhum dos status: 'CR', 'E0', 'EN', 'EP'.
                $query->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where('col.coleta_fixa', '!=', 'M')
                            ->whereIn('col.status', ['CR', 'E0', 'EN', 'EP'])
                            ->whereNull('col.solic_origem_id');
                    })->orWhere(function ($query) {
                        // As solicitações auxiliares iniciam na fase de entrega. Status = 'E1'
                        // As reentregas de solicitações auxiliares passam pela etapa de coleta e podem ficar com Status = 'CR'
                        $query->where('col.coleta_fixa', '=', 'M')
                            ->whereIn('col.status', ['CR', 'E0', 'EN', 'EP'])
                            ->whereNotNull('col.solic_origem_id');
                    })->orWhere(function ($query) {
                        // Não mostramos como entrega pendente uma solicitação origem Multi-Destinos, que conste como descarregada no pavilhão.
                        // Marcamos automaticamente (carga_pavilhao == S) quando todas as solicitações auxiliares foram geradas.
                        $query->where('col.coleta_fixa', '=', 'M')
                            ->where('col.status', '=', 'CR')
                            ->where(function ($query) {
                                $query->whereNull('col.carga_pavilhao')
                                    ->orWhere('col.carga_pavilhao', '!=', 'S');
                            })
                            ->whereNull('col.solic_origem_id');
                    });
                });

                $query->where(function ($query) {
                    $query->whereNull('col.ocultar_resumo')
                        ->orWhere('col.ocultar_resumo', '!=', 'S');
                });

                // Em qualquer situação mostramos apenas se a solicitação de reentrega não foi gerada.
                $query->where(function ($query) {
                    $query->whereNull('col.reentrega_gerada')
                        ->orWhere('col.reentrega_gerada', '!=', 'S');
                });
            })

            ->orderBy('dt_prev_col_ent', 'asc')
            ->orderBy('hr_prev_col_ent', 'asc')
            ->get();

        if (count($coletas) > 0) {

            $ind = 0;

            foreach ($coletas as $col) {

                $dados[$ind]['coleta_id'] = $col->coleta_id;
                $dados[$ind]['numero'] = $col->numero;
                $dados[$ind]['coleta_fixa'] = $col->coleta_fixa;
                $dados[$ind]['solic_origem_id'] = $col->solic_origem_id;
                $dados[$ind]['qtde_notas_distrib'] = $col->qtde_notas_distrib;

                $dados[$ind]['dt_prev_col_ent'] = $col->dt_prev_col_ent;
                $dados[$ind]['hr_prev_col_ent'] = $col->hr_prev_col_ent;

                $dados[$ind]['cod_loc_coleta']    = $col->cod_loc_coleta;
                $dados[$ind]['local_coleta']      = $col->local_coleta;
                $dados[$ind]['endereco_coleta']   = $col->endereco_coleta;
                $dados[$ind]['bairro_coleta']     = $col->bairro_coleta;
                $dados[$ind]['cidade_coleta']     = $col->cidade_coleta;
                $dados[$ind]['uf_coleta']         = $col->uf_coleta;
                $dados[$ind]['cep_coleta']        = $col->cep_coleta;
                $dados[$ind]['fone_coleta']       = $col->fone_coleta;
                $dados[$ind]['hr_ini_coleta_man'] = $col->hr_ini_coleta_man;
                $dados[$ind]['hr_fim_coleta_man'] = $col->hr_fim_coleta_man;
                $dados[$ind]['hr_ini_coleta_tar'] = $col->hr_ini_coleta_tar;
                $dados[$ind]['hr_fim_coleta_tar'] = $col->hr_fim_coleta_tar;
                $dados[$ind]['geo_lat_coleta']    = $col->geo_lat_coleta;
                $dados[$ind]['geo_lng_coleta']    = $col->geo_lng_coleta;

                $dados[$ind]['dt_efet_coleta']    = $col->dt_efet_coleta;
                $dados[$ind]['hr_partida_coleta'] = $col->hr_partida_coleta;
                $dados[$ind]['hr_cheg_coleta']    = $col->hr_cheg_coleta;
                $dados[$ind]['hr_atend_coleta']   = $col->hr_atend_coleta;
                $dados[$ind]['hr_sai_coleta']     = $col->hr_sai_coleta;
                $dados[$ind]['placa_coleta']      = $col->placa_coleta;
                $dados[$ind]['solicitante']       = $col->solicitante;

                $dados[$ind]['cod_loc_entrega']    = $col->cod_loc_entrega;
                $dados[$ind]['local_entrega']      = $col->local_entrega;
                $dados[$ind]['endereco_entrega']   = $col->endereco_entrega;
                $dados[$ind]['bairro_entrega']     = $col->bairro_entrega;
                $dados[$ind]['cidade_entrega']     = $col->cidade_entrega;
                $dados[$ind]['uf_entrega']         = $col->uf_entrega;
                $dados[$ind]['cep_entrega']        = $col->cep_entrega;
                $dados[$ind]['fone_entrega']       = $col->fone_entrega;
                $dados[$ind]['hr_ini_entrega_man'] = $col->hr_ini_entrega_man;
                $dados[$ind]['hr_fim_entrega_man'] = $col->hr_fim_entrega_man;
                $dados[$ind]['hr_ini_entrega_tar'] = $col->hr_ini_entrega_tar;
                $dados[$ind]['hr_fim_entrega_tar'] = $col->hr_fim_entrega_tar;
                $dados[$ind]['geo_lat_entrega']    = $col->geo_lat_entrega;
                $dados[$ind]['geo_lng_entrega']    = $col->geo_lng_entrega;

                $dados[$ind]['dt_efet_entrega']    = $col->dt_efet_entrega;
                $dados[$ind]['hr_partida_entrega'] = $col->hr_partida_entrega;
                $dados[$ind]['hr_cheg_entrega']    = $col->hr_cheg_entrega;
                $dados[$ind]['hr_atend_entrega']   = $col->hr_atend_entrega;
                $dados[$ind]['hr_sai_entrega']     = $col->hr_sai_entrega;
                $dados[$ind]['placa_entrega']      = $col->placa_entrega;
                $dados[$ind]['recebedor']          = $col->recebedor;

                $dados[$ind]['entrega_urgente'] = $col->entrega_urgente;
                $dados[$ind]['carga_pavilhao']  = $col->carga_pavilhao;
                $dados[$ind]['reentrega']       = $col->reentrega;

                $dados[$ind]['descricao_tipo_veiculo'] = $col->descricao_tipo_veiculo;
                $dados[$ind]['descricao_tipo_veiculo_nec'] = $col->descricao_tipo_veiculo_nec;

                $dados[$ind]['motor_coleta_id']  = $col->motor_coleta_id;
                $dados[$ind]['motor_entrega_id'] = $col->motor_entrega_id;
                $dados[$ind]['nome_motorista']   = $col->nome_motorista;
                $dados[$ind]['hr_ini_exped']     = $col->hr_ini_exped;
                $dados[$ind]['hr_fim_exped']     = $col->hr_fim_exped;

                $dados[$ind]['placa'] = $col->placa;

                $dados[$ind]['volumes'] = $col->volumes;
                $dados[$ind]['especie'] = $col->especie;
                $dados[$ind]['peso']    = $col->peso;

                $dados[$ind]['comp_carga'] = $col->comp_carga;
                $dados[$ind]['larg_carga'] = $col->larg_carga;
                $dados[$ind]['alt_carga']  = $col->alt_carga;

                $dados[$ind]['caract_coleta'] = $col->caract_coleta;

                if (rgIgualTrimNull($col->img_carga) == false) {
                    $dados[$ind]['url_imagem'] = rgRetornarUrlImagens($col->img_carga);
                } else {
                    $dados[$ind]['url_imagem'] = '';
                }

                $dados[$ind]['status'] = $col->status;

                if (in_array($col->status, ['C0', 'C1', 'C2', 'C3', 'C4'])) {
                    $dados[$ind]['etapa'] = 'C';
                    $dados[$ind]['coletado'] = 'N';

                    $dados[$ind]['definir_veiculo_previsto'] = ($col->status == 'C0') ? 'S' : 'N';
                    $dados[$ind]['definir_motorista_previsto'] = (in_array($col->status, ['C0', 'C1'])) ? 'S' : 'N';
                }

                if (in_array($col->status, ['CR', 'E0', 'EN', 'EP'])) {
                    $dados[$ind]['etapa'] = 'E';
                    $dados[$ind]['coletado'] = 'S';

                    if (in_array($col->status, ['CR', 'E0'])) {

                        if ($col->coleta_fixa == 'M' && rgIgualZeroNull($col->solic_origem_id) && $col->status == 'CR') {
                            $dados[$ind]['definir_veiculo_previsto'] = 'N';
                            $dados[$ind]['definir_motorista_previsto'] = 'N';
                        } else {
                            $dados[$ind]['definir_veiculo_previsto'] = ($col->status == 'CR') ? 'S' : 'N';
                            $dados[$ind]['definir_motorista_previsto'] = 'S';
                        }
                    } else {
                        $dados[$ind]['definir_veiculo_previsto'] = 'N';
                        $dados[$ind]['definir_motorista_previsto'] = 'N';
                    }
                }

                if (($col->hr_ini_exped != null) && ($col->hr_fim_exped != null)) {

                    $dados[$ind]['exped_ok'] = 'N';
                    $dados[$ind]['msg_exped'] = 'Fora do expediente';

                    if ($this->dentroDoExpediente($col->hr_prev_col_ent, $col->hr_ini_exped, $col->hr_fim_exped)) {
                        $dados[$ind]['exped_ok'] = 'S';
                        $dados[$ind]['msg_exped'] = '';
                    }
                } else {
                    $dados[$ind]['exped_ok'] = 'N';
                    $dados[$ind]['msg_exped'] = 'Expediente não definido';
                }

                $ind++;
            }
        }

        $retorno['dados'] = $dados;

        return $retorno;
    }

    public function dentroDoExpediente($hora, $ini_exped, $fim_exped)
    {

        if ($fim_exped < $ini_exped) {
            // Se o horário de término do expediente for menor que o de início, significa que se estende para o dia seguinte
            // Verificamos se o horário está após o início OU antes do fim para considerar o expediente
            return ($hora >= $ini_exped) || ($hora <= $fim_exped);
        } else {
            // Caso contrário, o expediente é dentro do mesmo dia
            // Verificamos se o horário está entre o início e o fim do expediente
            return ($hora >= $ini_exped) && ($hora <= $fim_exped);
        }
    }

    public function Local_DefinirMotoristaPrevisto($coleta_id, $motorista_id, $etapa)
    {

        $continuar = true;
        $retorno   = array();

        if (!($coleta = Coleta::find($coleta_id))) {
            $continuar = false;
            $retorno['cod_retorno'] = 'E200';
            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
            $retorno['msg_retorno'] = $msg_erro;
        }

        if ($continuar) {

            if ($etapa == 'C') {

                // Motorista PREVISTO: motorista atribuido à solicitação na etapa de organização da rotina de trabalho.
                // O motorista PREVISTO para atender à solicitação pode ser atribuido na etapa de COLETA somente SE a coleta NÃO FOI INICIADA. 
                // Quando a etapa de COLETA for INICIADA o sistema irá atribuir novamente o motorista que está no VEÍCULO naquele momento e que pode ser DIFERENTE do motorista atribuido por esta rotina.

                if (in_array($coleta->status, ['C0', 'C1'])) {
                    $continuar = true;   // já é true... apenas para clareza no fluxo
                } else {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E305';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            } else {

                // Motorista PREVISTO: motorista atribuido à solicitação na etapa de organização da rotina de trabalho.  
                // O motorista PREVISTO para atender à solicitação pode ser atribuido na etapa de ENTREGA somente SE a entrega NÃO FOI INICIADA. 
                // Quando a etapa de ENTREGA for INICIADA o sistema irá atribuir novamente o motorista que está no VEÍCULO naquele  momento e que pode ser DIFERENTE do motorista atribuido por esta rotina.

                if (in_array($coleta->status, ['CR', 'E0'])) {

                    // Solicitação origem Multi-Destinos com coleta realizada, não pode definir motorista PREVISTO
                    if (($coleta->coleta_fixa == 'M') && (rgIgualZeroNull($coleta->solic_origem_id)) && ($coleta->status == 'CR')) {
                        $continuar = false;
                        $retorno['cod_retorno'] = 'E307';
                        $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    } else {
                        $continuar = true;   // já é true... apenas para clareza no fluxo
                    }
                } else {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'E306';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            }

            if ($continuar) {

                if (rgIgualZeroNull($motorista_id)) {

                    // Setamos null para garantir que não vai dar erro de chave estrangeira.
                    // Isso serve para 'desvincular' o motorista previsto se necessário.
                    $motorista_id = null;
                } else {

                    $motorista = Motorista::where('id', '=', $motorista_id)->first();

                    if (empty($motorista) == false) {

                        if ($motorista->ativo == 'S') {
                            $continuar = true;   // já é true... apenas para clareza no fluxo
                        } else {
                            $continuar = false;
                            $retorno['cod_retorno'] = 'E303';
                            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        }
                    } else {
                        $continuar = false;
                        $retorno['cod_retorno'] = 'B223';
                        $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        $msg_erro = str_replace('$motorista_id', $motorista_id, $msg_erro);
                        $retorno['msg_retorno'] = $msg_erro;
                    }
                }
            }

            if ($continuar) {

                try {

                    // O registro da coleta já foi lido no início da rotina
                    // Utilizar o model para atualizar o registro para disparar os eventos Update

                    // Atualizamos o registro somente se o motorista for DIFERENTE
                    // do motorista já definido... para não gerar log desnecessário

                    if ($etapa == 'C') {
                        if ($coleta['motor_coleta_id'] != $motorista_id) {
                            $coleta['motor_coleta_id'] = $motorista_id;
                        }
                    } else {
                        if ($coleta['motor_entrega_id'] != $motorista_id) {
                            $coleta['motor_entrega_id'] = $motorista_id;
                        }
                    }

                    $coleta['ass_user_id'] = auth()->user()->id;
                    $coleta->save();

                    $retorno['cod_retorno'] = 'Z100';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } catch (\Exception $e) {
                    $retorno['cod_retorno'] = 'E304';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                    $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
                }
            }
        }

        $resultado['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }
}
