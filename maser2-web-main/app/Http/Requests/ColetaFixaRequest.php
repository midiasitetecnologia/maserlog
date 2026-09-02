<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class ColetaFixaRequest extends FormRequest
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
                'tipo_coleta' => 'required|in:D,C,M',
                'dt_ini' => 'required|date_format:Y-m-d|before_or_equal:dt_fim',
                'dt_fim' => 'required|date_format:Y-m-d|before:2038-01-19',
                'segunda' => 'required|in:S,N',
                'terca'   => 'required|in:S,N',
                'quarta'  => 'required|in:S,N',
                'quinta'  => 'required|in:S,N',
                'sexta'   => 'required|in:S,N',
                'sabado'  => 'required|in:S,N',
                'sis_carga' => 'required'
            ];

        return $rules;
    }

    public function messages()
    {
        return [

            'tipo_coleta.required' => 'O campo "Tipo de Coleta" é obrigatório.',
            'tipo_coleta.in' => 'O valor do campo "Tipo de Coleta" deve estar entre :values',

            'dt_ini.required' => 'A data inicial é obrigatória.',
            'dt_ini.date_format' => 'A data inicial deve estar no formato "aaaa-mm-dd".',
            'dt_ini.before_or_equal' => 'A data inicial deve ser menor ou igual a data final.',
            'dt_fim.required' => 'A data final é obrigatória.',
            'dt_fim.date_format' => 'A data final deve estar no formato "aaaa-mm-dd".',
            'dt_fim.before' => 'A data final deve ser menor que 19/01/2038.',

            'segunda.required' => 'O campo "Segunda" é obrigatório.',
            'segunda.in' => 'O valor do campo "Segunda" deve estar entre :values',

            'terca.required' => 'O campo "Terça" é obrigatório.',
            'terca.in' => 'O valor do campo "Terça" deve estar entre :values',

            'quarta.required' => 'O campo "Quarta" é obrigatório.',
            'quarta.in' => 'O valor do campo "Quarta" deve estar entre :values',

            'quinta.required' => 'O campo "Quinta" é obrigatório.',
            'quinta.in' => 'O valor do campo "Quinta" deve estar entre :values',

            'sexta.required' => 'O campo "Sexta" é obrigatório.',
            'sexta.in' => 'O valor do campo "Sexta" deve estar entre :values',

            'sabado.required' => 'O campo "Sábado" é obrigatório.',
            'sabado.in' => 'O valor do campo "Sábado" deve estar entre :values',

            'sis_carga.required' => 'O sistema de carga é obrigatório.',
        ];
    }

    public function ValidarDadosApiColetaFixa($data)
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
