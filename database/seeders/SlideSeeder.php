<?php

namespace Database\Seeders;

use App\Models\Slide;
use Illuminate\Database\Seeder;

class SlideSeeder extends Seeder
{
    protected array $slideData = [
        [
            'title' => 'HabboAcademy instalado con éxito',
            'description' => 'Programado con mucho café y esfuerzo por Nicollas#8412',
            'slug' => '#',
            'image_path' => 'https://i.imgur.com/Wx9WzhF.gif',
            'active' => 1,
            'fixed' => 1
        ],
        [
            'title' => 'HabboAcademy Discord',
            'description' => 'Entra en nuestro Discord',
            'slug' => 'https://discord.gg/dzjhdusRW2',
            'image_path' => 'https://images.habbo.com/web_images/habbo-web-articles/lpromo_genericval16.png',
            'active' => true,
            'new_tab' => true
        ]
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect($this->slideData)
            ->each(fn ($slide) => Slide::create($slide));
    }
}
