<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\LogPro;

class LogProController extends Controller
{
    public function index(Request $request)
    {
        //Setamos o tempo máximo de execução para 15 minutos(900 segundos).
        //O tempo padrão de execução de um processo no PHP está definido no arquivo 02phpini.config (.ebextensions).
        ini_set('max_execution_time', 900);

        $evento = 'todos'; //Valor padrão.
        $status = 'todos'; //Valor padrão.

        $timezone_app = date_default_timezone_get();
        $period1 = Carbon::now($timezone_app)->startOfDay()->format('Y-m-d H:i:s');
        $period2 = Carbon::now($timezone_app)->endOfDay()->format('Y-m-d H:i:s');

        if ($request->get('evento') != null) {
            $evento = $request->get('evento');
        }

        if ($request->get('status') != null) {
            $status = $request->get('status');
        }

        if ($request->get('period1') != null) {
            $period1 = $request->get('period1');
            $period1 = substr($period1, 0, 10);
            $period1 = Carbon::createFromFormat('Y-m-d', $period1)->startOfDay()->format('Y-m-d H:i:s');
        }

        if ($request->get('period2') != null) {
            $period2 = $request->get('period2');
            $period2 = substr($period2, 0, 10);
            $period2 = Carbon::createFromFormat('Y-m-d', $period2)->endOfDay()->format('Y-m-d H:i:s');
        }

        $logPro = LogPro::select([
            'id',
            'evento',
            'tipo',
            'msg',
            'status',
            'proc_id',
            'created_at',
            'updated_at'
        ])->where('tipo', '=', '9') //Trailler
            ->where(function ($query) use ($evento) {

                //Se o evento for "todos"(Todos) não precisamos aplicar filtro de evento
                if ($evento != 'todos') {
                    $query->where('evento', $evento);
                }
            })
            ->where(function ($query) use ($status) {

                //Se o evento for "todos"(Todos) não precisamos aplicar filtro de evento
                if ($status != 'todos') {
                    $query->where('status', $status);
                }
            })
            ->where('created_at', '>=', $period1) // Não utilizar "whereDate", senão vamos perder o índice do campo "created_at".
            ->where('created_at', '<=', $period2) // Não utilizar "whereDate", senão vamos perder o índice do campo "created_at".
            ->orderBy('proc_id', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return ['status' => true, 'logPro' => $logPro];
    }

    public function detalhe(Request $request)
    {
        $proc_id = $request->get('proc_id');

        $logProDetalhe = LogPro::where('id', '=', $proc_id)->orWhere('proc_id', '=', $proc_id)
            ->orderBy('created_at', 'desc')
            ->orderBy('tipo', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return ['status' => true, 'logProDetalhe' => $logProDetalhe];
    }
}