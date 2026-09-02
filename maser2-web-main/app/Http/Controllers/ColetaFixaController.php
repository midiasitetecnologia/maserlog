<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Requests\ColetaFixaRequest;
use App\ColetaFixa;
use App\Reconcile;
use Carbon\Carbon;
use DB;

class ColetaFixaController extends Controller
{
    public function index(Request $request)
    {
        $timezone_app = date_default_timezone_get();
        $dataAtual = Carbon::now($timezone_app)->format('Y-m-d');

        $ativo = $request->get('ativo');

        $coletaFixa = ColetaFixa::distinct()
            ->select(
                DB::raw('
                coleta_fixa.*, 
                empresa.nome as nome_empresa, 
                empresa.sigla, empresa.cor_fonte, empresa.cor_fundo,
                cliente.nome,
                cli_coleta.nome as local_coleta,
                cli_entrega.nome as local_entrega,
                tipo_veiculo.descricao as descricao_tipo_veiculo,

                (select count(cfb.id)
                 from coleta_fixa_bloq as cfb
                 where (cfb.coleta_fixa_id = coleta_fixa.id)
                ) as nro_bloqs,
                
                (select count(cfb.id)
                 from coleta_fixa_bloq as cfb
                 where (cfb.coleta_fixa_id = coleta_fixa.id) 
                 and   ((DATE(cfb.dt_ini) > DATE("' . $dataAtual . '")) 
                 or     (DATE("' . $dataAtual . '") >= DATE(cfb.dt_ini) and DATE("' . $dataAtual . '") <= DATE(cfb.dt_fim)))
                ) as nro_bloqs_fut
                ')
            )
            ->where(function ($query) use ($ativo, $dataAtual) {
                if ($ativo == 'S') {
                    $query->where('coleta_fixa.cont_cancel', '!=', 'S')
                        ->whereDate('coleta_fixa.dt_ini', '<=', $dataAtual)
                        ->whereDate('coleta_fixa.dt_fim', '>=', $dataAtual);
                }
            })
            ->join('empresa', 'empresa.codigo', '=', 'coleta_fixa.empresa')
            ->join('cliente', function ($join) {
                $join->on('cliente.codigo', '=', 'coleta_fixa.cod_cliente')
                    ->on('cliente.empresa', '=', 'coleta_fixa.empresa');
            })
            ->leftJoin('cliente as cli_coleta', function ($join) {
                $join->on('cli_coleta.codigo', '=', 'coleta_fixa.cod_loc_coleta')
                    ->on('cli_coleta.empresa', '=', 'coleta_fixa.empresa');
            })
            ->leftJoin('cliente as cli_entrega', function ($join) {
                $join->on('cli_entrega.codigo', '=', 'coleta_fixa.cod_loc_entrega')
                    ->on('cli_entrega.empresa', '=', 'coleta_fixa.empresa');
            })
            ->leftJoin('tipo_veiculo', 'tipo_veiculo.codigo', '=', 'coleta_fixa.cod_tipo_veiculo')
            ->get();

        return ['status' => true, 'coletaFixa' => $coletaFixa];
    }

    public function show($id)
    {
        $coletaFixa = ColetaFixa::select(
            'coleta_fixa.*',
            'empresa.nome as nome_empresa',
            'empresa.sigla',
            'empresa.cor_fonte',
            'empresa.cor_fundo',
            'cliente.nome',
            'cliente.cpf_cnpj',
            'cli_coleta.nome as nome_coleta',
            'cli_entrega.nome as nome_entrega',
            'tipo_veiculo.descricao as descricao_veiculo'
        )

            ->join('empresa', 'empresa.codigo', '=', 'coleta_fixa.empresa')
            ->join('cliente', function ($join) {
                $join->on('cliente.codigo', '=', 'coleta_fixa.cod_cliente')
                    ->on('cliente.empresa', '=', 'coleta_fixa.empresa');
            })
            ->join('cliente as cli_coleta', function ($join) {
                $join->on('cli_coleta.codigo', '=', 'coleta_fixa.cod_loc_coleta')
                    ->on('cli_coleta.empresa', '=', 'coleta_fixa.empresa');
            })
            ->join('cliente as cli_entrega', function ($join) {
                $join->on('cli_entrega.codigo', '=', 'coleta_fixa.cod_loc_entrega')
                    ->on('cli_entrega.empresa', '=', 'coleta_fixa.empresa');
            })
            ->leftJoin('tipo_veiculo', 'tipo_veiculo.codigo', '=', 'coleta_fixa.cod_tipo_veiculo')
            ->leftJoin('veiculo', 'veiculo.placa', '=', 'coleta_fixa.placa_coleta')
            ->where('coleta_fixa.id', $id)
            ->get();

        return ['status' => true, 'coletaFixa' => $coletaFixa];
    }

    public function edit($id)
    {
        $coletaFixa = ColetaFixa::select(
            'coleta_fixa.*',
            'empresa.nome as nome_empresa',
            'empresa.sigla',
            'empresa.cor_fonte',
            'empresa.cor_fundo',
            'cliente.nome',
            'cliente.cpf_cnpj',
            'cli_coleta.nome as nome_coleta',
            'cli_entrega.nome as nome_entrega',
            'tipo_veiculo.descricao as descricao_veiculo'
        )

            ->join('empresa', 'empresa.codigo', '=', 'coleta_fixa.empresa')
            ->join('cliente', function ($join) {
                $join->on('cliente.codigo', '=', 'coleta_fixa.cod_cliente')
                    ->on('cliente.empresa', '=', 'coleta_fixa.empresa');
            })
            ->join('cliente as cli_coleta', function ($join) {
                $join->on('cli_coleta.codigo', '=', 'coleta_fixa.cod_loc_coleta')
                    ->on('cli_coleta.empresa', '=', 'coleta_fixa.empresa');
            })
            ->join('cliente as cli_entrega', function ($join) {
                $join->on('cli_entrega.codigo', '=', 'coleta_fixa.cod_loc_entrega')
                    ->on('cli_entrega.empresa', '=', 'coleta_fixa.empresa');
            })
            ->leftJoin('tipo_veiculo', 'tipo_veiculo.codigo', '=', 'coleta_fixa.cod_tipo_veiculo')
            ->leftJoin('veiculo', 'veiculo.placa', '=', 'coleta_fixa.placa_coleta')
            ->where('coleta_fixa.id', $id)
            ->get();

        return ['status' => true, 'coletaFixa' => $coletaFixa];
    }

    public function store(Requests\ColetaFixaRequest $request)
    {
        $data = $request->all();

        $data['ass_user_id'] = Auth()->user()->id;

        try {
            ColetaFixa::create($data);
        } catch (\Exception $e) {
            $resultado['message'][0] = $e->getMessage();
            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }

    public function update(Request $request, $id)
    {
        if (!($coletaFixa = ColetaFixa::find($id))) {
            return ['status' => false];
        }

        $data = $request->all();
        $data['ass_user_id'] = Auth()->user()->id;

        $validator = new ColetaFixaRequest;
        $erros = $validator->ValidarDadosApiColetaFixa($data);

        if (!empty($erros)) {
            return ['status' => false, 'erros' => $erros['erros']];
        } else {

            try {
                $coletaFixa->fill($data);
                $coletaFixa->save();
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

        if (!($coletaFixa = ColetaFixa::find($data['id']))) {
            return ['status' => false];
        }

        try {
            $coletaFixa['ass_user_id'] = Auth()->user()->id;
            $coletaFixa->save();
            $coletaFixa->delete();
        } catch (\Exception $e) {
            $reconcile = new Reconcile();
            $resultado['message'][0] = $reconcile->TratarExceptionColetaFixa('delete', $e);
            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }
}
