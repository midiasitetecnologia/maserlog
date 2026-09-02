<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SysCfg extends Model
{
    protected $table = 'sys_cfg';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'status',
        'dt_ini_status',
        'dt_fim_status',
        'msg_status',
        'url_redirect',
        'gerar_coletas_fixas',
        'dia_coletas_fixas',
        'office_area',
        'garage_area',
        'pavilion_area',
        'geo_lat_pavilion',
        'geo_lng_pavilion',
        'url_sis_track',
        'user_sis_track',
        'pwd_sis_track',
        'ass_user_id'
    ];
}
