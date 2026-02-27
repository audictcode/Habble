<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateTopicComment extends FormRequest
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
            'active' => ['required', 'boolean'],
            'moderated' => ['required', 'in:pending,moderated']
        ];
    }

    public function messages()
    {
        return [
            'active.required' => 'El campo "active" no está presente en el formulario',
            'active.boolean' => 'Valor inválido en el campo "active"',
            'moderated.required' => 'El campo "moderado" no está presente en el formulario',
            'moderated.in' => 'Valor inválido en el campo "moderado"',
        ];
    }
}
