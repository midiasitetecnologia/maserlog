<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ColetaLog extends Model
{
    protected $table = 'coleta_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'coleta_id',
        'tipo',
        'descricao',
        'texto',
        'ass_user_id'
    ];
}
