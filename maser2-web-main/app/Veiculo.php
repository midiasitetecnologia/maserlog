<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Exception;
use DB;

class Veiculo extends Model
{
    protected $table = 'veiculo';
    protected $primaryKey = 'placa';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'placa',
        'cod_tipo_veiculo',
        'milk_run',
        'sis_carga_empilha',
        'sis_carga_ponte',
        'sis_carga_manual',
        'largura',
        'comprimento',
        'altura',
        'cap_cub',
        'cap_kg',
        'nivel_cons',
        'motorista_id',
        'ativo',
        'usar_gps',
        'placa_cavalo',
        'ignicao',
        'geo_lat',
        'geo_lng',
        'dt_geopos',
        'img_carga',
        'ocup_veiculo',
        'dt_carga_ocup',
        'transfer_code',
        'dt_transfer_code',
        'dur_atend_atual',
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
        $tipo_veiculo = DB::table('tipo_veiculo')
            ->select('classe')
            ->where('codigo', '=', $model->cod_tipo_veiculo)
            ->first();

        if (empty($tipo_veiculo) == false) {
            if ($tipo_veiculo->classe != 'R') {
                //Somente veículos do tipo "Carreta (R)" pode ter o campo "placa_cavalo" informado.
                $model->placa_cavalo = null;
            }
        }
    }

    static function validar_registro($model)
    {
        if (($model->sis_carga_empilha == 'N') && ($model->sis_carga_ponte == 'N') && ($model->sis_carga_manual == 'N')) {
            throw new Exception('Você deve marcar um ou mais sistema de carga (Empilhadeira, Ponte Rolante, Manual).');
        }

        if ($model->placa_cavalo != null) {

            $veiculo = DB::table('veiculo')
                ->select('veiculo.placa')
                ->where('placa_cavalo', '=', $model->placa_cavalo)
                ->where('placa', '!=', $model->placa)
                ->first();

            if (empty($veiculo) == false) {
                throw new Exception('A placa do cavalo "' . $model->placa_cavalo . '" já está vinculada a outro veículo.');
            }

            $veiculo = DB::table('veiculo')
                ->select('veiculo.placa', 'tipo_veiculo.classe')
                ->join('tipo_veiculo', 'tipo_veiculo.codigo', '=', 'veiculo.cod_tipo_veiculo')
                ->where('placa', '=', $model->placa_cavalo)
                ->first();

            if (empty($veiculo)) {
                throw new Exception('A placa do cavalo "' . $model->placa_cavalo . '" não está cadastrada.');
            } else {
                if ($veiculo->classe != 'C') {
                    throw new Exception('O veículo placa "' . $veiculo->placa . '" não é da classe "Cavalo".');
                }
            }
        }
    }

    //-------------------------------------------------------------------------------------------------------------------

    public function RetornarVeiculosDisponiveis($motorista_id, $com_cavalo = 'N')
    {
        $dados = array();

        // Retornamos um registro com o conteúdo nenhum independente se vai encontrar 
        // veículos ou não
        $ind = 0;

        $dados[$ind]['placa'] = null;
        $dados[$ind]['descr_tipo_veiculo'] = 'Nenhum (não estou atendendo)';

        // Selecionar registros da tabela VEICULO => $veiculo... que NÃO estejam relacionados a 
        // nenhum motorista... exceto o motorista atual ($motorista_id):

        $veiculo = DB::table('veiculo as v')
            ->select('v.placa', 'tv.descricao AS descr_tipo_veiculo')
            ->join('tipo_veiculo as tv', 'tv.codigo', '=', 'v.cod_tipo_veiculo')
            ->where('v.ativo', '=', 'S')

            ->where(function ($query) use ($com_cavalo) {

                if ($com_cavalo == 'N') {
                    // Consideramos apenas veículos: "M" => Monobloco ou "R" => Carrega/Reboque
                    $query->whereIn('tv.classe', ['M', 'R']);
                } else {
                    // Consideramos veículos: "M" ou "R" com cavalo vinculado     
                    $query->where(function ($query1) {
                        $query1->where('tv.classe', '=', 'M')
                            ->orWhere(function ($query2) {
                                $query2->where('tv.classe', '=', 'R')
                                    ->whereNotNull('v.placa_cavalo');
                            });
                    });
                }
            })

            ->where(function ($query) use ($motorista_id) {
                $query->whereNull('v.motorista_id')
                    ->orWhere('v.motorista_id', '=', $motorista_id);
            })
            ->orderby('v.placa', 'asc')
            ->get();



        foreach ($veiculo as $regveiculo) {
            $ind++;
            $dados[$ind]['placa'] = $regveiculo->placa;
            $dados[$ind]['descr_tipo_veiculo'] = $regveiculo->descr_tipo_veiculo;
        }

        return $dados;
    }


    public function Local_DesvincularMotoristaVeiculo($placa, $motorista_id)
    {

        try {
            $veiculo = Veiculo::where('placa', '=', $placa)
                ->where('motorista_id', '=', $motorista_id)
                ->update([
                    'motorista_id' => null,
                    'ass_user_id'  => auth()->user()->id
                ]);

            if ($veiculo > 0) {
                $result = true;
            } else {
                $result = false;
            }
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }
}
