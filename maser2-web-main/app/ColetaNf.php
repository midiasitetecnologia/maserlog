<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ColetaNf extends Model
{
    protected $table = 'coleta_nf';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'coleta_id',
        'cod_barras',
        'serie',
        'numero',
        'valor',
        'volumes',
        'dig_cnpj',
        'img_recibo',
        'solic_destino_id',
        'substituida',
        'mot_nao_entrega',
        'observ',
        'origem_reg',
        'ass_user_id'
    ];
}
