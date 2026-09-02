<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Requests\TipoVeiculoRequest;
use App\TipoVeiculo;
use App\Reconcile;

class TipoVeiculoController extends Controller
{
    public function index()
    {
        $tipoVeiculo = TipoVeiculo::orderBy('tipo_veiculo.descricao', 'asc')->get();

        return ['status' => true, 'tipoVeiculo' => $tipoVeiculo];
    }

    public function show($codigo)
    {
        $tipoVeiculo = TipoVeiculo::where('codigo', $codigo)->get();

        return ['status' => true, 'tipoVeiculo' => $tipoVeiculo];
    }

    public function edit($codigo)
    {
        $tipoVeiculo = TipoVeiculo::where('codigo', $codigo)->get();

        return ['status' => true, 'tipoVeiculo' => $tipoVeiculo];
    }

    public function store(Requests\TipoVeiculoRequest $request)
    {
        $data = $request->all();
        $data['ass_user_id'] = Auth()->user()->id;

        try {
            TipoVeiculo::create($data);
        } catch (\Exception $e) {
            $reconcile = new Reconcile();
            $resultado['message'][0] = $reconcile->TratarExceptionTipoVeiculo('insert', $e);

            //Aqui tivemos que tratar se tem o inserir da constraint, senão para dar as validações do model, iria carregar
            //toda mensagem de "Ocorreu um erro..." e não é a intenção.
            if (strpos($resultado['message'][0], 'já está cadastrado.') === false) {
                $resultado['message'][0] = $e->getMessage();
            }

            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }

    public function update(Request $request, $codigo)
    {
        if (!($tipoVeiculo = TipoVeiculo::find($codigo))) {
            return ['status' => false];
        }

        $data = $request->all();
        $data['ass_user_id'] = Auth()->user()->id;

        $validator = new TipoVeiculoRequest;
        $erros = $validator->ValidarDadosApiTipoVeiculo($data);

        if (!empty($erros)) {
            return ['status' => false, 'erros' => $erros['erros']];
        } else {

            try {
                $tipoVeiculo->fill($data);
                $tipoVeiculo->save();
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

        if (!($tipoVeiculo = TipoVeiculo::find($data['codigo']))) {
            return ['status' => false];
        }

        try {
            $tipoVeiculo['ass_user_id'] = Auth()->user()->id;
            $tipoVeiculo->save();
            $tipoVeiculo->delete();
        } catch (\Exception $e) {
            $reconcile = new Reconcile();
            $resultado['message'][0] = $reconcile->TratarExceptionTipoVeiculo('delete', $e);
            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }

    public function lerTipoVeiculo()
    {
        $tipoVeiculo = TipoVeiculo::where('tipo_veiculo.classe', '!=', 'C')
            ->orderBy('tipo_veiculo.descricao', 'asc')->get();

        return ['status' => true, 'tipoVeiculo' => $tipoVeiculo];
    }
}
