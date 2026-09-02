<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use DB;

class ApiGeral extends Model
{

    public function TokenAtivacaoCtaValido($email, $token, $user_id)
    {

        $token_valido = false;

        $str_token = $email . $user_id;

        $chave = rgGetHashKeyMaser();

        // Geramos um token criptografado da string enviada.. para comparar com o token original
        $token_calc = hash_hmac('sha256', $str_token, $chave);

        if ($token_calc == $token) {
            $token_valido = true;
        }

        return $token_valido;
    }

    public function Local_GetVeiculosDisponiveis()
    {

        $continuar = true;
        $dados     = array();
        $resultado = array();

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

            if ($motorista->ativo != 'S') {
                $retorno['cod_retorno'] = 'B217';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } else {

                $veiculo = new Veiculo();
                $dados = $veiculo->RetornarVeiculosDisponiveis($motorista->id);

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];
        $resultado['dados'] = $dados;

        return $resultado;
    }

    public function AtlzGeoPosVeiculos($apikey)
    {

        $continuar = true;
        $resultado = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            if (!rgGetApiKeyValido($apikey)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'A205';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        if ($continuar) {

            $app = new ApiUsoComum();
            $veiculos = $app->RetornarVeiculosRastreaveis();

            if (!empty($veiculos)) {

                foreach ($veiculos['data'] as $regveiculos) {

                    if (isset($regveiculos['rotulo'])) {

                        $placa = $regveiculos['rotulo']; //O "rotulo" vem do array sem formatação. Ex: ABC1234, XYZ9K21

                        $geo_lat = $regveiculos['latitude'];
                        $geo_lng = $regveiculos['longitude'];
                        $ignicao = $regveiculos['ignicao'] == '1' ? 'S' : 'N';

                        if (rgDifZeroNull($geo_lat) && rgDifZeroNull($geo_lng)) {

                            $timezone_app = date_default_timezone_get();
                            $data_hora_atual = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

                            //Atualizamos a posição do veículo e status de ignição
                            // Campos geo_lat, geo_lng e ignição não grava log.
                            $veiculo = Veiculo::where('usar_gps', '=', 'V') //Rastreador do Veículo.
                                ->where(function ($query) use ($placa) {
                                    $query->where(DB::raw('regex_alfanum(placa)'), '=', rgRegexAlfaNum($placa))
                                        ->orWhere(DB::raw('regex_alfanum(placa_cavalo)'), '=', rgRegexAlfaNum($placa));
                                })
                                ->update([
                                    'geo_lat'     => $geo_lat,
                                    'geo_lng'     => $geo_lng,
                                    'dt_geopos'   => $data_hora_atual,
                                    'ignicao'     => $ignicao,
                                    'ass_user_id' => null
                                ]);
                        }
                    }
                }

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } else {
                $retorno['cod_retorno'] = 'Z101';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function GravarOcupacaoVeiculo($placa, $img_carga, $ocup_veiculo, $img_base64)
    {

        $timezone_app = date_default_timezone_get();
        $data_hora_atual = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

        if (rgIgualTrimNull($img_carga) == true) {

            // Quando esta rotina for acionada pela API que atualiza a ocupação do 
            // veículo virá conteúdo em $img_base... e não virá nada em $img_carga
            if (rgIgualTrimNull($img_base64) == false) {

                $data_str = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_atual)->format("Ymd");
                $hora_str = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_atual)->format("His");

                // Monta o nome do arquivo. Ex: 'carga-veiculo-abc1234-20200401-143345.jpg' 
                // e já concatena o nome da subpasta de imagens de veículos => "veiculos/".
                $img_carga = 'veiculos/' . 'carga-veiculo-' . strtolower(rgRegexAlfaNum($placa)) . '-' .
                    $data_str . '-' . $hora_str . '.jpg';

                $image_path_file = rgRetornarPastaRaizImagens() . '/' . $img_carga;

                Storage::put($image_path_file, base64_decode($img_base64));
            } // else {  do if rgIgualZeroNull($img_carga) 

            // Quando esta rotina for acionada a partir da atualização da coleta... 
            // o arquivo de imagem da carga já foi armazenado numa pasta... então 
            // aqui apenas gravamos o nome do arquivo no registro do veículo.

        }

        // Aqui não precisamos assinar a alteração com 'user_id' porque estes campos 
        //não vão para o LOG. Então... vai ficar gravado no registro do veículo... 
        // o ID do último usuário que alterou campos que vão para o LOG.
        try {
            $veiculo = DB::table('veiculo')
                ->where('placa', '=', $placa)
                ->update([
                    'img_carga'    => $img_carga,
                    'ocup_veiculo' => $ocup_veiculo,
                    'dt_carga_ocup' => $data_hora_atual
                ]);
        } catch (\Exception $e) {
            //Se der erro não fazemos nada.
        }
    }


    public function Local_AtualizarOcupacaoVeiculo($img_base64, $ocup_veiculo)
    {

        $continuar = true;
        $retorno   = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        //Testamos se o veículo está vazio
        if (rgIgualZeroNull($ocup_veiculo)) {
            //Veiculo vazio: Limpamos a imagem da carga
            $img_base64 = null;
        } else {
            //Veiculo não está vazio. Exigimos a imagem da carga
            if (rgIgualTrimNull($img_base64)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'B218';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
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

            // Pegar Somente o campo PLACA para melhor perfomance
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

            try {

                //Neste caso... passamos "null" para "img_carga" porque o nome do arquivo
                //será montado na rotina "GravarOcupacaoVeiculo".
                $img_carga = null;

                $this->GravarOcupacaoVeiculo($veiculo->placa, $img_carga, $ocup_veiculo, $img_base64);

                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } catch (\Exception $e) {
                $retorno['cod_retorno'] = 'B219';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function Local_DesconectarMotoristas($apikey)
    {

        $continuar = true;
        $tem_erros = false;
        $retorno   = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            if (!rgGetApiKeyValido($apikey)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'A205';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        if ($continuar) {

            //Inicializar variáveis
            $timezone_app    = date_default_timezone_get();
            $data_hora_atual = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
            $data_atual      = Carbon::now($timezone_app)->format('Y-m-d');
            $hora_atual      = Carbon::now($timezone_app)->format('H:i:s');

            // Selecionamos motoristas ATIVOS e INATIVOS... para desconectar 
            // os motoristas que foram desligados da empresa e não fizeram
            // o logout no aplicativo

            $motoristas = DB::table('motorista as m')
                ->select('m.id', 'm.hr_ini_login', 'm.hr_fim_login', 'm.user_id')
                ->where('m.auto_logoff', '=', 'S')
                ->whereNotNull('m.user_id')
                ->get();

            foreach ($motoristas as $motor) {

                $atualizar = true;

                $dt_hr_ini_login = $data_atual . ' ' . $motor->hr_ini_login;
                $dt_hr_ini_login = Carbon::createFromFormat('Y-m-d H:i:s', $dt_hr_ini_login)->format('Y-m-d H:i:s');

                $dt_hr_fim_login = $data_atual . ' ' . $motor->hr_fim_login;
                $dt_hr_fim_login = Carbon::createFromFormat('Y-m-d H:i:s', $dt_hr_fim_login)->format('Y-m-d H:i:s');

                // O horário passa para dia seguinte?
                if ($motor->hr_fim_login < $motor->hr_ini_login) {

                    if ($hora_atual < $motor->hr_fim_login) {
                        $dia_anterior = date('Y-m-d', strtotime('-1 days', strtotime($data_atual)));

                        // Hora final passa para dia seguinte
                        $dt_hr_ini_login = $dia_anterior . ' ' . $motor->hr_ini_login;
                        $dt_hr_ini_login = Carbon::createFromFormat('Y-m-d H:i:s', $dt_hr_ini_login)->format('Y-m-d H:i:s');
                    } else {

                        // Hora final passa para dia seguinte
                        $dia_seguinte = date('Y-m-d', strtotime('+1 days', strtotime($data_atual)));
                        $dt_hr_fim_login = $dia_seguinte . ' ' . $motor->hr_fim_login;
                        $dt_hr_fim_login = Carbon::createFromFormat('Y-m-d H:i:s', $dt_hr_fim_login)->format('Y-m-d H:i:s');
                    }
                }

                // Verificar veículo
                // Pegar somente o campo PLACA para melhor performance
                $veiculo = DB::table('veiculo')
                    ->select('placa')
                    ->where('motorista_id', '=', $motor->id)
                    ->first();

                if (empty($veiculo)) {
                    $desocupar_veiculo = 'N';
                } else {

                    // Verificamos se existem solicitações sendo movimentadas para o veículo atual até HOJE.
                    // SE tiver solicitações sendo movimentadas para o veículo... o motorista NÃO será desconectado.
                    $coletas_veiculo = DB::table('coleta as col')
                        ->select('col.id')

                        // Não pegamos solicitações com DATA DE COLETA futura
                        ->where('col.dt_prev_coleta', '<=', $data_atual)

                        // Coletas e entregas em movimentação
                        ->where(function ($query) use ($veiculo) {
                            $query->where(function ($query1) use ($veiculo) {
                                $query1->whereIn('col.status', ['C2', 'C3', 'C4'])
                                    ->where('col.placa_coleta', '=', $veiculo->placa);
                            })->orWhere(function ($query2) use ($veiculo) {
                                $query2->whereIn('col.status', ['E2', 'E3', 'E4'])
                                    ->where('col.placa_entrega', '=', $veiculo->placa);
                            });
                        })
                        ->first();

                    if (empty($coletas_veiculo)) {
                        $desocupar_veiculo = 'S';
                    } else {
                        $atualizar = false;
                    }
                }

                if ($atualizar) {

                    // Verificar horários
                    if (($data_hora_atual < $dt_hr_ini_login) || ($data_hora_atual > $dt_hr_fim_login)) {
                        // Atualiza registros
                        $retorno = $this->AtualizaRegistrosDesconexao($desocupar_veiculo, $motor);

                        if ($retorno['cod_retorno'] != 'Z100') {
                            $tem_erros = true;
                            break;
                        }
                    }
                }
            }

            if ($tem_erros == false) {
                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }


    public function AtualizaRegistrosDesconexao($desocupar_veiculo, $motor)
    {

        $retorno = array();

        try {

            DB::beginTransaction();

            // Desocupa veículo
            if ($desocupar_veiculo == 'S') {

                // Desvinculamos o motorista do veículo atual (ou de todos que 
                // esteja vinculado... se ocorreu algum erro que permitiu que um 
                // motorista ocupasse mais de um veículo ao mesmo tempo)

                $veiculo = Veiculo::where('motorista_id', '=', $motor->id)
                    ->update([
                        'motorista_id' => null,
                        // chamada lambda -> ass_user_id será null
                        'ass_user_id'  => null
                    ]);
            }

            // Atualiza motorista
            $motorista = Motorista::where('id', '=', $motor->id)
                ->update([
                    'logado'      => 'N',
                    // chamada lambda -> ass_user_id será null
                    'ass_user_id' => null
                ]);


            // Atualiza usuário
            // Limpamos o token do usuário... assim no app o motorista será forçado a fazer um novo login.
            $user = User::find($motor->user_id);
            if ($user) {
                $user->tokens->each(function ($token) {
                    $token->delete();  // Exclui cada token do usuário
                });
            }

            DB::commit();

            $retorno['cod_retorno'] = 'Z100';
            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
        } catch (\Exception $e) {

            $retorno['cod_retorno'] = 'B222';
            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            $msg_erro = str_replace('$erro', $e->getMessage(), $msg_erro);
            $retorno['msg_retorno'] = $msg_erro;

            DB::rollback();
        }

        return $retorno;
    }
}
