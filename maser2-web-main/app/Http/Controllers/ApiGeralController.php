<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ApiGeral;

class ApiGeralController extends Controller
{
    public function GetVeiculosDisponiveis(Request $request)
    {

        try {
            $api = new ApiGeral();
            $resultado = $api->Local_GetVeiculosDisponiveis();
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function AtualizarOcupacaoVeiculo(Request $request)
    {

        try {

            $img_base64   = $request->get('img_base64');
            $ocup_veiculo = $request->get('ocup_veiculo');

            $api = new ApiGeral();
            $resultado = $api->Local_AtualizarOcupacaoVeiculo($img_base64, $ocup_veiculo);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }
}
