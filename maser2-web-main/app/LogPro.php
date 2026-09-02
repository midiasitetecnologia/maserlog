<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogPro extends Model
{

    protected $table = 'log_pro';

    protected $fillable = [
        'evento',
        'tipo',
        'msg',
        'err',
        'status',
        'proc_id'
    ];
}
