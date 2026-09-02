<?php

namespace App;

use Auth;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\EmailAtivarConta;
use App\ApiUsoComum;
use Carbon\Carbon;
use Exception;


class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $dates = ['created_at', 'updated_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'user_type',
        'cliente_id',
        'password',
        'active',
        'ass_user_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            self::beforeInsert($model);
        });

        static::updating(function ($model) {
            self::beforeUpdate($model);
        });

        static::created(function ($model) {
            self::AfterInsert($model);
        });
    }

    static function beforeInsert($model)
    {
        // colocar aqui o código que deseja executar antes do INSERT		
        self::tratar_campos($model);
        self::validar_registro($model);
    }

    static function beforeUpdate($model)
    {
        // colocar aqui o código que deseja executar antes do UPDATE      
        self::tratar_campos($model);
        self::validar_registro($model);
    }

    static function AfterInsert($model)
    {
        if (($model->user_type != 'M') && ($model->active == 'N')) {
            self::EnviarEmailAtivarConta($model);
        }
    }

    static function tratar_campos($model)
    {
        if ($model->user_type != 'C') {
            $model->cliente_id = null;
        }
    }

    static function validar_registro($model)
    {
        if ($model->user_type == 'C') {

            if (rgIgualZeroNull($model->cliente_id)) {
                throw new Exception('Selecione o cliente em que esta conta será vinculada.');
            }
        }
    }

    static function EnviarEmailAtivarConta($model)
    {
        $nome  = $model->name;
        $email = $model->email;
        $id    = $model->id;

        // Geramos um token criptografado com o e-email mais id do usuário que será enviado para ativação. 
        $chave = rgGetHashKeyMaser();
        $token = hash_hmac('sha256', $email . $id, $chave);

        try {
            $model->notify(new EmailAtivarConta($nome, $email, $id, $token));
        } catch (Exception $e) {
            $array_log = array();
            $timezone_app = date_default_timezone_get();
            $evento_log = 'erros_email';
            $funcao_log = 'EnviarEmailAtivarConta';
            $msg_log = 'Erro no envio de e-mail ativação de conta';

            // Inserir um elemento no array do log:
            $ind_log = 0;

            $array_log[$ind_log]['tipo']       = '0';
            $array_log[$ind_log]['msg']        = 'Início do processo';
            $array_log[$ind_log]['err']        = null;
            $array_log[$ind_log]['status']     = '0';
            $array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

            // O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
            // o indice do próximo elemento a ser inserido
            $ind_log = count($array_log);

            // Inserir um elemento no array do log:
            $array_log[$ind_log]['tipo'] = '1';
            $array_log[$ind_log]['msg'] = $msg_log;
            $array_log[$ind_log]['err'] = 'Erro: ' . $e->getMessage();
            $array_log[$ind_log]['status'] = '1';
            $array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

            $app = new ApiUsoComum();
            $app->AdicionarLogTrailler($array_log, $msg_log, $evento_log, $funcao_log, true);

            \Log::info('Computador Local (server): ' . gethostname() . ' ' . '[MASER-EnviarEmailAtivarConta]' . $e->getMessage());
        }
    }

    //------------------------------------------------------------------------------------------------

    public function AutenticarUsuarioViaApi($email, $password)
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

            try {

                if (Auth::attempt(['email' => $email, 'password' => $password, 'active' => 'S'])) {

                    // Revoga todos os tokens do Sanctum do usuário
                    auth()->user()->tokens()->delete();

                    // Gera e armazena o token no Sanctum
                    $sanctumToken = auth()->user()->createToken('API Token')->plainTextToken;
                    // Pega apenas a parte do token, sem o ID
                    list($tokenId, $tokenValue) = explode('|', $sanctumToken);

                    $users = auth()->user();

                    //Estas variáveis são necessárias porque o template está tratando.
                    $userInfo['uid'] = $users->id;
                    $userInfo['displayName'] = $users->name;

                    if ($users->user_type == 'C') {
                        $userInfo['userRole'] = 'cliente';
                    } else {
                        $userInfo['userRole'] = 'admin';
                    }

                    //O Token que devolvemos é o token gerado pelo Sanctum.
                    $dados['api_token'] = $tokenValue;
                    $dados['userInfo'] = $userInfo;

                    $retorno['cod_retorno'] = 'A100';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    $resultado['dados'] = $dados;
                } else {
                    $retorno['cod_retorno'] = 'A200';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            } catch (\Exception $e) {
                $retorno['cod_retorno'] = 'Z200';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
            }
        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }
}
