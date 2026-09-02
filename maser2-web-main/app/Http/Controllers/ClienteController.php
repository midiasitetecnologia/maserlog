<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ClienteRequest;
use App\Cliente;
use App\User;
use DB;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $comUsuarios = $request->get('comUsuarios');
        $semGeoLocalizacao = $request->get('semGeoLocalizacao');

        $solic_arq = '(
            SELECT cod_cliente, empresa, COUNT(id) AS nro_solic_arq
            FROM coleta
            WHERE ((coleta.status = "CN") OR (coleta.status = "ER"))
            GROUP BY cod_cliente, empresa
        ) AS solic_arq';

        $solic_pend = '(
            SELECT cod_cliente, empresa, COUNT(id) AS nro_solic_pend
            FROM coleta
            WHERE ((coleta.status <> "CN") AND (coleta.status <> "ER"))
            GROUP BY cod_cliente, empresa
        ) AS solic_pend';

        $cliente = Cliente::distinct()
            ->select(
                DB::raw('
                    cliente.*, 
                    empresa.nome as nome_empresa, 
                    empresa.sigla, empresa.cor_fonte, empresa.cor_fundo,

                    (select count(u.id)
                    from users as u
                    where (u.cliente_id = cliente.id)) as nro_users,

                    solic_arq.nro_solic_arq,
                    solic_pend.nro_solic_pend
                ')
            )
            ->join('empresa', 'empresa.codigo', '=', 'cliente.empresa')
            ->leftjoin('users', 'users.cliente_id', '=', 'cliente.id')
            ->leftJoin(DB::raw($solic_arq), function ($join) {
                $join->on('solic_arq.cod_cliente', '=', 'cliente.codigo')
                    ->on('solic_arq.empresa', '=', 'cliente.empresa');
            })
            ->leftJoin(DB::raw($solic_pend), function ($join) {
                $join->on('solic_pend.cod_cliente', '=', 'cliente.codigo')
                    ->on('solic_pend.empresa', '=', 'cliente.empresa');
            })
            ->where(function ($query) use ($comUsuarios) {
                if ($comUsuarios == "true") {
                    $query->where('users.cliente_id', '>', '0');
                }
            })
            ->where(function ($query) use ($semGeoLocalizacao) {
                if ($semGeoLocalizacao == "true") {
                    $query->whereNull('cliente.geo_lat')
                        ->orWhere('cliente.geo_lat', '=', '0')
                        ->orWhereNull('cliente.geo_lng')
                        ->orWhere('cliente.geo_lng', '=', '0');
                }
            })
            ->orderby('nome', 'ASC')
            ->get();

        return ['status' => true, 'cliente' => $cliente];
    }

    public function show(Request $request, $id)
    {
        $cliente = Cliente::select('cliente.*', 'empresa.nome as nome_empresa', 'empresa.sigla', 'empresa.cor_fonte', 'empresa.cor_fundo')
            ->join('empresa', 'empresa.codigo', '=', 'cliente.empresa')
            ->where('id', $id)
            ->get();

        return ['status' => true, 'cliente' => $cliente];
    }

    public function update(Request $request, $id)
    {
        if (!($cliente = Cliente::find($id))) {
            return ['status' => false];
        }

        $data = $request->all();

        if (isset($data['geo_lat'])) {
            $data['geo_lat'] = floatval($data['geo_lat']);
        }
        if (isset($data['geo_lng'])) {
            $data['geo_lng'] = floatval($data['geo_lng']);
        }

        $data['ass_user_id'] = Auth()->user()->id;

        $validator = new ClienteRequest;
        $erros = $validator->ValidarDadosApiCliente($data);

        if (!empty($erros)) {
            return ['status' => false, 'erros' => $erros['erros']];
        } else {

            try {
                $cliente->fill($data);
                $cliente->save();
            } catch (\Exception $e) {
                $resultado['message'][0] = $e->getMessage();
                return ['status' => false, 'erros' => $resultado];
            }

            return ['status' => true];
        }
    }

    public function getDadosCliente(Request $request)
    {
        $empresa = $request->get('empresa');

        if (rgDifZeroNull($empresa)) {

            $cliente = Cliente::select(
                DB::raw('cliente.*, empresa.nome as nome_empresa, empresa.sigla, empresa.cor_fonte, empresa.cor_fundo, 
                CONCAT(cliente.nome, " ", IFNULL(cliente.cpf_cnpj, "")) AS nome_cnpj')
            )
                ->join('empresa', 'empresa.codigo', '=', 'cliente.empresa')
                ->where('cliente.empresa', '=', $empresa)
                ->orderBy('cliente.nome', 'asc')
                ->get();
        } else {

            $cliente = Cliente::select(
                DB::raw('cliente.*, empresa.nome as nome_empresa, empresa.sigla, empresa.cor_fonte, empresa.cor_fundo, 
                CONCAT(cliente.nome, " ", IFNULL(cliente.cpf_cnpj, "")) AS nome_cnpj')
            )
                ->join('empresa', 'empresa.codigo', '=', 'cliente.empresa')
                ->orderBy('cliente.nome', 'asc')
                ->get();
        }

        return ['status' => true, 'cliente' => $cliente];
    }

    public function getUsersCliente(Request $request)
    {
        $id = $request->get('id');

        $usersCliente = User::select(
            'users.email',
            'users.name',
            'users.active',
            'empresa.nome as nome_empresa',
            'empresa.sigla',
            'empresa.cor_fonte',
            'empresa.cor_fundo'
        )
            ->join('cliente', 'cliente.id', '=', 'users.cliente_id')
            ->join('empresa', 'empresa.codigo', '=', 'cliente.empresa')
            ->where('cliente.id', '=', $id)
            ->orderBy('users.name', 'asc')
            ->get();

        return ['status' => true, 'usersCliente' => $usersCliente];
    }

    public function lerClienteFromUser(Request $request)
    {
        $id = $request->get('id');

        $cliente = DB::table('cliente')
            ->join('users', 'users.cliente_id', '=', 'cliente.id')
            ->where('users.id', '=', $id)
            ->get();

        return ['status' => true, 'cliente' => $cliente];
    }
}
