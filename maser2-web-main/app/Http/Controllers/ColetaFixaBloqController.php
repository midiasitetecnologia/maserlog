<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Requests\ColetaFixaBloqRequest;
use App\ColetaFixaBloq;
use App\Reconcile;

class ColetaFixaBloqController extends Controller
{
    public function index(Request $request)
    {
        $coleta_fixa_id = $request->get('coleta_fixa_id');

        $coletaFixaBloq = ColetaFixaBloq::where('coleta_fixa_id', '=', $coleta_fixa_id)->get();

        return ['status' => true, 'coletaFixaBloq' => $coletaFixaBloq];
    }

    public function show($id)
    {
        $coletaFixaBloq = ColetaFixaBloq::select(
            'coleta_fixa_bloq.*',
            'empresa.nome AS nome_empresa',
            'coleta_fixa.dt_ini AS per_ini',
            'coleta_fixa.dt_fim AS per_fim',
            'cliente.nome AS nome_cliente',
            'coleta_fixa.cod_cliente'
        )
            ->where('coleta_fixa_bloq.id', $id)
            ->join('coleta_fixa', 'coleta_fixa.id', '=', 'coleta_fixa_bloq.coleta_fixa_id')
            ->join('empresa', 'empresa.codigo', '=', 'coleta_fixa.empresa')
            ->join('cliente', function ($join) {
                $join->on('cliente.codigo', '=', 'coleta_fixa.cod_cliente')
                    ->on('cliente.empresa', '=', 'coleta_fixa.empresa');
            })
            ->get();

        return ['status' => true, 'coletaFixaBloq' => $coletaFixaBloq];
    }

    public function edit($id)
    {
        $coletaFixaBloq = ColetaFixaBloq::select(
            'coleta_fixa_bloq.*',
            'empresa.nome AS nome_empresa',
            'coleta_fixa.dt_ini AS per_ini',
            'coleta_fixa.dt_fim AS per_fim',
            'cliente.nome AS nome_cliente',
            'coleta_fixa.cod_cliente'
        )
            ->where('coleta_fixa_bloq.id', $id)
            ->join('coleta_fixa', 'coleta_fixa.id', '=', 'coleta_fixa_bloq.coleta_fixa_id')
            ->join('empresa', 'empresa.codigo', '=', 'coleta_fixa.empresa')
            ->join('cliente', function ($join) {
                $join->on('cliente.codigo', '=', 'coleta_fixa.cod_cliente')
                    ->on('cliente.empresa', '=', 'coleta_fixa.empresa');
            })
            ->get();

        return ['status' => true, 'coletaFixaBloq' => $coletaFixaBloq];
    }

    public function store(Requests\ColetaFixaBloqRequest $request)
    {
        $data = $request->all();

        $data['ass_user_id'] = Auth()->user()->id;

        try {
            ColetaFixaBloq::create($data);
        } catch (\Exception $e) {
            $resultado['message'][0] = $e->getMessage();
            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }

    public function update(Request $request, $id)
    {
        if (!($coletaFixaBloq = ColetaFixaBloq::find($id))) {
            return ['status' => false];
        }

        $data = $request->all();
        $data['ass_user_id'] = Auth()->user()->id;

        $validator = new ColetaFixaBloqRequest;
        $erros = $validator->ValidarDadosApiColetaFixaBloq($data);

        if (!empty($erros)) {
            return ['status' => false, 'erros' => $erros['erros']];
        } else {

            try {
                $coletaFixaBloq->fill($data);
                $coletaFixaBloq->save();
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

        if (!($coletaFixaBloq = ColetaFixaBloq::find($data['id']))) {
            return ['status' => false];
        }

        try {
            $coletaFixaBloq['ass_user_id'] = Auth()->user()->id;
            $coletaFixaBloq->save();
            $coletaFixaBloq->delete();
        } catch (\Exception $e) {
            $resultado['message'][0] = $e->getMessage();
            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }
}
