<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DispAppRequest extends FormRequest
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
                'id_disp' => 'required|max:100',
                'descricao' => 'max:255',
                'plataforma' => 'max:15',
                'versao_so' => 'max:50',
                'versao_app' => 'max:15',
                'push_token' => 'max:255'
            ];

        return $rules;
    }

    public function messages()
    {
        return [
            'id_disp.required' => 'A identificação do dispositivo é obrigatória.',
            'id_disp.max' => 'A identificação do dispositivo deve ter no máximo :max caracteres.',
            'descricao.max' => 'A descrição do dispositivo deve ter no máximo :max caracteres.',
            'plataforma.max' => 'A plataforma do dispositivo deve ter no máximo :max caracteres.',
            'versao_so.max' => 'A versão do sistema operacional do dispositivo deve ter no máximo :max caracteres.',
            'versao_app.max' => 'A versão do aplicativo do dispositivo deve ter no máximo :max caracteres.',
            'push_token.max' => 'O Push Token deve ter no máximo :max caracteres.'
        ];
    }
}
