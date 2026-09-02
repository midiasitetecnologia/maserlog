<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

// Declaracoes para o Baca que recalcula as Distancias
use App\ApiIntegracao;
use App\ApiUsoComum;
use App\ColetaPos;
use App\Coleta;
use DB;

class ApiUsoComumController extends Controller
{
    public function getDataAtual(Request $request)
    {
        $timezone_app = date_default_timezone_get();
        $dataAtual = Carbon::now($timezone_app)->format('Y-m-d');
        $dataHoraAtual = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

        return ['status' => true, 'dataAtual' => $dataAtual, 'dataHoraAtual' => $dataHoraAtual];
    }


    public function GetTeste(Request $request)
    {
        try {
            $api = new ApiUsoComum();
            $resultado = $api->Local_GetTeste($request);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function TestDrivingDistance(Request $request)
    {
        try {
            $latOrig = $request->get('latOrig');
            $lngOrig = $request->get('lngOrig');
            $latDest = $request->get('latDest');
            $lngDest = $request->get('lngDest');

            $apiService = $request->get('apiService');
            $apiKey     = $request->get('apiKey');

            $resultado = GetDrivingDistance($latOrig, $lngOrig, $latDest, $lngDest, $apiService, $apiKey);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    // -------------------------------------------------------------------
    //         INICIO DO BACA QUE RECALCULA AS DISTANCIAS
    // ---------------------------------------------------------------------

    // Baca que recalcula as distancias e tempos na coleta_Pos
    // Vamos deixar incorporado ao projeto, pois já tivemos que rodar por duas
    // vezes

    public function Baca_TotalizarDistanciaTempoSolic()
    {
        //Setamos o tempo máximo de execução para 15 minutos(900 segundos).
            //O tempo padrão de execução de um processo no PHP está definido no arquivo 02phpini.config (.ebextensions).
            ini_set('max_execution_time', 900);

        $coleta_aux = DB::table('coleta')
            ->where(function ($query) {
                $query->where('status', '=', 'ER')
                    ->orWhere(function ($query2) {
                        $query2->where('status', '=', 'CN')
                            ->where('mot_nao_coleta', '=', '01');
                    });
            })
            ->whereDate('data_cad', '>=', '2020-08-31')
            ->get();

        foreach ($coleta_aux as $col) {

            if ($coleta = Coleta::find($col->id)) {
                $this->TotalizarDistanciaTempoSolic($coleta);
            }
        }

        $resultado = 'Processo Finalizado com sucesso';

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function TotalizarDistanciaTempoSolic($coleta)
    {

        $continuar = true;

        $distancia_coleta  = 0;
        $tempo_coleta      = 0;

        $distancia_entrega = 0;
        $tempo_entrega     = 0;

        $distancia_total   = 0;
        $tempo_total       = 0;

        // Inicialmente consideramos que a COLETA terminou no mesmo dia em que começou
        $data_fim_coleta = $coleta->dt_efet_coleta;

        // A saída da coleta passou para o dia seguinte?
        if ($coleta->hr_sai_coleta < $coleta->hr_partida_coleta) {
            // Incrementamos 01 dia na data para passar para dia seguinte
            $data_fim_coleta = date('Y-m-d', strtotime('+1 days', strtotime($data_fim_coleta)));
        }

        // Para solicitações DIÁRIAS, COMANDAS e MULTI-DESTINOS AUXILIARES... o cálculo é o mesmo: 
        // acumulamos registros de COLETA_POS
        if (($coleta->coleta_fixa == 'D') || (rgDifZeroNull($coleta->solic_origem_id))) {

            // Solicitações DIÁRIAS, COMANDAS MULTI-DESTINOS AUXILIARES

            // Calculamos a distância de deslocamento (coleta e entrega)
            $dados_desloc = self::CalcDistanciaDeslocSolic($coleta->id);

            $distancia_coleta  = $dados_desloc['distancia_desloc_coleta'];
            $distancia_entrega = $dados_desloc['distancia_desloc_entrega'];

            // Calculamos o TEMPO DE COLETA somente se tiver valor nos campos envolvidos no cálculo. 
            // Assim... evitamos erros nas solicitações MULTI-DESTINOS AUXILIARES que NÃO tem horários 
            // de COLETA e outros casos onde esses valores não foram gravados (algum 'pau')
            if ((rgDifZeroNull($coleta->dt_efet_coleta)) && (rgDifZeroNull($coleta->hr_partida_coleta)) && (rgDifZeroNull($coleta->hr_sai_coleta))
            ) {

                // Calcula tempo da COLETA e soma ao tempo total
                $data_hora_ini = $coleta->dt_efet_coleta . ' ' . $coleta->hr_partida_coleta;
                $data_hora_ini = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_ini);

                $data_hora_fim = $data_fim_coleta . ' ' . $coleta->hr_sai_coleta;
                $data_hora_fim = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_fim);

                $tempo_coleta = (rgTimeToSeconds($data_hora_fim) - rgTimeToSeconds($data_hora_ini));
            }

            // Tem que usar o status passado como parâmetro do model, pois o status da leitura
            // da tabela de coletas é ('EN', 'EP', 'ER') ainda no banco. Neste momento estamos ainda fazendo a 
            // gravação. Ainda não foi alterado no banco
            if (in_array($coleta->status, ['EN', 'EP', 'ER'])) {
                // Teremos horários de ENTREGA somente quando a entrega foi realizada
                // Inicialmente consideramos que a ENTREGA terminou no mesmo dia em que começou
                $data_fim_entrega = $coleta->dt_efet_entrega;

                // A saída da entrega passou para o dia seguinte?
                if ($coleta->hr_sai_entrega < $coleta->hr_partida_entrega) {
                    // Incrementamos 01 dia na data para passar para dia seguinte
                    $data_fim_entrega = date('Y-m-d', strtotime('+1 days', strtotime($data_fim_entrega)));
                }

                // Calcula tempo da ENTREGA e soma ao tempo total
                $data_hora_ini = $coleta->dt_efet_entrega . ' ' . $coleta->hr_partida_entrega;
                $data_hora_ini = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_ini);

                $data_hora_fim = $data_fim_entrega . ' ' . $coleta->hr_sai_entrega;
                $data_hora_fim = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_fim);

                $tempo_entrega = (rgTimeToSeconds($data_hora_fim) - rgTimeToSeconds($data_hora_ini));
            }

            // Distância e tempo TOTAL
            $distancia_total = $distancia_coleta + $distancia_entrega;
            $tempo_total = $tempo_coleta + $tempo_entrega;
        } else {

            // O tempo da solicitação CONTRATO e MULTI-DESTINOS é o período de EXPEDIENTE.
            // O início é a hora de partida ao local de COLETA e o fim... é a hora de saída da entrega
            $data_hora_ini = $coleta->dt_efet_coleta . ' ' . $coleta->hr_partida_coleta;
            $data_hora_ini = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_ini);

            $data_hora_fim = $coleta->dt_efet_entrega . ' ' . $coleta->hr_sai_entrega;
            $data_hora_fim = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_fim);

            $tempo_total = (rgTimeToSeconds($data_hora_fim) - rgTimeToSeconds($data_hora_ini));

            // Quando é uma solicitação origem MULTI-DESTINOS... temos que somar também os KM percorridos 
            // para realizar a coleta... e que estão em COLETA_POS... vinculados a esta solicitação

            if ($coleta->coleta_fixa == 'M') {

                // Calcula tempo da COLETA porque a solicitação origem MULTI-DESTINOS 
                // tem tempo de COLETA registrado. Os tempos de ENTREGA estarão nas 
                // solicitações auxiliares
                $data_hora_ini = $coleta->dt_efet_coleta . ' ' . $coleta->hr_partida_coleta;
                $data_hora_ini = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_ini);

                $data_hora_fim = $data_fim_coleta . ' ' . $coleta->hr_sai_coleta;
                $data_hora_fim = Carbon::createFromFormat('Y-m-d H:i:s', $data_hora_fim);

                $tempo_coleta = (rgTimeToSeconds($data_hora_fim) - rgTimeToSeconds($data_hora_ini));

                // Calculamos a distância de deslocamento de coleta 
                $dados_desloc = $this->CalcDistanciaDeslocSolic($coleta->id);
                $distancia_coleta = $dados_desloc['distancia_desloc_coleta'];

                // Para a solicitação ORIGEM MULTI-DESTINOS não teremos KM de entrega. 
                // Os KM da entrega estão em COLETA_POS das solcitações AUXILIARES.
                //
                $distancia_entrega = 0;
            } else {
                // Selecionamos as comandas dos CONTRATOS da solicitação origem 
                // para acumular os KM da COLETA => $cmd_coletas
                //
                // Os registros com status "C3" tem os KM gastos na COLETA
                //
                // NÃO acumulamos o tempo de deslocamento para coleta. Quando precisar 
                // totalizar esse tempo para efeito de custo... é só acumular o tempo gravado 
                // em COLETA_POS... agrupando por placa motorista ou tipo de veículo
                // 
                $cmd_coletas = DB::table('coleta_pos as pos')
                    ->Join('coleta as cmd', 'cmd.id', '=', 'pos.coleta_id')
                    ->select(DB::raw('sum(pos.distancia) AS distancia'))
                    ->where('cmd.solic_origem_id', '=', $coleta->id)
                    ->where('pos.status', '=', 'C3')
                    ->first();

                if (rgNvl($cmd_coletas->distancia) > 0) {
                    $distancia_coleta = $cmd_coletas->distancia;
                }

                // Selecionamos as comandas dos CONTRATOS da solicitação origem 
                // para acumular os KM da COLETA => $cmd_coletas
                //
                // Os registros com status "E3" tem os KM gastos na ENTREGA
                //
                // NÃO acumulamos o tempo de deslocamento para coleta. Quando precisar 
                // totalizar esse tempo para efeito de custo... é só acumular o tempo gravado 
                // em COLETA_POS... agrupando por placa motorista ou tipo de veículo
                // 
                $cmd_entregas = DB::table('coleta_pos as pos')
                    ->Join('coleta as cmd', 'cmd.id', '=', 'pos.coleta_id')
                    ->select(DB::raw('sum(pos.distancia) AS distancia'))
                    ->where('cmd.solic_origem_id', '=', $coleta->id)
                    ->where('pos.status', '=', 'E3')
                    ->first();

                if (rgNvl($cmd_entregas->distancia) > 0) {
                    $distancia_entrega = $cmd_entregas->distancia;
                }
            }

            $distancia_total = $distancia_coleta + $distancia_entrega;
        }

        if ($continuar) {

            if (($distancia_total > 0) || ($tempo_total > 0)) {

                try {

                    // Esta alteração não é para disparar novos eventos no model da Coleta.
                    // Por isto usamos o update para atualizar a coleta.
                    $col_upd = Coleta::where('id', '=', $coleta->id)
                        ->update([
                            'distancia_coleta'  => $distancia_coleta,
                            'tempo_coleta'      => rgSecondsToTime($tempo_coleta),
                            'distancia_entrega' => $distancia_entrega,
                            'tempo_entrega'     => rgSecondsToTime($tempo_entrega),
                            'distancia_total'   => $distancia_total,
                            'tempo_total'       => rgSecondsToTime($tempo_total)
                        ]);
                } catch (\Exception $e) {
                    // Não faz nada - só para não dar erro
                }
            }
        }
    }


    static function CalcDistanciaDeslocSolic($coleta_id)
    {
        $distancia_coleta  = 0;
        $distancia_entrega = 0;

        $origem = array();
        $destino = array();

        // Selecionamos os registros de posição geográfica para eventos 'Deslocamento' e 'Chegada' 
        // da COLETA e da ENTREGA. É importante que seja na ordem em que ocorreram (id). 
        //
        // Para funcionar corretamente, os EVENTOS devem estar numa ordem lógica:
        //  => 'C2' antes de 'C3'  e  'E2' antes de 'E3.
        $coleta_pos = DB::table('coleta_pos as col_pos')
            ->select(
                'col_pos.id',
                'col_pos.geo_lat',
                'col_pos.geo_lng',
                'col_pos.status',
                'col_pos.created_at'
            )
            ->where('col_pos.coleta_id', '=', $coleta_id)
            ->whereIn('col_pos.status', ['C2', 'C3', 'E2', 'E3'])
            ->orderBy('col_pos.id', 'asc')
            ->get();

        if (count($coleta_pos) > 0) {

            $status_origem  = null;

            $geo_lat_origem = 0;
            $geo_lng_origem = 0;
            $dt_sai_origem  = null;

            $geo_lat_destino = 0;
            $geo_lng_destino = 0;
            $dt_cheg_destino = null;
            $id_reg_destino  = null;

            $idx = 0;

            foreach ($coleta_pos as $col_pos) {

                if ((rgDifZeroNull($col_pos->geo_lat)) && (rgDifZeroNull($col_pos->geo_lng))) {

                    if (($col_pos->status == 'C2') || ($col_pos->status == 'E2')) {
                        $status_origem  = $col_pos->status;
                        $geo_lat_origem = $col_pos->geo_lat;
                        $geo_lng_origem = $col_pos->geo_lng;
                        $dt_sai_origem  = $col_pos->created_at;
                    } else {

                        $geo_lat_destino = $col_pos->geo_lat;
                        $geo_lng_destino = $col_pos->geo_lng;
                        $dt_cheg_destino = $col_pos->created_at;
                        $id_reg_destino  = $col_pos->id;

                        // Testamos se a origem foi encontrada
                        // Quando não tem coodenadas de origem... descartamos a coordenada de destino. 
                        // Temos que ter sempre em pares.
                        if (($geo_lat_origem <> 0) && ($geo_lng_origem <> 0)) {
                            // Lista de coordenadas de 'origem' e 'destino' sincronizadas 
                            // (cada origem tem um destino correspondente)  
                            $origem[$idx]  =  [
                                'status'    => $status_origem,
                                'geo_lat'   => $geo_lat_origem,
                                'geo_lng'   => $geo_lng_origem,
                                'dt_saida'  => $dt_sai_origem
                            ];

                            $destino[$idx] =  [
                                'geo_lat'        => $geo_lat_destino,
                                'geo_lng'        => $geo_lng_destino,
                                'dt_chegada'     => $dt_cheg_destino,
                                'id_reg_destino' => $id_reg_destino
                            ];

                            $idx++;
                        }

                        // Depois que processamos os pares de coordenadas (origem e destino)... 
                        // zeramos as variáveis para não ficar com o conteúdo do registro anterior.
                        $status_origem  = null;
                        $geo_lat_origem = 0;
                        $geo_lng_origem = 0;
                        $dt_sai_origem  = null;

                        $geo_lat_destino = 0;
                        $geo_lng_destino = 0;
                        $dt_cheg_destino = null;
                        $id_reg_destino  = null;
                    }
                } else {
                    // Zeramos as coordenadas para não ficar com o conteúdo do registro anterior... 
                    // caso tenha coordenada de 'origem'... mas não tenha de 'destino'... por exemplo.
                    $status_origem  = null;
                    $geo_lat_origem = 0;
                    $geo_lng_origem = 0;
                    $dt_sai_origem  = null;

                    $geo_lat_destino = 0;
                    $geo_lng_destino = 0;
                    $dt_cheg_destino = null;
                    $id_reg_destino  = null;
                }
            }

            $idx = 0;

            // Para cada elemento do array $origem => $orig
            foreach ($origem as $orig) {
                // Pegamos as coordenadas do array de origem e o elemento correspondente do array de destino
                $status_origem  = $orig['status'];
                $geo_lat_origem = $orig['geo_lat'];
                $geo_lng_origem = $orig['geo_lng'];
                $dt_sai_origem  = $orig['dt_saida'];

                $geo_lat_destino = $destino[$idx]['geo_lat'];
                $geo_lng_destino = $destino[$idx]['geo_lng'];
                $dt_cheg_destino = $destino[$idx]['dt_chegada'];
                $id_reg_destino  = $destino[$idx]['id_reg_destino'];

                $api = new ApiIntegracao();
                $array_tempo_dist = $api->CalcularDistanciaOrigem_Destino(
                    $geo_lat_origem,
                    $geo_lng_origem,
                    $geo_lat_destino,
                    $geo_lng_destino
                );

                $distancia = $array_tempo_dist['distance'];
                $tempo = (rgTimeToSeconds($dt_cheg_destino) - rgTimeToSeconds($dt_sai_origem));

                // Gravamos a distância entre o ponto de partida ("C2" / "E2") 
                // E o ponto de chegada ("C3" /  "E3")
                ColetaPos::where('id', '=', $id_reg_destino)
                    ->update([
                        'distancia' => $distancia,
                        'tempo'     => rgSecondsToTime($tempo)
                    ]);

                if ($status_origem == 'C2') {
                    $distancia_coleta  = $distancia_coleta + $distancia;
                } else {
                    $distancia_entrega = $distancia_entrega + $distancia;
                }

                $idx++;
            }
        }

        $retorno['distancia_desloc_coleta']  = $distancia_coleta;
        $retorno['distancia_desloc_entrega'] = $distancia_entrega;

        return $retorno;
    }

    // -------------------------------------------------------------------
    //         FIM DO BACA QUE RECALCULA AS DISTANCIAS
    // -------------------------------------------------------------------


}
