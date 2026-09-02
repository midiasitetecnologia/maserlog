<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ColetaPos extends Model
{
    protected $table = 'coleta_pos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'coleta_id',
        'status',
        'placa',
        'motorista_id',
        'geo_lat',
        'geo_lng',
        'distancia',
        'tempo'
    ];
}
