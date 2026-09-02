<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Notif extends Model
{
    protected $table = 'notif';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tipo_usuario',
        'user_id',
        'evento',
        'titulo',
        'texto',
        'reg_id',
        'lida',
        'ass_user_id'
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            self::AfterInsert($model);
        });
    }

    static function AfterInsert($model)
    {
        self::EnviarNotificacaoUsuario($model);
    }

    static function EnviarNotificacaoUsuario($notif)
    {
        $continuar = true;

        $users = DB::table('users')->where('id', '=', $notif->user_id)->first();

        if (empty($users)) {
            // Em alguns casos o user_id pode ser NULL... então não enviamos PUSH NOTIFICATION
            // e retornamos uma mensagem de erro para sabermos disso. 
            $continuar = false;
            $retorno['cod_retorno'] = 'A209';
            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            $msg_erro = str_replace('$user_id', $notif->user_id, $msg_erro);
            $retorno['msg_retorno'] = $msg_erro;
        } else {

            //Enviamos notificações apenas para usuários do tipo 'M' => Motorista
            if ($users->user_type != 'M') {
                $continuar = false;
                $retorno['cod_retorno'] = 'A209';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$user_id', $notif->user_id, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }
        }

        if ($continuar) {

            $motorista = DB::table('motorista')->where('user_id', '=', $notif->user_id)->first();

            if (empty($motorista)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'B203';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$email', 'ID: ' . $notif->user_id, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            } else {

                if (rgIgualTrimNull($motorista->id_disp)) {
                    $continuar = false;
                    $retorno['cod_retorno'] = 'A300';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            }
        }

        if ($continuar) {

            $disp_app = DB::table('disp_app')->where('id_disp', '=', $motorista->id_disp)->first();

            if (empty($disp_app)) {
                $continuar = false;
                $retorno['cod_retorno'] = 'A207';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$id_disp', $motorista->id_disp, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            }
        }

        if ($continuar) {

            $som = "maser_app"; //Nome do arquivo é maser_app.mp3
            $array_device = array($disp_app->id_disp);

            $push = new Notif();
            $result = $push->EnviarPushNotification($notif->titulo, $som, $array_device);

            if ($result['retorno'] == false) {
                $retorno['cod_retorno'] = $result['cod_retorno'];
                $retorno['msg_retorno'] = $result['msg_retorno'];
            } else {
                $retorno['cod_retorno'] = 'Z100';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }

    public function EnviarPushNotification($titulo, $som, $lista_dest)
    {

        $app_id = rgGetAppIdOneSignal();
        $channel_id = rgGetChannelIDOneSignal();

        $content = array("en" => $titulo); //Texto da mensagem que será enviado, o idioma "en" é obrigatório.

        $fields = array(
            'priority' => 10, //Prioridade de entrega através do servidor de envio (exemplo GCM / FCM). Alta prioridade.
            //'android_accent_color' => "EF7840", //Define a cor de fundo do círculo de notificação "Laranja".
            'app_id' => $app_id,
            'android_channel_id' => $channel_id, //Canais Apartir Android Oreo.          
            'include_player_ids' => $lista_dest,
            'contents' => $content,
            'android_sound' => $som,
            'ios_sound' => $som . '.wav', //No IOS, precisa por a extensão.
        );

        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        $err = curl_error($ch);

        curl_close($ch);

        if ($err) {

            $retorno['retorno'] = false;
            $retorno['cod_retorno'] = 'Z200';
            $retorno['msg_retorno'] = 'Erro no envio da notificação Push:' . $err;

            // "Criamos" um token_error para que o retorno do crash geral fique igual
            // ao retorno do envio com erros/sucesso total. Desta forma não vai dar erro 
            // se as funções que chamaram a função estiverem esperando um "token_err"
            $retorno['token_err'] = $retorno['msg_retorno'];
        } else {

            $retenvio = $this->RetStatusEnvioNotificacaoPushOneSignal($response, $lista_dest);

            $retorno['cod_retorno'] = $retenvio['cod_retorno'];
            $retorno['retorno']     = $retenvio['retorno'];
            $retorno['token_err']   = $retenvio['token_err'];
        }

        return $retorno;
    }

    public function RetStatusEnvioNotificacaoPushOneSignal($response, $lista_dest)
    {

        $retorno = array();

        $ret_push = json_decode($response, true);

        //No início consideramos que não existem erros, mas que algum push possa ter
        //sido entregue/enviado. Depois iremos tratar alguma situacao em que
        //temos certeza que o erro foi geral e que nenhum push foi enviado
        //ex: player_id no formato invalido
        $retorno['cod_retorno'] = 'Z100';
        $retorno['token_err'] = array();

        if (array_key_exists('errors', $ret_push) == false) {

            $retorno['retorno'] = true;
        } else {

            $token_err = array();

            //Houve erros no envio. Já vamos sinalizar isto no retorno
            $retorno['retorno'] = false;

            foreach ($lista_dest as $regdisp => $player_id) {

                if (array_key_exists('errors', $ret_push) == true) {

                    if (array_key_exists('invalid_player_ids', $ret_push['errors']) == true) {

                        if (in_array($player_id, $ret_push['errors']['invalid_player_ids'])) {
                            array_push($token_err, $player_id);
                        }
                    }

                    // Se o retorno contiver um erro no formato a seguir, temos certeza que nenhum
                    // push foi enviado. Esta situação acontece se tiver um player_id no formato errado  
                    // {errors:[Incorrect player_id format in include_player_ids (not a valid UUID):  
                    //          4e2cb4f9-1d12-48d1-83f1-3efe28374f7aXXX]} 
                    if (isset($ret_push['errors'][0])) {

                        if (strpos($ret_push['errors'][0], 'Incorrect player_id format') >= 0) {
                            array_push($token_err, $ret_push['errors'][0]);
                            $retorno['cod_retorno'] = 'Z200'; //Erro Geral
                        }
                    }
                }
            }

            $retorno['token_err'] = $token_err;
        }

        return $retorno;
    }
}
