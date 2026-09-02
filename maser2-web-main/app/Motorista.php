<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Exception;
use DB;

class Motorista extends Model
{
    protected $table = 'motorista';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cpf',
        'nome',
        'celular',
        'user_id',
        'hr_ini_exped',
        'hr_fim_exped',
        'hr_ini_login',
        'hr_fim_login',
        'auto_logoff',
        'ativo',
        'geo_lat',
        'geo_lng',
        'dt_geopos',
        'id_disp',
        'logado',
        'dt_logado',
        'dt_alt_cad',
        'hr_alt_cad',
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
        });

        // static::deleting(function ($model) {
        //     self::beforeDelete($model);
        // });
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

    static function validar_registro($model)
    {
        if ($model->user_id != null) {

            $users = DB::table('users')->where('id', '=', $model->user_id)->first();

            if (empty($users)) {
                throw new Exception('Motorista não cadastrado para esta empresa.');
            }
        }

        if ($model->auto_logoff == 'S') {

            if ($model->hr_ini_login == null || $model->hr_fim_login == null) {
                throw new Exception('Para desconectar o motorista automaticamente, informe o horário que ele pode ficar conectado.');
            }
        }
    }
}
