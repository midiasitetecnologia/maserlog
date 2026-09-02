<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\ApiUsuario;
use App\User;

class ApiUsuarioController extends Controller
{

    public function AutenticarUsuario(Request $request)
    {

        try {
            //No request da tabela users existe validação para diversos campos e caracteristicas.
            //Não podemos submeter a validação da requisição como ela chega na chamada da API. 
            //O que temos que validar é apenas o email e o password se informados.

            $data['email'] = $request->get('email');
            $data['password'] = $request->get('password');

            $validator = Validator::make(
                $data,
                [
                    'email' => 'required|max:100',
                    'password' => 'required',
                ],
                [
                    'email.required' => 'O email deve ser informado.',
                    'email.max' => 'O email deve ter no máximo :max caracteres.',
                    'password.required' => 'A senha deve ser informada.'
                ]
            );

            if ($validator->fails()) {
                $erros = $validator->errors();
            }

            if (!empty($erros)) {
                $resultado['retorno']['cod_retorno'] = 'Z300';
                $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI($resultado['retorno']['cod_retorno']);
                $resultado['erros'] = $erros;
            } else {
                $email    = $request->get('email');
                $password = $request->get('password');

                $api = new User();
                $resultado = $api->AutenticarUsuarioViaApi($email, $password);
            }
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    /* A API de ativação da conta é uma API de método GET.
      Os parâmetros são enviados na URL e não na requisição como as demais APIs (POST)
    */
    public function AtivarContaUsuario($email, $token)
    {

        //Os parâmetros são obtidos na URL e não requisição      
        $api = new ApiUsuario();

        $resultado = $api->HabilitarContaUsuario($email, $token);

        $titulo = 'BEM-VINDO!';

        if (($resultado['retorno']['cod_retorno'] == 'Z100') || ($resultado['retorno']['cod_retorno'] == 'Z103')
        ) {
            $tipo_mensagem  = 'mensagem_sucesso';
            // Ao invés de retornar a mensagem padrão do Z100, retornamos uma mensagem mais amigavel para o usuário.
            $texto_mensagem = 'Sua conta foi ativada com sucesso.';
        } else {
            $tipo_mensagem  = 'mensagem_erro';
            $texto_mensagem = $resultado['retorno']['msg_retorno'];
        }

        return redirect('exibirNotificacao/' . $tipo_mensagem . '/' . $texto_mensagem . '/' . $titulo);
    }

    public function RegistrarDispositivoUsuario(Request $request)
    {

        try {
            $data = $request->all();

            $api = new ApiUsuario();
            $resultado = $api->RegistrarDevicesUsuario($data);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }
}
