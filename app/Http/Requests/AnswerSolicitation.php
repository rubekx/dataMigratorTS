<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnswerSolicitation extends FormRequest
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
            'solicitation_id' => 'required',
            'profile_id' => 'required',
            'complement' => 'required',
            'direct_answer' => 'required',
            'answer_attributes' => 'required',
            'permanent_education' => 'required',
            'references' => 'required',
            'tags' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'direct_answer.required' => 'O campo Resposta Direta não pode ser vazio',
            'complement.required' => 'O campo Complemento não pode ser vazio',
            'answer_attributes.required' => 'O campo Atributos não pode ser vazio',
            'permanent_education.required' => 'O campo Resposta Educação Permanente não pode ser vazio',
            'references.required' => 'O campo Referências não pode ser vazio',
            'tags.required' => 'O campo Termos de Busca não pode ser vazio',
        ];
    }


}
