<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\ApiColeta;


class ApiColetaController extends Controller
{

    public function GetDadosColeta(Request $request)
    {        
        try {

            $coleta_id = $request->get('coleta_id');

            $api = new ApiColeta();
            $resultado = $api->Local_GetDadosColeta($coleta_id);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function GetColetasPendentes(Request $request)
    {

        try {

            $api = new ApiColeta();
            $resultado = $api->Local_GetColetasPendentes();
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function SetarInicioAtendColeta(Request $request)
    {

        try {

            $coleta_id = $request->get('coleta_id');

            $api = new ApiColeta();
            $resultado = $api->Local_SetarInicioAtendColeta($coleta_id);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function SetarDeslocaColeta(Request $request)
    {

        try {

            $coleta_id  = $request->get('coleta_id');

            $api = new ApiColeta();
            $resultado = $api->Local_SetarDeslocaColeta($coleta_id);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function SetarChegadaColeta(Request $request)
    {

        try {

            $coleta_id = $request->get('coleta_id');
            $dur_prev_coleta = $request->get('dur_prev_coleta');

            $api = new ApiColeta();
            $resultado = $api->Local_SetarChegadaColeta($coleta_id, $dur_prev_coleta);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function AtualizarNotaFiscalColeta(Request $request)
    {

        try {

            $coleta_nf_id = $request->get('coleta_nf_id');
            $coleta_id    = $request->get('coleta_id');
            $cod_barras   = $request->get('cod_barras');
            $valor        = $request->get('valor');
            $volumes      = $request->get('volumes');

            $api = new ApiColeta();
            $resultado = $api->Local_AtualizarNotaFiscalColeta(
                $coleta_nf_id,
                $coleta_id,
                $cod_barras,
                $valor,
                $volumes
            );
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function ExcluirNotaFiscalColeta(Request $request)
    {

        try {

            $coleta_nf_id = $request->get('coleta_nf_id');
            $coleta_id    = $request->get('coleta_id');
            $cod_barras   = $request->get('cod_barras');

            $api = new ApiColeta();
            $resultado = $api->Local_ExcluirNotaFiscalColeta($coleta_nf_id, $coleta_id, $cod_barras);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function IncluirNotaFiscalColeta(Request $request)
    {

        try {

            $coleta_id  = $request->get('coleta_id');
            $cod_barras = $request->get('cod_barras');
            $serie      = $request->get('serie');
            $numero     = $request->get('numero');
            $valor      = $request->get('valor');
            $volumes    = $request->get('volumes');
            $dig_cnpj   = $request->get('dig_cnpj');
            $observ     = $request->get('observ');
            $origem_reg = $request->get('origem_reg');
            $img_base64 = $request->get('img_base64');

            $api = new ApiColeta();
            $resultado = $api->Local_IncluirNotaFiscalColeta(
                $coleta_id,
                $cod_barras,
                $serie,
                $numero,
                $valor,
                $volumes,
                $dig_cnpj,
                $observ,
                $origem_reg,
                $img_base64
            );
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function GetNotasFiscaisColeta(Request $request)
    {

        try {

            $coleta_id = $request->get('coleta_id');

            $api = new ApiColeta();
            $resultado = $api->Local_GetNotasFiscaisColeta($coleta_id);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function SetarDeslocaEntrega(Request $request)
    {

        try {

            $coleta_id  = $request->get('coleta_id');

            $api = new ApiColeta();
            $resultado = $api->Local_SetarDeslocaEntrega($coleta_id);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function SetarChegadaEntrega(Request $request)
    {

        try {

            $coleta_id = $request->get('coleta_id');
            $dur_prev_entrega = $request->get('dur_prev_entrega');

            $api = new ApiColeta();
            $resultado = $api->Local_SetarChegadaEntrega($coleta_id, $dur_prev_entrega);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function SetarInicioAtendEntrega(Request $request)
    {

        try {

            $coleta_id = $request->get('coleta_id');

            $api = new ApiColeta();
            $resultado = $api->Local_SetarInicioAtendEntrega($coleta_id);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function FinalizarColeta(Request $request)
    {

        try {

            $coleta_id      = $request->get('coleta_id');
            $realizada      = $request->get('realizada');
            $obs_nao_coleta = $request->get('obs_nao_coleta');
            $nfs_comerciais = $request->get('nfs_comerciais');
            $img_carga_base64 = $request->get('img_carga_base64');
            $ocup_veiculo     = $request->get('ocup_veiculo');
            $cod_tipo_veiculo_nec = $request->get('cod_tipo_veiculo_nec');
            $img_rom_base64 = $request->get('img_rom_base64');

            $api = new ApiColeta();
            $resultado = $api->Local_FinalizarColeta(
                $coleta_id,
                $realizada,
                $obs_nao_coleta,
                $nfs_comerciais,
                $img_carga_base64,
                $ocup_veiculo,
                $cod_tipo_veiculo_nec,
                $img_rom_base64
            );
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function FinalizarEntrega(Request $request)
    {

        try {

            $coleta_id = $request->get('coleta_id');
            $realizada = $request->get('realizada');
            $mot_nao_entrega = $request->get('mot_nao_entrega');
            $obs_nao_entrega = $request->get('obs_nao_entrega');
            $recebedor = $request->get('recebedor');
            $img_rom_base64 = $request->get('img_rom_base64');

            $api = new ApiColeta();
            $resultado = $api->Local_FinalizarEntrega($coleta_id, $realizada, $mot_nao_entrega, $obs_nao_entrega, $recebedor, $img_rom_base64);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function IncluirComanda(Request $request)
    {

        try {

            $solic_origem_id = $request->get('solic_origem_id');
            $local_coleta    = $request->get('local_coleta');
            $local_entrega   = $request->get('local_entrega');
            $obs_coleta      = $request->get('obs_coleta');

            $api = new ApiColeta();
            $resultado = $api->Local_IncluirComanda($solic_origem_id, $local_coleta, $local_entrega, $obs_coleta);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function AtualizarComanda(Request $request)
    {

        try {

            $solic_origem_id = $request->get('solic_origem_id');
            $coleta_id       = $request->get('coleta_id');
            $local_coleta    = $request->get('local_coleta');
            $local_entrega   = $request->get('local_entrega');
            $obs_coleta      = $request->get('obs_coleta');

            $api = new ApiColeta();
            $resultado = $api->Local_AtualizarComanda($solic_origem_id, $coleta_id, $local_coleta, $local_entrega, $obs_coleta);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function ExcluirComanda(Request $request)
    {

        try {

            $solic_origem_id = $request->get('solic_origem_id');
            $coleta_id       = $request->get('coleta_id');

            $api = new ApiColeta();
            $resultado = $api->Local_ExcluirComanda($solic_origem_id, $coleta_id);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function IniciarExpediente(Request $request)
    {

        try {

            $coleta_id = $request->get('coleta_id');

            $api = new ApiColeta();
            $resultado = $api->Local_IniciarExpediente($coleta_id);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function FinalizarExpediente(Request $request)
    {

        try {

            $coleta_id = $request->get('coleta_id');

            $api = new ApiColeta();
            $resultado = $api->Local_FinalizarExpediente($coleta_id);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function SetarDescargaColeta(Request $request)
    {

        try {

            $coleta_id = $request->get('coleta_id');

            $api = new ApiColeta();
            $resultado = $api->Local_SetarDescargaColeta($coleta_id);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function AtualizarReciboNotaFiscalColeta(Request $request)
    {

        try {

            $coleta_nf_id    = $request->get('coleta_nf_id');
            $coleta_id       = $request->get('coleta_id');
            $img_base64      = $request->get('img_base64');
            $observ          = $request->get('observ');
            $img_recibo      = $request->get('img_recibo');
            $mot_nao_entrega = $request->get('mot_nao_entrega');

            $api = new ApiColeta();
            $resultado = $api->Local_AtualizarReciboNotaFiscalColeta($coleta_nf_id, $coleta_id, $img_base64, $observ, $img_recibo, $mot_nao_entrega);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function BaldearColeta(Request $request)
    {

        try {

            $placa_destino = $request->get('placa_destino');
            $transfer_code = $request->get('transfer_code');
            $coleta_id     = $request->get('coleta_id');

            $api = new ApiColeta();
            $resultado = $api->Local_BaldearColeta($placa_destino, $transfer_code, $coleta_id);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function DevolverSemAtendColeta(Request $request)
    {

        try {

            $coleta_id      = $request->get('coleta_id');
            $obs_nao_coleta = $request->get('obs_nao_coleta');

            $api = new ApiColeta();
            $resultado = $api->Local_DevolverSemAtendColeta($coleta_id, $obs_nao_coleta);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function DesfazerStatusAtualColeta(Request $request)
    {

        try {
            $coleta_id = $request->get('coleta_id');

            $api = new ApiColeta();
            $resultado = $api->Local_DesfazerStatusAtualColeta($coleta_id);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function GerarSolicAuxiliarMultiDestinos(Request $request)
    {

        try {
            $solic_origem_id = $request->get('solic_origem_id');
            $lista_notas = $request->get('lista_notas');

            $api = new ApiColeta();
            $resultado = $api->Local_GerarSolicAuxiliarMultiDestinos($solic_origem_id, $lista_notas);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function GerarSolicReentrega(Request $request)
    {
        try {
            $coleta_id        = $request->get('coleta_id');
            $cod_loc_coleta   = $request->get('cod_loc_coleta');
            $dt_prev_coleta   = $request->get('dt_prev_coleta');
            $hr_prev_coleta   = $request->get('hr_prev_coleta');
            $cod_loc_entrega  = $request->get('cod_loc_entrega');
            $dt_prev_entrega  = $request->get('dt_prev_entrega');
            $hr_prev_entrega  = $request->get('hr_prev_entrega');
            $entrega_urgente  = $request->get('entrega_urgente');
            $solicitante      = $request->get('solicitante');
            $peso             = $request->get('peso');
            $volumes          = $request->get('volumes');
            $especie          = $request->get('especie');
            $sis_carga        = $request->get('sis_carga');
            $alt_carga        = $request->get('alt_carga');
            $larg_carga       = $request->get('larg_carga');
            $comp_carga       = $request->get('comp_carga');
            $tipo_frete       = $request->get('tipo_frete');
            $cod_tipo_veiculo = $request->get('cod_tipo_veiculo');
            $caract_coleta    = $request->get('caract_coleta');
            $obs_coleta       = $request->get('obs_coleta');
            $reentrega        = $request->get('reentrega');
            $lista_notas      = $request->get('lista_notas');

            $api = new ApiColeta();
            $resultado = $api->Local_GerarSolicReentrega(
                $coleta_id,
                $cod_loc_coleta,
                $dt_prev_coleta,
                $hr_prev_coleta,
                $cod_loc_entrega,
                $dt_prev_entrega,
                $hr_prev_entrega,
                $entrega_urgente,
                $solicitante,
                $peso,
                $volumes,
                $especie,
                $sis_carga,
                $alt_carga,
                $larg_carga,
                $comp_carga,
                $tipo_frete,
                $cod_tipo_veiculo,
                $caract_coleta,
                $obs_coleta,
                $reentrega,
                $lista_notas
            );
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }
}
