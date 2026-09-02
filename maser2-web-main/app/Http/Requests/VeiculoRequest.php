<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class VeiculoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules =
            [
                'placa' => 'required|max:8',                
                'cod_tipo_veiculo' => 'required',
                //'milk_run' => 'required|in:S,N',  //Não estamos utilizando este campo.
                'ativo' => 'required|in:S,N'
            ];

        return $rules;
    }

    public function messages()
    {
        return [
            'placa.required' => 'A placa deve ser informada.',
            'placa.max' => 'A placa deve ter no máximo :max caracteres.',                       

            'cod_tipo_veiculo.required' => 'O tipo de veículo deve ser informado.',

            //Não estamos utilizando este campo.
            //'milk_run.required' => 'O campo "Milk Run" é obrigatório.',
            //'milk_run.in' => 'O valor do campo "Milk Run" deve estar entre :values',

            'ativo.required' => 'O campo "Ativo" é obrigatório.',
            'ativo.in' => 'O valor do campo "Ativo" deve estar entre :values'
        ];
    }

    public function ValidarDadosApiVeiculo($data)
    {

        $array_regras_validar = array();
        $resultado = array();

        $regras = $this->rules();

        foreach ($regras as $key => $reg) {

            if (array_key_exists($key, $data)) {
                $array_regras_validar = $array_regras_validar + [$key => $reg];
            }
        }

        if (count($array_regras_validar) > 0) {

            //Buscamos o array de mensagens traduzidas, pois o laravel não associa automaticamente
            //quando invocamos o metodo validate. As mensagens tem que ser passadas como o terceiro parâmetro
            $messages = $this->messages();
            $validator = Validator::make($data, $array_regras_validar, $messages);

            if ($validator->fails()) {
                $resultado['erros'] = $validator->errors();
            }
        }

        return $resultado;
    }
}
