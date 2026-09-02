<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ColetasFixasAuto;
use App\ApiGeral;

class ApiRoboController extends Controller
{

    public function GerarColetasFixas(Request $request)
    {

        try {

            $apikey = $request->get('apikey');

            $api = new ColetasFixasAuto();
            $resultado = $api->GerarColetasFixasAuto($apikey);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function AtualizarGeoPosVeiculos(Request $request)
    {

        try {

            $apikey = $request->get('apikey');

            $api = new ApiGeral();
            $resultado = $api->AtlzGeoPosVeiculos($apikey);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function DesconectarMotoristas(Request $request)
    {

        try {

            $apikey = $request->get('apikey');

            $api = new ApiGeral();
            $resultado = $api->Local_DesconectarMotoristas($apikey);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }
}
