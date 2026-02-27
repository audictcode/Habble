<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Academy\Navigation;

class NavigationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $navigations = $this->getDefaultNavigations();

        collect($navigations)->each(
            function ($subNavigations, $navigationName) {

                $navigationObject = Navigation::create(
                    array_merge(
                        ['label' => $navigationName],
                        $this->getNavigationData($navigationName)
                    )
                );

                if (!$navigationObject) return;

                collect($subNavigations)->each(
                    function ($subNavigationName) use ($navigationObject) {

                        $navigationObject->subNavigations()->create([
                            'label' => $subNavigationName
                        ]);
                        
                    }
                );
            }
        );
    }

    public function getNavigationData(string $navigationLabel)
    {
        if ($navigationLabel == 'Inicio') {
            return [
                'order' => 0,
                'slug' => '/',
                'small_icon' => 'fas fa-house-user',
                'hover_icon' => asset('/images/menu/inicio.png')
            ];
        }

        if ($navigationLabel == 'HabboAcademy') {
            return [
                'order' => 1,
                'small_icon' => 'fab fa-hackerrank',
                'hover_icon' => asset('/images/menu/habble.png')
            ];
        }

        if ($navigationLabel == 'Placas') {
            return [
                'order' => 2,
                'small_icon' => 'fab fa-hire-a-helper',
                'hover_icon' => asset('/images/menu/placas.png')
            ];
        }

        if ($navigationLabel == 'Contenidos') {
            return [
                'order' => 3,
                'small_icon' => 'fab fa-neos',
                'hover_icon' => asset('/images/menu/contenidos.png')
            ];
        }

        if ($navigationLabel == 'Fan Center') {
            return [
                'order' => 4,
                'small_icon' => 'fas fa-magic',
                'hover_icon' => asset('/images/menu/fancenter.png')
            ];
        }

        if ($navigationLabel == 'Radio') {
            return [
                'order' => 5,
                'small_icon' => 'fas fa-music',
                'hover_icon' => asset('/images/menu/radio.png')
            ];
        }
    }

    public function getDefaultNavigations()
    {
        return [
            'Inicio' => [],
            'HabboAcademy' => [
                'Sobre nosotros',
                'Equipo'
            ],
            'Placas' => [
                'Nuevas placas',
                'Verificación placas'
            ],
            'Contenidos' => [],
            'Fan Center' => [
                'Generador de avatar'
            ],
            'Radio' => [
                'Horarios',
                'Sé locutor'
            ]
        ];
    }
}
