<?php

return [
    'Authentication' => [
        'bootstrap' => true,
    ],
    'Cake/Queue' => [
        'bootstrap' => true,
        'routes' => true,
    ],
    'Bake' => [
        'onlyCli' => false,
        'optional' => true,
    ],
    'Migrations' => [],
    'AdminTheme' => [
        'bootstrap' => true,
        'routes' => true,
        'optional' => true,
    ],
    'DefaultTheme' => [
        'bootstrap' => true,
        'routes' => true,
        'optional' => true,
    ],
    'ADmad/I18n' => [
        'bootstrap' => true,
        'routes' => true,
        'optional' => true,
    ],
    'DebugKit' => [
        'onlyDev' => true,
        'optional' => true,
    ],
    'MysqlNativePassword' => [],
    'Josegonzalez/Upload' => [],
    'All' => [
        'bootstrap' => true,
        'routes' => true,
        'optional' => true,
    ],
];
