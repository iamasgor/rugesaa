<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Association contact details
    |--------------------------------------------------------------------------
    |
    | Shown on the Contact and About pages. Contact form messages are emailed
    | to `email`. Leave a value empty to hide it on the site.
    |
    */

    'email' => env('RUGESAA_EMAIL'),
    'phone' => env('RUGESAA_PHONE'),
    'office_hours' => env('RUGESAA_OFFICE_HOURS'),

    'department' => [
        'chairman' => env('RUGESAA_CHAIRMAN_NAME', 'Dr. Md. Latifur Rahman Sarker'),
        'email' => env('RUGESAA_CHAIRMAN_EMAIL', 'lrsarker@ru.ac.bd'),
        'phone' => env('RUGESAA_DEPARTMENT_PHONE', '+88-02588864123'),
        'address' => 'Ground Floor, Sir Jagadish Chandra Bose Academic Building, University of Rajshahi, Rajshahi 6205',
        'latitude' => 24.3715141,
        'longitude' => 88.6363971,
    ],

    'social' => array_filter([
        'Facebook' => env('RUGESAA_FACEBOOK_URL'),
        'LinkedIn' => env('RUGESAA_LINKEDIN_URL'),
        'YouTube' => env('RUGESAA_YOUTUBE_URL'),
    ]),

];
