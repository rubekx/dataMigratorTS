<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectSolicitation extends FormRequest
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

    public function rules()
    {
        return [
            'solicitation_id' => 'required',
            'profile_id' => 'required',
            'description' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'description.required' => 'O campo Motivo da Observação não pode ser vazio',
        ];
    }
}
