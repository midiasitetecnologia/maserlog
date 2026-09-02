<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Requests\DistanceMatrixRequest;
use App\DistanceMatrix;
use App\Reconcile;

class DistanceMatrixController extends Controller
{
    public function index()
    {
        $distanceMatrix = DistanceMatrix::orderBy('id', 'asc')->get();

        return ['status' => true, 'distanceMatrix' => $distanceMatrix];
    }

    public function edit($id)
    {
        $distanceMatrix = DistanceMatrix::where('id', $id)->get();

        return ['status' => true, 'distanceMatrix' => $distanceMatrix];
    }

    public function store(Requests\DistanceMatrixRequest $request)
    {
        $data = $request->all();
        $data['ass_user_id'] = Auth()->user()->id;

        try {
            DistanceMatrix::create($data);
        } catch (\Exception $e) {
            $reconcile = new Reconcile();
            $resultado['message'][0] = $reconcile->TratarExceptionDistanceMatrix('insert', $e);

            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }

    public function update(Request $request, $id)
    {
        if (!($distanceMatrix = DistanceMatrix::find($id))) {
            return ['status' => false];
        }

        $data = $request->all();
        $data['ass_user_id'] = Auth()->user()->id;

        $validator = new DistanceMatrixRequest;
        $erros = $validator->ValidarDadosApiDistanceMatrix($data);

        if (!empty($erros)) {
            return ['status' => false, 'erros' => $erros['erros']];
        } else {

            try {
                // Só vamos alterar o número de solicitações na alteração, se o usuário marcar que é uma alteração manual.
                if ($data['api_usage_manual'] !== 'S') {
                    // Remove o campo api_usage do array de dados
                    unset($data['api_usage']);
                }

                $distanceMatrix->fill($data);
                $distanceMatrix->save();
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

        if (!($distanceMatrix = DistanceMatrix::find($data['id']))) {
            return ['status' => false];
        }

        try {
            $distanceMatrix->delete();
        } catch (\Exception $e) {
            $reconcile = new Reconcile();
            $resultado['message'][0] = $reconcile->TratarExceptionDistanceMatrix('delete', $e);
            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }
}
