<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateArticleCategory extends FormRequest
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
        return  [
            'name' => ['required', 'string', 'min:6', 'max:255'],
            'icon' => ['nullable', 'image']
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Digite o nome da categoria',
            'name.string' => 'Digite o nome da categoria',
            'name.min' => 'El campo nombre debe tener al menos 6 caracteres',
            'name.max' => 'El campo nombre debe tener como máximo 255 caracteres',
            'icon.image' => 'El campo ícono debe ser del tipo imagen',
        ];
    }
}
