<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ColetaNf;

class ColetaNfController extends Controller
{
    public function destroy(Request $request)
    {
        $data = $request->all();

        if (!($coletaNf = ColetaNf::find($data['id']))) {
            return ['status' => false];
        }

        try {
            $coletaNf['ass_user_id'] = Auth()->user()->id;
            $coletaNf->save();
            $coletaNf->delete();
        } catch (\Exception $e) {
            return ['status' => false, 'erros' => $e->getMessage()];
        }

        return ['status' => true];
    }
}
