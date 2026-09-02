<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Requests\VeiculoRequest;
use App\Veiculo;
use App\Reconcile;

class VeiculoController extends Controller
{
    public function index()
    {
        $veiculo = Veiculo::select('veiculo.*', 'tipo_veiculo.descricao as descricao_tipo', 'motorista.nome')
            ->leftJoin('tipo_veiculo', 'tipo_veiculo.codigo', '=', 'veiculo.cod_tipo_veiculo')
            ->leftJoin('motorista', 'motorista.id', '=', 'veiculo.motorista_id')
            ->get();

        $url_img = rgRetornarUrlImagens('');

        return ['status' => true, 'veiculo' => $veiculo, 'url_img' => $url_img];
    }

    public function show($placa)
    {
        $veiculo = Veiculo::select('veiculo.*', 'tipo_veiculo.descricao as descricao_tipo')
            ->leftJoin('tipo_veiculo', 'tipo_veiculo.codigo', '=', 'veiculo.cod_tipo_veiculo')
            ->where('placa', $placa)
            ->get();

        return ['status' => true, 'veiculo' => $veiculo];
    }

    public function edit($placa)
    {
        $veiculo = Veiculo::select('veiculo.*', 'tipo_veiculo.descricao as descricao_tipo')
            ->leftJoin('tipo_veiculo', 'tipo_veiculo.codigo', '=', 'veiculo.cod_tipo_veiculo')
            ->where('placa', $placa)
            ->get();

        return ['status' => true, 'veiculo' => $veiculo];
    }

    public function store(Requests\VeiculoRequest $request)
    {
        $data = $request->all();

        $data['placa'] = strtoupper($data['placa']); //Sempre vamos gravar a placa com UpperCase.                
        $data['largura'] = rgStringToFloat($data['largura']);
        $data['comprimento'] = rgStringToFloat($data['comprimento']);
        $data['altura'] = rgStringToFloat($data['altura']);
        $data['cap_cub'] = rgStringToFloat($data['cap_cub']);
        $data['cap_kg'] = rgStringToFloat($data['cap_kg']);
        $data['placa_cavalo'] = strtoupper($data['placa_cavalo']); //Sempre vamos gravar a placa com UpperCase.

        $data['ass_user_id'] = Auth()->user()->id;

        try {
            Veiculo::create($data);
        } catch (\Exception $e) {
            $reconcile = new Reconcile();
            $resultado['message'][0] = $reconcile->TratarExceptionVeiculo('insert', $e);

            //Aqui tivemos que tratar se tem o inserir da constraint, senão para dar as validações do model, iria carregar
            //toda mensagem de "Ocorreu um erro..." e não é a intenção.
            if (strpos($resultado['message'][0], 'já está cadastrado.') === false) {
                $resultado['message'][0] = $e->getMessage();
            }

            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }

    public function update(Request $request, $placa)
    {
        if (!($veiculo = Veiculo::find($placa))) {
            return ['status' => false];
        }

        $data = $request->all();

        $data['largura'] = rgStringToFloat($data['largura']);
        $data['comprimento'] = rgStringToFloat($data['comprimento']);
        $data['altura'] = rgStringToFloat($data['altura']);
        $data['cap_cub'] = rgStringToFloat($data['cap_cub']);
        $data['cap_kg'] = rgStringToFloat($data['cap_kg']);
        $data['placa_cavalo'] = strtoupper($data['placa_cavalo']); //Sempre vamos gravar a placa com UpperCase.

        $data['ass_user_id'] = Auth()->user()->id;

        $validator = new VeiculoRequest;
        $erros = $validator->ValidarDadosApiVeiculo($data);

        if (!empty($erros)) {
            return ['status' => false, 'erros' => $erros['erros']];
        } else {

            try {
                $veiculo->fill($data);
                $veiculo->save();
            } catch (\Exception $e) {
                $resultado['message'][0] = $e->getMessage();
                return ['status' => false, 'erros' => $resultado];
            }

            return ['status' => true];
        }
    }

    public function destroy(Request $request)
    {
        $data = $request->all();

        if (!($veiculo = Veiculo::find($data['placa']))) {
            return ['status' => false];
        }

        try {
            $veiculo['ass_user_id'] = Auth()->user()->id;
            $veiculo->save();
            $veiculo->delete();
        } catch (\Exception $e) {
            $reconcile = new Reconcile();
            $resultado['message'][0] = $reconcile->TratarExceptionVeiculo('delete', $e);
            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }

    public function lerVeiculo()
    {
        $veiculo = Veiculo::select('veiculo.placa')
            ->join('tipo_veiculo', 'tipo_veiculo.codigo', '=', 'veiculo.cod_tipo_veiculo')
            ->whereIn('tipo_veiculo.classe', ['M', 'R'])
            ->orderBy('veiculo.placa', 'asc')
            ->get();

        return ['status' => true, 'veiculo' => $veiculo];
    }


    public function DesvincularMotoristaVeiculo(Request $request)
    {

        try {
            $placa = $request->get('placa');
            $motorista_id = $request->get('motorista_id');

            $api = new Veiculo();
            $resultado = $api->Local_DesvincularMotoristaVeiculo($placa, $motorista_id);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }
}
