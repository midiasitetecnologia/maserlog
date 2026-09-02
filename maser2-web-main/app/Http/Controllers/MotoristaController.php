<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Requests\MotoristaRequest;
use App\Motorista;

class MotoristaController extends Controller
{
    public function index(Request $request)
    {

        $ativo = $request->get('ativo');

        $motorista = Motorista::select('motorista.*', 'veiculo.placa', 'users.email')
            ->where(function ($query) use ($ativo) {
                if ($ativo == 'S') {
                    $query->where('motorista.ativo', '=', 'S');
                }
            })
            ->leftJoin('veiculo', 'veiculo.motorista_id', 'motorista.id')
            ->leftJoin('users', 'users.id', 'motorista.user_id')
            ->orderBy('motorista.nome', 'asc')
            ->get();

        return ['status' => true, 'motorista' => $motorista];
    }

    public function show($id)
    {
        $motorista = Motorista::select('motorista.*', 'veiculo.placa', 'users.name', 'users.email')
            ->leftJoin('veiculo', 'veiculo.motorista_id', 'motorista.id')
            ->leftJoin('users', 'users.id', 'motorista.user_id')
            ->where('motorista.id', $id)
            ->get();

        return ['status' => true, 'motorista' => $motorista];
    }

    public function edit($id)
    {
        $motorista = Motorista::select('motorista.*', 'veiculo.placa', 'users.name', 'users.email')
            ->leftJoin('veiculo', 'veiculo.motorista_id', 'motorista.id')
            ->leftJoin('users', 'users.id', 'motorista.user_id')
            ->where('motorista.id', $id)
            ->get();

        return ['status' => true, 'motorista' => $motorista];
    }

    public function store(Requests\MotoristaRequest $request)
    {
        $data = $request->all();

        $data['ass_user_id'] = Auth()->user()->id;

        try {
            Motorista::create($data);
        } catch (\Exception $e) {
            $resultado['message'][0] = $e->getMessage();
            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }

    public function update(Request $request, $id)
    {
        if (!($motorista = Motorista::find($id))) {
            return ['status' => false];
        }

        $data = $request->all();
        $data['ass_user_id'] = Auth()->user()->id;

        $validator = new MotoristaRequest;
        $erros = $validator->ValidarDadosApiMotorista($data);

        if (!empty($erros)) {
            return ['status' => false, 'erros' => $erros['erros']];
        } else {

            try {
                $motorista->fill($data);
                $motorista->save();
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

        if (!($motorista = Motorista::find($data['id']))) {
            return ['status' => false];
        }

        try {
            $motorista['ass_user_id'] = Auth()->user()->id;
            $motorista->save();
            $motorista->delete();
        } catch (\Exception $e) {
            return ['status' => false];
        }

        return ['status' => true];
    }
}
