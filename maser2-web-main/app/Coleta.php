<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\ColetaPos;
use Exception;
use DB;

class Coleta extends Model
{
    protected $table = 'coleta';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'empresa',
        'numero',
        'data_cad',
        'hora_cad',
        'cod_cliente',
        'dt_prev_coleta',
        'hr_prev_coleta',
        'dt_prev_entrega',
        'hr_prev_entrega',
        'entrega_urgente',
        'cod_loc_coleta',
        'local_coleta_cmd',
        'cod_loc_entrega',
        'local_entrega_cmd',
        'peso',
        'solicitante',
        'volumes',
        'especie',
        'sis_carga',
        'alt_carga',
        'larg_carga',
        'comp_carga',
        'cod_tipo_veiculo',
        'placa_coleta',
        'caract_coleta',
        'obs_coleta',
        'motor_coleta_id',
        'coleta_fixa',
        'coleta_fixa_id',
        'dt_efet_coleta',
        'hr_partida_coleta',
        'hr_cheg_coleta',
        'hr_atend_coleta',
        'hr_sai_coleta',
        'cod_tipo_veiculo_nec',
        'placa_entrega',
        'motor_entrega_id',
        'dt_efet_entrega',
        'hr_partida_entrega',
        'hr_cheg_entrega',
        'hr_atend_entrega',
        'hr_sai_entrega',
        'tempo_desloc_pavilhao',
        'entrega_consolidada',
        'recebedor',
        'receber_nf_frete',
        'nfs_comerciais',
        'aceitar_foto_rom',
        'ocultar_resumo',
        'tipo_frete',
        'nf_frete',
        'sit_nf_frete',
        'distancia_km',
        'tempo_estimado',
        'distancia_coleta',
        'tempo_coleta',
        'distancia_entrega',
        'tempo_entrega',
        'distancia_total',
        'tempo_total',
        'dur_prev_coleta',
        'dur_prev_entrega',
        'instrucao',
        'txt_instrucao',
        'placa_baldeacao',
        'baldeada',
        'status',
        'carga_pavilhao',
        'mot_nao_coleta',
        'obs_nao_coleta',
        'mot_nao_entrega',
        'obs_nao_entrega',
        'reentrega',
        'reentrega_gerada',
        'solic_reentrega_id',
        'solic_origem_id',
        'img_carga',
        'ocup_veiculo',
        'img_rom_coleta',
        'img_rom_entrega',
        'seq_atend',
        'rota_calc',
        'origem_reg',
        'coleta_export',
        'dt_coleta_export',
        'entrega_export',
        'dt_entrega_export',
        'ass_user_id'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            self::beforeInsert($model);
        });

        static::updating(function ($model) {
            self::beforeUpdate($model);
            self::AfterUpdate($model);
        });
    }

    static function beforeInsert($model)
    {
        // colocar aqui o código que deseja executar antes do INSERT
        self::tratar_campos($model);
        self::validar_registro($model);
    }

    static function beforeUpdate($model)
    {
        // colocar aqui o código que deseja executar antes do UPDATE
        self::tratar_campos($model);
        self::validar_registro($model);
    }

    static function tratar_campos($model)
    {
        if ($model->coleta_fixa == 'M') {
            $model->aceitar_foto_rom = 'N';
        }
    }

    static function validar_registro($model)
    {
        $empresa = DB::table('empresa')->where('codigo', '=', $model->empresa)->first();

        if (empty($empresa)) {
            throw new Exception('Informe a empresa da solicitação.');
        }

        $cliente = DB::table('cliente')->where('codigo', '=', $model->cod_cliente)
            ->where('empresa', '=', $model->empresa)->first();

        if (empty($cliente)) {
            throw new Exception('Cliente não cadastrado para esta empresa.');
        }

        if ($model->coleta_fixa == null) {
            throw new Exception('O tipo de coleta é obrigatório.');
        }

        if ($model->dt_prev_coleta < $model->data_cad) {
            $data_cadastro = Carbon::createFromFormat('Y-m-d', $model->data_cad)->format('d/m/Y');
            throw new Exception('Data da coleta deve ser maior ou igual à data de cadastro (' . $data_cadastro . ').');
        }

        if ($model->dt_prev_entrega < $model->dt_prev_coleta) {
            $data_prev_coleta = Carbon::createFromFormat('Y-m-d', $model->data_cad)->format('d/m/Y');
            throw new Exception('Data da entrega deve ser maior ou igual à data da coleta (' . $data_prev_coleta . ').');
        }

        //Só vamos exigir local de coleta se não for Comanda.
        if (($model->coleta_fixa == 'C' && $model->solic_origem_id != null) == false) { //Este teste representa a comanda, temos que negar.
            $coleta = DB::table('cliente')->where('codigo', '=', $model->cod_loc_coleta)
                ->where('empresa', '=', $model->empresa)->first();

            if (empty($coleta)) {
                throw new Exception('Local de coleta não cadastrado para esta empresa.');
            }
        }

        //Só vamos exigir local de entrega se não for Comanda.
        if (($model->coleta_fixa == 'C' && $model->solic_origem_id != null) == false) { //Este teste representa a comanda, temos que negar.
            $entrega = DB::table('cliente')->where('codigo', '=', $model->cod_loc_entrega)
                ->where('empresa', '=', $model->empresa)->first();

            if (empty($entrega)) {
                throw new Exception('Local de entrega não cadastrado para esta empresa.');
            }
        }

        // Solicitaçoes do tipo "M"(Multi-Destinos) e "C"(contrato) não validamos: peso, volumes, especie, 
        // comp_carga, larg_carga, alt_carga e sis_carga
        if ($model->coleta_fixa == 'D') {

            //Para solicitações Diárias, só vamos exigir a validação se não for uma coleta fixa. 
            //Não temos estes campos na geração de coleta fixas automatica e sempre irá gravar um coleta_fixa_id
            if ($model->coleta_fixa_id == null) {

                //Desligamos as validações de "Peso e Volumes" para não dar erro. 
                //Porque no sistema ERP da DOMPER permite valores zerados.

                // if (rgIgualZeroNull($model->peso)) {
                //     throw new Exception('Informe o peso da carga.');
                // }

                // if (rgIgualZeroNull($model->volumes)) {
                //     throw new Exception('Informe a quantidade de volumes da carga.');
                // }

                if (rgIgualTrimNull($model->especie)) {
                    throw new Exception('Informe a espécie dos volumes da carga.');
                }

                if (rgIgualZeroNull($model->comp_carga)) {
                    throw new Exception('Informe o comprimento da carga.');
                }

                if (rgIgualZeroNull($model->larg_carga)) {
                    throw new Exception('Informe a largura da carga.');
                }

                if (rgIgualZeroNull($model->alt_carga)) {
                    throw new Exception('Informe a altura da carga.');
                }

                if (rgIgualTrimNull($model->sis_carga)) {
                    throw new Exception('Informe o sistema de carga.');
                }
            }
        }

        if ($model->origem_reg != 'SD') {
        }

        if ($model->origem_reg != 'SD') {
            if ($model->cod_tipo_veiculo == null) {
                throw new Exception('O tipo de veículo é obrigatório.');
            }

            if ($model->cod_tipo_veiculo != null) {

                $tipoVeiculo = DB::table('tipo_veiculo')->where('codigo', '=', $model->cod_tipo_veiculo)->first();

                if (empty($tipoVeiculo)) {
                    throw new Exception('Tipo de veículo não cadastrado.');
                }
            }
        }

        if ($model->origem_reg != 'SD') {

            if (rgNvl(($model->placa_coleta) != '')) {

                $veiculo = DB::table('veiculo')->where('placa', '=', $model->placa_coleta)->first();

                if (empty($veiculo)) {
                    throw new Exception('Veículo não cadastrado.');
                }
            }
        }
    }

    static function AfterUpdate($model)
    {
        self::RegistrarEventosAfterUpdateColeta($model);
        self::TotalizarDistanciaETemposSolic($model);
    }


    static function RegistrarEventosAfterUpdateColeta($model)
    {
        // SOMENTE dispara os eventos se tiver alteração em algum dos campos da coleta
        // Para disparar os eventos deve ser utilizado o MODEL na gravação
        $old_placa_coleta     = $model->getOriginal('placa_coleta');
        $old_placa_entrega    = $model->getOriginal('placa_entrega');

        $old_motor_coleta_id  = $model->getOriginal('motor_coleta_id');
        $old_motor_entrega_id = $model->getOriginal('motor_entrega_id');

        $old_txt_instrucao    = $model->getOriginal('txt_instrucao');
        $old_carga_pavilhao   = $model->getOriginal('carga_pavilhao');
        $old_solic_origem_id  = $model->getOriginal('solic_origem_id');
        $old_status           = $model->getOriginal('status');
        $old_baldeada         = $model->getOriginal('baldeada');
        $old_mot_nao_coleta   = $model->getOriginal('mot_nao_coleta');

        // -------------------- I M P O R T A N T E --------------------------------------------------
        // Como o "ID" da coleta NUNCA é atualizado, temos que obter o valor que está em "old_value".
        //--------------------------------------------------------------------------------------------
        $old_coleta_id = $model->getOriginal('id');

        // Inicializar variáveis
        $tipo = '';
        $descricao = '';
        $sem_txt_instrucao = null; // Somente deve ser gravado o "txt_instrucao" para os casos em que for realmente alterado o "txt_instrucao"

        // Manter a ordem dos testes dos eventos conforme abaixo, para que fique
        // numa sequencia mais lógica na tabela COLETA_LOG
        //
        // 1. Status
        // 2. Motivos não-coleta
        // 3. Instrução
        // 4. Descarga pavilhão
        // 5. Veiculo e motorista coleta
        // 6. Veiculo e motorista entrega
        // 7. Solicitação origem
        // 8. Baldeação

        if ($model->status <> $old_status) {
            self::GravarRegColetaStatusColeta($model, $old_coleta_id);
        }

        // Motivo não coleta (SEM ATENDIMENTO)
        if (rgIgualTrimNull($old_mot_nao_coleta) && ($model->mot_nao_coleta == '03')) {
            self::GravarRegColetaLogMotivoSemAtend($model);
        }

        // No texto da instrução não pode mandar caracteres especiais como "emojicons", senão NÃO vai gravar
        // a instrução e nem mandar o push. 
        if (($model->txt_instrucao <> $old_txt_instrucao) && rgDifTrimNull($model->txt_instrucao)) {

            $tipo = '03';
            $descricao = 'Instrução => ' . self::RetornarDescrInstrucaoColeta($model->instrucao);

            self::GravarRegColetaLog($old_coleta_id, $tipo, $descricao, $model->txt_instrucao, $model->ass_user_id);

            self::GravarNotifAlteracaoInstrucao($model);
        }

        // Descarga Pavilhão
        if (($model->carga_pavilhao <> $old_carga_pavilhao) && rgDifTrimNull($model->carga_pavilhao)) {

            $tipo = '06';
            $descricao = '';
            $texto = '';

            self::GravarRegColetaLogDescargaPavilhao($model, $old_coleta_id, $tipo, $descricao, $texto);
        }

        if ($model->placa_coleta <> $old_placa_coleta) {
            self::GravarRegColetaVeiculoColeta($model, $old_coleta_id, $sem_txt_instrucao);
        }

        if ($model->motor_coleta_id <> $old_motor_coleta_id) {
            self::GravarRegColetaMotoristaColeta($model, $old_coleta_id, $sem_txt_instrucao);
        }

        if ($model->placa_entrega <> $old_placa_entrega) {
            self::GravarRegColetaVeiculoEntrega($model, $old_coleta_id, $sem_txt_instrucao);
        }

        if ($model->motor_entrega_id <> $old_motor_entrega_id) {
            self::GravarRegColetaMotoristaEntrega($model, $old_coleta_id, $sem_txt_instrucao);
        }

        // Solicitação de origem(comandas)
        if ($model->solic_origem_id <> $old_solic_origem_id) {
            self::GravarRegColetaSolicOrigemComandas($model, $old_coleta_id, $sem_txt_instrucao);
        }

        if (($model->baldeada == 'S') && ($old_baldeada <> 'S')) {
            self::GravarNotifAlteracaoBaldeacao($model, $old_placa_coleta, $old_placa_entrega);
        }
    }


    static function GravarRegColetaStatusColeta($model, $old_coleta_id)
    {

        $old_status = $model->getOriginal('status');

        $api = new ApiColeta();
        $tipo = '04';
        $descricao = $api->RetornarDescrStatusColeta($model->status);
        $texto = "";

        if ($model->status == 'CN') {

            if ($model->mot_nao_coleta == '01') {
                $descricao = $descricao . ' (com deslocamento)';
                $texto = 'Motivo: ' . $model->obs_nao_coleta;
            } else {
                if ($model->mot_nao_coleta == '02') {
                    $descricao = $descricao . ' (sem deslocamento)';
                    $texto = 'Motivo: ' . $model->obs_nao_coleta;
                }
            }
        }

        // Define a etapa da solicitação
        if (substr($model->status, 0, 1) == 'C') {

            // Coleta: Cancelamento de deslocamento 
            if (($old_status == 'C2') && ($model->status == 'C1')) {
                $descricao = 'Motorista cancelou deslocamento (coleta)';
            }

            // Coleta: Cancelamento de chegada
            if (($old_status == 'C3') && ($model->status == 'C2')) {
                $descricao = 'Motorista cancelou chegada (coleta)';
            }

            // Coleta: Cancelamento de início de atendimento
            if (($old_status == 'C4') && ($model->status == 'C3')) {
                $descricao = 'Motorista cancelou início atendimento (coleta)';
            }

            // Coleta: Cancelamento de finalização
            if (($old_status == 'CR') && ($model->status == 'C4')) {
                $descricao = 'Motorista cancelou finalização (coleta)';
            }
        } else {

            // Na etapa de ENTREGA não permitimos desfazer a realização da entrega
            // por isso não testamos o status 'ER'
            // Entrega: Cancelamento de deslocamento 
            if (($old_status == 'E2') && ($model->status == 'E1')) {
                $descricao = 'Motorista cancelou deslocamento (entrega)';
            }

            // Coleta: Cancelamento de chegada
            if (($old_status == 'E3') && ($model->status == 'E2')) {
                $descricao = 'Motorista cancelou chegada (entrega)';
            }

            // Coleta: Cancelamento de início de atendimento
            if (($old_status == 'E4') && ($model->status == 'E3')) {
                $descricao = 'Motorista cancelou início atendimento (entrega)';
            }
        }

        self::GravarRegColetaLog($old_coleta_id, $tipo, $descricao, $texto, $model->ass_user_id);
    }

    static function GravarRegColetaVeiculoColeta($model, $old_coleta_id, $sem_txt_instrucao)
    {

        $tipo = '02';
        $old_placa_coleta  = $model->getOriginal('placa_coleta');

        if (rgIgualTrimNull($old_placa_coleta)) {
            $descricao = 'Veículo definido (coleta) => ' . $model->placa_coleta;
        } else {
            if (rgIgualTrimNull($model->placa_coleta)) {
                $descricao = 'Veículo removido (coleta) => ' . $old_placa_coleta;
            } else {
                $descricao = 'Veículo alterado (coleta): ' . $old_placa_coleta . ' => ' . $model->placa_coleta;
            }
        }

        self::GravarRegColetaLog($old_coleta_id, $tipo, $descricao, $sem_txt_instrucao, $model->ass_user_id);
    }

    static function GravarRegColetaMotoristaColeta($model, $old_coleta_id, $sem_txt_instrucao)
    {

        $tipo = '01';
        $old_motor_coleta_id = $model->getOriginal('motor_coleta_id');

        if (rgIgualZeroNull($old_motor_coleta_id)) {

            // Aqui $motor_coleta_id tem valor
            $new_motor = DB::table('motorista')
                ->select('nome')
                ->where('id', '=', $model->motor_coleta_id)
                ->first();

            $descricao = 'Motorista definido (coleta) => ' . $new_motor->nome;
        } else {

            if (rgIgualZeroNull($model->motor_coleta_id)) {

                // Aqui $OLD_motor_coleta_id tem valor
                $old_motor = DB::table('motorista')
                    ->select('nome')
                    ->where('id', '=', $old_motor_coleta_id)
                    ->first();

                $descricao = 'Motorista removido (coleta) => ' . $old_motor->nome;
            } else {

                // Aqui $OLD_motor_coleta_id tem valor
                $old_motor = DB::table('motorista')
                    ->select('nome')
                    ->where('id', '=', $old_motor_coleta_id)
                    ->first();

                // Aqui $motor_coleta_id tem valor
                $new_motor = DB::table('motorista')
                    ->select('nome')
                    ->where('id', '=', $model->motor_coleta_id)
                    ->first();

                $descricao = 'Motorista alterado (coleta): ' . $old_motor->nome . ' => ' . $new_motor->nome;
            }
        }

        self::GravarRegColetaLog($old_coleta_id, $tipo, $descricao, $sem_txt_instrucao, $model->ass_user_id);
    }


    static function GravarRegColetaVeiculoEntrega($model, $old_coleta_id, $sem_txt_instrucao)
    {

        $tipo = '02';
        $old_placa_entrega  = $model->getOriginal('placa_entrega');

        if (rgIgualTrimNull($old_placa_entrega)) {
            $descricao = 'Veículo definido (entrega) => ' . $model->placa_entrega;
        } else {
            if (rgIgualTrimNull($model->placa_entrega)) {
                $descricao = 'Veículo removido (entrega) => ' . $old_placa_entrega;
            } else {
                $descricao = 'Veículo alterado (entrega): ' . $old_placa_entrega . ' => ' . $model->placa_entrega;
            }
        }

        self::GravarRegColetaLog($old_coleta_id, $tipo, $descricao, $sem_txt_instrucao, $model->ass_user_id);
    }


    static function GravarRegColetaMotoristaEntrega($model, $old_coleta_id, $sem_txt_instrucao)
    {

        $tipo = '01';
        $old_motor_entrega_id  = $model->getOriginal('motor_entrega_id');

        if (rgIgualZeroNull($old_motor_entrega_id)) {

            // Aqui $motor_entrega_id tem valor
            $new_motor = DB::table('motorista')
                ->select('nome')
                ->where('id', '=', $model->motor_entrega_id)
                ->first();

            $descricao = 'Motorista definido (entrega) => ' . $new_motor->nome;
        } else {

            if (rgIgualZeroNull($model->motor_entrega_id)) {

                // Aqui $OLD_motor_entrega_id tem valor
                $old_motor = DB::table('motorista')
                    ->select('nome')
                    ->where('id', '=', $old_motor_entrega_id)
                    ->first();

                $descricao = 'Motorista removido (entrega) => ' . $old_motor->nome;
            } else {

                // Aqui $OLD_motor_entrega_id tem valor
                $old_motor = DB::table('motorista')
                    ->select('nome')
                    ->where('id', '=', $old_motor_entrega_id)
                    ->first();

                // Aqui $motor_coleta_id tem valor
                $new_motor = DB::table('motorista')
                    ->select('nome')
                    ->where('id', '=', $model->motor_entrega_id)
                    ->first();

                $descricao = 'Motorista alterado (entrega):' . $old_motor->nome . ' => ' . $new_motor->nome;
            }
        }

        self::GravarRegColetaLog($old_coleta_id, $tipo, $descricao, $sem_txt_instrucao, $model->ass_user_id);
    }


    static function GravarRegColetaSolicOrigemComandas($model, $old_coleta_id, $sem_txt_instrucao)
    {

        $old_solic_origem_id = $model->getOriginal('solic_origem_id');

        $new_solicitacao = '';
        $old_solicitacao = '';

        // Pegamos o número da solicitação ATUAL onde esta comanda está vinculada
        // Ler tabela COLETA => $old_coleta com  $old_solic_origem_id
        $old_coleta = DB::table('coleta')
            ->select('id', 'numero')
            ->where('id', '=', $old_solic_origem_id)
            ->first();

        if (empty($old_coleta) == false) {

            if (rgIgualZeroNull($old_coleta->numero)) {
                $old_solicitacao = 'ID: ' . $old_coleta->id;
            } else {
                $old_solicitacao = $old_coleta->numero;
            }
        }

        // Pegamos o número da NOVA solicitação onde esta comanda atual vinculada
        // Ler tabela COLETA => $new_coleta  com  $model->solic_origem_id
        $new_coleta = DB::table('coleta')
            ->select('id', 'numero')
            ->where('id', '=', $model->solic_origem_id)
            ->first();

        if (empty($new_coleta) == false) {

            if (rgIgualZeroNull($new_coleta->numero)) {
                $new_solicitacao = 'ID: ' . $new_coleta->id;
            } else {
                $new_solicitacao = $new_coleta->numero;
            }
        }

        // Registramos com tipo '05' => mudança de solicitação origem
        $tipo = '05';
        $descricao = 'Comanda transferida: ' . $old_solicitacao . ' => ' . $new_solicitacao;

        self::GravarRegColetaLog($old_coleta_id, $tipo, $descricao, $sem_txt_instrucao, $model->ass_user_id);
    }


    static function GravarRegColetaLogMotivoSemAtend($coleta)
    {

        $old_motor_coleta_id = $coleta->getOriginal('motor_coleta_id');
        $old_dt_efet_coleta  = $coleta->getOriginal('dt_efet_coleta');
        $old_hr_partida_coleta = $coleta->getOriginal('hr_partida_coleta');
        $old_hr_cheg_coleta  = $coleta->getOriginal('hr_cheg_coleta');
        $old_placa_coleta    = $coleta->getOriginal('placa_coleta');

        $nome_motorista = '';

        if (rgDifZeroNull($old_motor_coleta_id)) {

            $old_motor = DB::table('motorista')
                ->select('id', 'user_id', 'nome')
                ->where('id', '=', $old_motor_coleta_id)
                ->first();

            if (empty($old_motor) == false) {
                $nome_motorista = ' Motorista: ' . $old_motor->nome;
            }
        }

        // Registramos com tipo '04' => mudança de status
        $tipo = '04';
        $descricao = 'Coleta devolvida: sem atendimento';

        if (strtotime($old_dt_efet_coleta)) {
            $dt_efet_coleta = Carbon::createFromFormat('Y-m-d', $old_dt_efet_coleta)->format('d-m-Y');
        } else {
            $dt_efet_coleta = null;
        }

        // Gravamos os dados da chegada (que estão nos campos OLD + observação que está no campo NEW)
        $texto = 'Data: ' . $dt_efet_coleta . PHP_EOL .
            'Partida: ' . $old_hr_partida_coleta . PHP_EOL .
            'Chegada: ' . $old_hr_cheg_coleta . PHP_EOL .
            'Motivo: ' . $coleta->obs_nao_coleta . PHP_EOL .
            'Veiculo: ' . $old_placa_coleta . PHP_EOL .
            'Motorista: ' . $nome_motorista;

        self::GravarRegColetaLog($coleta->id, $tipo, $descricao, $texto, $coleta->ass_user_id);
    }


    static function RetornarDescrInstrucaoColeta($instrucao)
    {
        return match ($instrucao) {
            '01' => 'Fazer coleta',
            '02' => 'Manter carga no veículo',
            '03' => 'Descarregar no pavilhão',
            '04' => 'Ir para o pavilhão',
            '05' => 'Fazer baldeação',
            '06' => 'Fazer entrega',
            '07' => 'Baldeação no pátio',
            '99' => 'Instrucao livre',
            default => '',  // Nenhuma instrucao
        };
    }


    static function GravarRegColetaLogDescargaPavilhao($model, $old_coleta_id, $tipo, $descricao, $texto)
    {
        // Define a etapa da solicitação
        if (substr($model->status, 0, 1) == 'C') {
            $descricao = 'Coleta - Descarga Pavilhão';
        } else {
            $descricao = 'Entrega - Descarga Pavilhão';
        }

        self::GravarRegColetaLog($old_coleta_id, $tipo, $descricao, $texto, $model->ass_user_id);
    }


    static function GravarRegColetaLog($coleta_id, $tipo, $descricao, $texto, $ass_user_id)
    {

        // SE ocorrer uma falha na gravação deste registro... apenas retornamos FALSE para a 
        // rotina chamadora saber que a gravação não foi executada. 
        // Inserir registro em COLETA_LOG

        try {

            $coleta_log = new ColetaLog();

            $coleta_log['coleta_id']   = $coleta_id;
            $coleta_log['tipo']        = $tipo;
            $coleta_log['descricao']   = $descricao;
            $coleta_log['texto']       = $texto;
            $coleta_log['ass_user_id'] = $ass_user_id;

            $coleta_log->save();

            $result = true;
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }


    static function TotalizarDistanciaETemposSolic($model)
    {
        if (($model->status == 'CN' && $model->mot_nao_coleta == '01') || ($model->status == 'ER')) {
            self::TotalizarDistanciaTempoSolic($model);
        }
    }


    static function TotalizarDistanciaTempoSolic($coleta)
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
                $dados_desloc = self::CalcDistanciaDeslocSolic($coleta->id);
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


    static function GravarNotifAlteracaoInstrucao($coleta)
    {

        // -------------------- I M P O R T A N T E --------------------------------------------------
        // Como o "ID" da coleta NUNCA é atualizado, temos que obter o valor que está em "old_value".
        //--------------------------------------------------------------------------------------------
        $coleta_id  = $coleta->getOriginal('id');

        // Enviamos a notificação SEMPRE para o MOTORISTA ATUAL do veículo.
        // Não importa o motorista que está na solicitação

        if (substr($coleta->status, 0, 1) == 'C') {
            $placa = $coleta->placa_coleta;
            $evento = 'C01';
        } else {
            $placa = $coleta->placa_entrega;
            $evento = 'E01';
        }

        // O motorista que vai receber a notificação é SEMPRE aquele que está no veículo
        // para o qual a solicitação foi atribuida. O motorista PREVISTO que pode ter sido
        // definido na solicitação NÃO recebe a notificação: SE o veículo da solicitação
        // NÃO tiver motorista no momento que a solicitação foi autorizada, NINGUÉM recebe
        // notificação.
        //
        $user_id = self::BuscarUsuarioMotoristaVeiculo($placa);

        $tipo = 'M';    // Motorista

        if (rgDifZeroNull($coleta->numero)) {
            $solicitacao = $coleta->numero;
        } else {
            $solicitacao = $coleta_id;
        }

        if ($coleta->instrucao <> '99') {

            // Buscamos a descricao padrão da instrucao "05-Fazer baldeação" para não estourar o tamanho título
            // porque nesta instrucao concatenamos o conteudo do campo "txt_instrucao" digitado pelo usuário
            if ($coleta->instrucao == '05') {
                $titulo = self::RetornarDescrInstrucaoColeta($coleta->instrucao) . ' => ' . $coleta->placa_baldeacao;
            } else {
                $titulo = $coleta->txt_instrucao;
            }

            $titulo = 'Sol: ' . $solicitacao . ' - ' . $titulo;
            $texto = $coleta->txt_instrucao;
        } else {
            $titulo = 'Sol: ' . $solicitacao . ' - Nova instrução';
            $texto = $coleta->txt_instrucao;
        }

        self::GravarRegNotif($tipo, $user_id, $evento, $titulo, $texto, $coleta_id, $coleta->ass_user_id);
    }


    static function GravarNotifAlteracaoBaldeacao($coleta, $old_placa_coleta, $old_placa_entrega)
    {

        if (rgDifZeroNull($coleta->numero)) {
            $coleta_id = $coleta->numero;
        } else {
            $coleta_id = $coleta->id;
        }

        if (substr($coleta->status, 0, 1) == 'C') {
            $placa  = $coleta->placa_coleta;
            $titulo = 'Sol: ' . $coleta_id . ' - ' . 'Baldeada para seu veículo';
            $texto  = 'De: ' . $old_placa_coleta . ' para ' . $coleta->placa_coleta;
            $evento = 'C99';    // Coleta - Outras mensagens
        } else {
            $placa  = $coleta->placa_entrega;
            $titulo = 'Sol: ' . $coleta_id . ' - ' . 'Baldeada para seu veículo';
            $texto  = 'De: ' . $old_placa_entrega . ' para ' . $coleta->placa_entrega;
            $evento = 'E99';    // Entrega - Outras mensagens
        }

        $tipo = 'M';    // Motorista

        $user_id = self::BuscarUsuarioMotoristaVeiculo($placa);

        self::GravarRegNotif($tipo, $user_id, $evento, $titulo, $texto, $coleta->id, $coleta->ass_user_id);
    }


    static function BuscarUsuarioMotoristaVeiculo($placa)
    {

        $user_id = null;

        $veiculo = DB::table('veiculo')
            ->select('motorista_id')
            ->where('placa', '=', $placa)
            ->first();

        if (empty($veiculo) == false) {

            $motorista = DB::table('motorista')
                ->select('user_id')
                ->where('id', '=', $veiculo->motorista_id)
                ->first();

            if (empty($motorista) == false) {
                $user_id = $motorista->user_id;
            }
        }

        return $user_id;
    }


    static function GravarRegNotif($tipo, $user_id, $evento, $titulo, $texto, $reg_id, $ass_user_id)
    {

        // SE ocorrer uma falha na gravação deste registro... apenas retornamos FALSE para a 
        // rotina chamadora saber que a gravação não foi executada. 
        try {

            $notif = new Notif();

            $notif['tipo_usuario'] = $tipo;
            $notif['user_id']      = $user_id;
            $notif['evento']       = $evento;
            $notif['titulo']       = $titulo;
            $notif['texto']        = $texto;
            $notif['reg_id']       = $reg_id;
            $notif['lida']         = 'N';
            $notif['ass_user_id']  = $ass_user_id;

            $notif->save();

            $result = true;
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }

    // ------------------------------------------------------------------------------//
    // APIs para acumular tempos e distancias
    // ------------------------------------------------------------------------------//

    public function Local_RetornarTotaisKmTempoCliente($data_ini, $data_fim)
    {
        $dados = array();

        // A consulta SQL com os parâmetros de data sendo passados via bindings
        $select = "
        SELECT cli.empresa, cli.codigo AS cod_cliente, cli.nome AS nome_cliente,
            COUNT(col.id) AS total_coletas,
            SUM(col.distancia_coleta) AS total_km_coleta,
            SUM(TIME_TO_SEC(col.tempo_coleta)) AS total_tempo_coleta,
            SUM(col.distancia_entrega) AS total_km_entrega,
            SUM(TIME_TO_SEC(col.tempo_entrega)) AS total_tempo_entrega,
            SUM(col.distancia_total) AS total_km,
            SUM(TIME_TO_SEC(col.tempo_coleta)) + SUM(TIME_TO_SEC(col.tempo_entrega)) AS total_tempo
        FROM coleta col
        INNER JOIN cliente cli ON (cli.empresa = col.empresa AND cli.codigo = col.cod_cliente)
        WHERE col.dt_prev_coleta BETWEEN ? AND ?
        AND (
            col.status = 'ER' OR
            (col.status = 'CN' AND col.mot_nao_coleta = '01')
        )
        AND (
            col.coleta_fixa <> 'C' OR
            (col.coleta_fixa = 'C' AND IFNULL(col.solic_origem_id, 0) <> 0)
        )
        GROUP BY cli.empresa, cli.codigo, cli.nome
        ORDER BY cli.nome, cli.codigo, cli.empresa
        ";

        // Passando os parâmetros para a consulta de maneira segura
        $clientes = DB::select($select, [$data_ini, $data_fim]);

        if (count($clientes) > 0) {
            $ind = 0;

            // Variáveis para os totais
            $total_coletas = 0;
            $total_km_coleta = 0;
            $total_tempo_coleta = 0;
            $total_entregas = 0;
            $total_km_entrega = 0;
            $total_tempo_entrega = 0;
            $total_km = 0;
            $total_tempo = 0;

            // Processamento dos resultados
            foreach ($clientes as $cli) {
                $dados[$ind]['empresa'] = $cli->empresa;
                $dados[$ind]['cod_cliente'] = $cli->cod_cliente;
                $dados[$ind]['descricao'] = $cli->nome_cliente;
                $dados[$ind]['total_coletas'] = $cli->total_coletas;
                $dados[$ind]['total_km_coleta'] = rgFloatVal($cli->total_km_coleta);
                $dados[$ind]['total_tempo_coleta'] = rgSecondsToTime($cli->total_tempo_coleta);
                $dados[$ind]['total_km_entrega'] = rgFloatVal($cli->total_km_entrega);
                $dados[$ind]['total_tempo_entrega'] = rgSecondsToTime($cli->total_tempo_entrega);
                $dados[$ind]['total_km'] = rgFloatVal($cli->total_km);
                $dados[$ind]['total_tempo'] = rgSecondsToTime($cli->total_tempo);

                // Acumula os totais para a linha de rodapé
                $total_coletas += $cli->total_coletas;
                $total_km_coleta += $cli->total_km_coleta;
                $total_tempo_coleta += $cli->total_tempo_coleta;
                $total_km_entrega += $cli->total_km_entrega;
                $total_tempo_entrega += $cli->total_tempo_entrega;
                $total_km += $cli->total_km;
                $total_tempo += $cli->total_tempo;

                $ind++;
            }

            // Linha de rodapé com os totais
            $dados[$ind] = $this->MontaRodapeTotaisKmTempo(
                $total_coletas,
                $total_km_coleta,
                $total_tempo_coleta,
                $total_entregas,
                $total_km_entrega,
                $total_tempo_entrega,
                $total_km,
                $total_tempo
            );
        }

        // Retorna os dados formatados
        $resultado['dados'] = $dados;

        return $resultado;
    }


    public function Local_RetornarTotaisKmTempoVeiculo($data_ini, $data_fim)
    {
        $dados = array();

        // => KM acumulado de COLETA_POS para COLETA e ENTREGA por VEICULO
        //
        // => TEMPO DE OPERAÇÃO acumulado de COLETA para COLETA e ENTREGA
        //    para o último veiculo de COLETA_POS. Consideramos que o 
        //    último veiculo foi quem fez o atendimento.
        $select = "
            SELECT
                placa,
                SUM(COALESCE(qtde_coletas, 0)) AS total_coletas,
                SUM(COALESCE(sum_km_coleta, 0)) AS total_km_coleta,
                SUM(COALESCE(sum_tempo_coleta, TIME_TO_SEC('00:00:00'))) AS total_tempo_coleta,
                SUM(COALESCE(qtde_entregas, 0)) AS total_entregas,
                SUM(COALESCE(sum_km_entrega, 0)) AS total_km_entrega,
                SUM(COALESCE(sum_tempo_entrega, TIME_TO_SEC('00:00:00'))) AS total_tempo_entrega,
                SUM(COALESCE(sum_km_coleta, 0) + COALESCE(sum_km_entrega, 0)) AS total_km,
                SUM(COALESCE(sum_tempo_coleta, TIME_TO_SEC('00:00:00')) + COALESCE(sum_tempo_entrega, TIME_TO_SEC('00:00:00'))) AS total_tempo
            FROM (
                SELECT
                    pos.placa,
                    CASE WHEN pos.status = 'C3' THEN 1 ELSE 0 END AS qtde_coletas,
                    CASE WHEN pos.status = 'C3' THEN pos.distancia ELSE 0 END AS sum_km_coleta,
                    CASE
                        WHEN pos.status = 'C3' AND rn_coleta = 1
                        THEN TIME_TO_SEC(COALESCE(col.tempo_coleta, '00:00:00'))
                        ELSE TIME_TO_SEC('00:00:00')
                    END AS sum_tempo_coleta,
                    CASE WHEN pos.status = 'E3' THEN 1 ELSE 0 END AS qtde_entregas,
                    CASE WHEN pos.status = 'E3' THEN pos.distancia ELSE 0 END AS sum_km_entrega,
                    CASE
                        WHEN pos.status = 'E3' AND rn_entrega = 1
                        THEN TIME_TO_SEC(COALESCE(col.tempo_entrega, '00:00:00'))
                        ELSE TIME_TO_SEC('00:00:00')
                    END AS sum_tempo_entrega
                FROM (
                    SELECT
                        pos.*,
                        ROW_NUMBER() OVER (
                            PARTITION BY pos.coleta_id
                            ORDER BY CASE WHEN pos.status = 'C3' THEN pos.id END DESC
                        ) AS rn_coleta,
                        ROW_NUMBER() OVER (
                            PARTITION BY pos.coleta_id
                            ORDER BY CASE WHEN pos.status = 'E3' THEN pos.id END DESC
                        ) AS rn_entrega
                    FROM coleta_pos pos
                    INNER JOIN coleta col ON col.id = pos.coleta_id
                    WHERE pos.status IN ('C3', 'E3')
                    AND col.dt_prev_coleta >= ?
                    AND col.dt_prev_coleta <= ?
                    AND (col.status = 'ER' OR (col.status = 'CN' AND col.mot_nao_coleta = '01'))
                    AND (col.coleta_fixa != 'C' OR (col.coleta_fixa = 'C' AND IFNULL(col.solic_origem_id, 0) != 0))
                ) pos
                INNER JOIN coleta col ON col.id = pos.coleta_id
            ) dataset
            GROUP BY placa
            ORDER BY placa
        ";

        $veiculos = DB::select($select, [$data_ini, $data_fim]);        

        if (count($veiculos) > 0) {

            $total_coletas       = 0;
            $total_km_coleta     = 0;
            $total_tempo_coleta  = 0;
            $total_entregas      = 0;
            $total_km_entrega    = 0;
            $total_tempo_entrega = 0;
            $total_km            = 0;
            $total_tempo         = 0;

            $ind = 0;

            foreach ($veiculos as $vei) {

                $dados[$ind]['descricao']           = $vei->placa;
                $dados[$ind]['total_coletas']       = $vei->total_coletas;
                $dados[$ind]['total_km_coleta']     = rgFloatVal($vei->total_km_coleta);
                $dados[$ind]['total_tempo_coleta']  = rgSecondsToTime($vei->total_tempo_coleta);

                $dados[$ind]['total_entregas']      = $vei->total_entregas;
                $dados[$ind]['total_km_entrega']    = rgFloatVal($vei->total_km_entrega);
                $dados[$ind]['total_tempo_entrega'] = rgSecondsToTime($vei->total_tempo_entrega);
                $dados[$ind]['total_km']            = rgFloatVal($vei->total_km);
                $dados[$ind]['total_tempo']         = rgSecondsToTime($vei->total_tempo);

                // Acumula os totais para a linha de rodapé
                $total_coletas       = $total_coletas + $vei->total_coletas;
                $total_km_coleta     = $total_km_coleta + $vei->total_km_coleta;
                $total_tempo_coleta  = $total_tempo_coleta + $vei->total_tempo_coleta;
                $total_entregas      = $total_entregas + $vei->total_entregas;
                $total_km_entrega    = $total_km_entrega + $vei->total_km_entrega;
                $total_tempo_entrega = $total_tempo_entrega + $vei->total_tempo_entrega;
                $total_km            = $total_km + $vei->total_km;
                $total_tempo         = $total_tempo + $vei->total_tempo;

                $ind++;
            }

            $dados[$ind] = $this->MontaRodapeTotaisKmTempo(
                $total_coletas,
                $total_km_coleta,
                $total_tempo_coleta,
                $total_entregas,
                $total_km_entrega,
                $total_tempo_entrega,
                $total_km,
                $total_tempo
            );
        }

        $resultado['dados'] = $dados;

        return $resultado;
    }


    public function Local_RetornarTotaisKmTempoTipoVeiculo($data_ini, $data_fim)
    {

        $dados = array();

        // => KM acumulado de COLETA_POS para COLETA e ENTREGA por TIPO DE VEÍCULO
        //
        // => TEMPO DE OPERAÇÃO acumulado de COLETA para COLETA e ENTREGA
        //    para o último veiculo de COLETA_POS. Consideramos que o 
        //    último veiculo foi quem fez o atendimento.
        // 
        $select = "
            SELECT
                cod_tipo_veiculo,
                descr_tipo_veiculo,
                SUM(COALESCE(qtde_coletas, 0)) AS total_coletas,
                SUM(COALESCE(sum_km_coleta, 0)) AS total_km_coleta,
                SUM(COALESCE(sum_tempo_coleta, TIME_TO_SEC('00:00:00'))) AS total_tempo_coleta,
                SUM(COALESCE(qtde_entregas, 0)) AS total_entregas,
                SUM(COALESCE(sum_km_entrega, 0)) AS total_km_entrega,
                SUM(COALESCE(sum_tempo_entrega, TIME_TO_SEC('00:00:00'))) AS total_tempo_entrega,
                SUM(COALESCE(sum_km_coleta, 0) + COALESCE(sum_km_entrega, 0)) AS total_km,
                SUM(COALESCE(sum_tempo_coleta, TIME_TO_SEC('00:00:00')) + COALESCE(sum_tempo_entrega, TIME_TO_SEC('00:00:00'))) AS total_tempo
            FROM (
                SELECT
                    tv.codigo AS cod_tipo_veiculo,
                    tv.descricao AS descr_tipo_veiculo,
                    CASE WHEN pos.status = 'C3' THEN 1 ELSE 0 END AS qtde_coletas,
                    CASE WHEN pos.status = 'C3' THEN pos.distancia ELSE 0 END AS sum_km_coleta,
                    CASE
                        WHEN pos.status = 'C3' AND rn_coleta = 1
                        THEN TIME_TO_SEC(COALESCE(col.tempo_coleta, '00:00:00'))
                        ELSE TIME_TO_SEC('00:00:00')
                    END AS sum_tempo_coleta,
                    CASE WHEN pos.status = 'E3' THEN 1 ELSE 0 END AS qtde_entregas,
                    CASE WHEN pos.status = 'E3' THEN pos.distancia ELSE 0 END AS sum_km_entrega,
                    CASE
                        WHEN pos.status = 'E3' AND rn_entrega = 1
                        THEN TIME_TO_SEC(COALESCE(col.tempo_entrega, '00:00:00'))
                        ELSE TIME_TO_SEC('00:00:00')
                    END AS sum_tempo_entrega
                FROM (
                    SELECT
                        pos.*,
                        ROW_NUMBER() OVER (
                            PARTITION BY pos.coleta_id
                            ORDER BY CASE WHEN pos.status = 'C3' THEN pos.id END DESC
                        ) AS rn_coleta,
                        ROW_NUMBER() OVER (
                            PARTITION BY pos.coleta_id
                            ORDER BY CASE WHEN pos.status = 'E3' THEN pos.id END DESC
                        ) AS rn_entrega
                    FROM coleta_pos pos
                    INNER JOIN coleta col ON col.id = pos.coleta_id
                    WHERE pos.status IN ('C3', 'E3')
                    AND col.dt_prev_coleta >= ?
                    AND col.dt_prev_coleta <= ?
                    AND (col.status = 'ER' OR (col.status = 'CN' AND col.mot_nao_coleta = '01'))
                    AND (col.coleta_fixa != 'C' OR (col.coleta_fixa = 'C' AND IFNULL(col.solic_origem_id, 0) != 0))
                ) pos
                INNER JOIN coleta col ON col.id = pos.coleta_id
                INNER JOIN veiculo v ON v.placa = pos.placa
                LEFT JOIN tipo_veiculo tv ON tv.codigo = v.cod_tipo_veiculo
            ) dataset
            GROUP BY cod_tipo_veiculo, descr_tipo_veiculo
            ORDER BY descr_tipo_veiculo, cod_tipo_veiculo
        ";

        $tipos_veic = DB::select($select, [$data_ini, $data_fim]);        

        if (count($tipos_veic) > 0) {

            $total_coletas       = 0;
            $total_km_coleta     = 0;
            $total_tempo_coleta  = 0;
            $total_entregas      = 0;
            $total_km_entrega    = 0;
            $total_tempo_entrega = 0;
            $total_km            = 0;
            $total_tempo         = 0;

            $ind = 0;

            foreach ($tipos_veic as $tp_vei) {

                $dados[$ind]['cod_tipo_veiculo']    = $tp_vei->cod_tipo_veiculo;
                $dados[$ind]['descricao']           = $tp_vei->descr_tipo_veiculo;
                $dados[$ind]['total_coletas']       = $tp_vei->total_coletas;
                $dados[$ind]['total_km_coleta']     = rgFloatVal($tp_vei->total_km_coleta);
                $dados[$ind]['total_tempo_coleta']  = rgSecondsToTime($tp_vei->total_tempo_coleta);
                $dados[$ind]['total_entregas']      = $tp_vei->total_entregas;
                $dados[$ind]['total_km_entrega']    = rgFloatVal($tp_vei->total_km_entrega);
                $dados[$ind]['total_tempo_entrega'] = rgSecondsToTime($tp_vei->total_tempo_entrega);
                $dados[$ind]['total_km']            = rgFloatVal($tp_vei->total_km);
                $dados[$ind]['total_tempo']         = rgSecondsToTime($tp_vei->total_tempo);

                // Acumula os totais para a linha de rodapé
                $total_coletas       = $total_coletas + $tp_vei->total_coletas;
                $total_km_coleta     = $total_km_coleta + $tp_vei->total_km_coleta;
                $total_tempo_coleta  = $total_tempo_coleta + $tp_vei->total_tempo_coleta;
                $total_entregas      = $total_entregas + $tp_vei->total_entregas;
                $total_km_entrega    = $total_km_entrega + $tp_vei->total_km_entrega;
                $total_tempo_entrega = $total_tempo_entrega + $tp_vei->total_tempo_entrega;
                $total_km            = $total_km + $tp_vei->total_km;
                $total_tempo         = $total_tempo + $tp_vei->total_tempo;

                $ind++;
            }

            $dados[$ind] = $this->MontaRodapeTotaisKmTempo(
                $total_coletas,
                $total_km_coleta,
                $total_tempo_coleta,
                $total_entregas,
                $total_km_entrega,
                $total_tempo_entrega,
                $total_km,
                $total_tempo
            );
        }

        $resultado['dados'] = $dados;

        return $resultado;
    }


    public function Local_RetornarTotaisKmTempoMotorista($data_ini, $data_fim)
    {

        $dados = array();

        // => KM acumulado de COLETA_POS para COLETA e ENTREGA por MOTORISTA
        //
        // => TEMPO DE OPERAÇÃO acumulado de COLETA para COLETA e ENTREGA
        //    para o último motorista de COLETA_POS. Consideramos que o 
        //    último motorista foi quem fez o atendimento.
        // 
        $select = "
            SELECT
                cod_motorista,
                nome_motorista,
                SUM(COALESCE(qtde_coletas, 0)) AS total_coletas,
                SUM(COALESCE(sum_km_coleta, 0)) AS total_km_coleta,
                SUM(COALESCE(sum_tempo_coleta, TIME_TO_SEC('00:00:00'))) AS total_tempo_coleta,
                SUM(COALESCE(qtde_entregas, 0)) AS total_entregas,
                SUM(COALESCE(sum_km_entrega, 0)) AS total_km_entrega,
                SUM(COALESCE(sum_tempo_entrega, TIME_TO_SEC('00:00:00'))) AS total_tempo_entrega,
                SUM(COALESCE(sum_km_coleta, 0) + COALESCE(sum_km_entrega, 0)) AS total_km,
                SUM(COALESCE(sum_tempo_coleta, TIME_TO_SEC('00:00:00')) + COALESCE(sum_tempo_entrega, TIME_TO_SEC('00:00:00'))) AS total_tempo
            FROM (
                SELECT
                    m.id AS cod_motorista,
                    m.nome AS nome_motorista,
                    CASE WHEN pos.status = 'C3' THEN 1 ELSE 0 END AS qtde_coletas,
                    CASE WHEN pos.status = 'C3' THEN pos.distancia ELSE 0 END AS sum_km_coleta,
                    CASE
                        WHEN pos.status = 'C3' AND rn_coleta = 1
                        THEN TIME_TO_SEC(COALESCE(col.tempo_coleta, '00:00:00'))
                        ELSE TIME_TO_SEC('00:00:00')
                    END AS sum_tempo_coleta,
                    CASE WHEN pos.status = 'E3' THEN 1 ELSE 0 END AS qtde_entregas,
                    CASE WHEN pos.status = 'E3' THEN pos.distancia ELSE 0 END AS sum_km_entrega,
                    CASE
                        WHEN pos.status = 'E3' AND rn_entrega = 1
                        THEN TIME_TO_SEC(COALESCE(col.tempo_entrega, '00:00:00'))
                        ELSE TIME_TO_SEC('00:00:00')
                    END AS sum_tempo_entrega
                FROM (
                    SELECT
                        pos.*,
                        ROW_NUMBER() OVER (
                            PARTITION BY pos.coleta_id
                            ORDER BY CASE WHEN pos.status = 'C3' THEN pos.id END DESC
                        ) AS rn_coleta,
                        ROW_NUMBER() OVER (
                            PARTITION BY pos.coleta_id
                            ORDER BY CASE WHEN pos.status = 'E3' THEN pos.id END DESC
                        ) AS rn_entrega
                    FROM coleta_pos pos
                    INNER JOIN coleta col ON col.id = pos.coleta_id
                    WHERE pos.status IN ('C3', 'E3')
                    AND col.dt_prev_coleta >= ?
                    AND col.dt_prev_coleta <= ?
                    AND (col.status = 'ER' OR (col.status = 'CN' AND col.mot_nao_coleta = '01'))
                    AND (col.coleta_fixa != 'C' OR (col.coleta_fixa = 'C' AND IFNULL(col.solic_origem_id, 0) != 0))
                ) pos
                INNER JOIN coleta col ON col.id = pos.coleta_id
                LEFT JOIN motorista m ON m.id = pos.motorista_id
            ) dataset
            GROUP BY cod_motorista, nome_motorista
            ORDER BY nome_motorista, cod_motorista
        ";

        $motoristas = DB::select($select, [$data_ini, $data_fim]);

        if (count($motoristas) > 0) {

            $total_coletas       = 0;
            $total_km_coleta     = 0;
            $total_tempo_coleta  = 0;
            $total_entregas      = 0;
            $total_km_entrega    = 0;
            $total_tempo_entrega = 0;
            $total_km            = 0;
            $total_tempo         = 0;

            $ind = 0;

            foreach ($motoristas as $motor) {

                $dados[$ind]['cod_motorista']       = $motor->cod_motorista;
                $dados[$ind]['descricao']           = $motor->nome_motorista;

                $dados[$ind]['total_coletas']       = $motor->total_coletas;
                $dados[$ind]['total_km_coleta']     = rgFloatVal($motor->total_km_coleta);
                $dados[$ind]['total_tempo_coleta']  = rgSecondsToTime($motor->total_tempo_coleta);

                $dados[$ind]['total_entregas']      = $motor->total_entregas;
                $dados[$ind]['total_km_entrega']    = rgFloatVal($motor->total_km_entrega);
                $dados[$ind]['total_tempo_entrega'] = rgSecondsToTime($motor->total_tempo_entrega);

                $dados[$ind]['total_km']            = rgFloatVal($motor->total_km);
                $dados[$ind]['total_tempo']         = rgSecondsToTime($motor->total_tempo);

                // Acumula os totais para a linha de rodapé
                $total_coletas       = $total_coletas + $motor->total_coletas;
                $total_km_coleta     = $total_km_coleta + $motor->total_km_coleta;
                $total_tempo_coleta  = $total_tempo_coleta + $motor->total_tempo_coleta;
                $total_entregas      = $total_entregas + $motor->total_entregas;
                $total_km_entrega    = $total_km_entrega + $motor->total_km_entrega;
                $total_tempo_entrega = $total_tempo_entrega + $motor->total_tempo_entrega;
                $total_km            = $total_km + $motor->total_km;
                $total_tempo         = $total_tempo + $motor->total_tempo;

                $ind++;
            }

            $dados[$ind] = $this->MontaRodapeTotaisKmTempo(
                $total_coletas,
                $total_km_coleta,
                $total_tempo_coleta,
                $total_entregas,
                $total_km_entrega,
                $total_tempo_entrega,
                $total_km,
                $total_tempo
            );
        }

        $resultado['dados'] = $dados;

        return $resultado;
    }



    public function MontaRodapeTotaisKmTempo(
        $total_coletas,
        $total_km_coleta,
        $total_tempo_coleta,
        $total_entregas,
        $total_km_entrega,
        $total_tempo_entrega,
        $total_km,
        $total_tempo
    ) {

        $rodape['descricao']           = 'TOTAL';

        $rodape['total_coletas']       = $total_coletas;
        $rodape['total_km_coleta']     = rgFloatVal($total_km_coleta);

        // Tempo está em segundos. Convertemos para Horas:minutos:segundos
        $rodape['total_tempo_coleta']  = rgSecondsToTime($total_tempo_coleta);

        $rodape['total_entregas']      = $total_entregas;
        $rodape['total_km_entrega']    = rgFloatVal($total_km_entrega);

        // Tempo está em segundos. Convertemos para Horas:minutos:segundos
        $rodape['total_tempo_entrega'] = rgSecondsToTime($total_tempo_entrega);

        $rodape['total_km']            = rgFloatVal($total_km);

        // Tempo está em segundos. Convertemos para Horas:minutos:segundos
        $rodape['total_tempo']         = rgSecondsToTime($total_tempo);

        return $rodape;
    }


    public function Local_CancelarColetaSemDesloc($coleta_id, $obs_nao_coleta)
    {

        $retorno = array();
        $result  = true;

        if (rgIgualTrimNull($obs_nao_coleta)) {
            $result = false;
            $retorno['cod_retorno'] = 'E215';
            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
        }

        if ($result) {

            if (!($coleta = Coleta::find($coleta_id))) {

                $result = false;

                $retorno['cod_retorno'] = 'E200';
                $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                $retorno['msg_retorno'] = $msg_erro;
            } else {

                // Aceita cancelar a coleta SEM DESLOCAMENTO somente quando ainda NÃO houve deslocamento
                if (($coleta->status == 'C0') || ($coleta->status == 'C1')) {

                    // Não permitimos o cancelamento da COLETA para solicitações de REENTREGA.
                    // O motorista deve cancelar os passsos: Atendimento -> Chegada -> Deslocamento e solicitar 
                    // para o "CONTROLE" a definição sobre oque fazer com a solicitação.
                    //
                    // Essa função também é usada na interface.
                    if (rgDifTrimNull($coleta->reentrega)) {
                        $result = false;
                        $retorno['cod_retorno'] = 'E292';
                        $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                    } else {

                        try {

                            $coleta['status']         = 'CN';      // Coeleta-não realizada
                            $coleta['mot_nao_coleta'] = '02';      // Sem deslocamento
                            $coleta['obs_nao_coleta'] = $obs_nao_coleta;
                            $coleta['ass_user_id']    = auth()->user()->id;

                            $coleta->save();

                            $result = true;
                            $retorno['cod_retorno'] = 'Z100';
                            $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                        } catch (\Exception $e) {
                            $result = false;

                            $retorno['cod_retorno'] = 'E206';
                            $msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                            $msg_erro = str_replace('$coleta_id', $coleta_id, $msg_erro);
                            $retorno['msg_retorno'] = rgGetMsgRetornoExecaoAPI($msg_erro, $e->getMessage());
                        }
                    }
                } else {
                    $result = false;
                    $retorno['cod_retorno'] = 'E255';
                    $retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
                }
            }
        }

        $resultado['status'] = $result;

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

        return $resultado;
    }
}
