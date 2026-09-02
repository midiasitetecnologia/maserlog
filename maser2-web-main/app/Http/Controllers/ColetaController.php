<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Coleta;
use App\ColetaNf;
use App\ColetaLog;
use App\ColetaPos;
use App\Reconcile;
use DB;

class ColetaController extends Controller
{
    public function index(Request $request)
    {
        $cod_empresa = $request->get('cod_empresa');
        $cliente_id = $request->get('cliente_id');
        $placa = $request->get('placa');
        $periodo = $request->get('periodo');
        $nro_nf = $request->get('nro_nf');

        $filtro_ini = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y'))); //Inicio do mês
        if ($request->get('filtro_ini') != null) {
            $filtro_ini = $request->get('filtro_ini');
            $filtro_ini = substr($filtro_ini, 0, 10);
        }

        $filtro_fim = Carbon::now(date_default_timezone_get())->format("Y-m-d"); //Hoje        
        if ($request->get('filtro_fim') != null) {
            $filtro_fim = $request->get('filtro_fim');
            $filtro_fim = substr($filtro_fim, 0, 10);
        }

        //Se for um usuário do tipo "CLIENTE" - Vamos procurar solicitações por empresa ligando CPF/CNPJ se existir.
        $cpf_cnpj = null;
        if (auth()->user()->user_type == 'C') {

            $cliente = DB::table('cliente')
                ->select('id', 'cpf_cnpj')
                ->where('id', '=', auth()->user()->cliente_id)
                ->first();

            if (empty($cliente) == false) {
                if (rgDifTrimNull($cliente->cpf_cnpj)) {
                    $cpf_cnpj = $cliente->cpf_cnpj;
                }
            }
        }

        $coleta = Coleta::select(
            DB::raw('
                    coleta.*,
                    empresa.nome as nome_empresa,
                    empresa.sigla,
                    empresa.cor_fonte,
                    empresa.cor_fundo,
                    cliente.nome,
                    cli_coleta.nome as local_coleta,
                    cli_entrega.nome as local_entrega,
                    coleta_origem.id as coleta_origem_id,
                    coleta_origem.numero as coleta_origem_numero,

                    (select count(cnf.id)
                     from coleta_nf as cnf
                     where (cnf.coleta_id = coleta.id)
                    ) as notas_fiscais,
                    
                    (select count(cp.id)
                     from coleta_pos as cp
                     where (cp.coleta_id = coleta.id)
                    ) as coleta_pos,
                    
                    (select count(cl.id)
                     from coleta_log as cl
                     where (cl.coleta_id = coleta.id)
                    ) as coleta_log')
        )
            ->join('empresa', 'empresa.codigo', '=', 'coleta.empresa')
            ->join('cliente', function ($join) {
                $join->on('cliente.codigo', '=', 'coleta.cod_cliente')
                    ->on('cliente.empresa', '=', 'coleta.empresa');
            })
            ->leftJoin('cliente as cli_coleta', function ($join) {
                $join->on('cli_coleta.codigo', '=', 'coleta.cod_loc_coleta')
                    ->on('cli_coleta.empresa', '=', 'coleta.empresa');
            })
            ->leftJoin('cliente as cli_entrega', function ($join) {
                $join->on('cli_entrega.codigo', '=', 'coleta.cod_loc_entrega')
                    ->on('cli_entrega.empresa', '=', 'coleta.empresa');
            })
            ->leftJoin('coleta as coleta_origem', 'coleta_origem.id', '=', 'coleta.solic_origem_id')
            ->where(function ($query) use ($cod_empresa) {
                if (rgDifZeroNull($cod_empresa)) {
                    $query->where('coleta.empresa', '=', $cod_empresa);
                }
            })
            ->where(function ($query) use ($cliente_id, $cpf_cnpj) {

                if (auth()->user()->user_type == 'C') {

                    $query->where(function ($query1) use ($cpf_cnpj) {
                        if (rgDifTrimNull($cpf_cnpj)) {
                            $query1->where('cliente.cpf_cnpj', '=', $cpf_cnpj);
                        } else {
                            $query1->where('cliente.id', '=', auth()->user()->cliente_id);
                        }
                    });
                }

                if (rgDifZeroNull($cliente_id)) {
                    $query->where('cliente.id', '=', $cliente_id);
                }
            })
            ->where(function ($query) use ($placa) {
                if (!rgIgualTrimNull($placa)) {
                    $query->where('coleta.placa_coleta', '=', $placa)
                        ->orWhere('coleta.placa_entrega', '=', $placa);
                }
            })
            ->where(function ($query) use ($periodo, $filtro_ini, $filtro_fim) {

                if ($filtro_ini != null && $filtro_fim != null) {

                    if ($periodo == 'A') {

                        $query->where(function ($query2) use ($filtro_ini, $filtro_fim) {
                            $query2->where('coleta.dt_prev_coleta', '>=', $filtro_ini)
                                ->where('coleta.dt_prev_coleta', '<=', $filtro_fim);
                        })->orWhere(function ($query2) use ($filtro_ini, $filtro_fim) {
                            $query2->where('coleta.dt_prev_entrega', '>=', $filtro_ini)
                                ->where('coleta.dt_prev_entrega', '<=', $filtro_fim);
                        });
                    }

                    if ($periodo == 'C') {
                        $query->where('coleta.dt_prev_coleta', '>=', $filtro_ini)
                            ->where('coleta.dt_prev_coleta', '<=', $filtro_fim);
                    }

                    if ($periodo == 'E') {
                        $query->where('coleta.dt_prev_entrega', '>=', $filtro_ini)
                            ->where('coleta.dt_prev_entrega', '<=', $filtro_fim);
                    }
                }
            })
            ->where(function ($query) use ($nro_nf) {
                if (rgDifZeroNull($nro_nf)) {
                    $query->whereRaw(DB::raw(
                        'exists (select coleta_nf.id from coleta_nf where coleta_nf.coleta_id = coleta.id and coleta_nf.numero = ' . $nro_nf . ')'
                    ));
                }
            })

            ->where(function ($query) {
                $query->whereIn('coleta.status', ['CN', 'ER'])
                    ->orWhere(function ($query2) {
                        $query2->whereIn('coleta.status', ['EN', 'EP'])
                            ->where('coleta.reentrega_gerada', '=', 'S');
                    });
            })

            ->orderBy('coleta.dt_prev_coleta', 'asc')
            ->orderBy('coleta.hr_prev_coleta', 'asc')
            ->orderBy('coleta.id', 'asc')
            ->get();

        $url_img = rgRetornarUrlImagens('');

        return ['status' => true, 'coleta' => $coleta, 'url_img' => $url_img];
    }

    public function show(Request $request, $id)
    {
        //Se for um usuário do tipo "CLIENTE" - Vamos procurar solicitações por empresa ligando CPF/CNPJ se existir.
        $cpf_cnpj = null;
        if (auth()->user()->user_type == 'C') {

            $cliente = DB::table('cliente')
                ->select('id', 'cpf_cnpj')
                ->where('id', '=', auth()->user()->cliente_id)
                ->first();

            if (empty($cliente) == false) {
                if (rgDifTrimNull($cliente->cpf_cnpj)) {
                    $cpf_cnpj = $cliente->cpf_cnpj;
                }
            }
        }

        $coleta = Coleta::select(
            'coleta.*',
            'empresa.nome as nome_empresa',
            'empresa.sigla',
            'empresa.cor_fonte',
            'empresa.cor_fundo',
            'cliente.nome',
            'cli_coleta.nome as local_coleta',
            'cli_entrega.nome as local_entrega',
            'tipo_veiculo.descricao as descr_veiculo',
            'mot_coleta.nome as mot_coleta',
            'mot_entrega.nome as mot_entrega',
            'coleta_origem.id as coleta_origem_id',
            'coleta_origem.numero as coleta_origem_numero',
            'coleta_reentrega.id as coleta_reentrega_id',
            'coleta_reentrega.numero as coleta_reentrega_numero'
        )
            ->join('empresa', 'empresa.codigo', '=', 'coleta.empresa')
            ->join('cliente', function ($join) {
                $join->on('cliente.codigo', '=', 'coleta.cod_cliente')
                    ->on('cliente.empresa', '=', 'coleta.empresa');
            })
            ->leftJoin('cliente as cli_coleta', function ($join) {
                $join->on('cli_coleta.codigo', '=', 'coleta.cod_loc_coleta')
                    ->on('cli_coleta.empresa', '=', 'coleta.empresa');
            })
            ->leftJoin('cliente as cli_entrega', function ($join) {
                $join->on('cli_entrega.codigo', '=', 'coleta.cod_loc_entrega')
                    ->on('cli_entrega.empresa', '=', 'coleta.empresa');
            })
            ->leftJoin('tipo_veiculo', 'tipo_veiculo.codigo', '=', 'coleta.cod_tipo_veiculo')
            ->leftJoin('motorista as mot_coleta', 'mot_coleta.id', '=', 'coleta.motor_coleta_id')
            ->leftJoin('motorista as mot_entrega', 'mot_entrega.id', '=', 'coleta.motor_entrega_id')
            ->leftJoin('coleta as coleta_origem', 'coleta_origem.id', '=', 'coleta.solic_origem_id')
            ->leftJoin('coleta as coleta_reentrega', 'coleta_reentrega.id', '=', 'coleta.solic_reentrega_id')
            ->where(function ($query) use ($cpf_cnpj) {

                if (auth()->user()->user_type == 'C') {

                    $query->where(function ($query1) use ($cpf_cnpj) {
                        if (rgDifTrimNull($cpf_cnpj)) {
                            $query1->where('cliente.cpf_cnpj', '=', $cpf_cnpj);
                        } else {
                            $query1->where('cliente.id', '=', auth()->user()->cliente_id);
                        }
                    });
                }
            })
            ->where('coleta.id', $id)
            ->get();

        return ['status' => true, 'coleta' => $coleta];
    }

    public function edit(Request $request, $id)
    {
        //Se for um usuário do tipo "CLIENTE" - Vamos procurar solicitações por empresa ligando CPF/CNPJ se existir.
        $cpf_cnpj = null;
        if (auth()->user()->user_type == 'C') {

            $cliente = DB::table('cliente')
                ->select('id', 'cpf_cnpj')
                ->where('id', '=', auth()->user()->cliente_id)
                ->first();

            if (empty($cliente) == false) {
                if (rgDifTrimNull($cliente->cpf_cnpj)) {
                    $cpf_cnpj = $cliente->cpf_cnpj;
                }
            }
        }

        $coleta = Coleta::select(
            'coleta.*',
            'empresa.nome as nome_empresa',
            'empresa.sigla',
            'empresa.cor_fonte',
            'empresa.cor_fundo',
            'cliente.nome',
            'cli_coleta.nome as local_coleta',
            'cli_entrega.nome as local_entrega',
            'tipo_veiculo.descricao as descr_veiculo',
            'mot_coleta.nome as mot_coleta',
            'mot_entrega.nome as mot_entrega',
            'coleta_origem.id as coleta_origem_id',
            'coleta_origem.numero as coleta_origem_numero'
        )
            ->join('empresa', 'empresa.codigo', '=', 'coleta.empresa')
            ->join('cliente', function ($join) {
                $join->on('cliente.codigo', '=', 'coleta.cod_cliente')
                    ->on('cliente.empresa', '=', 'coleta.empresa');
            })
            ->leftJoin('cliente as cli_coleta', function ($join) {
                $join->on('cli_coleta.codigo', '=', 'coleta.cod_loc_coleta')
                    ->on('cli_coleta.empresa', '=', 'coleta.empresa');
            })
            ->leftJoin('cliente as cli_entrega', function ($join) {
                $join->on('cli_entrega.codigo', '=', 'coleta.cod_loc_entrega')
                    ->on('cli_entrega.empresa', '=', 'coleta.empresa');
            })
            ->leftJoin('tipo_veiculo', 'tipo_veiculo.codigo', '=', 'coleta.cod_tipo_veiculo')
            ->leftJoin('motorista as mot_coleta', 'mot_coleta.id', '=', 'coleta.motor_coleta_id')
            ->leftJoin('motorista as mot_entrega', 'mot_entrega.id', '=', 'coleta.motor_entrega_id')
            ->leftJoin('coleta as coleta_origem', 'coleta_origem.id', '=', 'coleta.solic_origem_id')
            ->where(function ($query) use ($cpf_cnpj) {

                if (auth()->user()->user_type == 'C') {

                    $query->where(function ($query1) use ($cpf_cnpj) {
                        if (rgDifTrimNull($cpf_cnpj)) {
                            $query1->where('cliente.cpf_cnpj', '=', $cpf_cnpj);
                        } else {
                            $query1->where('cliente.id', '=', auth()->user()->cliente_id);
                        }
                    });
                }
            })
            ->where(function ($query) {
                if (auth()->user()->user_type == 'C') {
                    $query->where('coleta.origem_reg', '=', 'A3')
                        ->where('coleta.status', '=', 'C0');
                } else {
                    //Esta é a edição da coleta, podendo editar até coletas geradas pelo sistema DOMPER.
                    //Devemos apenas desconsiderar as solicitações criadas pelos clientes. ELES poderão fazer suas alterações enquanto for 'C0'.
                    $query->where('coleta.origem_reg', '!=', 'A3')
                        ->whereIn('coleta.status', ['C0', 'C1', 'E0', 'E1']);
                }
            })
            ->where('coleta.id', $id)
            ->get();

        return ['status' => true, 'coleta' => $coleta];
    }

    public function indexColetaWeb(Request $request)
    {
        //Se for um usuário do tipo "CLIENTE" - Vamos procurar solicitações por empresa ligando CPF/CNPJ se existir.
        $cpf_cnpj = null;
        if (auth()->user()->user_type == 'C') {

            $cliente = DB::table('cliente')
                ->select('id', 'cpf_cnpj')
                ->where('id', '=', auth()->user()->cliente_id)
                ->first();

            if (empty($cliente) == false) {
                if (rgDifTrimNull($cliente->cpf_cnpj)) {
                    $cpf_cnpj = $cliente->cpf_cnpj;
                }
            }
        }

        $coleta = Coleta::select(
            DB::raw(
                'coleta.*,           

                empresa.nome as nome_empresa,
                empresa.sigla,
                empresa.cor_fonte,
                empresa.cor_fundo,

                cliente.nome,
                cli_coleta.nome as local_coleta,
                cli_coleta.endereco as endereco_coleta,
                cli_coleta.bairro as bairro_coleta,
                cli_coleta.cidade as cidade_coleta,
                cli_coleta.uf as uf_coleta,
                cli_coleta.cep as cep_coleta,
                cli_coleta.fone as fone_coleta,
                cli_coleta.hr_ini_coleta_man,
                cli_coleta.hr_fim_coleta_man,
                cli_coleta.hr_ini_coleta_tar,
                cli_coleta.hr_fim_coleta_tar,
                cli_coleta.geo_lat as geo_lat_coleta,
                cli_coleta.geo_lng as geo_lng_coleta,

                cli_entrega.nome as local_entrega,
                cli_entrega.endereco as endereco_entrega,
                cli_entrega.bairro as bairro_entrega,
                cli_entrega.cidade as cidade_entrega,
                cli_entrega.uf as uf_entrega,
                cli_entrega.cep as cep_entrega,
                cli_entrega.fone as fone_entrega,
                cli_entrega.hr_ini_entrega_man,
                cli_entrega.hr_fim_entrega_man,
                cli_entrega.hr_ini_entrega_tar,
                cli_entrega.hr_fim_entrega_tar,
                cli_entrega.geo_lat as geo_lat_entrega,
                cli_entrega.geo_lng as geo_lng_entrega,

                tv.descricao as descricao_tipo_veiculo,
                tvn.descricao as descricao_tipo_veiculo_nec,
                
                (select count(cp.id)
                    from coleta_pos as cp
                    where (cp.coleta_id = coleta.id)
                ) as coleta_pos,
                        
                (select count(cl.id)
                    from coleta_log as cl
                    where (cl.coleta_id = coleta.id)
                ) as coleta_log'
            )
        )
            ->join('empresa', 'empresa.codigo', '=', 'coleta.empresa')
            ->join('cliente', function ($join) {
                $join->on('cliente.codigo', '=', 'coleta.cod_cliente')
                    ->on('cliente.empresa', '=', 'coleta.empresa');
            })
            ->join('cliente as cli_coleta', function ($join) {
                $join->on('cli_coleta.codigo', '=', 'coleta.cod_loc_coleta')
                    ->on('cli_coleta.empresa', '=', 'coleta.empresa');
            })
            ->join('cliente as cli_entrega', function ($join) {
                $join->on('cli_entrega.codigo', '=', 'coleta.cod_loc_entrega')
                    ->on('cli_entrega.empresa', '=', 'coleta.empresa');
            })
            ->leftJoin('tipo_veiculo as tv', 'tv.codigo', '=', 'coleta.cod_tipo_veiculo')
            ->leftJoin('tipo_veiculo as tvn', 'tvn.codigo', '=', 'coleta.cod_tipo_veiculo_nec')

            ->where(function ($query) use ($cpf_cnpj) {

                if (auth()->user()->user_type == 'C') {

                    $query->where(function ($query1) use ($cpf_cnpj) {
                        if (rgDifTrimNull($cpf_cnpj)) {
                            $query1->where('cliente.cpf_cnpj', '=', $cpf_cnpj);
                        } else {
                            $query1->where('cliente.id', '=', auth()->user()->cliente_id);
                        }
                    });
                }
            })

            ->where('coleta.status', '=', 'C0') //Coleta - Solicitada

            ->where(function ($query) {
                if (auth()->user()->user_type == 'C') {
                    $query->where('coleta.origem_reg', '=', 'A3');
                } else {
                    $query->where('coleta.origem_reg', '=', 'A4');
                }
            })

            // Desconsideramos as comandas dos contratos (solic_origem_id <> null)
            // Consideramos as solicitações MULTI-DESTINOS e AUXILIARES.            
            ->where(function ($query) {
                $query->where(function ($query1) {
                    $query1->whereNull('coleta.solic_origem_id')
                        ->orWhere('coleta.solic_origem_id', '=', '0');
                })->orWhere('coleta.coleta_fixa', '=', 'M');
            })
            ->orderBy('coleta.dt_prev_coleta', 'asc')
            ->orderBy('coleta.hr_prev_coleta', 'asc')
            ->get();

        return ['status' => true, 'coleta' => $coleta];
    }

    public function store(Request $request)
    {
        $data = $request->all();

        /* ATENÇÃO - 
            O teste "strpos" existe para os campos, pois eles não são carregados na interface ou foram carregados e alterados 
            para um tipo de coleta que não exigia, logo não passaram pelo tratamento de diretivas que está formando a máscara dos dados (input da tela) 
            e irá gerar uma exceção de violação do tipo de campo no MySql.
        */

        if (isset($data['peso'])) {
            if (strpos($data['peso'], ',') > 0) {
                $data['peso'] = rgStringToFloat($data['peso']);
            }
        }

        if (isset($data['comp_carga'])) {
            if (strpos($data['comp_carga'], ',') > 0) {
                $data['comp_carga'] = rgStringToFloat($data['comp_carga']);
            }
        }

        if (isset($data['larg_carga'])) {
            if (strpos($data['larg_carga'], ',') > 0) {
                $data['larg_carga'] = rgStringToFloat($data['larg_carga']);
            }
        }

        if (isset($data['alt_carga'])) {
            if (strpos($data['alt_carga'], ',') > 0) {
                $data['alt_carga'] = rgStringToFloat($data['alt_carga']);
            }
        }

        $data['status'] = 'C0'; //Coleta - Solicitada

        if (auth()->user()->user_type == 'C') {
            $data['origem_reg'] = 'A3'; // Criado pelos clientes da Maser na plataforma            
        } else {
            $data['origem_reg'] = 'A4'; // Criado pelos usuários da Maser na plataforma
        }

        $data['ass_user_id'] = Auth()->user()->id;

        try {
            Coleta::create($data);
        } catch (\Exception $e) {
            $resultado['message'][0] = $e->getMessage();
            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }

    public function update(Request $request, $id)
    {
        if (!($coleta = Coleta::find($id))) {
            return ['status' => false];
        }

        $data = $request->all();

        /* ATENÇÃO - 
            O teste "strpos" existe para os campos, pois eles não são carregados na interface ou foram carregados e alterados 
            para um tipo de coleta que não exigia, logo não passaram pelo tratamento de diretivas que está formando a máscara dos dados (input da tela) 
            e irá gerar uma exceção de violação do tipo de campo no MySql.
        */

        if (isset($data['peso'])) {
            if (strpos($data['peso'], ',') > 0) {
                $data['peso'] = rgStringToFloat($data['peso']);
            }
        }

        if (isset($data['comp_carga'])) {
            if (strpos($data['comp_carga'], ',') > 0) {
                $data['comp_carga'] = rgStringToFloat($data['comp_carga']);
            }
        }

        if (isset($data['larg_carga'])) {
            if (strpos($data['larg_carga'], ',') > 0) {
                $data['larg_carga'] = rgStringToFloat($data['larg_carga']);
            }
        }

        if (isset($data['alt_carga'])) {
            if (strpos($data['alt_carga'], ',') > 0) {
                $data['alt_carga'] = rgStringToFloat($data['alt_carga']);
            }
        }

        $data['ass_user_id'] = Auth()->user()->id;

        try {
            $coleta->fill($data);
            $coleta->save();
        } catch (\Exception $e) {
            $resultado['message'][0] = $e->getMessage();
            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }

    public function destroy(Request $request)
    {

        $data = $request->all();

        if (!($coleta = Coleta::find($data['id']))) {
            return ['status' => false];
        }

        if (rgDifTrimNull($coleta->reentrega)) {
            $resultado['message'][0] = rgGetMsgRetornoAPI('E293');
            return ['status' => false, 'erros' => $resultado];
        }

        try {
            $coleta['ass_user_id'] = Auth()->user()->id;
            $coleta->save();
            $coleta->delete();
        } catch (\Exception $e) {
            $reconcile = new Reconcile();
            $resultado['message'][0] = $reconcile->TratarExceptionColeta('delete', $e);
            return ['status' => false, 'erros' => $resultado];
        }

        return ['status' => true];
    }

    public function getSolicitacoes()
    {
        //Se for um usuário do tipo "CLIENTE" - Vamos procurar solicitações por empresa ligando CPF/CNPJ se existir.
        $cpf_cnpj = null;
        $cliente = DB::table('cliente')
            ->select('id', 'cpf_cnpj')
            ->where('id', '=', auth()->user()->cliente_id)
            ->first();

        if (empty($cliente) == false) {
            if (rgDifTrimNull($cliente->cpf_cnpj)) {
                $cpf_cnpj = $cliente->cpf_cnpj;
            }
        }

        $solic = Coleta::select(
            DB::raw('
                coleta.id,
                coleta.empresa,
                coleta.numero,
                coleta.data_cad,
                coleta.hora_cad,
                coleta.coleta_fixa,
                coleta.cod_loc_coleta,
                coleta.placa_coleta,
                coleta.cod_loc_entrega,
                coleta.placa_entrega,
                coleta.img_carga,
                coleta.img_rom_coleta,
                coleta.img_rom_entrega,
                coleta.dt_prev_coleta,
                coleta.hr_prev_coleta,
                coleta.dt_efet_coleta,
                coleta.hr_sai_coleta,
                coleta.dt_prev_entrega,
                coleta.hr_prev_entrega,
                coleta.dt_efet_entrega,
                coleta.hr_sai_entrega,
                coleta.status,

                empresa.nome as nome_empresa,
                empresa.sigla,
                empresa.cor_fonte,
                empresa.cor_fundo,

                cli_coleta.nome as local_coleta,
                cli_entrega.nome as local_entrega,

                tipo_veiculo.descricao as descricao_tipo_veiculo, 
                
                (select count(cnf.id)
                 from coleta_nf as cnf
                 where (cnf.coleta_id = coleta.id)
                ) as notas_fiscais')
        )
            ->join('empresa', 'empresa.codigo', '=', 'coleta.empresa')
            ->join('cliente', function ($join) {
                $join->on('cliente.codigo', '=', 'coleta.cod_cliente')
                    ->on('cliente.empresa', '=', 'coleta.empresa');
            })
            ->join('cliente as cli_coleta', function ($join) {
                $join->on('cli_coleta.codigo', '=', 'coleta.cod_loc_coleta')
                    ->on('cli_coleta.empresa', '=', 'coleta.empresa');
            })
            ->join('cliente as cli_entrega', function ($join) {
                $join->on('cli_entrega.codigo', '=', 'coleta.cod_loc_entrega')
                    ->on('cli_entrega.empresa', '=', 'coleta.empresa');
            })
            ->leftJoin('tipo_veiculo', 'tipo_veiculo.codigo', '=', 'coleta.cod_tipo_veiculo')
            ->where(function ($query) use ($cpf_cnpj) {
                if (rgDifTrimNull($cpf_cnpj)) {
                    $query->where('cliente.cpf_cnpj', '=', $cpf_cnpj);
                } else {
                    $query->where('cliente.id', '=', auth()->user()->cliente_id);
                }
            })
            ->where(function ($query) {
                //Somente solicitações em andamento
                $query->where('coleta.status', '!=', 'CN')
                    ->where('coleta.status', '!=', 'ER');
            })
            ->get();

        $url_img = rgRetornarUrlImagens('');

        return ['status' => true, 'solic' => $solic, 'url_img' => $url_img];
    }


    public function getNotasFiscais(Request $request)
    {
        $coleta_id = $request->get('coleta_id');

        $notas_fiscais = ColetaNf::select(
            'coleta_nf.*',
            'coleta.status',
            'coleta.coleta_fixa',
            'coleta.solic_origem_id',
            'coleta.mot_nao_entrega as mot_nao_entrega_coleta',
            'coleta.obs_nao_entrega',
            'le.local_distrib',
            'coleta_reentrega.id as coleta_reentrega_id',
            'coleta_reentrega.numero as coleta_reentrega_numero'
        )
            ->join('coleta', 'coleta.id', '=', 'coleta_nf.coleta_id')
            ->leftjoin('cliente as le', function ($join) {
                $join->on('le.codigo', '=', 'coleta.cod_loc_entrega')
                    ->on('le.empresa', '=', 'coleta.empresa');
            })
            ->leftJoin('coleta as coleta_reentrega', 'coleta_reentrega.id', '=', 'coleta_nf.solic_destino_id')
            ->where('coleta_nf.coleta_id', '=', $coleta_id)
            ->orderBy('coleta_nf.serie', 'asc')
            ->orderBy('coleta_nf.numero', 'asc')
            ->get();

        if (count($notas_fiscais) > 0) {
            $permissao_incluir_editar_nf = false;

            if (in_array($notas_fiscais[0]->status, ['EP', 'ER'])) {
                $permissao_incluir_editar_nf = true;
                // Não permitimos incluir ou editar registros de NFS para solicitações
                // do tipo "MULTI-DESTINOS" origem (solicitação mãe)
                if (($notas_fiscais[0]->coleta_fixa == "M") && rgIgualTrimNull($notas_fiscais[0]->solic_origem_id)) {
                    $permissao_incluir_editar_nf = false;
                }
            }

            $dados_coleta_nf = $notas_fiscais[0];
        } else {
            $permissao_incluir_editar_nf = false;
            $dados_coleta_nf = [];
        }

        $url_img = rgRetornarUrlImagens('');

        return [
            'status' => true,
            'notas_fiscais' => $notas_fiscais,
            'permissao' => $permissao_incluir_editar_nf,
            'dados_coleta_nf' => $dados_coleta_nf,
            'url_img' => $url_img
        ];
    }

    public function getColetaPos(Request $request)
    {
        $coleta_id = $request->get('coleta_id');

        $coletasPos = ColetaPos::select('coleta_pos.*', 'motorista.nome')
            ->leftJoin('motorista', 'motorista.id', '=', 'coleta_pos.motorista_id')
            ->where('coleta_id', '=', $coleta_id)
            ->orderBy('id', 'asc')
            ->get();

        return ['status' => true, 'coletasPos' => $coletasPos];
    }

    public function getColetaLog(Request $request)
    {
        $coleta_id = $request->get('coleta_id');

        $coletasLog = ColetaLog::select('coleta_log.*', 'users.email', 'users.name')
            ->leftJoin('users', 'users.id', '=', 'coleta_log.ass_user_id')
            ->where('coleta_log.coleta_id', '=', $coleta_id)
            ->orderBy('coleta_log.id', 'asc')
            ->get();

        return ['status' => true, 'coletasLog' => $coletasLog];
    }


    public function RetornarTotaisKmTempoCliente(Request $request)
    {

        try {

            $data_ini = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
            $data_fim = Carbon::now(date_default_timezone_get())->format("Y-m-d");

            if ($request->get('data_ini') != null) {
                $data_ini = $request->get('data_ini');
                $data_ini = substr($data_ini, 0, 10);
            }

            if ($request->get('data_fim') != null) {
                $data_fim = $request->get('data_fim');
                $data_fim = substr($data_fim, 0, 10);
            }

            $api = new Coleta();
            $resultado = $api->Local_RetornarTotaisKmTempoCliente($data_ini, $data_fim);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function RetornarTotaisKmTempoVeiculo(Request $request)
    {

        try {

            $data_ini = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
            $data_fim = Carbon::now(date_default_timezone_get())->format("Y-m-d");

            if ($request->get('data_ini') != null) {
                $data_ini = $request->get('data_ini');
                $data_ini = substr($data_ini, 0, 10);
            }

            if ($request->get('data_fim') != null) {
                $data_fim = $request->get('data_fim');
                $data_fim = substr($data_fim, 0, 10);
            }

            $api = new Coleta();
            $resultado = $api->Local_RetornarTotaisKmTempoVeiculo($data_ini, $data_fim);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function RetornarTotaisKmTempoTipoVeiculo(Request $request)
    {

        try {

            $data_ini = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
            $data_fim = Carbon::now(date_default_timezone_get())->format("Y-m-d");

            if ($request->get('data_ini') != null) {
                $data_ini = $request->get('data_ini');
                $data_ini = substr($data_ini, 0, 10);
            }

            if ($request->get('data_fim') != null) {
                $data_fim = $request->get('data_fim');
                $data_fim = substr($data_fim, 0, 10);
            }

            $api = new Coleta();
            $resultado = $api->Local_RetornarTotaisKmTempoTipoVeiculo($data_ini, $data_fim);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function RetornarTotaisKmTempoMotorista(Request $request)
    {

        try {

            $data_ini = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
            $data_fim = Carbon::now(date_default_timezone_get())->format("Y-m-d");

            if ($request->get('data_ini') != null) {
                $data_ini = $request->get('data_ini');
                $data_ini = substr($data_ini, 0, 10);
            }

            if ($request->get('data_fim') != null) {
                $data_fim = $request->get('data_fim');
                $data_fim = substr($data_fim, 0, 10);
            }

            $api = new Coleta();
            $resultado = $api->Local_RetornarTotaisKmTempoMotorista($data_ini, $data_fim);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function CancelarColetaSemDesloc(Request $request)
    {

        try {
            $coleta_id = $request->get('coleta_id');
            $obs_nao_coleta = $request->get('obs_nao_coleta');

            $api = new Coleta();
            $resultado = $api->Local_CancelarColetaSemDesloc($coleta_id, $obs_nao_coleta);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }
}
