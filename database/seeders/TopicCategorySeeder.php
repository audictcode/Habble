<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Topic\TopicCategory;

class TopicCategorySeeder extends Seeder
{
    protected $categories = [
        'HabboAcademy',
        'Presentación',
        'Dudas',
        'Novedades',
        'Eventos/Promociones',
        'Videos/Música',
        'Habbo',
        'Juegos',
        'Sugerencias',
        'Humor',
        'Otros',
        'Intercambios y negocios',
        'Curiosidades'
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->categories as $category) {
            TopicCategory::create([
                'name' => $category
            ]);
        }
    }
}
