<?php
return [

    'pdf' => [
        'enabled' => true,
        'binary' => '/usr/local/bin/wkhtmltopdf',
        'timeout' => false,
        'options' => [
            'encoding' => 'UTF-8',
            'page-size' => 'A4',
            'orientation' => 'Landscape',
            'enable-local-file-access' => true,
        ],
        'env' => [],
    ],

    'image' => [
        'enabled' => true,
        'binary' => '/usr/local/bin/wkhtmltopdf',
        'timeout' => false,
        'options' => [],
        'env'     => [],
    ],

];