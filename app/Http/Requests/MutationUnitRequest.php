<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MutationUnitRequest extends FormRequest
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
            'city' => 'required',
            'cnes' => 'required|digits:7',
            'description' => 'required',
        ];

    }

    public function messages()
    {
        return [
            'city.required' => 'A nova UBS deve ser associada a uma cidade',
            'cnes.digits' => 'Por favor, insira exatamente 7 digitos',
            'cnes.unique' => 'O número CNES inserido já está cadastrado',
        ];
    }
}
