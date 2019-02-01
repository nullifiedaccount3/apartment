<?php

function config()
{
    return [
        'Facilities' => [
            'Club House' => [
                'cost' => [
                    'hourly' => [
                        [
                            'from' => 10,
                            'to' => 16,
                            'price' => 100
                        ],
                        [
                            'from' => 16,
                            'to' => 22,
                            'price' => 500
                        ]
                    ]
                ]
            ],
            'Tennis Court' => [
                'cost' => [
                    'hourly' => [
                        [
                            'from' => 0,
                            'to' => 24,
                            'price' => 50
                        ]
                    ]
                ]
            ]
        ]
    ];
}
