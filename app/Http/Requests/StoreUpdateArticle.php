<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateArticle extends FormRequest
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
        $rules = [
            'title' => ['required', 'string', 'min:6', 'max:255'],
            'description' => ['required', 'string', 'min:6', 'max:255'],
            'category' => ['required', 'numeric', 'exists:articles_categories,id'],
            'image' => ['required', 'image'],
            'content' => ['required', 'min:10']
        ];

        if($this->isMethod('PUT')) {
            $rules['image'] = ['nullable', 'image'];
            $rules['status'] = ['required', 'boolean'];
            $rules['fixed'] = ['required', 'boolean'];
            $rules['reviewed'] = ['required', 'boolean'];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'title.required' => 'Ingresa un título',
            'title.min' => 'Ingresa un título',
            'title.max' => 'Título demasiado largo, usa uno más corto',
            'description.required' => 'Ingresa una descripción',
            'description.min' => 'Ingresa una descripción',
            'description.max' => 'Descripción demasiado larga, usa una más corta',
            'category.required' => 'Informe a categoria',
            'category.numeric' => 'Informe a categoria',
            'category.exists' => 'La categoría no existe',
            'image.required' => 'Insira a imagem',
            'image.image' => 'Ingresa una imagen válida',
            'content.required' => 'Ingresa tu noticia',
            'content.min' => 'Ingresa tu noticia',
            'status.required' => 'Indica el estado de la noticia',
            'status.boolean' => 'Indica el estado de la noticia',
            'fixed.required' => 'Informe o campo "fixa"',
            'fixed.boolean' => 'Informe o campo "fixa"',
            'reviewed.required' => 'Informe o campo "revisada"',
            'reviewed.boolean' => 'Informe o campo "revisada"',
        ];
    }
}
