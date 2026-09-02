<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\ApiGeral;
use App\User;
use DB;


class ApiUsuario extends Model
{

    public function HabilitarContaUsuario($email, $token)
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
            $users = DB::table('users')
                ->select('id', 'active')
                ->where('email', '=', $email)
                ->first();

            if (empty($users)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'A200';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        if ($continuar) {

            $api = new ApiGeral();

            if (!$api->TokenAtivacaoCtaValido($email, $token, $users->id)) {
                $retorno['cod_retorno'] = 'A201';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                //Sustituimos na mensagem de erro padrão a variavel de email pelo email enviado
                $msg_erro = str_replace('$email', $email, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            } else {

                if ($users->active == 'S') {
                    $retorno['cod_retorno'] = 'Z103';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } else {
                    if ($users->active == 'B') {
                        $retorno['cod_retorno'] = 'A206';
                        $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    } else {

                        //Se o campo "active" atual for "N" atualizamos o para "S"
                        try {

                            //Temos que utilizar o método save(), senão não executa os afterUpdate no Model.
                            //E nesta situação temos que executar, pois será enviado um e-mail de alteração de status.                    
                            if (!($usersUpdate = User::find($users->id))) {
                                $retorno['cod_retorno'] = 'A200';
                                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                            } else {
                                $usersUpdate['active'] = 'S';
                                $usersUpdate['ass_user_id'] = $users->id;
                                $usersUpdate->save();
                            }

                            $retorno['cod_retorno'] = 'Z100';
                            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        } catch (\Exception $e) {
                            $retorno['cod_retorno'] = 'A208';
                            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                            $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
                        }
                    }
                }
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }

    public function RegistrarDevicesUsuario($data)
    {

        $continuar = true;
        $retorno = array();

        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            if (!rgGetApiKeyValido($data['apikey'])) {
                $retorno['cod_retorno'] = 'A205';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } else {

                if (empty($data['id_disp'])) {
                    $retorno['cod_retorno'] = 'A300';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                } else {

                    if ($disp_app = DispApp::find($data['id_disp'])) {

                        try {

                            $disp_app['descricao']  = $data['descricao'];
                            $disp_app['plataforma'] = $data['plataforma'];
                            $disp_app['versao_so']  = $data['versao_so'];
                            $disp_app['versao_app'] = $data['versao_app'];
                            $disp_app['push_token'] = $data['push_token'];

                            $disp_app->save();

                            $retorno['cod_retorno'] = 'Z100';
                            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        } catch (\Exception $e) {
                            $retorno['cod_retorno'] = 'A302';
                            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                            $msg_erro = str_replace('$id_disp', $data['id_disp'], $msg_erro);
                            $retorno['msg_retorno'] = $msg_erro;
                        }
                    } else {

                        try {

                            $disp_app = new DispApp();

                            $disp_app['id_disp']    = $data['id_disp'];
                            $disp_app['descricao']  = $data['descricao'];
                            $disp_app['plataforma'] = $data['plataforma'];
                            $disp_app['versao_so']  = $data['versao_so'];
                            $disp_app['versao_app'] = $data['versao_app'];
                            $disp_app['push_token'] = $data['push_token'];

                            $disp_app->save();

                            $retorno['cod_retorno'] = 'Z100';
                            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        } catch (\Exception $e) {
                            $retorno['cod_retorno'] = 'A301';
                            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                            $msg_erro = str_replace('$id_disp', $data['id_disp'], $msg_erro);
                            $retorno['msg_retorno'] = $msg_erro;
                        }
                    }
                }
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }
}
