<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Exception;
use DB;

class ColetaFixa extends Model
{
    protected $table = 'coleta_fixa';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'empresa',
        'cod_cliente',
        'tipo_coleta',
        'dt_ini',
        'dt_fim',
        'cod_loc_coleta',
        'cod_loc_entrega',
        'cod_tipo_veiculo',
        'sis_carga',
        'receber_nf_frete',
        'aceitar_foto_rom',
        'ocultar_resumo',
        'tipo_frete',
        'placa_coleta',
        'autoriza_coleta',
        'caract_coleta',
        'segunda',
        'terca',
        'quarta',
        'quinta',
        'sexta',
        'sabado',
        'hr_prev_coleta',
        'hr_prev_entrega',
        'dois_turnos',
        't1_hora_ini',
        't1_hora_fim',
        't2_hora_ini',
        't2_hora_fim',
        'cont_cancel',
        'dt_cancel',
        'ass_user_id'
    ];

    public function coleta_fixa_bloq()
    {
        return $this->belongsTo('App\ColetaFixaBloq');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            self::beforeInsert($model);
        });

        static::updating(function ($model) {
            self::beforeUpdate($model);
        });

        // static::deleting(function ($model) {
        //     self::beforeDelete($model);
        // });
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
        if ($model->tipo_coleta == 'D') {
            //Se a coleta for do tipo "Diária" vamos setar não para Dois Turnos.
            $model->dois_turnos = 'N';
        }

        if ($model->tipo_coleta == 'C') {
            //Se a coleta for do tipo "Contrato" vamos setar NULL para Hora da Coleta.
            $model->hr_prev_coleta = null;
        }
    }

    static function validar_registro($model)
    {

        $empresa = DB::table('empresa')->where('codigo', '=', $model->empresa)->first();

        if (empty($empresa)) {
            throw new Exception('Informe a empresa contratada.');
        }

        $cliente = DB::table('cliente')->where('codigo', '=', $model->cod_cliente)
            ->where('empresa', '=', $model->empresa)->first();

        if (empty($cliente)) {
            throw new Exception('Cliente não cadastrado para esta empresa.');
        }

        if ($model->dt_ini > $model->dt_fim) {
            throw new Exception('A data final do contrato deve ser maior ou igual a data inicial.');
        }

        $coleta = DB::table('cliente')->where('codigo', '=', $model->cod_loc_coleta)
            ->where('empresa', '=', $model->empresa)->first();

        if (empty($coleta)) {
            throw new Exception('Local de coleta não cadastrado para esta empresa.');
        }

        $entrega = DB::table('cliente')->where('codigo', '=', $model->cod_loc_entrega)
            ->where('empresa', '=', $model->empresa)->first();

        if (empty($entrega)) {
            throw new Exception('Local de entrega não cadastrado para esta empresa.');
        }

        if (rgNvl(($model->placa_coleta) != '')) {

            $veiculo = DB::table('veiculo')->where('placa', '=', $model->placa_coleta)->first();

            if (empty($veiculo)) {
                throw new Exception('Veículo não cadastrado.');
            }
        }

        if ($model->cod_tipo_veiculo == null) {
            throw new Exception('O tipo de veículo contratado é obrigatório.');
        }

        if ($model->cod_tipo_veiculo != null) {

            $tipoVeiculo = DB::table('tipo_veiculo')->where('codigo', '=', $model->cod_tipo_veiculo)->first();

            if (empty($tipoVeiculo)) {
                throw new Exception('Tipo de veículo não cadastrado.');
            }
        }


        if (($model->segunda == 'N') && ($model->terca == 'N') && ($model->quarta == 'N') && ($model->quinta == 'N') && ($model->sexta == 'N') && ($model->sabado == 'N')
        ) {
            throw new Exception('Você deve marcar um ou mais dias da semana.');
        }

        if ($model->tipo_coleta == 'D') { //Diária

            if ($model->hr_prev_coleta == null) {
                throw new Exception('Informe uma hora para a coleta.');
            }
        }

        if ($model->tipo_coleta == 'C') {

            if (($model->t1_hora_ini == null) || ($model->t1_hora_fim == null)) {
                throw new Exception('O horário do primeiro turno é obrigatório.');
            }
        }

        if ($model->dois_turnos == 'S') {

            if (($model->t2_hora_ini == null) || ($model->t2_hora_fim == null)) {
                throw new Exception('O horário do segundo turno é obrigatório quando a opção "Dois Turnos" está marcada.');
            }
        }

        if ($model->cont_cancel == 'S') {

            if ($model->dt_cancel == null) {
                throw new Exception('Informe a data de cancelamento do contrato.');
            }
        }
    }
}
