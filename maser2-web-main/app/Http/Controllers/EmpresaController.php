<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Requests\EmpresaRequest;
use App\Empresa;
use App\Reconcile;

class EmpresaController extends Controller
{
    public function index()
    {
        $empresa = Empresa::get();

        return ['status' => true, 'empresa' => $empresa];
    }

    public function show(Request $request, $codigo)
    {
        $empresa = Empresa::where('codigo', $codigo)->get();

        return ['status' => true, 'empresa' => $empresa];
    }

    public function edit($codigo)
    {
        $empresa = Empresa::where('codigo', $codigo)->get();

        return ['status' => true, 'empresa' => $empresa];
    }

    public function store(Requests\EmpresaRequest $request)
    {
        $data = $request->all();

        $data['sigla'] = strtoupper($data['sigla']); //Sempre vamos gravar a sigla com UpperCase.

        $data['ass_user_id'] = Auth()->user()->id;

        try {
            Empresa::create($data);
        } catch (\Exception $e) {
            $reconcile = new Reconcile();
            $resultado['message'][0] = $reconcile->TratarExceptionEmpresa('insert', $e);
            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }

    public function update(Request $request, $codigo)
    {
        if (!($empresa = Empresa::find($codigo))) {
            return ['status' => false];
        }

        $data = $request->all();

        $data['sigla'] = strtoupper($data['sigla']); //Sempre vamos gravar a sigla com UpperCase.

        $data['ass_user_id'] = Auth()->user()->id;

        $validator = new EmpresaRequest;
        $erros = $validator->ValidarDadosApiEmpresa($data);

        if (!empty($erros)) {
            return ['status' => false, 'erros' => $erros['erros']];
        } else {

            try {
                $empresa->fill($data);
                $empresa->save();
            } catch (\Exception $e) {
                return ['status' => false];
            }

            return ['status' => true];
        }
    }

    public function destroy(Request $request)
    {
        $data = $request->all();

        if (!($empresa = Empresa::find($data['codigo']))) {
            return ['status' => false];
        }

        try {
            $empresa['ass_user_id'] = Auth()->user()->id;
            $empresa->save();
            $empresa->delete();
        } catch (\Exception $e) {
            return ['status' => false];
        }

        return ['status' => true];
    }
}
