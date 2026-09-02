<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class ColetaFixaBloqRequest extends FormRequest
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
                'coleta_fixa_id' => 'required',
                'dt_ini' => 'required|date_format:Y-m-d|before_or_equal:dt_fim',
                'dt_fim' => 'required|date_format:Y-m-d|before:2038-01-19'
            ];

        return $rules;
    }

    public function messages()
    {
        return [
            'coleta_fixa_id.required' => 'A identificação da coleta fixa deve ser informada.',

            'dt_ini.required' => 'A data inicial é obrigatória.',
            'dt_ini.date_format' => 'A data inicial deve estar no formato "dd/mm/aaaa".',
            'dt_ini.before_or_equal' => 'A data inicial deve ser menor ou igual a data final.',
            'dt_fim.required' => 'A data final é obrigatória.',
            'dt_fim.date_format' => 'A data final deve estar no formato "dd/mm/aaaa".',
            'dt_fim.before' => 'A data final deve ser menor que 19/01/2038.'
        ];
    }

    public function ValidarDadosApiColetaFixaBloq($data)
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
