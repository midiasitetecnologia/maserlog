<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\WisdomUser;
use Carbon\Carbon;
use DB;

class Dashboard extends Model
{

    public function Local_RetornarClientesColetasCadIncomp()
    {

        $dados = array();

        // Selecionar solicitações pendentes

        // Verificar solicitações pendentes onde o local de coleta ou entrega não tenha 
        // coordenadas OU que não tenha os horários de atendimento
        $coletas = $this->SelecionaClientesComCadIncompleto();

        if (count($coletas) > 0) {

            $ind = 0;

            foreach ($coletas as $col) {

                // Coleta
                $erros_coleta = '';

                if (rgIgualZeroNull($col->geo_lat_coleta) || rgIgualZeroNull($col->geo_lat_coleta)) {
                    $erros_coleta = 'Coordenadas;  ';
                }

                // A função "strtotime" quando aplicada a uma data inválida (vazio/null), retorna FALSE.

                if ($col->hr_prev_coleta <= '12:00:00') {

                    if ((strtoTime($col->hr_ini_coleta_man) == false) || (strtoTime($col->hr_fim_coleta_man) == false)) {
                        $erros_coleta = $erros_coleta . 'Horários coleta manhã';
                    }
                } else {

                    if ((strtoTime($col->hr_ini_coleta_tar) == false) || (strtoTime($col->hr_fim_coleta_tar) == false)) {
                        $erros_coleta = $erros_coleta . 'Horários coleta tarde';
                    }
                }

                // Entrega
                $erros_entrega = '';

                if (rgIgualZeroNull($col->geo_lat_entrega)  || rgIgualZeroNull($col->geo_lat_entrega)) {
                    $erros_entrega = 'Coordenadas;  ';
                }

                if ($col->hr_prev_entrega <= '12:00:00') {

                    if ((strtoTime($col->hr_ini_entrega_man) == false) || (strtoTime($col->hr_fim_entrega_man) == false)) {
                        $erros_entrega = $erros_entrega . 'Horários entrega manhã';
                    }
                } else {

                    if ((strtoTime($col->hr_ini_entrega_tar) == false) || (strtoTime($col->hr_fim_entrega_tar) == false)) {
                        $erros_entrega = $erros_entrega . 'Horários entrega tarde';
                    }
                }

                // Só adicionamos no array de retorno se pelo menos uma das mensagens de erro tiverem 
                // algum conteudo (coleta ou entrega)
                if ((trim($erros_coleta) <> '') || (trim($erros_entrega) <> '')) {
                    $dados[$ind] = $this->MontaArrayColetasCadIncompleto($col, $erros_coleta, $erros_entrega);

                    $ind++;
                }
            }
        }

        $retorno['dados'] = $dados;

        return $retorno;
    }

    public function SelecionaClientesComCadIncompleto()
    {

        $coletas = DB::table('coleta as col')
            ->select(
                'col.id AS coleta_id',
                'col.empresa',
                'col.numero',
                'col.data_cad',
                'col.hora_cad',
                'col.coleta_fixa',
                'col.dt_prev_coleta',
                'col.hr_prev_coleta',
                'col.dt_prev_entrega',
                'col.hr_prev_entrega',
                'col.status',

                'e.nome as nome_empresa',
                'e.sigla',
                'e.cor_fonte',
                'e.cor_fundo',

                'lc.id AS id_local_coleta',
                'lc.codigo AS cod_local_coleta',
                'lc.nome AS local_coleta',
                'lc.geo_lat AS geo_lat_coleta',
                'lc.geo_lng AS geo_lng_coleta',
                'lc.hr_ini_coleta_man',
                'lc.hr_fim_coleta_man',
                'lc.hr_ini_coleta_tar',
                'lc.hr_fim_coleta_tar',

                'le.id AS id_local_entrega',
                'le.codigo AS cod_local_entrega',
                'le.nome AS local_entrega',
                'le.geo_lat AS geo_lat_entrega',
                'le.geo_lng AS geo_lng_entrega',
                'le.hr_ini_entrega_man',
                'le.hr_fim_entrega_man',
                'le.hr_ini_entrega_tar',
                'le.hr_fim_entrega_tar'

            )

            ->Join('empresa AS e', 'e.codigo', '=', 'col.empresa')
            ->leftjoin('cliente AS lc', function ($join) {
                $join->on('lc.codigo', '=', 'col.cod_loc_coleta')
                    ->on('lc.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente AS le', function ($join) {
                $join->on('le.codigo', '=', 'col.cod_loc_entrega')
                    ->on('le.empresa', '=', 'col.empresa');
            })
            // Desconsideramos solicitações encerradas
            ->where('col.status', '<>', 'CN')
            ->where('col.status', '<>', 'ER')

            // Desconsideramos coletas fixas tipo CONTRATO e as COMANDAS
            ->where('col.coleta_fixa', '<>', 'C')
            ->where(function ($query) {
                $query->whereNull('col.solic_origem_id')
                    ->orWhere('col.solic_origem_id', '=', '0');
            })

            ->whereRaw('( (lc.geo_lat = 0 OR lc.geo_lat IS NULL)' .
                ' OR (lc.geo_lng = 0 OR lc.geo_lng IS NULL)' .
                ' OR (le.geo_lat = 0 OR le.geo_lat IS NULL)' .
                ' OR (le.geo_lng = 0 OR le.geo_lng IS NULL)' .

                ' OR (lc.hr_ini_coleta_man = 0 OR lc.hr_ini_coleta_man IS NULL)' .
                ' OR (lc.hr_fim_coleta_man = 0 OR lc.hr_fim_coleta_man IS NULL)' .
                ' OR (lc.hr_ini_coleta_tar = 0 OR lc.hr_ini_coleta_tar IS NULL)' .
                ' OR (lc.hr_fim_coleta_tar = 0 OR lc.hr_fim_coleta_tar IS NULL)' .

                ' OR (le.hr_ini_entrega_man = 0 OR le.hr_ini_entrega_man IS NULL)' .
                ' OR (le.hr_fim_entrega_man = 0 OR le.hr_fim_entrega_man IS NULL)' .
                ' OR (le.hr_ini_entrega_tar = 0 OR le.hr_ini_entrega_tar IS NULL)' .
                ' OR (le.hr_fim_entrega_tar = 0 OR le.hr_fim_entrega_tar IS NULL) )')

            ->orderBy('col.numero', 'asc')
            ->orderBy('col.data_cad', 'asc')
            ->get();

        return $coletas;
    }


    public function MontaArrayColetasCadIncompleto($col, $erros_coleta, $erros_entrega)
    {

        $arr_col['coleta_id'] = $col->coleta_id;
        $arr_col['empresa']   = $col->empresa;
        $arr_col['data_cad']  = $col->data_cad;
        $arr_col['hora_cad']  = $col->hora_cad;

        $arr_col['nome_empresa'] = $col->nome_empresa;
        $arr_col['sigla']        = $col->sigla;
        $arr_col['cor_fonte']    = $col->cor_fonte;
        $arr_col['cor_fundo']    = $col->cor_fundo;

        $arr_col['numero'] = $col->numero;

        $arr_col['dt_prev_coleta'] = $col->dt_prev_coleta;
        $arr_col['hr_prev_coleta'] = $col->hr_prev_coleta;

        $arr_col['dt_prev_entrega'] = $col->dt_prev_entrega;
        $arr_col['hr_prev_entrega'] = $col->hr_prev_entrega;

        $arr_col['id_local_coleta']  = $col->id_local_coleta;
        $arr_col['cod_local_coleta'] = $col->cod_local_coleta;
        $arr_col['local_coleta']     = $col->local_coleta;

        $arr_col['id_local_entrega']  = $col->id_local_entrega;
        $arr_col['cod_local_entrega'] = $col->cod_local_entrega;
        $arr_col['local_entrega']     = $col->local_entrega;

        $arr_col['coleta_fixa'] = $col->coleta_fixa;
        $arr_col['status']      = $col->status;

        $arr_col['erros_coleta']  = $erros_coleta;
        $arr_col['erros_entrega'] = $erros_entrega;

        return $arr_col;
    }


    public function Local_RetornarMsgWisdomUser($user_id)
    {
        $retorno = array();
        $dados = array();
        $continuar = true;

        $timezone_app = date_default_timezone_get();
        $hoje = Carbon::now($timezone_app)->format('Y-m-d');

        if (rgIgualZeroNull($user_id)) {
            // Pegamos o ID do usuário autenticado
            $user_id = auth()->user()->id;
        }

        // Verificamos se existem mensagens na tabela
        $wis_count = DB::table('wisdom')->count();

        if ($wis_count <= 0) {
            $continuar = false;
            $dados['texto'] = 'Hoje... não tenho nenhuma mensagem para você. :(';
            $dados['fonte'] = '';
        }

        if ($continuar) {

            // Verificar mensagem do dia para o usuário
            // Verificamos se já existe uma mensagem para o usuário hoje
            $wisdom = DB::table('wisdom_user as wu')
                ->join('wisdom as w', 'w.id', '=', 'wu.wisdom_id')
                ->select('w.texto', 'w.fonte', 'wu.created_at')
                ->where('wu.user_id', '=', $user_id)
                ->where('wu.created_at', '>=', $hoje . ' 00:00:00')
                ->where('wu.created_at', '<=', $hoje . ' 23:59:59')
                ->first();

            if (!empty($wisdom)) {
                $continuar = false;
                $dados['texto'] = $wisdom->texto;
                $dados['fonte'] = $wisdom->fonte;
            }
        }

        if ($continuar) {

            // Verificamos a quantidade de mensagens recebidas pelo usuário
            // para saber se é a primeira mensagem... ou se ele já recebeu todas
            $qtde_msg_user = DB::table('wisdom_user as wu')
                ->where('wu.user_id', '=', $user_id)
                ->count();

            if ($qtde_msg_user == 0) {

                // É a primeira mensagem que o usuário vai receber, então... 
                // Selecionaremos a 1a. mensagem da tabela que deveria 
                // ser uma mensagem de boas-vindas.
                $continuar = false;

                $welcome = DB::table('wisdom')
                    ->select('id', 'texto', 'fonte')
                    ->orderBy('id', 'asc')
                    ->first();

                $dados['texto'] = $welcome->texto;
                $dados['fonte'] = $welcome->fonte;

                // Gravamos a mensagem escolhida na tabela 
                $wisdow_user = new WisdomUser();
                $wisdow_user['user_id']   = $user_id;
                $wisdow_user['wisdom_id'] = $welcome->id;
                $wisdow_user->save();
            }
        }

        if ($continuar) {

            // Mensagens que o usuário ainda não recebeu            
            $wisdom = DB::table('wisdom as w')
                ->whereNotExists(function ($query) use ($user_id) {
                    $query->select(DB::raw(1))
                        ->from('wisdom_user as wu')
                        ->where('wu.user_id', '=', $user_id)
                        ->whereColumn('wu.wisdom_id', '=', 'w.id');
                })
                ->orderBy('w.id', 'asc')
                ->get();

            // Se já recebeu todas as mensagens, retorna uma aleatória para o dia.
            if (count($wisdom) <= 0) {
                $wisdom = DB::table('wisdom')->get();
            }

            // Descontamos 1 porque o índice do array inicia em ZERO
            $maximo = (count($wisdom) - 1);

            // Define o índice aleatório para escolher a mensagem do array
            $idx = rand(0, $maximo);

            $dados['texto'] = $wisdom[$idx]->texto;
            $dados['fonte'] = $wisdom[$idx]->fonte;

            // Gravamos a mensagem que escolhida na tabela 
            $wisdow_user = new WisdomUser();
            $wisdow_user['user_id']   = $user_id;
            $wisdow_user['wisdom_id'] = $wisdom[$idx]->id;
            $wisdow_user->save();
        }

        $retorno['dados'] = $dados;

        return $retorno;
    }

    public function Local_RetornarResumoFrotaHome()
    {
        $dados = array();
        $retorno = array();

        //Total da frota
        $total = DB::table('veiculo')
            ->select('veiculo.placa')
            ->join('tipo_veiculo', 'tipo_veiculo.codigo', '=', 'veiculo.cod_tipo_veiculo')
            ->where('veiculo.ativo', '=', 'S')
            //Consideramos apenas veículos: "M" => Monobloco ou "R" => Carreta/Reboque
            ->whereIn('tipo_veiculo.classe', ['M', 'R'])
            ->count();

        //Veículos ocupados
        $ocupados = DB::table('veiculo')
            ->select('veiculo.placa')
            ->join('tipo_veiculo', 'tipo_veiculo.codigo', '=', 'veiculo.cod_tipo_veiculo')
            ->where('veiculo.ativo', '=', 'S')
            //Consideramos apenas veículos: "M" => Monobloco ou "R" => Carreta/Reboque
            ->whereIn('tipo_veiculo.classe', ['M', 'R'])
            ->whereRaw(DB::raw('
                (select count(coleta.id)
                 from coleta
                 where ((coleta.placa_coleta = veiculo.placa) OR (coleta.placa_entrega = veiculo.placa))
                 and   (not coleta.status IN ("CN", "ER"))
                 and   ((coleta.carga_pavilhao != "S") OR (coleta.carga_pavilhao IS NULL))
                ) > 0'))
            ->count();

        //Veículos ociosos
        $ociosos = $total - $ocupados;

        //Percentuais
        if ($total > 0) {
            $perc_ocupados = round(($ocupados / $total) * 100, 0);
        } else {
            $perc_ocupados = '0';
        }

        if ($total > 0) {
            $perc_ociosos = round(($ociosos / $total) * 100, 0);
        } else {
            $perc_ociosos = '0';
        }

        $grafico = array(
            [
                'label' => 'Ocupados',
                'counts' => $ocupados,
                'color' => 'danger'
            ],
            [
                'label' => 'Ociosos',
                'counts' => $ociosos,
                'color' => 'warning'
            ]
        );

        $dados['grafico'] = $grafico;
        $dados['percentuais'] = [$perc_ocupados, $perc_ociosos];
        $dados['total'] = $total;

        $retorno['dados'] = $dados;

        return $retorno;
    }


    public function Local_RetornarResumoColetasHome()
    {
        $dados = array();

        $timezone_app = date_default_timezone_get();
        $hoje = Carbon::now($timezone_app)->format('Y-m-d');

        // Contar solicitações NOVAS (que ainda não foram autorizdas)... com data 
        // prevista de coleta ATÉ HOJE  =>  $coletas
        $coletas = DB::table('coleta AS col')
            ->where('col.dt_prev_coleta', '<=', $hoje)
            ->where('col.status', '=', 'C0')

            // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
            // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
            //
            ->where(function ($query) {
                $query->where('col.coleta_fixa', '!=', 'C')
                    ->orWhere(function ($query1) {
                        $query1->where('col.coleta_fixa', '=', 'C')
                            ->where(function ($query2) {
                                $query2->whereNull('col.solic_origem_id')
                                    ->orWhere('col.solic_origem_id', '=', '0');
                            });
                    });
            })
            ->count();

        $novas = $coletas;

        // Contar solicitações ABERTAS ou EM ANDAMENTO (com coleta autorizada e que 
        // ainda não foram finalizadas)... com data prevista de coleta ATÉ HOJE  =>  $coletas
        $coletas = DB::table('coleta AS col')
            ->where('col.dt_prev_coleta', '<=', $hoje)
            ->whereNotIn('col.status', ['C0', 'CN', 'EN', 'EP', 'ER'])

            // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
            // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
            //
            ->where(function ($query) {
                $query->where('col.coleta_fixa', '!=', 'C')
                    ->orWhere(function ($query1) {
                        $query1->where('col.coleta_fixa', '=', 'C')
                            ->where(function ($query2) {
                                $query2->whereNull('col.solic_origem_id')
                                    ->orWhere('col.solic_origem_id', '=', '0');
                            });
                    });
            })
            ->count();

        $abertas = $coletas;

        // Contar solicitações FINALIZADAS com data de ENTREGA no dia de hoje  =>  $coletas
        // $coletas = DB::table('coleta AS col')

        //     // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
        //     // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
        //     //
        //     ->where(function ($query) {
        //         $query->where('col.coleta_fixa', '!=', 'C')
        //             ->orWhere(function ($query1) {
        //                 $query1->where('col.coleta_fixa', '=', 'C')
        //                     ->where(function ($query2) {
        //                         $query2->whereNull('col.solic_origem_id')
        //                             ->orWhere('col.solic_origem_id', '=', '0');
        //                     });
        //             });
        //     })

        //     ->where(function ($query) use ($hoje) {
        //         $query->where(function ($query1) use ($hoje) {
        //             $query1->where('col.dt_prev_coleta', '=', $hoje)
        //                 ->where('col.status', '=', 'CN');
        //         })->orWhere(function ($query2) use ($hoje) {
        //             $query2->where('col.dt_efet_entrega', '=', $hoje)
        //                 ->where(function ($query3) {
        //                     $query3->where('col.status', '=', 'ER')
        //                         ->orWhere(function ($query4) {
        //                             $query4->whereIn('col.status', ['EN', 'EP'])
        //                                 ->where('col.reentrega_gerada', '=', 'S');
        //                         });
        //                 });
        //         });
        //     })
        //     ->count();

        // $finalizadas = $coletas;

        // Contar solicitações FINALIZADAS com data de ENTREGA no dia de hoje  =>  $coletas        

        // Consideramos as solicitações DIÁRIAS, CONTRATOS, MULTI-DESTINOS e AUXILIARES.
        // Desconsideramos as COMANDAS (col.coleta_fixa = 'C'  E  solic_origem_id <> zero/null)
        $naoComanda = function ($query) {
            $query->where('col.coleta_fixa', '!=', 'C')
                ->orWhere(function ($query1) {
                    $query1->where('col.coleta_fixa', '=', 'C')
                        ->where(function ($query2) {
                            $query2->whereNull('col.solic_origem_id')
                                ->orWhere('col.solic_origem_id', '=', '0');
                        });
                });
        };

        // Canceladas no dia
        $parte1 = DB::table('coleta AS col')
            ->select('col.id')
            ->where($naoComanda)
            ->where('col.dt_prev_coleta', '=', $hoje)
            ->where('col.status', '=', 'CN');

        // Entregues/encerradas no dia
        $parte2 = DB::table('coleta AS col')
            ->select('col.id')
            ->where($naoComanda)
            ->where('col.dt_efet_entrega', '=', $hoje)
            ->where(function ($query) {
                $query->where('col.status', '=', 'ER')
                    ->orWhere(function ($query1) {
                        $query1->whereIn('col.status', ['EN', 'EP'])
                            ->where('col.reentrega_gerada', '=', 'S');
                    });
            });

        $finalizadas = $parte1->union($parte2)->get()->count();

        // Percentuais
        $total = $novas + $abertas + $finalizadas;

        if ($total == 0) {
            $perc_finalizadas = 0;
        } else {
            $perc_finalizadas = round(($finalizadas / $total) * 100, 0);
        }

        $meta = array(
            [
                'Novas'       => $novas,
                'Abertas'     => $abertas,
                'Finalizadas' => $finalizadas
            ]
        );

        $dados['dados']['meta']        = $meta;
        $dados['dados']['total']       = $total;
        $dados['dados']['percentuais'] = [$perc_finalizadas];

        return $dados;
    }

    public function Local_RetornarResumoKmTempoHome()
    {
        $timezone_app = date_default_timezone_get();

        // Estamos pegando o primeiro dia do mês atual e descontando 1 dia, para obter o mês anterior.
        // Utilizando "-1 month" na função strtotime, estava descontando de forma errada. Ex: Dia 31/07/2020.
        $inicio_data_atual = Carbon::now($timezone_app)->startOfMonth()->format('Y-m-d');
        $data_ant = strtotime('-1 day', strtotime($inicio_data_atual));
        $mes_ant  = date('m', $data_ant);
        $ano_ant  = date('Y', $data_ant);

        $data_atual = Carbon::now($timezone_app)->format('Y-m-d');
        $mes_atu    = date('m', strtotime($data_atual));
        $ano_atu    = date('Y', strtotime($data_atual));

        // Datas para o índice
        $dt_inicio = Carbon::create($ano_ant, $mes_ant, 1)->format('Y-m-d');
        $dt_fim    = Carbon::create($ano_atu, $mes_atu, 1)->addMonth()->format('Y-m-d');

        // Defina a localidade (locale) corretamente para o português brasileiro
        setlocale(LC_TIME, 'pt_BR.UTF-8');  // Essa linha é importante para garantir a tradução correta.

        $titulo = ucfirst(Carbon::now($timezone_app)->locale('pt_BR')->isoFormat('MMMM YYYY')); // Mês e Ano

        $dados = array();

        $dados['qtde_ant']  = 0;
        $dados['km_ant']    = 0;
        $dados['tempo_ant'] = '';
        $dados['tempo_ant_segundos'] = 0; //Utilizado para dar %

        $dados['qtde_atu']  = 0;
        $dados['km_atu']    = 0;
        $dados['tempo_atu'] = '';
        $dados['tempo_atu_segundos'] = 0; //Utilizado para dar %

        $var_qtde  = 0;
        $var_km    = 0;
        $var_tempo = 0;

        $select = '
            SELECT 
                YEAR(col.dt_prev_coleta) AS ano,
                MONTH(col.dt_prev_coleta) AS mes,
                COUNT(col.id) AS qtde_solic,
                SUM(col.distancia_total) AS km_total,
                SUM(TIME_TO_SEC(col.tempo_coleta)) + SUM(TIME_TO_SEC(col.tempo_entrega)) AS tempo_total
            FROM coleta col
            WHERE col.dt_prev_coleta >= ?
            AND col.dt_prev_coleta < ?
            AND (col.status IN ("EN", "EP", "ER") 
                OR (col.status = "CN" AND col.mot_nao_coleta = "01"))
            AND (col.coleta_fixa <> "C" 
                OR (col.coleta_fixa = "C" AND IFNULL(col.solic_origem_id, 0) <> 0))
            GROUP BY ano, mes
        ';

        $coletas = DB::select($select, [$dt_inicio, $dt_fim]);   

        if (count($coletas) > 0) {
            foreach ($coletas as $col) {
                if (($col->mes == $mes_ant) && ($col->ano == $ano_ant)) {
                    $dados['qtde_ant']  = $col->qtde_solic;
                    $dados['km_ant']    = $col->km_total;
                    $dados['tempo_ant'] = rgSecondsToTime($col->tempo_total);

                    if (rgDifZeroNull($col->tempo_total)) {
                        $dados['tempo_ant_segundos'] = $col->tempo_total;
                    }
                } else {
                    $dados['qtde_atu']  = $col->qtde_solic;
                    $dados['km_atu']    = $col->km_total;
                    $dados['tempo_atu'] = rgSecondsToTime($col->tempo_total);

                    if (rgDifZeroNull($col->tempo_total)) {
                        $dados['tempo_atu_segundos'] = $col->tempo_total;
                    }
                }
            }
        }

        // Calcular percentuais
        if ($dados['qtde_ant'] > 0) {
            $var_qtde = round(($dados['qtde_atu'] / $dados['qtde_ant'] * 100) - 100, 0);
        } else {
            if ($dados['qtde_atu'] > 0) {
                $var_qtde = 100;
            }
        }

        if ($dados['km_ant'] > 0) {
            $var_km = round(($dados['km_atu'] / $dados['km_ant'] * 100) - 100, 0);
        } else {
            if ($dados['km_atu'] > 0) {
                $var_km = 100;
            }
        }

        if (rgDifZeroNull($dados['tempo_ant_segundos'])) {
            $var_tempo = round(($dados['tempo_atu_segundos'] / $dados['tempo_ant_segundos'] * 100) - 100, 0);
        } else {
            if (rgDifZeroNull($dados['tempo_atu_segundos'])) {
                $var_tempo = 100;
            }
        }

        $retorno['dados']['titulo'] = $titulo;
        $retorno['dados']['progresso'] = array(
            [
                'id' => 0,
                'nome' => 'Solicitações',
                'percentual' => $var_qtde,
                'resultadoAtual' => $dados['qtde_atu'],
                'resultadoAnt' => $dados['qtde_ant']
            ],
            [
                'id' => 1,
                'nome' => 'Km Percorridos',
                'percentual' => $var_km,
                'resultadoAtual' => rgFormataDistancia($dados['km_atu']),
                'resultadoAnt' => rgFormataDistancia($dados['km_ant'])
            ],
            [
                'id' => 2,
                'nome' => 'Tempo Gasto',
                'percentual' => $var_tempo,
                'resultadoAtual' => rgRetornaFormataTempoExt($dados['tempo_atu']),
                'resultadoAnt' => rgRetornaFormataTempoExt($dados['tempo_ant'])
            ]
        );

        return $retorno;
    }



    public function Local_RetornarColetasEmissaoNotas()
    {

        $dados = array();

        $timezone_app = date_default_timezone_get();
        $data_atual_serv = Carbon::now($timezone_app)->format('Y-m-d');

        $coletas =  DB::table('coleta as col')
            ->select(
                'col.id as coleta_id',
                'col.numero',
                'col.cod_cliente',
                'col.cod_loc_coleta',
                'col.cod_loc_entrega',
                'col.placa_coleta',
                'col.dt_efet_coleta',
                'col.hr_sai_coleta',
                'col.status',
                'e.nome as nome_empresa',
                'e.cor_fonte',
                'cli.nome as nome_cliente',
                'lc.nome as local_coleta',
                'le.nome as local_entrega',
                'tipo_veiculo.descricao as descricao_tipo_veiculo'
            )

            ->join('empresa as e', 'e.codigo', '=', 'col.empresa')
            ->join('cliente as cli', function ($join) {
                $join->on('cli.codigo', '=', 'col.cod_cliente')
                    ->on('cli.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as lc', function ($join) {
                $join->on('lc.codigo', '=', 'col.cod_loc_coleta')
                    ->on('lc.empresa', '=', 'col.empresa');
            })
            ->leftjoin('cliente as le', function ($join) {
                $join->on('le.codigo', '=', 'col.cod_loc_entrega')
                    ->on('le.empresa', '=', 'col.empresa');
            })
            ->leftJoin('veiculo', 'veiculo.placa', '=', 'col.placa_coleta')
            ->leftJoin('tipo_veiculo', 'tipo_veiculo.codigo', '=', 'veiculo.cod_tipo_veiculo')

            // Não pegamos solicitações com DATA DE COLETA futura
            ->where('col.dt_prev_coleta', '<=', $data_atual_serv)

            // Consideramos coletas realizadas ('CR') ou não-realizadas ('CN') com deslocamento (motivo = '01)
            ->where(function ($query) {
                $query->where('col.status', '=', 'CR')
                    ->orWhere(function ($query1) {
                        $query1->where('col.status', '=', 'CN')
                            ->where('col.mot_nao_coleta', '=', '01');
                    });
            })

            // CONSIDERAMOS as solicitações: 
            //  
            // - DIÁRIAS: coleta_fixa = 'D'
            // - MULTI-DESTINOS (origem): coleta_fixa = 'M' e solic_origem_id == zero/null
            // 
            // DESCONSIDERAMOS as solicitações:
            //
            //  - CONTRATO:  coleta_fixa = 'C'
            //  - COMANDAS:  coleta_fixa = 'C' e solic_origem_id <> zero/null
            //  - SOLICITAÇÕES AUXILIARES:  coleta_fixa = 'M' e solic_origem_id <> zero/null
            //
            ->where(function ($query) {
                $query->where('col.coleta_fixa', '=', 'D')
                    ->orWhere(function ($query1) {
                        $query1->where('col.coleta_fixa', '=', 'M')
                            ->where(function ($query2) {
                                $query2->whereNull('col.solic_origem_id')
                                    ->orWhere('col.solic_origem_id', '=', '0');
                            });
                    });
            })

            // Quando definido que precisa receber NF de frete junto com as notas 
            ->where('col.receber_nf_frete', '=', 'S')

            // Somente se tiver notas com fins comerciais na coleta
            ->where('col.nfs_comerciais', '=', 'S')

            // Desconsideramos aquelas com NF já emitida ou que NÃO serão cobradas
            ->where(function ($query) {
                $query->whereNull('col.nf_frete')
                    ->orWhere('col.nf_frete', '=', '0');
            })

            // Desconsideramos aquelas com NF já emitida ou que NÃO serão cobradas
            ->where(function ($query) {
                $query->whereNull('col.sit_nf_frete')
                    ->orWhere('col.sit_nf_frete', '=', 'N');
            })

            ->get();

        if (count($coletas) > 0) {

            $idx = 0;

            foreach ($coletas as $col) {

                // Montar retorno => $dados 
                $dados[$idx]['coleta_id'] = $col->coleta_id;
                $dados[$idx]['numero']    = $col->numero;

                $dados[$idx]['nome_empresa'] = $col->nome_empresa;
                $dados[$idx]['cor_fonte']    = $col->cor_fonte;

                $dados[$idx]['nome_cliente'] = $col->nome_cliente;
                $dados[$idx]['cod_cliente']  = $col->cod_cliente;

                $dados[$idx]['local_coleta']   = $col->local_coleta;
                $dados[$idx]['cod_loc_coleta'] = $col->cod_loc_coleta;

                $dados[$idx]['local_entrega']   = $col->local_entrega;
                $dados[$idx]['cod_loc_entrega'] = $col->cod_loc_entrega;

                $dados[$idx]['placa_coleta']           = $col->placa_coleta;
                $dados[$idx]['descricao_tipo_veiculo'] = $col->descricao_tipo_veiculo;

                $dados[$idx]['dt_efet_coleta'] = $col->dt_efet_coleta;
                $dados[$idx]['hr_sai_coleta']  = $col->hr_sai_coleta;

                $dados[$idx]['status'] = $col->status;

                $idx++;
            }
        }

        $retorno['dados'] = $dados;

        return $retorno;
    }

    public function Local_RetornarMotoristasDisponiveis()
    {
        $retorno = array();

        $motoristas = DB::table('motorista')
            ->select(DB::Raw('nome, logado, time(dt_logado) as dt_logado'))
            ->where('logado', '=', 'S')
            ->orderBy('dt_logado', 'asc')
            ->orderBy('nome', 'asc')
            ->get();

        $retorno['dados'] = $motoristas;

        return $retorno;
    }


    public function Local_RetornarColetasMultiDestinosRealizadas($coleta_id = null)
    {

        $dados = array();
        $qtde_solic_distrib = 0;

        $timezone_app = date_default_timezone_get();
        $data_atual_serv = Carbon::now($timezone_app)->format('Y-m-d');

        // Selecionar coletas multi-destinos realizadas
        // Verificar as coletas Multi-destinos realizadas =>  (coleta_fixa = "M") 
        $coletas =  DB::table('coleta as col')
            ->select(
                'col.empresa',
                'emp.sigla',
                'emp.nome as nome_empresa',
                'emp.cor_fonte',
                'emp.cor_fundo',
                'col.id',
                'col.numero',
                'col.placa_coleta',
                'col.dt_efet_coleta',
                'col.hr_sai_coleta',

                'dt_prev_entrega',
                'hr_prev_entrega',
                'entrega_urgente',
                'col.status',

                'cli.codigo AS cod_cliente',
                'cli.nome AS nome_cliente',

                'lc.codigo AS cod_local_coleta',
                'lc.nome AS local_coleta'
            )

            ->Join('empresa as emp', 'emp.codigo', '=', 'col.empresa')
            ->leftjoin('cliente as cli', function ($join) {
                $join->on('cli.empresa', '=', 'col.empresa')
                    ->on('cli.codigo', '=', 'col.cod_cliente');
            })
            ->leftjoin('cliente as lc', function ($join) {
                $join->on('lc.empresa', '=', 'col.empresa')
                    ->on('lc.codigo', '=', 'col.cod_loc_coleta');
            })

            // Não pegamos solicitações com DATA DE COLETA futura
            ->where('col.dt_prev_coleta', '<=', $data_atual_serv)

            // Consideramos apenas 'CR' - Coleta realizada independente de onde está a carga.
            // Pode estar com com o veículo ou foi descarregada no pavilhão.
            ->where('col.status', '=', 'CR')

            // Tem que ser a solicitação Multi-destinos principal
            ->where('col.coleta_fixa', '=', 'M')
            ->whereNull('col.solic_origem_id')

            // Consideramos apenas solicitações com notas fiscais.
            ->whereRaw('( ( SELECT count(nf.id)' .
                ' FROM coleta_nf nf' .
                ' WHERE nf.coleta_id = col.id ) > 0 )')

            // Filtra para retornar apenas uma coleta se passado o parametro    
            ->where(function ($query) use ($coleta_id) {
                if ($coleta_id <> null) {
                    $query->where('col.id', '=', $coleta_id);
                }
            })

            ->orderBy('col.dt_prev_coleta', 'asc')
            ->orderBy('hr_prev_coleta', 'asc')
            ->get();


        if (count($coletas) > 0) {

            $idx = 0;

            foreach ($coletas as $col) {

                // Apenas as notas fiscais que ainda NÃO foram distribuidas
                // Selecionar registros da tabela COLETA_NF => $notas:
                $notas = DB::table('coleta_nf as nf')
                    ->select(
                        'nf.*',
                        'le.codigo AS cod_loc_entrega',
                        'le.nome AS local_entrega',
                        'le.cpf_cnpj',
                        'sd.dt_prev_entrega',
                        'sd.hr_prev_entrega',
                        'sd.entrega_urgente'
                    )

                    ->leftjoin('coleta as sd', 'sd.id', '=', 'nf.solic_destino_id')
                    ->leftjoin('cliente as le', function ($join) {
                        $join->on('le.empresa', '=', 'sd.empresa')
                            ->on('le.codigo', '=', 'sd.cod_loc_entrega');
                    })

                    ->where('nf.coleta_id', '=', $col->id)
                    ->orderBy('nf.numero', 'asc')
                    ->get();

                if (count($notas) > 0) {

                    $dados[$idx]['coleta_id']       = $col->id;
                    $dados[$idx]['numero']          = $col->numero;

                    $dados[$idx]['empresa']         = $col->empresa;
                    $dados[$idx]['sigla']           = $col->sigla;
                    $dados[$idx]['nome_empresa']    = $col->nome_empresa;
                    $dados[$idx]['cor_fonte']       = $col->cor_fonte;
                    $dados[$idx]['cor_fundo']       = $col->cor_fundo;

                    $dados[$idx]['nome_cliente']    = $col->nome_cliente;
                    $dados[$idx]['local_coleta']    = $col->local_coleta;

                    $dados[$idx]['placa_coleta']    = $col->placa_coleta;
                    $dados[$idx]['dt_efet_coleta']  = $col->dt_efet_coleta;
                    $dados[$idx]['hr_sai_coleta']   = $col->hr_sai_coleta;

                    $dados[$idx]['dt_prev_entrega'] = $col->dt_prev_entrega;
                    $dados[$idx]['hr_prev_entrega'] = $col->hr_prev_entrega;
                    $dados[$idx]['entrega_urgente'] = $col->entrega_urgente;

                    $api = new ApiColeta();
                    $dados[$idx]['status']     = $api->RetornarDescrStatusColeta($col->status);

                    $dados[$idx]['qtde_notas'] = count($notas);

                    $ind = 0;
                    $arr_notas = array();
                    $qtde_notas_distrib = 0;

                    foreach ($notas as $nf) {

                        if (rgIgualZeroNull($nf->solic_destino_id)) {
                            $qtde_notas_distrib++;
                        }

                        $arr_notas[$ind]['coleta_nf_id'] = $nf->id;
                        $arr_notas[$ind]['coleta_id']    = $nf->coleta_id;
                        $arr_notas[$ind]['cod_barras']   = $nf->cod_barras;
                        $arr_notas[$ind]['numero']       = $nf->numero;
                        $arr_notas[$ind]['serie']        = $nf->serie;
                        $arr_notas[$ind]['volumes']      = $nf->volumes;
                        $arr_notas[$ind]['valor']        = $nf->valor;
                        $arr_notas[$ind]['cod_loc_entrega']  = $nf->cod_loc_entrega;
                        $arr_notas[$ind]['local_entrega']    = $nf->local_entrega;
                        $arr_notas[$ind]['cpf_cnpj']         = $nf->cpf_cnpj;
                        $arr_notas[$ind]['dt_prev_entrega']  = $nf->dt_prev_entrega;
                        $arr_notas[$ind]['hr_prev_entrega']  = $nf->hr_prev_entrega;
                        $arr_notas[$ind]['entrega_urgente']  = $nf->entrega_urgente;
                        $arr_notas[$ind]['solic_destino_id'] = $nf->solic_destino_id;

                        $ind++;
                    }

                    if ($qtde_notas_distrib > 0) {
                        $qtde_solic_distrib++;
                    }

                    // Adicionamos a quantidade de notas a serem distribuidadas
                    $dados[$idx]['qtde_notas_distrib'] = $qtde_notas_distrib;

                    // Adicionamos o array de notas da coleta com notas distribuida/a distribuir
                    $dados[$idx]['notas'] = $arr_notas;

                    $idx++;
                }
            }
        }

        $retorno['dados']['qtde_solic_distrib'] = $qtde_solic_distrib;
        $retorno['dados']['coletas'] = $dados;

        return $retorno;
    }


    public function Local_RetornarEntregasNaoRealizadasReentrega($coleta_id = null)
    {

        $dados = array();
        $qtde_solic_reentrega = 0;

        // Solicitações com entrega não realizada        
        $coletas =  DB::table('coleta as col')
            ->select(
                'col.empresa',
                'emp.sigla',
                'emp.nome as nome_empresa',
                'emp.cor_fonte',
                'emp.cor_fundo',
                'col.id',
                'col.numero',
                'col.coleta_fixa',
                'col.status',
                'col.aceitar_foto_rom',
                'col.placa_coleta',
                'col.dt_efet_coleta',
                'col.hr_sai_coleta',
                'col.placa_entrega',
                'col.dt_efet_entrega',
                'col.hr_sai_entrega',
                'col.mot_nao_entrega',
                'col.obs_nao_entrega',
                'col.entrega_urgente',
                'col.carga_pavilhao',
                'col.solicitante',
                'col.especie',
                'col.peso',
                'col.volumes',
                'col.comp_carga',
                'col.larg_carga',
                'col.alt_carga',
                'col.caract_coleta',
                'col.cod_tipo_veiculo',
                'col.sis_carga',
                'col.tipo_frete',

                'cli.codigo AS cod_cliente',
                'cli.nome AS nome_cliente',

                'lc.codigo AS cod_local_coleta',
                'lc.nome AS local_coleta',

                'le.codigo AS cod_local_entrega',
                'le.nome AS local_entrega'
            )

            ->Join('empresa as emp', 'emp.codigo', '=', 'col.empresa')
            ->leftjoin('cliente as cli', function ($join) {
                $join->on('cli.empresa', '=', 'col.empresa')
                    ->on('cli.codigo', '=', 'col.cod_cliente');
            })
            ->leftjoin('cliente as lc', function ($join) {
                $join->on('lc.empresa', '=', 'col.empresa')
                    ->on('lc.codigo', '=', 'col.cod_loc_coleta');
            })
            ->leftjoin('cliente as le', function ($join) {
                $join->on('le.empresa', '=', 'col.empresa')
                    ->on('le.codigo', '=', 'col.cod_loc_entrega');
            })

            // Consideramos apenas 'EN' - Entrega não realizada  e  'EP' - Entrega Parcial           
            ->whereIn('col.status', ['EN', 'EP'])

            // Somente solicitações 'D' - Diárias ou Auxiliares Multi-destinos
            ->where(function ($query) {
                $query->where('col.coleta_fixa', '=', 'D')
                    ->orWhere(function ($query1) {
                        $query1->where('col.coleta_fixa', '=', 'M')
                            ->whereNotNull('col.solic_origem_id');
                    });
            })

            // Com reentrega não gerada
            ->whereNull('col.reentrega_gerada')

            // Filtra para retornar apenas uma coleta se passado o parametro    
            ->where(function ($query) use ($coleta_id) {

                if (rgDifZeroNull($coleta_id)) {
                    $query->where('col.id', '=', $coleta_id);
                }
            })

            ->orderBy('col.dt_prev_coleta', 'asc')
            ->orderBy('col.hr_prev_coleta', 'asc')
            ->get();


        if (count($coletas) > 0) {

            $api = new ApiColeta();

            $idx = 0;

            foreach ($coletas as $col) {

                $dados[$idx]['coleta_id']       = $col->id;
                $dados[$idx]['numero']          = $col->numero;

                $dados[$idx]['empresa']         = $col->empresa;
                $dados[$idx]['sigla']           = $col->sigla;
                $dados[$idx]['nome_empresa']    = $col->nome_empresa;
                $dados[$idx]['cor_fonte']       = $col->cor_fonte;
                $dados[$idx]['cor_fundo']       = $col->cor_fundo;

                $dados[$idx]['nome_cliente']    = $col->nome_cliente;
                $dados[$idx]['local_coleta']    = $col->local_coleta;
                $dados[$idx]['local_entrega']   = $col->local_entrega;

                $dados[$idx]['placa_coleta']    = $col->placa_coleta;
                $dados[$idx]['dt_efet_coleta']  = $col->dt_efet_coleta;
                $dados[$idx]['hr_sai_coleta']   = $col->hr_sai_coleta;

                $dados[$idx]['placa_entrega']   = $col->placa_entrega;
                $dados[$idx]['dt_efet_entrega'] = $col->dt_efet_entrega;
                $dados[$idx]['hr_sai_entrega']  = $col->hr_sai_entrega;
                $dados[$idx]['entrega_urgente'] = $col->entrega_urgente;

                $dados[$idx]['carga_pavilhao']  = $col->carga_pavilhao;
                $dados[$idx]['mot_nao_entrega'] = rgGetMsgMotNaoEntregaColeta($col->mot_nao_entrega);
                $dados[$idx]['obs_nao_entrega'] = $col->obs_nao_entrega;

                $dados[$idx]['solicitante']      = $col->solicitante;
                $dados[$idx]['especie']          = $col->especie;
                $dados[$idx]['peso']             = $col->peso;
                $dados[$idx]['volumes']          = $col->volumes;
                $dados[$idx]['comp_carga']       = $col->comp_carga;
                $dados[$idx]['larg_carga']       = $col->larg_carga;
                $dados[$idx]['alt_carga']        = $col->alt_carga;
                $dados[$idx]['caract_coleta']    = $col->caract_coleta;
                $dados[$idx]['cod_tipo_veiculo'] = $col->cod_tipo_veiculo;
                $dados[$idx]['sis_carga']        = $col->sis_carga;
                $dados[$idx]['tipo_frete']       = $col->tipo_frete;

                $dados[$idx]['aceitar_foto_rom'] = $col->aceitar_foto_rom;
                $dados[$idx]['coleta_fixa']      = $col->coleta_fixa;
                $dados[$idx]['status'] = $api->RetornarDescrStatusColeta($col->status);

                $qtde_notas = 0;
                $qtde_notas_reentrega = 0;

                // Aqui... o documento de carga é Nota Fiscal,  selecionamos todas as notas fiscais 
                // da solicitação. Na sequência... vamos considerar apenas as notas fiscais que ainda 
                // NÃO foram atribuidas a uma solicitação de REENTREGA / DEVOLUÇÃO
                $notas = DB::table('coleta_nf as nf')
                    ->select('nf.*', 'sd.reentrega')
                    ->leftjoin('coleta as sd', 'sd.id', '=', 'nf.solic_destino_id')
                    ->where('nf.coleta_id', '=', $col->id)
                    ->orderBy('nf.numero', 'asc')
                    ->get();

                if (count($notas) == 0) {

                    $dados[$idx]['qtde_notas']           = $qtde_notas;
                    $dados[$idx]['qtde_notas_reentrega'] = $qtde_notas_reentrega;
                    $dados[$idx]['notas'] = array();

                    $qtde_solic_reentrega++;
                } else {

                    $ind = 0;
                    $arr_notas = array();
                    $qtde_notas = count($notas);

                    foreach ($notas as $nf) {

                        // O teste abaixo funciona para contar as notas definidas como não entregues (mot_nao_entrega <> null) e 
                        // também para status = 'EN', porque neste caso, as notas não terão nenhuma resposta (img_recibo == null  E  mot_nao_entrega == null)                            
                        if (
                            rgIgualZeroNull($nf->solic_destino_id) && ($nf->substituida != 'S') && (rgDifTrimNull($nf->mot_nao_entrega) || (rgIgualTrimNull($nf->img_recibo) && rgIgualTrimNull($nf->mot_nao_entrega)))
                        ) {
                            $qtde_notas_reentrega++;
                        }

                        $arr_notas[$ind]['coleta_nf_id']     = $nf->id;
                        $arr_notas[$ind]['coleta_id']        = $nf->coleta_id;
                        $arr_notas[$ind]['cod_barras']       = $nf->cod_barras;
                        $arr_notas[$ind]['serie']            = $nf->serie;
                        $arr_notas[$ind]['numero']           = $nf->numero;
                        $arr_notas[$ind]['valor']            = $nf->valor;
                        $arr_notas[$ind]['volumes']          = $nf->volumes;
                        $arr_notas[$ind]['mot_nao_entrega']  = rgGetMsgMotNaoEntregaColetaNf($nf->mot_nao_entrega);
                        $arr_notas[$ind]['solic_destino_id'] = $nf->solic_destino_id;
                        $arr_notas[$ind]['substituida']      = $nf->substituida;
                        $arr_notas[$ind]['img_recibo']       = $nf->img_recibo;
                        $arr_notas[$ind]['reentrega']        = $nf->reentrega;
                        $arr_notas[$ind]['acao']             = null; // A ação será atribuida na interface

                        $ind++;
                    }

                    if ($qtde_notas_reentrega > 0) {
                        $qtde_solic_reentrega++;
                    }

                    $dados[$idx]['qtde_notas'] = $qtde_notas;
                    $dados[$idx]['qtde_notas_reentrega'] = $qtde_notas_reentrega;
                    $dados[$idx]['notas'] = $arr_notas;
                }

                $idx++;
            }
        }

        $retorno['dados']['qtde_solic_reentrega'] = $qtde_solic_reentrega;
        $retorno['dados']['coletas'] = $dados;

        return $retorno;
    }


    public function Local_RetornarTarefasHome()
    {
        $dados = array();
        $idx = 0;

        // Retorno de cadastros incompletos de clientes com coletas
        $cadastros = $this->Local_RetornarClientesColetasCadIncomp();

        if (empty($cadastros['dados'])) {
            $regs = 0;
        } else {
            $regs = count($cadastros['dados']);
        }

        $dados[$idx]['descricao'] = 'Corrigir cadastros';
        $dados[$idx]['regs']      = $regs;
        $dados[$idx]['icon']      = 'UsersIcon';
        $dados[$idx]['fundo']     = 'danger';
        $dados[$idx]['url']       = '/corrigir-cadastros';

        // Retorno das coletas com notas a emitir
        $notas = $this->Local_RetornarColetasEmissaoNotas();

        if (empty($notas['dados'])) {
            $regs = 0;
        } else {
            $regs = count($notas['dados']);
        }

        $idx++;

        $dados[$idx]['descricao'] = 'Emitir notas fiscais';
        $dados[$idx]['regs']      = $regs;
        $dados[$idx]['icon']      = 'FileTextIcon';
        $dados[$idx]['fundo']     = 'primary';
        $dados[$idx]['url']       = '/emitir-notas-fiscais';

        // Retorno das coletas multi-destinos que devem ser criadas solicitações de distribuição
        $coletas = $this->Local_RetornarColetasMultiDestinosRealizadas();

        if (empty($coletas['dados'])) {
            $regs = 0;
        } else {
            $regs = $coletas['dados']['qtde_solic_distrib'];
        }

        $idx++;

        $dados[$idx]['descricao'] = 'Distribuir entregas';
        $dados[$idx]['regs']      = $regs;
        $dados[$idx]['icon']      = 'ClipboardIcon';
        $dados[$idx]['fundo']     = 'warning';
        $dados[$idx]['url']       = '/distribuicao-entregas';

        // Retorno das coletas de reentrega ou devolução
        $reentregas = $this->Local_RetornarEntregasNaoRealizadasReentrega();

        if (empty($reentregas['dados'])) {
            $regs = 0;
        } else {
            $regs = $reentregas['dados']['qtde_solic_reentrega'];
        }

        $idx++;

        $dados[$idx]['descricao'] = 'Definir reentrega';
        $dados[$idx]['regs']      = $regs;
        $dados[$idx]['icon']      = 'ClipboardIcon';
        $dados[$idx]['fundo']     = 'success';
        $dados[$idx]['url']       = '/definir-reentrega';

        $retorno['dados'] = $dados;

        return $retorno;
    }
}
