<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DispApp extends Model
{
    protected $table = 'disp_app';
    protected $primaryKey = 'id_disp';

    protected $fillable = [
        'id_disp',
        'descricao',
        'plataforma',
        'versao_so',
        'versao_app',
        'push_token'
    ];
}
