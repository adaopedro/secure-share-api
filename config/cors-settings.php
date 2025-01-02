<?php

    return [
        'allow_credentials' => true,
        'allow_origin'      => ['*'],
        'allow_methods'     => ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS'],
        'allow_headers'     => ['DNT','X-Custom-Header','Keep-Alive','User-Agent','X-Requested-With','If-Modified-Since','Cache-Control','Content-Type','Content-Range','Range', 'Authorization'],
        'expose_headers'    => ['DNT', 'X-Custom-Header','Keep-Alive','User-Agent','X-Requested-With','If-Modified-Since','Cache-Control','Content-Type','Content-Range','Range'],
        'max_age'           => 60 * 60 * 24 * 20,
    ];