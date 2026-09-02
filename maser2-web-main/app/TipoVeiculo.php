<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Exception;

class TipoVeiculo extends Model
{
    protected $table = 'tipo_veiculo';
    protected $primaryKey = 'codigo';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'codigo',
        'descricao',
        'classe',
        'dur_prev_atend',
        'tempo_desloc_pavilhao',
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
        if ($model->classe == 'C') {
            $model->dur_prev_atend = 0; //Aqui queremos gravar zero mesmo. Se alterar a classe, já fica sugerido "00:00".
            $model->tempo_desloc_pavilhao = 0; //Aqui queremos gravar zero mesmo. Se alterar a classe, já fica sugerido "00:00".
        }
    }

    static function validar_registro($model)
    {
        if (($model->classe != 'C') && (($model->dur_prev_atend == null) || ($model->dur_prev_atend === "00:00") || ($model->dur_prev_atend === "00:00:00"))) {
            throw new Exception('Informe a duração prevista de atendimento.');
        }

        if (($model->classe != 'C') && (($model->tempo_desloc_pavilhao < "00:00") || ($model->tempo_desloc_pavilhao > "23:59"))) {
            throw new Exception('O tempo de deslocamento até o pavilhão deve ser entre "00:00" e "23:59".');
        }
    }
}
