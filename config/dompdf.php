<?php

return [

    'show_warnings' => false,

    'public_path' => public_path(),

    'convert_entities' => true,

    'font_dir' => storage_path('fonts'),
    'font_cache' => storage_path('fonts'),

    'temp_dir' => sys_get_temp_dir(),

    'chroot' => realpath(base_path()),

    'allowed_protocols' => [
        'file://' => ['rules' => []],
        'http://' => ['rules' => []],
        'https://' => ['rules' => []],
    ],

    'pdf_backend' => 'CPDF',

    'default_media_type' => 'screen',

    'default_paper_size' => 'a4',

    'default_paper_orientation' => 'portrait',

'default_font' => 'Tajawal',

'font_family' => [
    'Tajawal' => [
        'normal' => storage_path('fonts/Tajawal-Regular.ttf'),
        'bold'   => storage_path('fonts/Tajawal-Bold.ttf'),
    ],
],

    'dpi' => 96,

    'enable_php' => false,

    'enable_javascript' => false,

    'enable_remote' => true,

    'allowed_remote_hosts' => null,

    'font_height_ratio' => 1.1,

    'enable_html5_parser' => true,
];
