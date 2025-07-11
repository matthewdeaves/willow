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
        'routes' => true,
        'optional' => true,
    ],
    'DefaultTheme' => [],
    'ADmad/I18n' => [],
    'DebugKit' => [
        'onlyDev' => true,
        'optional' => true,
    ],
    'MysqlNativePassword' => [],
    'Josegonzalez/Upload' => [],
];
