<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Coleta;
use App\Controle;
use Carbon\Carbon;
use DB;

class ControleController extends Controller
{

    // Contadores - Cards dos paíneis, devem respeitar o mesmo select de cada etapa, mas retornar somente o Count
    public function countColetasPendentes(Request $request)
    {
        $hoje = $request->get('hoje');
        $tarde = $request->get('tarde');

        $countColetasPendentes = Coleta::select('coleta.id')

            // *** ATENÇÃO *** Se as condições desse WHERE for alterada, deve ser feito o mesmo na rotina "getColetasPendentes".

            ->where('coleta.status', '=', 'C0') //Coleta - Solicitada

            // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
            // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
            //
            ->where(function ($query) {
                $query->where('coleta.coleta_fixa', '!=', 'C')
                    ->orWhere(function ($query1) {
                        $query1->where('coleta.coleta_fixa', '=', 'C')
                            ->where(function ($query2) {
                                $query2->whereNull('coleta.solic_origem_id')
                                    ->orWhere('coleta.solic_origem_id', '=', '0');
                            });
                    });
            })

            ->where(function ($query) use ($hoje, $tarde) {
                if ($hoje == "true") {
                    $timezone_app = date_default_timezone_get();
                    $data_hora_serv = Carbon::now($timezone_app)->format('Y-m-d'); //Queremos somente a data.
                    $query->where('coleta.dt_prev_coleta', '<=', $data_hora_serv);

                    if ($tarde == "false") {
                        $query->where(function ($query) use ($data_hora_serv) {
                            $query->whereTime('coleta.hr_prev_coleta', '<=', '12:00:00')
                                ->orWhere('coleta.dt_prev_coleta', '<', $data_hora_serv);
                        });
                    }
                }
            })
            ->count();

        return ['status' => true, 'countColetasPendentes' => $countColetasPendentes];
    }

    public function countColetasAndamento()
    {
        $countColetasAndamento = Coleta::select('coleta.id')

            // *** ATENÇÃO *** Se as condições desse WHERE for alterada, deve ser feito o mesmo na rotina "getColetasAndamento".

            ->where(function ($query) {
                $query->where(function ($query1) {
                    $query1->whereNotNull('coleta.placa_coleta')
                        ->whereIn('coleta.status', ['C1', 'C2', 'C3', 'C4']);
                });
            })

            // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
            // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
            //
            ->where(function ($query) {
                $query->where('coleta.coleta_fixa', '!=', 'C')
                    ->orWhere(function ($query1) {
                        $query1->where('coleta.coleta_fixa', '=', 'C')
                            ->where(function ($query2) {
                                $query2->whereNull('coleta.solic_origem_id')
                                    ->orWhere('coleta.solic_origem_id', '=', '0');
                            });
                    });
            })

            ->count();

        return ['status' => true, 'countColetasAndamento' => $countColetasAndamento];
    }

    public function countEntregasPendentes()
    {
        $countEntregasPendentes = Coleta::select('coleta.id')

            // *** ATENÇÃO *** Se as condições desse WHERE for alterada, deve ser feito o mesmo na rotina "getEntregasPendentes".

            // Desconsideramos as COMANDAS dos contratos => (coleta_fixa = "C"  E  solic_origem_id <> zero/null)
            // com qualquer status. Os contratos => (coleta_fixa = "C"  E  solic_origem_id == zero/null) são desconsiderados
            // porque não ficam nenhum dos status: 'CR', 'E0', 'EN', 'EP'.            
            //
            ->where(function ($query) {
                $query->where(function ($query1) {
                    $query1->where('coleta.coleta_fixa', '!=', 'M')
                        ->whereIn('coleta.status', ['CR', 'E0', 'EN', 'EP'])
                        ->whereNull('coleta.solic_origem_id');
                })->orWhere(function ($query1) {
                    // As solicitações auxiliares iniciam na fase de entrega. Status = 'E1'
                    // As reentregas de solicitações auxiliares passam pela etapa de coleta e podem ficar com Status = 'CR'
                    $query1->where('coleta.coleta_fixa', '=', 'M')
                        ->whereIn('coleta.status', ['CR', 'E0', 'EN', 'EP'])
                        ->whereNotNull('coleta.solic_origem_id');
                })->orWhere(function ($query1) {
                    // Não mostramos como entrega pendente uma solicitação origem Multi-Destinos, que conste como descarregada no pavilhão.
                    // Marcamos automaticamente (carga_pavilhao == S) quando todas as solicitações auxiliares foram geradas.
                    $query1->where('coleta.coleta_fixa', '=', 'M')
                        ->where('coleta.status', '=', 'CR')
                        ->where(function ($query2) {
                            $query2->whereNull('coleta.carga_pavilhao')
                                ->orWhere('coleta.carga_pavilhao', '!=', 'S');
                        })
                        ->whereNull('coleta.solic_origem_id');
                });
            })

            // Em qualquer situação mostramos apenas se a solicitação de reentrega não foi gerada.
            // 
            ->where(function ($query) {
                $query->whereNull('coleta.reentrega_gerada')
                    ->orWhere('coleta.reentrega_gerada', '!=', 'S');
            })

            ->count();

        return ['status' => true, 'countEntregasPendentes' => $countEntregasPendentes];
    }

    public function countEntregasAndamento()
    {
        $countEntregasAndamento = Coleta::select('coleta.id')

            // *** ATENÇÃO *** Se as condições desse WHERE for alterada, deve ser feito o mesmo na rotina "getEntregasAndamento".

            // Desconsideramos as COMANDAS dos contratos => (coleta_fixa = "C"  E  solic_origem_id <> zero/null)
            // com qualquer status. Os contratos => (coleta_fixa = "C"  E  solic_origem_id == zero/null) são desconsiderados
            // porque não ficam nenhum dos status de entrega em andamento.
            // 
            // As solicitações ORIGEM Multi-Destinos => (coleta_fixa = "M"  E  solic_origem_id == zero/null) são desconsideradas
            // porque não ficam com nenhum status de entrega em andamento. 
            //
            ->where(function ($query) {
                $query->whereIn('coleta.status', ['E1', 'E2', 'E3', 'E4'])
                    ->whereNotNull('coleta.placa_entrega');
            })

            ->where(function ($query) {
                $query->where(function ($query1) {
                    $query1->where('coleta.coleta_fixa', '!=', 'M')
                        ->whereNull('coleta.solic_origem_id');
                })->orWhere(function ($query1) {
                    $query1->where('coleta.coleta_fixa', '=', 'M')
                        ->whereNotNull('coleta.solic_origem_id');
                });
            })

            ->count();

        return ['status' => true, 'countEntregasAndamento' => $countEntregasAndamento];
    }

    // public function countSolicitacoesFinalizadas()
    // {
    //     //$timezone_app = date_default_timezone_get();
    //     //$data_hora_serv = Carbon::now($timezone_app)->format('Y-m-d'); //Queremos somente a data.

    //     //$countSolicitacoesFinalizadas = Coleta::select('coleta.id')

    //         // *** ATENÇÃO *** Se as condições desse WHERE for alterada, deve ser feito o mesmo na rotina "getSolicitacoesFinalizadas".

    //         // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
    //         // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
    //         //
    //         // ->where(function ($query) {
    //         //     $query->where('coleta.coleta_fixa', '!=', 'C')
    //         //         ->orWhere(function ($query1) {
    //         //             $query1->where('coleta.coleta_fixa', '=', 'C')
    //         //                 ->where(function ($query2) {
    //         //                     $query2->whereNull('coleta.solic_origem_id')
    //         //                         ->orWhere('coleta.solic_origem_id', '=', '0');
    //         //                 });
    //         //         });
    //         // })

    //         ->where(function ($query) use ($data_hora_serv) {
    //             $query->where(function ($query1) use ($data_hora_serv) {
    //                 $query1->where('coleta.dt_prev_coleta', '=', $data_hora_serv)
    //                     ->where('coleta.status', '=', 'CN');
    //             })->orWhere(function ($query2) use ($data_hora_serv) {
    //                 $query2->where('coleta.dt_efet_entrega', '=', $data_hora_serv)
    //                     ->where(function ($query3) {
    //                         $query3->where('coleta.status', '=', 'ER')
    //                             ->orWhere(function ($query4) {
    //                                 $query4->whereIn('coleta.status', ['EN', 'EP'])
    //                                     ->where('coleta.reentrega_gerada', '=', 'S');
    //                             });
    //                     });
    //             });
    //         })

    //         ->count();

    //     return ['status' => true, 'countSolicitacoesFinalizadas' => $countSolicitacoesFinalizadas];
    // }

    public function countSolicitacoesFinalizadas()
    {
        $timezone_app = date_default_timezone_get();
        $data_hora_serv = Carbon::now($timezone_app)->format('Y-m-d');

        // *** ATENÇÃO *** Se as condições desse WHERE for alterada, deve ser feito o mesmo na rotina "getSolicitacoesFinalizadas".

        // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
        // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
        //        
        $naoComanda = function ($query) {
            $query->where('coleta.coleta_fixa', '!=', 'C')
                ->orWhere(function ($query1) {
                    $query1->where('coleta.coleta_fixa', '=', 'C')
                        ->where(function ($query2) {
                            $query2->whereNull('coleta.solic_origem_id')
                                ->orWhere('coleta.solic_origem_id', '=', '0');
                        });
                });
        };

        // Parte 1: canceladas no dia
        $parte1 = Coleta::select('coleta.id')
            ->where($naoComanda)
            ->where('coleta.dt_prev_coleta', '=', $data_hora_serv)
            ->where('coleta.status', '=', 'CN');

        // Parte 2: entregues/encerradas no dia
        $parte2 = Coleta::select('coleta.id')
            ->where($naoComanda)
            ->where('coleta.dt_efet_entrega', '=', $data_hora_serv)
            ->where(function ($query) {
                $query->where('coleta.status', '=', 'ER')
                    ->orWhere(function ($query1) {
                        $query1->whereIn('coleta.status', ['EN', 'EP'])
                            ->where('coleta.reentrega_gerada', '=', 'S');
                    });
            });

        $countSolicitacoesFinalizadas = $parte1->union($parte2)->get()->count();

        return ['status' => true, 'countSolicitacoesFinalizadas' => $countSolicitacoesFinalizadas];
    }


    // Fim dos contadores.

    public function getColetasPendentes(Request $request)
    {
        $hoje = $request->get('hoje');
        $tarde = $request->get('tarde');

        $coletasPendentes = Coleta::select(
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

            // *** ATENÇÃO *** Se as condições desse WHERE for alterada, deve ser feito o mesmo na rotina "countColetasPendentes".

            ->where('coleta.status', '=', 'C0') //Coleta - Solicitada

            // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
            // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
            //
            ->where(function ($query) {
                $query->where('coleta.coleta_fixa', '!=', 'C')
                    ->orWhere(function ($query1) {
                        $query1->where('coleta.coleta_fixa', '=', 'C')
                            ->where(function ($query2) {
                                $query2->whereNull('coleta.solic_origem_id')
                                    ->orWhere('coleta.solic_origem_id', '=', '0');
                            });
                    });
            })

            ->where(function ($query) use ($hoje, $tarde) {
                if ($hoje == "true") {
                    $timezone_app = date_default_timezone_get();
                    $data_hora_serv = Carbon::now($timezone_app)->format('Y-m-d'); //Queremos somente a data.
                    $query->where('coleta.dt_prev_coleta', '<=', $data_hora_serv);

                    if ($tarde == "false") {
                        $query->where(function ($query) use ($data_hora_serv) {
                            $query->whereTime('coleta.hr_prev_coleta', '<=', '12:00:00')
                                ->orWhere('coleta.dt_prev_coleta', '<', $data_hora_serv);
                        });
                    }
                }
            })
            ->orderBy('coleta.dt_prev_coleta', 'asc')
            ->orderBy('coleta.hr_prev_coleta', 'asc')
            ->get();

        return ['status' => true, 'coletasPendentes' => $coletasPendentes];
    }

    public function getColetasAndamento()
    {
        $coletasAndamento = Coleta::select(
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

                mot_coleta.nome as nome_motorista_coleta,
                mot_entrega.nome as nome_motorista_entrega,

                tv.descricao as descricao_tipo_veiculo,
                tvn.descricao as descricao_tipo_veiculo_nec,

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
            ->leftJoin('motorista as mot_coleta', 'mot_coleta.id', '=', 'coleta.motor_coleta_id')
            ->leftJoin('motorista as mot_entrega', 'mot_entrega.id', '=', 'coleta.motor_entrega_id')
            ->leftJoin('tipo_veiculo as tv', 'tv.codigo', '=', 'coleta.cod_tipo_veiculo')
            ->leftJoin('tipo_veiculo as tvn', 'tvn.codigo', '=', 'coleta.cod_tipo_veiculo_nec')

            // *** ATENÇÃO *** Se as condições desse WHERE for alterada, deve ser feito o mesmo na rotina "countColetasAndamento".

            ->where(function ($query) {
                $query->where(function ($query1) {
                    $query1->whereNotNull('coleta.placa_coleta')
                        ->whereIn('coleta.status', ['C1', 'C2', 'C3', 'C4']);
                });
            })

            // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
            // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
            //
            ->where(function ($query) {
                $query->where('coleta.coleta_fixa', '!=', 'C')
                    ->orWhere(function ($query1) {
                        $query1->where('coleta.coleta_fixa', '=', 'C')
                            ->where(function ($query2) {
                                $query2->whereNull('coleta.solic_origem_id')
                                    ->orWhere('coleta.solic_origem_id', '=', '0');
                            });
                    });
            })

            ->orderBy('coleta.dt_prev_coleta', 'asc')
            ->orderBy('coleta.hr_prev_coleta', 'asc')
            ->get();

        $url_img = rgRetornarUrlImagens('');

        return ['status' => true, 'coletasAndamento' => $coletasAndamento, 'url_img' => $url_img];
    }

    public function getEntregasPendentes()
    {
        $entregasPendentes = Coleta::select(
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

                motorista.nome as nome_motorista,

                tv.descricao as descricao_tipo_veiculo,
                tvn.descricao as descricao_tipo_veiculo_nec,
                
                (select count(cnf.id)
                    from coleta_nf as cnf
                    where (cnf.coleta_id = coleta.id)
                ) as notas_fiscais,

                (select count(cnf.id)
                    from coleta_nf as cnf
                    where (cnf.coleta_id = coleta.id)
                    AND (cnf.solic_destino_id IS NULL)
                ) as qtde_notas_distrib,
                        
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
            ->leftJoin('motorista', 'motorista.id', '=', 'coleta.motor_coleta_id')
            ->leftJoin('tipo_veiculo as tv', 'tv.codigo', '=', 'coleta.cod_tipo_veiculo')
            ->leftJoin('tipo_veiculo as tvn', 'tvn.codigo', '=', 'coleta.cod_tipo_veiculo_nec')

            // *** ATENÇÃO *** Se as condições desse WHERE for alterada, deve ser feito o mesmo na rotina "countEntregasPendentes".

            // Desconsideramos as COMANDAS dos contratos => (coleta_fixa = "C"  E  solic_origem_id <> zero/null)
            // com qualquer status. Os contratos => (coleta_fixa = "C"  E  solic_origem_id == zero/null) são desconsiderados
            // porque não ficam nenhum dos status: 'CR', 'E0', 'EN', 'EP'.            
            //
            ->where(function ($query) {
                $query->where(function ($query1) {
                    $query1->where('coleta.coleta_fixa', '!=', 'M')
                        ->whereIn('coleta.status', ['CR', 'E0', 'EN', 'EP'])
                        ->whereNull('coleta.solic_origem_id');
                })->orWhere(function ($query1) {
                    // As solicitações auxiliares iniciam na fase de entrega. Status = 'E1'
                    // As reentregas de solicitações auxiliares passam pela etapa de coleta e podem ficar com Status = 'CR'
                    $query1->where('coleta.coleta_fixa', '=', 'M')
                        ->whereIn('coleta.status', ['CR', 'E0', 'EN', 'EP'])
                        ->whereNotNull('coleta.solic_origem_id');
                })->orWhere(function ($query1) {
                    // Não mostramos como entrega pendente uma solicitação origem Multi-Destinos, que conste como descarregada no pavilhão.
                    // Marcamos automaticamente (carga_pavilhao == S) quando todas as solicitações auxiliares foram geradas.
                    $query1->where('coleta.coleta_fixa', '=', 'M')
                        ->where('coleta.status', '=', 'CR')
                        ->where(function ($query2) {
                            $query2->whereNull('coleta.carga_pavilhao')
                                ->orWhere('coleta.carga_pavilhao', '!=', 'S');
                        })
                        ->whereNull('coleta.solic_origem_id');
                });
            })

            // Em qualquer situação mostramos apenas se a solicitação de reentrega não foi gerada.
            // 
            ->where(function ($query) {
                $query->whereNull('coleta.reentrega_gerada')
                    ->orWhere('coleta.reentrega_gerada', '!=', 'S');
            })

            ->orderBy('coleta.dt_prev_entrega', 'asc')
            ->orderBy('coleta.hr_prev_entrega', 'asc')
            ->get();

        $url_img = rgRetornarUrlImagens('');

        return ['status' => true, 'entregasPendentes' => $entregasPendentes, 'url_img' => $url_img];
    }

    public function getEntregasAndamento()
    {
        $entregasAndamento = Coleta::select(
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

                mot_coleta.nome as nome_motorista_coleta,
                mot_entrega.nome as nome_motorista_entrega,

                tv.descricao as descricao_tipo_veiculo,
                tvn.descricao as descricao_tipo_veiculo_nec,

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
            ->leftJoin('motorista as mot_coleta', 'mot_coleta.id', '=', 'coleta.motor_coleta_id')
            ->leftJoin('motorista as mot_entrega', 'mot_entrega.id', '=', 'coleta.motor_entrega_id')
            ->leftJoin('tipo_veiculo as tv', 'tv.codigo', '=', 'coleta.cod_tipo_veiculo')
            ->leftJoin('tipo_veiculo as tvn', 'tvn.codigo', '=', 'coleta.cod_tipo_veiculo_nec')

            // *** ATENÇÃO *** Se as condições desse WHERE for alterada, deve ser feito o mesmo na rotina "countEntregasAndamento".

            // Desconsideramos as COMANDAS dos contratos => (coleta_fixa = "C"  E  solic_origem_id <> zero/null)
            // com qualquer status. Os contratos => (coleta_fixa = "C"  E  solic_origem_id == zero/null) são desconsiderados
            // porque não ficam nenhum dos status de entrega em andamento.
            // 
            // As solicitações ORIGEM Multi-Destinos => (coleta_fixa = "M"  E  solic_origem_id == zero/null) são desconsideradas
            // porque não ficam com nenhum status de entrega em andamento. 
            //
            ->where(function ($query) {
                $query->whereIn('coleta.status', ['E1', 'E2', 'E3', 'E4'])
                    ->whereNotNull('coleta.placa_entrega');
            })

            ->where(function ($query) {
                $query->where(function ($query1) {
                    $query1->where('coleta.coleta_fixa', '!=', 'M')
                        ->whereNull('coleta.solic_origem_id');
                })->orWhere(function ($query1) {
                    $query1->where('coleta.coleta_fixa', '=', 'M')
                        ->whereNotNull('coleta.solic_origem_id');
                });
            })
            ->get();

        $url_img = rgRetornarUrlImagens('');

        return ['status' => true, 'entregasAndamento' => $entregasAndamento, 'url_img' => $url_img];
    }

    public function getSolicitacoesFinalizadas()
    {
        $timezone_app = date_default_timezone_get();
        $data_hora_serv = Carbon::now($timezone_app)->format('Y-m-d'); //Queremos somente a data.

        $solicitacoesFinalizadas = Coleta::select(
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

                mot_coleta.nome as nome_motorista_coleta,
                mot_entrega.nome as nome_motorista_entrega,
                
                tv.descricao as descricao_tipo_veiculo,
                tvn.descricao as descricao_tipo_veiculo_nec,
                
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
            ->leftJoin('motorista as mot_coleta', 'mot_coleta.id', '=', 'coleta.motor_coleta_id')
            ->leftJoin('motorista as mot_entrega', 'mot_entrega.id', '=', 'coleta.motor_entrega_id')
            ->leftJoin('tipo_veiculo as tv', 'tv.codigo', '=', 'coleta.cod_tipo_veiculo')
            ->leftJoin('tipo_veiculo as tvn', 'tvn.codigo', '=', 'coleta.cod_tipo_veiculo_nec')

            // *** ATENÇÃO *** Se as condições desse WHERE for alterada, deve ser feito o mesmo na rotina "countSolicitacoesFinalizadas".

            // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
            // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
            //
            ->where(function ($query) {
                $query->where('coleta.coleta_fixa', '!=', 'C')
                    ->orWhere(function ($query1) {
                        $query1->where('coleta.coleta_fixa', '=', 'C')
                            ->where(function ($query2) {
                                $query2->whereNull('coleta.solic_origem_id')
                                    ->orWhere('coleta.solic_origem_id', '=', '0');
                            });
                    });
            })

            ->where(function ($query) use ($data_hora_serv) {
                $query->where(function ($query1) use ($data_hora_serv) {
                    $query1->where('coleta.dt_prev_coleta', '=', $data_hora_serv)
                        ->where('coleta.status', '=', 'CN');
                })->orWhere(function ($query2) use ($data_hora_serv) {
                    $query2->where('coleta.dt_efet_entrega', '=', $data_hora_serv)
                        ->where(function ($query3) {
                            $query3->where('coleta.status', '=', 'ER')
                                ->orWhere(function ($query4) {
                                    $query4->whereIn('coleta.status', ['EN', 'EP'])
                                        ->where('coleta.reentrega_gerada', '=', 'S');
                                });
                        });
                });
            })

            ->orderBy('coleta.dt_prev_entrega', 'asc')
            ->orderBy('coleta.hr_prev_entrega', 'asc')
            ->get();

        $url_img = rgRetornarUrlImagens('');

        return ['status' => true, 'solicitacoesFinalizadas' => $solicitacoesFinalizadas, 'url_img' => $url_img];
    }

    public function RetornarVeiculosColeta(Request $request)
    {
        try {
            $coleta_id     = $request->get('coleta_id');
            $com_motorista = $request->get('com_motorista');

            $painel = new Controle();
            $resultado = $painel->Local_RetornarVeiculosColeta($coleta_id, $com_motorista);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function DefinirVeiculoColeta(Request $request)
    {
        try {
            $coleta_id = $request->get('coleta_id');
            $placa = $request->get('placa');
            $autorizar = $request->get('autorizar');

            $painel = new Controle();
            $resultado = $painel->Local_DefinirVeiculoColeta($coleta_id, $placa, $autorizar);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function DefinirVeiculoEntrega(Request $request)
    {

        try {

            $coleta_id = $request->get('coleta_id');
            $placa = $request->get('placa');
            $autorizar = $request->get('autorizar');

            $painel = new Controle();
            $resultado = $painel->Local_DefinirVeiculoEntrega($coleta_id, $placa, $autorizar);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function RetornarVeiculosFrota(Request $request)
    {

        try {

            $data = $request->all();

            $painel = new Controle();
            $resultado = $painel->Local_RetornarVeiculosFrota($data);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function EnviarInstrucaoColeta(Request $request)
    {

        try {

            $coleta_id = $request->get('coleta_id');
            $instrucao = $request->get('instrucao');
            $txt_instrucao = $request->get('txt_instrucao');
            $placa_baldeacao = $request->get('placa_baldeacao');

            $painel = new Controle();
            $resultado = $painel->Local_EnviarInstrucaoColeta($coleta_id, $instrucao, $txt_instrucao, $placa_baldeacao);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function RetornarDadosVeiculoCarga(Request $request)
    {

        try {

            $placa = $request->get('placa');

            $painel = new Controle();
            $resultado = $painel->Local_RetornarDadosVeiculoCarga($placa);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function RetornarEntregasPendentesCarga(Request $request)
    {

        try {
            $placa = $request->get('placa');

            $painel = new Controle();
            $resultado = $painel->Local_RetornarEntregasPendentesCarga($placa);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function RetornarColetasVeiculoCarga(Request $request)
    {

        try {
            $placa = $request->get('placa');
            $local_saida = $request->get('local_saida');
            $hora_saida = $request->get('hora_saida');

            $painel = new Controle();
            $resultado = $painel->Local_RetornarColetasVeiculoCarga($placa, $local_saida, $hora_saida);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function RetornarColetasResumoDia(Request $request)
    {

        try {
            $data_prevista = $request->get('data_prevista');

            $painel = new Controle();
            $resultado = $painel->Local_RetornarColetasResumoDia($data_prevista);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function DefinirMotoristaPrevisto(Request $request)
    {
        try {
            $coleta_id = $request->get('coleta_id');
            $motorista_id = $request->get('motorista_id');
            $etapa = $request->get('etapa');

            $painel = new Controle();
            $resultado = $painel->Local_DefinirMotoristaPrevisto($coleta_id, $motorista_id, $etapa);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function DesvincularVeiculoSolicitacao(Request $request)
    {

        try {
            $coleta_id = $request->get('coleta_id');
            $placa = $request->get('placa');

            $painel = new Controle();
            $resultado = $painel->Local_DesvincularVeiculoSolicitacao($coleta_id, $placa);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function RetornarInstrucoesColeta(Request $request)
    {

        try {
            $coleta_id = $request->get('coleta_id');

            $painel = new Controle();
            $resultado = $painel->Local_RetornarInstrucoesColeta($coleta_id);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function RetornarVeiculosBaldeacaoSimples(Request $request)
    {

        try {
            $coleta_id     = $request->get('coleta_id');
            $com_motorista = $request->get('com_motorista');

            $painel = new Controle();
            $resultado = $painel->Local_RetornarVeiculosBaldeacaoSimples($coleta_id, $com_motorista);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function GravarSeqAtendRotaCarga(Request $request)
    {

        try {
            $lista_coletas = $request->get('lista_coletas');

            $painel = new Controle();
            $resultado = $painel->Local_GravarSeqAtendRotaCarga($lista_coletas);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function RetornarVeiculosBaldeacao(Request $request)
    {

        try {
            $coleta_id = $request->get('coleta_id');
            $com_motorista = $request->get('com_motorista');

            $painel = new Controle();
            $resultado = $painel->Local_RetornarVeiculosBaldeacao($coleta_id, $com_motorista);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function ExecutarBaldeacaoPatio(Request $request)
    {

        try {
            $coleta_id     = $request->get('coleta_id');
            $placa_destino = $request->get('placa_destino');

            $painel = new Controle();
            $resultado = $painel->Local_ExecutarBaldeacaoPatio($coleta_id, $placa_destino);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function SetarEntregaConsolidada(Request $request)
    {

        try {

            $coleta_id = $request->get('coleta_id');
            $consolidada = $request->get('consolidada');

            $painel = new Controle();
            $resultado = $painel->Local_SetarEntregaConsolidada($coleta_id, $consolidada);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }
}
