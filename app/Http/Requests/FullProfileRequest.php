<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FullProfileRequest extends FormRequest
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
        return [
            'name' => 'required',
            'cpf' => 'required',
            'email' => 'required|email',
            'role_id' => 'required',
            'cbo_id' => 'required',
        ];

    }

    public function messages()
    {
        return [
            'name.required' => 'O campo nome é obrigatório',
            'cpf.required' => 'O campo CPF é obrigatório',
            // 'cpf.cpf' => 'Por favor, insira um CPF válido',
            'email.required' => 'O campo Email é obrigatório',
            'email.email' => 'Por favor, insira um email válido',
            'role_id.required' => 'Ocorreu um erro ao associar o cargo',
            'cbo_id.required' => 'Ocorreu um erro ao associar a profissão',
        ];
    }
}
