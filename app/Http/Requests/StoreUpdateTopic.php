<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateTopic extends FormRequest
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
            'title' => ['required', 'min:6', 'max:80'],
            'content' => ['required', 'min:10', 'max:5000'],
            'category' => ['required', 'numeric', 'exists:topics_categories,id']
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Ingresa un título',
            'title.min' => 'Ingresa un título',
            'title.max' => 'Título demasiado largo, usa uno más corto',
            'content.required' => 'Ingresa tu tema',
            'content.min' => 'Ingresa tu tema',
            'content.max' => 'Tema demasiado largo, usa uno más corto',
            'category.required' => 'Informe a categoria',
            'category.numeric' => 'Informe a categoria',
            'category.exists' => 'Categoría no encontrada'
        ];
    }
}
