<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MutationTeamRequest extends FormRequest
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
            'description' => 'required',
            'ine' => 'required',
            'city_id' => 'required',
            'unit_id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'description.required' => 'O campo nome é obrigatório',
            'ine.required'         => 'O campo INE é obrigatório',
            'city_id.required'     => 'O campo Cidade é obrigatório',
            'unit_id.required'     => 'O campo Unidade é obrigatório',
        ];
    }
}
