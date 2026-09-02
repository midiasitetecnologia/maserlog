<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SysCfg;

class SysCfgController extends Controller
{
    public function show()
    {
        $sysCfg = SysCfg::limit(1)
            ->orderBy('id', 'asc')
            ->get();

        return ['status' => true, 'sysCfg' => $sysCfg];
    }

    public function edit()
    {
        $sysCfg = SysCfg::limit(1)
            ->orderBy('id', 'asc')
            ->get();

        return ['status' => true, 'sysCfg' => $sysCfg];
    }

    public function store(Request $request)
    {
        $data = $request->all();

        if (isset($data['geo_lat_pavilion'])) {
            $data['geo_lat_pavilion'] = floatval($data['geo_lat_pavilion']);
        }

        if (isset($data['geo_lng_pavilion'])) {
            $data['geo_lng_pavilion'] = floatval($data['geo_lng_pavilion']);
        }

        $data['ass_user_id'] = Auth()->user()->id;

        try {
            SysCfg::create($data);
        } catch (\Exception $e) {
            $resultado['message'][0] = $e->getMessage();
            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }

    public function update(Request $request, $id)
    {
        if (!($sysCfg = SysCfg::find($id))) {
            return ['status' => false];
        }

        $data = $request->all();

        if (isset($data['geo_lat_pavilion'])) {
            $data['geo_lat_pavilion'] = floatval($data['geo_lat_pavilion']);
        }

        if (isset($data['geo_lng_pavilion'])) {
            $data['geo_lng_pavilion'] = floatval($data['geo_lng_pavilion']);
        }

        $data['ass_user_id'] = Auth()->user()->id;

        try {
            $sysCfg->fill($data);
            $sysCfg->save();
        } catch (\Exception $e) {
            $resultado['message'][0] = $e->getMessage();
            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }
}
