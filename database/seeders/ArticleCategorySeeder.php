<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article\ArticleCategory;

class ArticleCategorySeeder extends Seeder
{
    protected $categories = [
        'HabboAcademy',
        'Novedades',
        'Promociones/Eventos',
        'Videos/Música',
        'Habbo',
        'Juegos',
        'Temas externos',
        'Otros'
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->categories as $category) {
            ArticleCategory::create([
                'name' => $category
            ]);
        }
    }
}
