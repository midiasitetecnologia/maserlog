<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class ClienteRequest extends FormRequest
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
    public function rules($param_tipo_pes = null)
    {       
        $empresa = request()->get('empresa');
        $codigo = request()->get('codigo');

        //A rotina rules é chamada pela Api e pela interface web. Quando chamada pela Api temos que
        //passar o tipo de pessoa pois o laravel não tem acesso aos valores da requisição na rotina Rules.
        //Quando vem da interface web o parâmetro "$param_tipo_pes" vai ser nullo e o valor é pego 
        //através do get da requisição
        if (!empty($param_tipo_pes)) {
            $tipo_pessoa = $param_tipo_pes;
        } else {
            $tipo_pessoa = request()->get('tipo_pessoa');
        }

        $documentType = $tipo_pessoa == 'F' ? 'cpf' : 'cnpj';

        $rules =
            [
                //Esta regra diz que "empresa" deve ser único na tabela "cliente", mas apenas onde "codigo" igual a "$codigo".
                'empresa' => 'required|unique:cliente,empresa,NULL,id,codigo,' . $codigo,

                //Esta regra diz que "codigo" deve ser único na tabela "cliente", mas apenas onde "empresa" igual a "$empresa".
                'codigo' => 'required|unique:cliente,codigo,NULL,id,empresa,' . $empresa,

                'tipo_pessoa' => 'required',
                'cpf_cnpj' => "documento:$documentType",
            ];

        return $rules;
    }

    public function messages()
    {
        return [
            'empresa.required' => 'A empresa deve ser informada.',
            'empresa.unique' => 'A empresa informada já está cadastrada para este código.',
            'codigo.required' => 'O código deve ser informado.',
            'codigo.unique' => 'O código informado já está cadastrado para esta empresa.',

            'tipo_pessoa.required' => 'O tipo de pessoa deve ser informado.',
            'cpf_cnpj.documento'  => 'Informe um CPF / CNPJ válido.'
        ];
    }

    public function ValidarDadosApiCliente($data)
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
