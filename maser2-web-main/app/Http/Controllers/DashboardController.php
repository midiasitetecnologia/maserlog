<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Dashboard;

class DashboardController extends Controller
{

    public function RetornarClientesColetasCadIncomp(Request $request)
    {

        try {

            $api = new Dashboard();
            $resultado = $api->Local_RetornarClientesColetasCadIncomp();
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function RetornarMsgWisdomUser(Request $request)
    {

        try {

            $user_id = $request->get('user_id');

            $api = new Dashboard();
            $resultado = $api->Local_RetornarMsgWisdomUser($user_id);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function RetornarResumoFrotaHome()
    {

        try {
            $api = new Dashboard();
            $resultado = $api->Local_RetornarResumoFrotaHome();
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function RetornarResumoColetasHome()
    {

        try {
            $api = new Dashboard();
            $resultado = $api->Local_RetornarResumoColetasHome();
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function RetornarResumoKmTempoHome()
    {

        try {
            $api = new Dashboard();
            $resultado = $api->Local_RetornarResumoKmTempoHome();
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function RetornarColetasEmissaoNotas()
    {

        try {
            $api = new Dashboard();
            $resultado = $api->Local_RetornarColetasEmissaoNotas();
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function RetornarMotoristasDisponiveis()
    {

        try {
            $api = new Dashboard();
            $resultado = $api->Local_RetornarMotoristasDisponiveis();
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function RetornarColetasMultiDestinosRealizadas(Request $request)
    {

        try {

            $coleta_id = $request->get('coleta_id');

            $api = new Dashboard();
            $resultado = $api->Local_RetornarColetasMultiDestinosRealizadas($coleta_id);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function RetornarEntregasNaoRealizadasReentrega(Request $request)
    {

        try {

            $coleta_id = $request->get('coleta_id');

            $api = new Dashboard();
            $resultado = $api->Local_RetornarEntregasNaoRealizadasReentrega($coleta_id);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function RetornarTarefasHome()
    {

        try {
            $api = new Dashboard();
            $resultado = $api->Local_RetornarTarefasHome();
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }
}
