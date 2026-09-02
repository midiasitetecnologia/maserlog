<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class ApiMotorista extends Model
{

    public function AutenticarMotoristaViaApi($email, $password, $id_disp)
    {
        $continuar = true;
        $retorno = array();
        $dados = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $user = new User();
            $usuario = $user->AutenticarUsuarioViaApi($email, $password);

            $retorno['cod_retorno'] = $usuario['retorno']['cod_retorno'];
            $retorno['msg_retorno'] = $usuario['retorno']['msg_retorno'];

            if (($usuario['retorno']['cod_retorno']) != 'A100') {
                $continuar = false;
            }
        }

        if ($continuar) {

            $motorista = Motorista::where('user_id', '=', auth()->user()->id)->first();

            if (empty($motorista)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'B203';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$email', $email, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }
        }

        if ($continuar) {

            if ($motorista->ativo != "S") {
                $continuar = false;
                $retorno['cod_retorno'] = 'B215';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$email', $email, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }

            try {

                $timezone_app = date_default_timezone_get();

                $motorista['id_disp'] = $id_disp;
                $motorista['logado'] = "S";
                $motorista['dt_logado'] = Carbon::now($timezone_app);;
                $motorista['ass_user_id'] = auth()->user()->id;

                $motorista->save();
            } catch (\Exception $e) {
                $retorno['cod_retorno'] = 'Z200';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
            }
        }

        if ($continuar) {

            $placa_veiculo = null;
            $ocup_veiculo  = null;
            $url_img_carga = null;
            $gps = null;

            $veiculo = DB::table('veiculo')
                ->select('placa', 'ocup_veiculo', 'img_carga', 'usar_gps')
                ->where('veiculo.motorista_id', '=', $motorista->id)
                ->orderBy('veiculo.placa')
                ->first();

            if (!empty($veiculo)) {
                $placa_veiculo = $veiculo->placa;
                $ocup_veiculo  = $veiculo->ocup_veiculo;
                $gps           = $veiculo->usar_gps;

                if (rgIgualTrimNull($veiculo->img_carga) == false) {
                    $url_img_carga = rgRetornarUrlImagens($veiculo->img_carga);
                }
            }

            $dados = $usuario['dados'];

            // Dados específicos do motorista
            $dados['nome']          = $motorista->nome;
            $dados['celular']       = $motorista->celular;
            $dados['gps']           = $gps;
            $dados['placa_veiculo'] = $placa_veiculo;
            $dados['ocup_veiculo']  = $ocup_veiculo;
            $dados['url_img_carga'] = $url_img_carga;
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];
        $resultado['dados'] = $dados;

        return $resultado;
    }


    public function BuscarDadosMotorista()
    {

        $continuar = true;
        $retorno   = array();
        $dados     = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $motorista = DB::table('motorista')->where('user_id', '=', auth()->user()->id)->first();

            if (empty($motorista)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'B203';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$email', auth()->user()->email, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }
        }

        if ($continuar) {

            $placa = null;
            $gps = null;
            $cod_tipo = null;
            $descr_tipo = null;

            $veiculo = DB::table('veiculo')->select('veiculo.placa', 'veiculo.cod_tipo_veiculo', 'veiculo.usar_gps', 'tipo_veiculo.descricao AS descr_tipo')
                ->leftjoin('tipo_veiculo', 'tipo_veiculo.codigo', '=', 'veiculo.cod_tipo_veiculo')
                ->where('motorista_id', '=', $motorista->id)
                ->first();

            if (!empty($veiculo)) {
                $placa = $veiculo->placa;

                $gps = $veiculo->usar_gps;
                $cod_tipo = $veiculo->cod_tipo_veiculo;
                $descr_tipo = $veiculo->descr_tipo;
            }

            $dados['nome']       = $motorista->nome;
            $dados['cpf']        = $motorista->cpf;
            $dados['celular']    = $motorista->celular;
            $dados['ativo']      = $motorista->ativo;
            $dados['placa']      = $placa;
            $dados['gps']        = $gps;
            $dados['cod_tipo']   = $cod_tipo;
            $dados['descr_tipo'] = $descr_tipo;

            $retorno['cod_retorno'] = 'Z100';
            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];
        $resultado['dados'] = $dados;

        return $resultado;
    }


    public function TrocarVeiculoMotorista($placa, $utilizar)
    {

        $continuar = true;
        $retorno   = array();
        $dados     = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $motorista = DB::table('motorista')->where('user_id', '=', auth()->user()->id)->first();

            if (!empty($motorista)) {
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

            $ocup_veiculo  = null;
            $url_img_carga = null;
            $gps = null;

            $veiculo = DB::table('veiculo')
                ->where('placa', '=', $placa)
                ->first();

            if (empty($veiculo)) {

                $continuar = false;

                if (rgIgualTrimNull($placa)) {
                    $retorno['cod_retorno'] = 'Z100';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } else {
                    $retorno['cod_retorno'] = 'B211';
                    $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $msg_erro = str_replace('$placa', $placa, $msg_erro);
                    $retorno['msg_retorno'] = $msg_erro;
                }
            }
        }

        if ($continuar) {

            if ($utilizar == 'S') {

                $gps = $veiculo->usar_gps;
                $ocup_veiculo = $veiculo->ocup_veiculo;

                if (rgIgualTrimNull($veiculo->img_carga) == false) {
                    $url_img_carga = rgRetornarUrlImagens($veiculo->img_carga);
                }

                //SE o motorista já está ocupando o veículo informado... não precisamos fazer nada.
                if ($veiculo->motorista_id == $motorista_id) {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'Z103';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            } else {
                // Se é para DESOCUPAR o veículo...limpamos o ID do motorista
                $motorista_id = null;
            }
        }

        if ($continuar) {

            try {

                DB::beginTransaction();

                if ($motorista_id != null) {
                    //Desvincular o motorista do veículo anterior (ou de todos que estaja vinculado... 
                    //se ocorreu algum erro que permitiu que um motorista ocupasse mais de um veículo ao mesmo tempo).
                    $veiculoUpdate = Veiculo::where('motorista_id', '=', $motorista_id)
                        ->update([
                            'motorista_id' => null,
                            'ass_user_id'  => auth()->user()->id
                        ]);
                }

                //Vincular o motorista ao novo veículo
                $veiculoUpdate = Veiculo::where('placa', '=', $placa)
                    ->update([
                        'motorista_id' => $motorista_id,
                        'ass_user_id'  => auth()->user()->id
                    ]);

                DB::commit();

                //Retornamos o código de operação executado com sucesso para ambas as situações:
                //se atualizou as informações ou se não atualizou porque eram iguais
                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {
                $retorno['cod_retorno'] = 'B212';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
                DB::rollback();
            }
        }

        // Se conclui a alteração com sucesso, carrega o array "Dados" com o retorno
        if ($retorno['cod_retorno'] == 'Z100') {
            $dados['placa_veiculo'] = $placa;
            $dados['gps']           = $gps;
            $dados['ocup_veiculo']  = $ocup_veiculo;
            $dados['url_img_carga'] = $url_img_carga;
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];
        $resultado['dados'] = $dados;

        return $resultado;
    }

    public function SetaLogoutMotorista()
    {

        $continuar = true;
        $retorno = array();
        $resultado = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            $motorista = Motorista::where('user_id', '=', auth()->user()->id)->first();

            if (empty($motorista)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'B203';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$email', auth()->user()->email, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }
        }

        if ($continuar) {

            try {

                DB::beginTransaction();

                // Fazer logout
                $timezone_app = date_default_timezone_get();

                $motorista['logado'] = 'N';
                $motorista['dt_logado'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
                $motorista['ass_user_id'] = auth()->user()->id;

                $motorista->save();

                // No Logout... removemos o motorista do veículo

                // Desvinculamos o motorista do veículo atual (ou de todos que 
                // esteja vinculado... se ocorreu algum erro que permitiu que um 
                // motorista ocupasse mais de um veículo ao mesmo tempo)
                $Veiculo = Veiculo::where('motorista_id', '=', $motorista->id)
                    ->update([
                        'motorista_id' => null,
                        'ass_user_id'  => auth()->user()->id
                    ]);

                DB::commit();

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {
                $retorno['cod_retorno'] = 'B216';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
                DB::rollback();
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function Local_GetNotificacoesMotorista()
    {

        $continuar = true;
        $retorno   = array();
        $dados     = array();

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
        }

        if ($continuar) {

            $veiculo_placa = $veiculo->placa;

            $timezone_app = date_default_timezone_get();
            $data_atual_serv = Carbon::now($timezone_app)->format('Y-m-d');

            // Selecionamos as notificações das solicitações atribuidas ao veículo (coleta ou entrega) 
            $notif = DB::table('notif')
                ->select('notif.*')

                // Pegamos todas as notificações das COLETAS e ENTREGAS
                ->join('coleta as col', function ($join) {
                    $join->on('col.id', '=', 'notif.reg_id')
                        ->where('notif.evento', '>=', 'C00')
                        ->where('notif.evento', '<=', 'E99');
                })

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

                // CONSIDERAMOS as solicitações em com status 'em andamento' e mais:
                //
                // - 'EN' / 'EP' => a carga ou parte dela ainda está com o veículo 
                //
                //  DESCONSIDERAMOS os status:
                //
                // - 'CN' => porque a coleta não foi realizada.
                // - 'ER'  => porque na prática, a carga não está mais com o veículo da entrega.
                //
                ->where(function ($query) use ($veiculo_placa) {
                    $query->where(function ($query1) use ($veiculo_placa) {
                        $query1->whereIn('col.status', ['C1', 'C2', 'C3', 'C4', 'CR'])
                            ->where('col.placa_coleta', '=', $veiculo_placa);
                    })->orWhere(function ($query2) use ($veiculo_placa) {
                        $query2->whereIn('col.status', ['E1', 'E2', 'E3', 'E4', 'EN', 'EP'])
                            ->where('col.placa_entrega', '=', $veiculo_placa);
                    });
                })

                // Em qualquer situação, a carga não pode ter sido descarregada                
                ->where(function ($query) {
                    $query->whereNull('col.carga_pavilhao')
                        ->orWhere('col.carga_pavilhao', '!=', 'S');
                })

                ->orderBy('notif.id', 'desc')
                ->get();

            if (count($notif) == 0) {
                $continuar = false;
                $retorno['cod_retorno'] = 'Z101';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        if ($continuar) {

            $ind = 0;

            foreach ($notif as $not) {

                $dados[$ind]['notif_id']   = $not->id;
                $dados[$ind]['coleta_id']  = $not->reg_id;
                $dados[$ind]['titulo']     = $not->titulo;
                $dados[$ind]['texto']      = $not->texto;
                $dados[$ind]['lida']       = $not->lida;
                $dados[$ind]['dt_criada']  = $not->created_at;

                if ($not->lida == 'S') {
                    $dados[$ind]['dt_lida']  = $not->updated_at;
                } else {
                    $dados[$ind]['dt_lida'] = null;
                }

                $ind++;
            }

            $retorno['cod_retorno'] = 'Z100';
            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];
        $resultado['dados'] = $dados;

        return $resultado;
    }


    public function Local_SetarNotifLidaMotorista($notif_id)
    {

        $continuar = true;
        $retorno   = array();
        $dados     = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            try {

                // Colocamos 'lida' = 'N' na condição para atualizar o registro 
                // somente se realmente teve alteração... porque utilizamos o 
                // campo 'updated_at'  como 'data de leitura da notificação'

                $notif = Notif::where('id', '=', $notif_id)
                    ->where('lida', '=', 'N')
                    ->update([
                        'lida'        => 'S',
                        'ass_user_id' => auth()->user()->id
                    ]);

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {
                $retorno['cod_retorno'] = 'N202';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function Local_GetTransferCodeVeiculo()
    {

        $continuar = true;
        $retorno   = array();
        $dados     = array();

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
            // Ler o PRIMEIRO registro do veículo => $veiculo... onde:
            $veiculo = DB::table('veiculo')
                ->select('placa', 'transfer_code', 'dt_transfer_code')
                ->where('motorista_id', '=', $motorista_id)
                ->first();

            if (empty($veiculo) == false) {

                $timezone_app = date_default_timezone_get();
                $data_hora_atual = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

                $transfer_code    = $veiculo->transfer_code;
                $dt_transfer_code = $veiculo->dt_transfer_code;

                $dados['transfer_code']    = $transfer_code;
                $dados['dt_transfer_code'] = $dt_transfer_code;
            } else {
                $continuar = false;
                $retorno['cod_retorno'] = 'B201';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }

            if ($continuar) {

                // Verificação do código atual
                if ((rgDifZeroNull($transfer_code)) && ($dt_transfer_code <> null)) {

                    // Se a data da validade do código de transferência 
                    if ($data_hora_atual > $dt_transfer_code) {
                        $continuar = true;   // Já é true - Somente para facilitar o fluxo
                    } else {
                        $continuar = false;
                        $retorno['cod_retorno'] = 'Z103';
                        $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    }
                }
            }
        }

        if ($continuar) {
            // Geramos um código aleatório entre 1.000 e 9.999
            $transfer_code = rand(1000, 9999);
            $dt_transfer_code = date('Y-m-d H:i:s', strtotime('+60 minute', strtotime($data_hora_atual)));

            try {

                Veiculo::where('placa', '=', $veiculo->placa)
                    ->update([
                        'transfer_code'    => $transfer_code,
                        'dt_transfer_code' => $dt_transfer_code,
                        'ass_user_id'      => auth()->user()->id
                    ]);

                $dados['transfer_code']    = $transfer_code;
                $dados['dt_transfer_code'] = $dt_transfer_code;

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {
                $retorno['cod_retorno'] = 'Z200';
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];
        $resultado['dados'] = $dados;

        return $resultado;
    }
}
