<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'cliente';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'empresa',
        'codigo',
        'tipo_pessoa',
        'nome',
        'fantasia',
        'cpf_cnpj',
        'fone',
        'cep',
        'endereco',
        'bairro',
        'cidade',
        'uf',
        'geo_lat',
        'geo_lng',
        'hr_ini_coleta_man',
        'hr_fim_coleta_man',
        'hr_ini_coleta_tar',
        'hr_fim_coleta_tar',
        'hr_ini_entrega_man',
        'hr_fim_entrega_man',
        'hr_ini_entrega_tar',
        'hr_fim_entrega_tar',
        'local_distrib',
        'solicitar_coletas',
        'dt_alt_cad',
        'hr_alt_cad',
        'user_id',
        'ass_user_id'
    ];
}
