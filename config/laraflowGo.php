<?php

return [

    'workflow' => [

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

    ]

];