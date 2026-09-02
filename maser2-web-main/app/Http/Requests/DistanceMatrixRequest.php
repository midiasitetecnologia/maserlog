<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class DistanceMatrixRequest extends FormRequest
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
        $id = request()->get('id');

        $rules = [
            'api_service' => 'required|max:20',
            'api_account' => 'required|max:100',
            'api_key' => 'required|max:255|unique:distance_matrix,api_key,' . $id . ',id'
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'api_service.required' => 'A identificação do serviço é obrigatório.',
            'api_service.max' => 'A identificação do serviço deve ter no máximo :max caracteres.',
            'api_account.required' => 'A identificação da conta é obrigatória.',
            'api_account.max' => 'A identificação do conta deve ter no máximo :max caracteres.',
            'api_key.required' => 'A chave de API é obrigatória.',
            'api_key.max' => 'A chave de API deve ter no máximo :max caracteres.',
            'api_key.unique' => 'A chave de API informada já está cadastrada.'
        ];
    }

    public function ValidarDadosApiDistanceMatrix($data)
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
