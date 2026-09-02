<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class ColetasFixasAuto extends Model
{

    public function GerarColetasFixasAuto($apikey)
    {

        $continuar = true;
        $retorno = array();

        //Testamos se o site não foi colocado em manutenção
        $app = new ApiUsoComum();
        $retorno = $app->AplicacaoLiberada();

        if (($retorno['cod_retorno']) != 'Z998') {
            $continuar = false;
        }

        if ($continuar) {

            if (!rgGetApiKeyValido($apikey)) {
                $retorno['cod_retorno'] = 'A205';
                $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
            } else {

                // Ler registro de SYS_CFG  => $sys_cfg
                $sys_cfg = DB::table('sys_cfg')
                    ->select('gerar_coletas_fixas', 'dia_coletas_fixas')
                    ->first();

                if (empty($sys_cfg) == false) {

                    if ($sys_cfg->gerar_coletas_fixas == 'S') {

                        $this->InicProcColetasFixas($sys_cfg->dia_coletas_fixas);
                        $retorno['cod_retorno'] = 'Z100';
                        $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    }
                }
            }
        }

        return $retorno;
    }


    public function InicProcColetasFixas($dia_coletas_fixas)
    {
        //Inicializar variáveis       
        $timezone_app = date_default_timezone_get();

        $cont_fixas = 0;
        $cont_bloq  = 0;
        $global_cont_solic = 0;
        $global_cont_exist = 0;
        $global_cont_erros = 0;

        $global_array_log = array();

        //Dia atual                
        $data_coleta_fixa = Carbon::now($timezone_app)->format('Y-m-d');

        // Se for para gerar para o dia seguinte
        if ($dia_coletas_fixas == 'S') {
            $data_coleta_fixa = date('Y-m-d', strtotime('+1 days', strtotime($data_coleta_fixa)));
        }

        // Dia da semana: somamos +1 para resultar dias de 1 a 7
        $dia_semana = date('w', strtotime($data_coleta_fixa)) + 1;

        // Inserir um elemento no array do log:
        $ind_log = count($global_array_log);

        $global_array_log[$ind_log]['tipo']   = '0';
        $global_array_log[$ind_log]['msg']    = 'Início do Processamento';
        $global_array_log[$ind_log]['err']    = '';
        $global_array_log[$ind_log]['status'] = '0';
        $global_array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

        // Busca todas as coletas fixas com contrato ativo
        $coletas_fixas = DB::table('coleta_fixa as cf')
            ->select('cf.*')
            ->where('dt_ini', '<=', $data_coleta_fixa)
            ->where('dt_fim', '>=', $data_coleta_fixa)
            ->where('cont_cancel', '<>', 'S')
            ->get();

        // Pré-carrega TODOS os bloqueios das coletas encontradas (1 única query)
        $ids_coletas_fixas = $coletas_fixas->pluck('id');

        $bloqueios = DB::table('coleta_fixa_bloq')
            ->select('coleta_fixa_id', 'dt_ini', 'dt_fim')
            ->whereIn('coleta_fixa_id', $ids_coletas_fixas)
            ->where('dt_ini', '<=', $data_coleta_fixa)
            ->where('dt_fim', '>=', $data_coleta_fixa)
            ->get()
            ->keyBy('coleta_fixa_id'); // indexa pelo id para busca O(1)                    

        // Loop sem nenhuma query dentro
        foreach ($coletas_fixas as $regcolfix) {

            if (!$this->DiasSemana(
                $dia_semana,
                $regcolfix->segunda,
                $regcolfix->terca,
                $regcolfix->quarta,
                $regcolfix->quinta,
                $regcolfix->sexta,
                $regcolfix->sabado
            )) {
                continue;
            }

            // Busca em memória — sem query
            $coleta_fixa_bloq = $bloqueios->get($regcolfix->id);

            if (!empty($coleta_fixa_bloq)) {

                $global_array_log = $this->AdicionarLogDetailProcesso(
                    $global_array_log,
                    'E302',
                    $regcolfix->id,
                    $regcolfix->cod_cliente,
                    $regcolfix->cod_loc_coleta,
                    $regcolfix->cod_loc_entrega,
                    $coleta_fixa_bloq->dt_ini,
                    $coleta_fixa_bloq->dt_fim,
                    null
                );

                $cont_bloq++;
            } else {

                if ($regcolfix->tipo_coleta == 'D' || $regcolfix->tipo_coleta == 'M') {
                    $this->ProcColetaFixaDiaria(
                        $regcolfix,
                        $data_coleta_fixa,
                        $global_cont_exist,
                        $global_cont_solic,
                        $global_cont_erros,
                        $global_array_log
                    );
                } else {
                    $this->ProcColetaFixaContrato(
                        $regcolfix,
                        $data_coleta_fixa,
                        $global_cont_exist,
                        $global_cont_solic,
                        $global_cont_erros,
                        $global_array_log
                    );
                }
            }
        }

        $cont_fixas = count($coletas_fixas);

        $global_array_log = $this->AdicionarLogDetailFinal(
            $global_array_log,
            $cont_fixas,
            $global_cont_solic,
            $global_cont_exist,
            $cont_bloq,
            $global_cont_erros
        );

        $global_array_log = $this->AdicionarLogTrailler($global_array_log, $global_cont_erros);

        $this->GravarLog($global_array_log, 'gerar_coletas_fixas', 'Geração das Coletas Fixas');
    }

    public function DiasSemana($dia_semana, $segunda, $terca, $quarta, $quinta, $sexta, $sabado)
    {
        $retorno = false;

        if ($dia_semana == 2 && $segunda == 'S') {
            $retorno = true;
        } else if ($dia_semana == 3 && $terca == 'S') {
            $retorno = true;
        } else if ($dia_semana == 4 && $quarta == 'S') {
            $retorno = true;
        } else if ($dia_semana == 5 && $quinta == 'S') {
            $retorno = true;
        } else if ($dia_semana == 6 && $sexta == 'S') {
            $retorno = true;
        } else if ($dia_semana == 7 && $sabado == 'S') {
            $retorno = true;
        }

        return $retorno;
    }

    public function ProcColetaFixaDiaria(
        $regcolfix,
        $data_coleta_fixa,     // Parâmetro por valor - Não será alterado
        &$global_cont_exist,   // Parâmetro por referencia - Valor será alterado
        &$global_cont_solic,   // Parâmetro por referencia - Valor será alterado
        &$global_cont_erros,   // Parâmetro por referencia - Valor será alterado
        &$global_array_log     // Parâmetro por referencia - Valor será alterado
    ) {

        // Para 'tipo_coleta' = 'D' (diária) e 'M' (multi-destinos)... a hora da coleta 
        // é a hora prevista definida no registro da COLETA FIXA => hr_prev_coleta
        $hora_coleta  = $regcolfix->hr_prev_coleta;
        $hora_entrega = $regcolfix->hr_prev_entrega;

        $existe_coleta = $this->ExisteSolicColetaDiariaGerada($regcolfix, $data_coleta_fixa);

        if ($existe_coleta) {
            $global_cont_exist = $global_cont_exist + 1;
        } else {

            try {

                // Valores para tipo coleta = 'DIÁRIA' e 'MULTI-DESTINOS'

                // Transforma um array de objetos (STD Class) em array normal
                $array_col_fix = get_object_vars($regcolfix);

                $array_col_fix['dt_prev_coleta'] = $data_coleta_fixa;
                $array_col_fix['hr_prev_coleta'] = $hora_coleta;

                $array_col_fix['dt_prev_entrega'] = $data_coleta_fixa;
                $array_col_fix['hr_prev_entrega'] = $hora_entrega;

                // Para coleta fixas do tipo 'DIÁRIA'... o veículo da ENTREGA
                // será definido pelo usuário do depto 'CONTROLE'. 
                $array_col_fix['placa_entrega'] = null;

                if ($this->TipoVeiculoEhCavalo($regcolfix->placa_coleta) == true) {
                    // Impedimos que uma coleta seja atribuida para um veículo do tipo 'cavalo'
                    $array_col_fix['placa_coleta'] = null;
                }

                if (rgDifTrimNull($array_col_fix['placa_coleta']) && ($regcolfix->autoriza_coleta == 'S')) {
                    $array_col_fix['instrucao'] = '01';   // '01' => Fazer coleta
                    $array_col_fix['status']    = 'C1';   // 'C1' => Coleta - Autorizada
                } else {
                    $array_col_fix['instrucao'] = null;   // Sem instrução inicial
                    $array_col_fix['status']    = 'C0';   // 'C0' => Coleta - Solicitada
                }

                $coleta = new Coleta();

                // Tem que passar por parâmetro O "$coleta", pois as atribuições devem ser feitas
                // para um array de objetos. A passagem deste parâmetro deve ser feita por referência
                $coleta = $this->AtribuirCamposRegColeta($coleta, $array_col_fix);

                $coleta->save();

                //INSERIR registro na tabela COLETA
                $global_cont_solic = $global_cont_solic + 1;
            } catch (\Exception $e) {

                $dt_ini_bloq_null = null;
                $dt_fim_bloq_null = null;

                $global_array_log = $this->AdicionarLogDetailProcesso(
                    $global_array_log,
                    'E301',
                    $regcolfix->id,
                    $regcolfix->cod_cliente,
                    $regcolfix->cod_loc_coleta,
                    $regcolfix->cod_loc_entrega,
                    $dt_ini_bloq_null,
                    $dt_fim_bloq_null,
                    $e->getMessage()
                );

                $global_cont_erros = $global_cont_erros + 1;
            }
        }
    }


    public function ProcColetaFixaContrato(
        $regcolfix,
        $data_coleta_fixa,     // Parâmetro por valor - Não será alterado
        &$global_cont_exist,   // Parâmetro por referencia - Valor será alterado
        &$global_cont_solic,   // Parâmetro por referencia - Valor será alterado
        &$global_cont_erros,   // Parâmetro por referencia - Valor será alterado
        &$global_array_log     // Parâmetro por referencia - Valor será alterado
    ) {

        // Para 'tipo_coleta' = 'C' (contrato)... a hora da coleta é a hora 
        // inicial de cada TURNO => t1_hora_ini  E  t2_hora_ini
        $hora_coleta = $regcolfix->t1_hora_ini;    // TURNO #1

        // Hora prevista para entrega: fim do TURNO #1
        $hora_entrega = $regcolfix->t1_hora_fim;   // TURNO #1

        // TURNO #1: Verificamos se já tem uma solicitação de coleta gerada para este dia, 
        // para este cliente + local de coleta + local de entrega + hora da coleta +
        // tipo_coleta + placa_coleta (se informada)
        $existe_coleta = $this->ExisteSolicColetaContratoGerada($regcolfix, $data_coleta_fixa, $hora_coleta);

        if ($existe_coleta) {
            $global_cont_exist = $global_cont_exist + 1;
        } else {

            try {

                // Transforma um array de objetos (STD Class) em array normal
                $array_col_fix = get_object_vars($regcolfix);

                // Valores para tipo coleta = 'CONTRATO': TURNO #1
                $array_col_fix['dt_prev_coleta'] = $data_coleta_fixa;
                $array_col_fix['hr_prev_coleta'] = $hora_coleta;

                $array_col_fix['dt_prev_entrega'] = $data_coleta_fixa;
                $array_col_fix['hr_prev_entrega'] = $hora_entrega;

                // Para coleta fixas do tipo 'CONTRATO'... NÃO precisa de placa
                // de entrega porque nunca ficam com status na fase de entrega
                //
                $array_col_fix['placa_entrega'] = null;

                if ($this->TipoVeiculoEhCavalo($regcolfix->placa_coleta) == true) {
                    // Impedimos que uma coleta seja atribuida para um veículo do tipo 'cavalo'
                    $array_col_fix['placa_coleta'] = null;
                }

                if (rgDifTrimNull($array_col_fix['placa_coleta']) && ($regcolfix->autoriza_coleta == 'S')) {
                    $array_col_fix['instrucao'] = '01';   // '01' => Fazer coleta
                    $array_col_fix['status']    = 'C1';   // 'C1' => Coleta - Autorizada
                } else {
                    $array_col_fix['instrucao'] = null;   // Nenhuma
                    $array_col_fix['status']    = 'C0';   // 'C0' => Coleta - Solicitada
                }

                //INSERIR registro na tabela COLETA
                $coleta = new Coleta();

                // Tem que passar por parâmetro O "$coleta", pois as atribuições devem ser feitas
                // para um array de objetos. A passagem deste parâmetro deve ser feita por referência
                $coleta = $this->AtribuirCamposRegColeta($coleta, $array_col_fix);
                $coleta->save();

                $global_cont_solic = $global_cont_solic + 1;
            } catch (\Exception $e) {

                $dt_ini_bloq_null = null;
                $dt_fim_bloq_null = null;

                $global_array_log = $this->AdicionarLogDetailProcesso(
                    $global_array_log,
                    'E301',
                    $regcolfix->id,
                    $regcolfix->cod_cliente,
                    $regcolfix->cod_loc_coleta,
                    $regcolfix->cod_loc_entrega,
                    $dt_ini_bloq_null,
                    $dt_fim_bloq_null,
                    $e->getMessage()
                );

                $global_cont_erros = $global_cont_erros + 1;
            }
        }

        if (($regcolfix->dois_turnos == 'S') && ($regcolfix->t2_hora_ini <> null)) {

            // Para 'tipo_coleta' = 'C' (contrato)... a hora da coleta é a hora 
            // inicial de cada TURNO => t1_hora_ini  E  t2_hora_ini
            $hora_coleta = $regcolfix->t2_hora_ini;     // TURNO #2

            // Hora prevista para entrega: fim do TURNO #2
            $hora_entrega = $regcolfix->t2_hora_fim;    // TURNO #2

            $existe_coleta = $this->ExisteSolicColetaContratoGerada($regcolfix, $data_coleta_fixa, $hora_coleta);

            if ($existe_coleta) {
                $global_cont_exist = $global_cont_exist + 1;
            } else {

                try {

                    // Transforma um array de objetos (STD Class) em array 
                    $array_col_fix = get_object_vars($regcolfix);

                    // Valores para tipo coleta = 'CONTRATO': TURNO #2
                    $array_col_fix['dt_prev_coleta'] = $data_coleta_fixa;
                    $array_col_fix['hr_prev_coleta'] = $hora_coleta;

                    // Para coleta fixas do tipo 'CONTRATO'... data+hora ENTREGA 
                    // podem ser os mesmos da COLETA.
                    $array_col_fix['dt_prev_entrega'] = $data_coleta_fixa;
                    $array_col_fix['hr_prev_entrega'] = $hora_entrega;

                    // Para coleta fixas do tipo 'CONTRATO'... NÃO precisa de placa
                    // de entrega porque nunca ficam com status na fase de entrega
                    //
                    $array_col_fix['placa_entrega'] = null;

                    if ($this->TipoVeiculoEhCavalo($regcolfix->placa_coleta) == true) {
                        // Impedimos que uma coleta seja atribuida para um veículo do tipo 'cavalo'
                        $array_col_fix['placa_coleta'] = null;
                    }

                    if (rgDifTrimNull($array_col_fix['placa_coleta']) && ($regcolfix->autoriza_coleta == 'S')) {
                        $array_col_fix['instrucao'] = '01';   // '01' => Fazer coleta
                        $array_col_fix['status']    = 'C1';   // 'C1' => Coleta - Autorizada
                    } else {
                        $array_col_fix['instrucao'] = null;   // Nenhuma
                        $array_col_fix['status']    = 'C0';   // 'C0' => Coleta - Solicitada
                    }

                    $coleta = new Coleta();

                    // Tem que passar por parâmetro O "$coleta", pois as atribuições devem ser feitas
                    // para um array de objetos. A passagem deste parâmetro deve ser feita por referência
                    $coleta = $this->AtribuirCamposRegColeta($coleta, $array_col_fix);
                    $coleta->save();

                    $global_cont_solic = $global_cont_solic + 1;
                } catch (\Exception $e) {

                    $dt_ini_bloq_null = null;
                    $dt_fim_bloq_null = null;

                    $global_array_log = $this->AdicionarLogDetailProcesso(
                        $global_array_log,
                        'E301',
                        $regcolfix->id,
                        $regcolfix->cod_cliente,
                        $regcolfix->cod_loc_coleta,
                        $regcolfix->cod_loc_entrega,
                        $dt_ini_bloq_null,
                        $dt_fim_bloq_null,
                        $e->getMessage()
                    );

                    $global_cont_erros = $global_cont_erros + 1;
                }
            }
        }

        return true;
    }

    public function TipoVeiculoEhCavalo($placa)
    {

        $result = false;

        if (rgDifTrimNull($placa)) {

            $veiculo = DB::table('veiculo as vei')
                ->Join('tipo_veiculo as tv', 'tv.codigo', '=', 'vei.cod_tipo_veiculo')
                ->select('vei.placa')
                ->where('vei.placa', '=', $placa)
                ->where('tv.classe', '=', 'C')
                ->first();

            if (empty($veiculo) == false) {
                $result = true;
            }
        }

        return $result;
    }

    public function ExisteSolicColetaDiariaGerada($regcolfix, $data_coleta_fixa)
    {

        // Verificamos se já tem uma solicitação de coleta gerada 
        // para este registro de coleta fixa (id)  E  para a data

        $coleta = DB::table('coleta')
            ->select('id')
            ->where('coleta_fixa_id', '=', $regcolfix->id)
            ->where('dt_prev_coleta', '>=', $data_coleta_fixa)
            ->first();

        if (empty($coleta) == false) {
            return true;
        } else {
            return false;
        }
    }


    public function ExisteSolicColetaContratoGerada($regcolfix, $data_coleta_fixa, $hora_coleta)
    {

        // TURNO #1: Verificamos se já tem uma solicitação de coleta gerada para este
        // registro de coleta fixa (id)  E  para a data E para a hora inicial do TURNO #1.

        // TURNO #2: Verificamos se já tem uma solicitação de coleta gerada para este
        // registro de coleta fixa (id)  E  para a data E para a hora inicial do TURNO #2.

        $coleta = DB::table('coleta')
            ->select('id')
            ->where('coleta_fixa_id', '=', $regcolfix->id)
            ->where('dt_prev_coleta', '>=', $data_coleta_fixa)
            ->where('hr_prev_coleta', '>=', $hora_coleta)
            ->first();

        if (empty($coleta) == false) {
            return true;
        } else {
            return false;
        }
    }


    // Parâmetro por referencia - Objeto "Coleta"
    public function AtribuirCamposRegColeta(&$coleta, $array_col_fix)
    {

        $timezone_app = date_default_timezone_get();

        $coleta['empresa']          = $array_col_fix['empresa'];
        $coleta['numero']           = null;
        $coleta['data_cad']         = Carbon::now($timezone_app)->format('Y-m-d');
        $coleta['hora_cad']         = Carbon::now($timezone_app)->format('H:i:s');
        $coleta['cod_cliente']      = $array_col_fix['cod_cliente'];

        $coleta['dt_prev_coleta']   = $array_col_fix['dt_prev_coleta'];
        $coleta['hr_prev_coleta']   = $array_col_fix['hr_prev_coleta'];

        $coleta['dt_prev_entrega']  = $array_col_fix['dt_prev_entrega'];
        $coleta['hr_prev_entrega']  = $array_col_fix['hr_prev_entrega'];

        if ($coleta['hr_prev_entrega'] < $coleta['hr_prev_coleta']) {
            // Data de entrega é o dia seguinte            
            $coleta['dt_prev_entrega'] = date('Y-m-d', strtotime('+1 days', strtotime($coleta['dt_prev_entrega'])));
        }

        $coleta['entrega_urgente']  = 'N';

        $coleta['cod_loc_coleta']   = $array_col_fix['cod_loc_coleta'];
        $coleta['cod_loc_entrega']  = $array_col_fix['cod_loc_entrega'];

        $coleta['caract_coleta']    = $array_col_fix['caract_coleta'];

        $coleta['tipo_frete']       = $array_col_fix['tipo_frete'];
        $coleta['sis_carga']        = $array_col_fix['sis_carga'];
        $coleta['cod_tipo_veiculo'] = $array_col_fix['cod_tipo_veiculo'];

        $coleta['placa_coleta']     = rgSetaDefault($array_col_fix['placa_coleta'], null);
        $coleta['placa_entrega']    = rgSetaDefault($array_col_fix['placa_entrega'], null);

        // 'motor_coleta_id' e 'motor_entrega_id serão gravados 
        // pelas rotinas que atualizam o status da solicitação.
        $coleta['motor_coleta_id']  = null;
        $coleta['motor_entrega_id'] = null;

        $coleta['coleta_fixa']      = $array_col_fix['tipo_coleta'];
        $coleta['coleta_fixa_id']   = $array_col_fix['id'];

        $coleta['receber_nf_frete'] = rgSetaDefault($array_col_fix['receber_nf_frete'], 'N');
        $coleta['aceitar_foto_rom'] = rgSetaDefault($array_col_fix['aceitar_foto_rom'], 'N');

        $coleta['ocultar_resumo'] = rgSetaDefault($array_col_fix['ocultar_resumo'], 'N');

        $geo_pos_coleta = $this->RetornaCoordenadasLocal_Col_Ent(
            $array_col_fix['cod_loc_coleta'],
            $array_col_fix['empresa']
        );

        $geo_pos_entrega = $this->RetornaCoordenadasLocal_Col_Ent(
            $array_col_fix['cod_loc_entrega'],
            $array_col_fix['empresa']
        );

        $distance_matrix = new DistanceMatrix();
        $route = $distance_matrix->getServiceRoutes();

        $api_service = $route['api_service'];
        $api_key = $route['api_key'];

        $array_tempo_dist = GetDrivingDistance(
            $geo_pos_coleta['geo_lat'],
            $geo_pos_coleta['geo_lng'],
            $geo_pos_entrega['geo_lat'],
            $geo_pos_entrega['geo_lng'],
            $api_service,
            $api_key
        );

        $coleta['distancia_km']     = $array_tempo_dist['distance'];
        $tempo_estimado             = $array_tempo_dist['duration'];

        //O tempo estimado está calculado em segundos. Fazemos a conversão para "H:i:s" 
        $coleta['tempo_estimado']   = rgSecondsToTime($tempo_estimado);

        $coleta['instrucao']        = $array_col_fix['instrucao'];

        $col = new Coleta();
        $coleta['txt_instrucao']    = $col->RetornarDescrInstrucaoColeta($coleta['instrucao']);

        $coleta['status']           = $array_col_fix['status'];

        $coleta['origem_reg']       = 'A1';    // Gerado pelas coletas fixas

        // Não vem nos parâmetros. Gravamos Fixo "N"
        $coleta['coleta_export']  = 'N';
        // Não vem nos parâmetros. Gravamos Fixo "N"
        $coleta['entrega_export'] = 'N';

        // TEMP - Não estamos gravando a assinatura do usuário pois 
        // esta API será um lambda Function e não passa pela autenticacao
        // $coleta['ass_user_id']      = '';

        return $coleta;
    }


    public function RetornaCoordenadasLocal_Col_Ent($cod_loc_ent_col, $empresa)
    {

        $geo_lat = 0;
        $geo_lng = 0;

        $local_col_ent = DB::table('cliente')
            ->select('geo_lat', 'geo_lng')
            ->where('cliente.codigo', '=', $cod_loc_ent_col)
            ->where('cliente.empresa', '=', $empresa)
            ->first();

        if (!empty($local_col_ent)) {
            $geo_lat = $local_col_ent->geo_lat;
            $geo_lng = $local_col_ent->geo_lng;
        }

        $retorno['geo_lat'] = $geo_lat;
        $retorno['geo_lng'] = $geo_lng;

        return $retorno;
    }


    public function AdicionarLogDetailProcesso(
        &$global_array_log,   // Parâmetro por referencia - Valor será alterado
        $cod_erro,
        $colfix_id,
        $cod_cliente,
        $cod_loc_coleta,
        $cod_loc_entrega,
        $dt_ini_bloq = null,
        $dt_fim_bloq = null,
        $e_getMessage
    ) {

        $ind_log = count($global_array_log);
        $timezone_app = date_default_timezone_get();

        // Inserir um elemento no array do log:  DETAIL
        $global_array_log[$ind_log]['tipo'] = '1';

        if ($cod_erro == 'E301') {

            $msg_erro = rgGetMsgRetornoAPI($cod_erro);
            $msg_erro = str_replace('$coleta_fixa_id', $colfix_id, $msg_erro);
            $msg_erro = str_replace('$cod_cliente', $cod_cliente, $msg_erro);
            $msg_erro = str_replace('$cod_loc_coleta', $cod_loc_coleta, $msg_erro);
            $msg_erro = str_replace('$cod_loc_entrega', $cod_loc_entrega, $msg_erro);
            $global_array_log[$ind_log]['msg'] = $msg_erro;

            $global_array_log[$ind_log]['err'] = $e_getMessage;

            $global_array_log[$ind_log]['status'] = '1';
            $global_array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
        } else {

            if ($cod_erro == 'E302') {

                $msg_erro = rgGetMsgRetornoAPI($cod_erro);
                $msg_erro = str_replace('$coleta_fixa_id', $colfix_id, $msg_erro);
                $msg_erro = str_replace('$cod_cliente', $cod_cliente, $msg_erro);
                $msg_erro = str_replace('$cod_loc_coleta', $cod_loc_coleta, $msg_erro);
                $msg_erro = str_replace('$cod_loc_entrega', $cod_loc_entrega, $msg_erro);
                $global_array_log[$ind_log]['msg'] = $msg_erro;

                $global_array_log[$ind_log]['err'] = 'Inicio do bloqueio: ' . $dt_ini_bloq  .
                    ' | Fim do bloqueio: ' . $dt_fim_bloq;

                $global_array_log[$ind_log]['status'] = '0';
                $global_array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
            }
        }

        return $global_array_log;
    }


    public function AdicionarLogDetailFinal(
        &$global_array_log,   // Parâmetro por referencia - Valor será alterado
        $cont_fixas,
        $global_cont_solic,
        $global_cont_exist,
        $cont_bloq,
        $global_cont_erros
    ) {

        $ind_log = count($global_array_log);

        $global_array_log[$ind_log]['tipo'] = '1';

        $global_array_log[$ind_log]['msg'] = 'Coletas fixas processadas: ' . $cont_fixas . ' | ' .
            'Solicitações geradas: ' . $global_cont_solic . ' | ' .
            'Existentes: ' . $global_cont_exist . ' | ' .
            'Bloqueios: ' . $cont_bloq . ' | ' .
            'Erros: ' . $global_cont_erros;

        $global_array_log[$ind_log]['err'] = '';

        $global_array_log[$ind_log]['status'] = '0';

        $timezone_app = date_default_timezone_get();
        $global_array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

        return $global_array_log;
    }


    // Parâmetro por referencia - Valor será alterado
    public function AdicionarLogTrailler(&$global_array_log, $cont_erros)
    {
        // Adicionar LOG: Trailler
        // Inserir um elemento no array do log:
        $ind_log = count($global_array_log);

        $global_array_log[$ind_log]['tipo'] = '9';

        $timezone_app = date_default_timezone_get();
        $global_array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

        if ($cont_erros > 0) {
            $global_array_log[$ind_log]['msg'] = 'Processamento concluído com erros: ' . $cont_erros;
            $global_array_log[$ind_log]['err'] = null;
            $global_array_log[$ind_log]['status'] = '1';
        } else {
            $global_array_log[$ind_log]['msg'] = 'Processamento concluido com sucesso!';
            $global_array_log[$ind_log]['err'] = null;
            $global_array_log[$ind_log]['status'] = '0';
        }

        return $global_array_log;
    }


    public function GravarLog($global_array_log, $evento_log, $funcao_log)
    {
        // Variável que vai guardar o ID de processamento do registro HEADER
        $proc_id = 0;

        // Para cada elemento de array_log, inserir registro na tabela LOG_PRO com 
        // os dados de $array_log
        foreach ($global_array_log as $reglog) {

            try {

                $log_pro = new LogPro;

                $log_pro['evento'] = $evento_log;
                $log_pro['tipo']   = $reglog['tipo'];
                $log_pro['msg']    = $reglog['msg'];
                $log_pro['err']    = $reglog['err'];
                $log_pro['status'] = $reglog['status'];

                // O primeiro elemento de 'array_log' a ser gravado será o registro HEADER (tipo = '0').
                // O campo 'proc_id' do registro HEADER será gravado com ZERO porque é o valor inicial da variável. 
                // Antigamente este valor era gerado através de uma TRIGGER do banco de dados.
                // Com a mudança para o MySql 8.0.36, analisamos a estrutura do log e chegamos na conclusão que não precisamos da trigger.
                $log_pro['proc_id']    = $proc_id;
                $log_pro['created_at'] = $reglog['created_at'];

                $log_pro->save();

                // Guardamos o ID do registro HEADER (tipo = '0') para gravarmos no campo 'proc_id' 
                // de todos os registros deste processamento.
                if ($log_pro['tipo'] == '0') {
                    $proc_id = $log_pro->id;
                }
            } catch (\Exception $e) {

                \Log::info('Computador Local (server): ' . gethostname() . ' ' .
                    '[' . $funcao_log . ']' . $e->getMessage());
            }
        }
    }
}
