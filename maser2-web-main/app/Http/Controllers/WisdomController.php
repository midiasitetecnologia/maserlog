<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Requests\WisdomRequest;
use App\Wisdom;
use App\Reconcile;

class WisdomController extends Controller
{
    public function index()
    {
        $wisdom = Wisdom::orderBy('wisdom.id', 'asc')->get();

        return ['status' => true, 'wisdom' => $wisdom];
    }
    
    public function edit($id)
    {
        $wisdom = Wisdom::where('id', $id)->get();

        return ['status' => true, 'wisdom' => $wisdom];
    }

    public function store(Requests\WisdomRequest $request)
    {
        $data = $request->all();
        $data['ass_user_id'] = Auth()->user()->id;

        try {
            Wisdom::create($data);
        } catch (\Exception $e) {
            $reconcile = new Reconcile();
            $resultado['message'][0] = $reconcile->TratarExceptionWisdom('insert', $e);

            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }

    public function update(Request $request, $id)
    {
        if (!($wisdom = Wisdom::find($id))) {
            return ['status' => false];
        }

        $data = $request->all();
        $data['ass_user_id'] = Auth()->user()->id;

        $validator = new WisdomRequest;
        $erros = $validator->ValidarDadosApiWisdom($data);

        if (!empty($erros)) {
            return ['status' => false, 'erros' => $erros['erros']];
        } else {

            try {
                $wisdom->fill($data);
                $wisdom->save();
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

        if (!($wisdom = Wisdom::find($data['id']))) {
            return ['status' => false];
        }

        try {
            $wisdom->delete();
        } catch (\Exception $e) {
            $reconcile = new Reconcile();
            $resultado['message'][0] = $reconcile->TratarExceptionWisdom('delete', $e);
            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }
}
