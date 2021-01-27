<?php
return [
	'prefix' => 'ab',
	'experiments' => [
        'v3_5' => [
            'cart' => [
                'percent' => 100,
                'variant' => [
                    'a' => 50,
                    'b' => 50,
                    'c' => 0,
                    'd' => 0,
                ],
            ],
            'home_you_may_also_like' => [
                'percent' => 100,
                'variant' => [
                    'a' => 0,
                    'b' => 100
                ],
            ],
        ],
        'v3_6' => [
            'cart' => [
                'percent' => 100,
                'variant' => [
                    'a' => 50,
                    'b' => 50,
                    'c' => 0,
                    'd' => 0,
                ],
            ],
            'home_you_may_also_like' => [
                'percent' => 100,
                'variant' => [
                    'a' => 0,
                    'b' => 100
                ],
            ],
        ],
        
	],
];