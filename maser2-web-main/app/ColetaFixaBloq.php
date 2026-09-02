<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Exception;
use DB;

class ColetaFixaBloq extends Model
{
    protected $table = 'coleta_fixa_bloq';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'coleta_fixa_id',
        'dt_ini',
        'dt_fim',
        'observ',
        'ass_user_id'
    ];

    public function coleta_fixa()
    {
        return $this->hasMany('App\ColetaFixa');
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

        static::deleting(function ($model) {
            self::beforeDelete($model);
        });
    }

    static function beforeInsert($model)
    {
        // colocar aqui o código que deseja executar antes do INSERT		
        self::validar_registro($model);
    }

    static function beforeUpdate($model)
    {
        // colocar aqui o código que deseja executar antes do UPDATE      
        self::validar_registro($model);
    }

    static function beforeDelete($model)
    {
        // colocar aqui o código que deseja executar antes do DELETE
        self::validar_exclusao($model);
    }

    static function validar_registro($model)
    {
        $dt_ini = $model->dt_ini;
        $dt_fim = $model->dt_fim;

        $coletaFixaBloq = DB::table('coleta_fixa_bloq')
            ->where('id', '!=', $model->id)
            ->where('coleta_fixa_id', '=', $model->coleta_fixa_id)
            ->get();

        foreach ($coletaFixaBloq as $regbloq) {

            if (($dt_ini >= $regbloq->dt_ini) && ($dt_ini <= $regbloq->dt_fim)) {
                throw new Exception('Já existe um registro de bloqueio neste período para este mesmo contrato.');
            }

            if (($dt_fim >= $regbloq->dt_ini) && ($dt_fim <= $regbloq->dt_fim)) {
                throw new Exception('Já existe um registro de bloqueio neste período para este mesmo contrato.');
            }

            if (($dt_ini < $regbloq->dt_ini) && ($dt_fim > $regbloq->dt_fim)) {
                throw new Exception('Já existe um registro de bloqueio neste período para este mesmo contrato.');
            }
        }
    }

    static function validar_exclusao($model)
    {
        $dt_ini = $model->dt_ini;
        $timezone_app = date_default_timezone_get();
        $data_hora_atual_serv = Carbon::now($timezone_app)->format('Y-m-d');

        if ($dt_ini < $data_hora_atual_serv) {
            throw new Exception('Não é possível excluir este bloqueio. Data inicial passada.');
        }
    }
}
