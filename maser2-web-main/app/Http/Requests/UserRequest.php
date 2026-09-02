<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class UserRequest extends FormRequest
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
        $user_type = request()->get('user_type');
        $active = request()->get('active');

        $rules =
            [
                'user_type' => 'required|in:C,M,U',
                'name' => 'required|max:100',                
                'active' => 'required|in:S,N,B'
            ];

        if ($user_type == "M") {
            $rules = array_merge($rules, [
                'email' => 'required|max:100|unique:users,email,' . $id,
            ]);
        } else {
            $rules = array_merge($rules, [
                'email' => 'required|email|max:100|unique:users,email,' . $id,
            ]);
        }

        if ($active == "S"){
            $rules = array_merge($rules, [
                'password' => 'required|min:6|max:100|confirmed',
                'password_confirmation' => 'required|min:6|max:100',                
            ]);            
        }

        return $rules;
    }

    public function messages()
    {
        $user_type = request()->get('user_type');

        $messages = [
            'name.required' => 'O nome deve ser informado.',
            'name.max' => 'O nome deve ter no máximo :max caracteres.',            

            'password.required' => 'A senha deve ser informada.',
            'password.min' => 'A senha deve ter no mínimo :min caracteres.',
            'password.max' => 'A senha deve ter no máximo :max caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere com a senha informada.',

            'password_confirmation.required' => 'A confirmação da senha deve ser informada.',
            'password_confirmation.min' => 'A confirmação da senha deve ter no mínimo :min caracteres.',
            'password_confirmation.max' => 'A confirmação da senha deve ter no máximo :max caracteres.',

            'user_type.required' => 'O campo tipo de usuário é obrigatório.',
            'user_type.in' => 'O valor do campo tipo de usuário deve estar entre :values',

            'active.required' => 'O campo ativo é obrigatório.',
            'active.in' => 'O valor do campo ativo deve estar entre :values'
        ];

        if ($user_type == "M") {
            $messages = array_merge($messages, [
                'email.required' => 'O ID Login deve ser informado.',
                'email.max' => 'O ID Login deve ter no máximo :max caracteres.',
                'email.unique' => 'O ID Login informado já está cadastrado.',                
            ]);
        } else {
            $messages = array_merge($messages, [
                'email.required' => 'O email deve ser informado.',
                'email.max' => 'O email deve ter no máximo :max caracteres.',
                'email.unique' => 'O email informado já está cadastrado.',
                'email.email' => 'O email deve ser um email válido',
            ]);
        }

        return $messages;
    }

    public function ValidarDadosApiUser($data)
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
