<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ApiKey model
    |--------------------------------------------------------------------------
    |
    | If you're extending the ApiKey model, define the namespace of the class
    | here.
    |
    */

    'model'                => 'Chrisbjr\ApiGuard\Models\ApiKey',

    /*
    |--------------------------------------------------------------------------
    | Key name
    |--------------------------------------------------------------------------
    |
    | This is the name of the variable that will provide us the API key in the
    | header
    |
    */

    'keyName'              => 'X-Authorization',
];