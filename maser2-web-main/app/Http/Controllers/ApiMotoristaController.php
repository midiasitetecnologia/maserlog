<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\ApiMotorista;

class ApiMotoristaController extends Controller
{

    public function AutenticarMotorista(Request $request)
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
                $id_disp  = $request->get('id_disp');

                $api = new ApiMotorista();
                $resultado = $api->AutenticarMotoristaViaApi($email, $password, $id_disp);
            }
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function GetDadosMotorista(Request $request)
    {

        try {

            $api = new ApiMotorista();
            $resultado = $api->BuscarDadosMotorista();
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function AlterarVeiculoMotorista(Request $request)
    {

        try {

            $placa = $request->get('placa');
            $utilizar = $request->get('utilizar');

            $api = new ApiMotorista();
            $resultado = $api->TrocarVeiculoMotorista($placa, $utilizar);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function SetarLogoutMotorista(Request $request)
    {

        try {

            $api = new ApiMotorista();
            $resultado = $api->SetaLogoutMotorista();
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function GetNotificacoesMotorista(Request $request)
    {

        try {
            $api = new ApiMotorista();
            $resultado = $api->Local_GetNotificacoesMotorista();
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function SetarNotifLidaMotorista(Request $request)
    {

        try {
            $notif_id = $request->get('notif_id');

            $api = new ApiMotorista();
            $resultado = $api->Local_SetarNotifLidaMotorista($notif_id);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function GetTransferCodeVeiculo(Request $request)
    {

        try {
            $api = new ApiMotorista();
            $resultado = $api->Local_GetTransferCodeVeiculo();
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }
}
