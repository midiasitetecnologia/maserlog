<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class MotoristaRequest extends FormRequest
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

        $rules =
            [
                'cpf' => 'required|documento:cpf|unique:motorista,cpf,' . $id,                
                'nome' => 'required|max:60',
                'ativo' => 'required|in:S,N'
            ];

        return $rules;
    }

    public function messages()
    {
        return [
            'cpf.required'  => 'O CPF deve ser informado.',
            'cpf.documento'  => 'Informe um CPF válido.',
            'id_login.unique' => 'O CPF informado já está cadastrado.',

            'nome.required' => 'O nome deve ser informado.',
            'nome.max' => 'O nome deve ter no máximo :max caracteres.',

            'ativo.required' => 'O campo "Ativo" é obrigatório.',
            'ativo.in' => 'O valor do campo "Ativo" deve estar entre :values'
        ];
    }

    public function ValidarDadosApiMotorista($data)
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
