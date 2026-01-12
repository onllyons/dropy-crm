<?php

return [
    'tenants' => [
        'default' => env('DROPY_TENANT_DEFAULT', 'onllyons_en'),
        'allowed' => [
            'onllyons_en' => 'English',
            'onllyons_de' => 'German',
        ],
    ],
];
