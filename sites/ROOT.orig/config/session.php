<?php

return array(
    'default' => 'cache_apc',

    'connections' => array(
        'cache_apc' => array(
            'driver' => 'cache',
            'cache_driver' => 'apc',
        ),
    ),

    'cookie' => 'SESSION',
);