<?php

return [

    /*
    |--------------------------------------------------------------------------
    | State machine configuration
    |--------------------------------------------------------------------------
    |
    | This array is the default state machine configuration. The value is used
    | when the Eloquent object which responsible for the state machine try
    | to save the required configuration, but the user didn't add that.
    |
    */

    'configuration' => [

        'default' => [

            'property_path' => 'last_state',
            'steps' => [],
            'transitions' => [],
            'callbacks' => [
                'guard' => [],
                'before' => [],
                'after' => [],
            ]

        ]

    ],

    /*
    |--------------------------------------------------------------------------
    | Laraflow validators
    |--------------------------------------------------------------------------
    |
    | This array is the list of the default validators. You can add these
    | to your state machine if you want to check one/more attribute(s)
    | before the status change. You can use ony Laravel validators.
    |
    */
    'validators' => [

        'required' => [
            'name' => 'Field required',
            'description' => 'The field under validation must be present in the input data and not empty.',
            'validator' => 'required',
        ],

        'string' => [
            'name' => 'Field must be string',
            'description' => 'The field under validation must be a string. If you would like to allow the field to also be null, you should assign the nullable rule to the field.',
            'validator' => 'string',
        ],

        'numeric' => [
            'name' => 'Field must be a number',
            'description' => 'The field under validation must be numeric.',
            'validator' => 'numeric',
        ],

        'timezone' => [
            'name' => 'Field must be a valid timezone',
            'description' => 'The field under validation must be a valid timezone identifier according to the  timezone_identifiers_list PHP function.',
            'validator' => 'timezone',
        ]

    ],

    /*
    |--------------------------------------------------------------------------
    | Laraflow callbacks
    |--------------------------------------------------------------------------
    |
    | This array is the list of the post and pre function which can be assigned
    | to the transitions. Every callback has to be an array and each one of
    | them has three manadatory attributes. Name, description, class.
    |
    */
    'callbacks' => []

];
