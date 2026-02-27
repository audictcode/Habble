<?php

/**
 * Para colocar as alterações do arquivo em evidência, acesse a pasta principal e execute o comando:
 * 
 * > php artisan academy:config
 */

return [
    'defaultProfileImagePath' => 'profiles/default.png',

    'site' => [
        'maintenance' => false,
        'defaultImagerUrl' => 'https://www.habbo.com.br/habbo-imaging/avatarimage?&user=',

        'register' => [
            'activated' => true,
            'accountsPerIp' => 3,
            'captchaActivated' => (bool) env('RECAPTCHA_ENABLED', false),

            // Isso é necessário caso você queira bloquear registros de usuários se passando como staffs
            'blockedUsernames' => []
        ]
    ],

    'panel' => [
        /**
         * Coloque o nome de pessoas confiáveis e que fazem parte da equipe do seu site,
         * as funções em que são habilitadas para esses usuários podem ser críticas em mãos erradas.
         **/ 
        'superAdmins' => [
            'Admin'
        ],

        // Binario PHP CLI para ejecutar comandos en segundo plano desde HK.
        'console_php_binary' => env('HK_CONSOLE_PHP_BINARY', '/Applications/MAMP/bin/php/php8.3.30/bin/php'),
    ]
];
