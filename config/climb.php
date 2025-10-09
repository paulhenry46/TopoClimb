<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cotation
    |--------------------------------------------------------------------------
    |
    | Used to create grade of routes from french cotation system
    |
    */


    'default_cotation' => [
        'free' => false,
        'hint' => 'System is Fontainebleau scale : https://fr.wikipedia.org/wiki/Cotation_en_escalade#Cotation_fran%C3%A7aise_2',
        'points'=>
        ['3a' => 300, '3a+' => 310, '3b' => 320, '3b+' => 330, '3c' => 340, '3c+' => 350, 
        '4a' => 400, '4a+' => 410, '4b' => 420, '4b+' => 430, '4c' => 440, '4c+' => 450, 
        '5a' => 500, '5a+' => 510, '5b' => 520, '5b+' => 530, '5c' => 540, '5c+' => 550, 
        '6a' => 600, '6a+' => 610, '6b' => 620, '6b+' => 630, '6c' => 640, '6c+' => 650, 
        '7a' => 700, '7a+' => 710, '7b' => 720, '7b+' => 730, '7c' => 740, '7c+' => 750, 
        '8a' => 800, '8a+' => 810, '8b' => 820, '8b+' => 830, '8c' => 840, '8c+' => 850, 
        '9a' => 900, '9a+' => 910, '9b' => 920, '9b+' => 930, '9c' => 940, '9c+' => 950,]],
    
    'default_cotation_reverse' => [
            300 => '3a', 310 => '3a+', 320 => '3b', 330 => '3b+', 340 => '3c', 350 => '3c+',
            400 => '4a', 410 => '4a+', 420 => '4b', 430 => '4b+', 440 => '4c', 450 => '4c+',
            500 => '5a', 510 => '5a+', 520 => '5b', 530 => '5b+', 540 => '5c', 550 => '5c+',
            600 => '6a', 610 => '6a+', 620 => '6b', 630 => '6b+', 640 => '6c', 650 => '6c+',
            700 => '7a', 710 => '7a+', 720 => '7b', 730 => '7b+', 740 => '7c', 750 => '7c+',
            800 => '8a', 810 => '8a+', 820 => '8b', 830 => '8b+', 840 => '8c', 850 => '8c+',
            900 => '9a', 910 => '9a+', 920 => '9b', 930 => '9b+', 940 => '9c', 950 => '9c+',
    ],

    'site_1_cotation' =>
    ['free'=> true, //or false
    'hint' => 'very easy, easy, medium, hard, very hard, impossible',
    'points' =>[//always betwwen 300 and 950.
        'very easy' => 300, 
        'easy' => 500, 
        'medium' => 600, 
        'hard' => 700,
         'very hard' => 800,
        'impossible' => 900,]],
    
    'points_cotation_reverse' => [
            300 => 'very easy', 
            500 => 'easy', 
            600 => 'medium', 
            700 => 'hard', 
            800 => 'very hard', 
            900 => 'impossible',
    ],

    /*
    |--------------------------------------------------------------------------
    | Available colors
    |--------------------------------------------------------------------------
    |
    | This value determines the colors available and their hex code.
    |
    */

    'colors' => [
        'red' => '#ef4444',
        'blue' => '#3b82f6',
        'green' => '#22c55e',
        'yellow' => '#fde047',
        'purple' => '#d8b4fe',
        'pink' => '#f9a8d4',
        'gray' => '#d1d5db',
        'black' => '#000000',
        'white' => '#ffffff',
        'emerald' => '#6ee7b7',
        'orange' => '#fdba74',
        'amber' => '#fbbf24',
        'teal' => '#00bba7',
        'lime' => '#7ccf00',
        'cyan' => '#00b8db',
        'sky' => '#00a6f4',
        'indigo' => '#615fff',
        'violet' => '#8e51ff',
        'fuchsia' => '#d946ef',
        'rose' => '#f43f5e',
        'slate' => '#64748b',
        'gray' => '#6b7280',
        'zinc' => '#71717a'
    ],
    'colorsHSV' => [
        'red' => [[0, 72, 94]],
    'blue' => [[217, 76, 96]],
    'green' => [[142, 83, 77]],
    'yellow' => [[50, 72, 99]],
    'purple' => [[269, 29, 100]],
    'pink' => [[327, 33, 98]],
    'gray' => [[220, 16, 50]],
    'black' => [[0, 0, 0]],
    'white' => [[0, 0, 100]],
    'emerald' => [[156, 52, 91]],
    'orange' => [[31, 54, 99]],
    'amber' => [[43, 86, 98]],
    'teal' => [[174, 100, 73]],
    'lime' => [[84, 100, 81]],
    'cyan' => [[190, 100, 86]],
    'sky' => [[199, 100, 96]],
    'indigo' => [[241, 63, 100]],
    'violet' => [[261, 68, 100]],
    'fuchsia' => [[292, 71, 94]],
    'rose' => [[350, 74, 96]],
    'slate' => [[215, 28, 55]],
    'zinc' => [[240, 7, 48]],],

    /*
    |--------------------------------------------------------------------------
    | Interval of HSV colors used to filter picture. Created from HSV value of 
    | color with improved tolerance for varying lighting conditions.
    | Lower V values allow detection in darker environments.
    | Lower S values allow detection in brighter/washed-out environments.
    |--------------------------------------------------------------------------
    */

    'colorsInterval' => [
    'red' => [[[0, 30, 20], [20, 100, 100]], [[340, 30, 20], [365, 100, 100]]], // Improved for varying light
    'blue' => [[185, 30, 20], [235, 100, 100]], // Widened H, lower S and V for better detection
    'green' => [[100, 25, 20], [170, 100, 100]], // Widened range for better tolerance
    'yellow' => [[30, 25, 20], [70, 100, 100]], // Lower S and V for varying conditions
    'purple' => [[250, 10, 20], [290, 70, 100]], // Improved range
    'pink' => [[305, 10, 20], [345, 70, 100]], // Wider range
    'gray' => [[0, 0, 15], [360, 30, 85]], // Full hue range with low saturation
    'black' => [[0, 0, 0], [360, 100, 30]], // Low value for black
    'white' => [[0, 0, 70], [360, 20, 100]], // High value, low saturation for white
    'emerald' => [[135, 20, 20], [175, 100, 100]], // Green-based, improved
    'orange' => [[10, 25, 20], [50, 100, 100]], // Between red and yellow
    'amber' => [[25, 40, 20], [60, 100, 100]], // Yellow-orange, improved
    'teal' => [[155, 40, 20], [195, 100, 100]], // Blue-green, improved
    'lime' => [[65, 40, 20], [105, 100, 100]], // Yellow-green, improved
    'cyan' => [[170, 40, 20], [210, 100, 100]], // Blue variant, improved
    'sky' => [[180, 40, 20], [220, 100, 100]], // Light blue, improved
    'indigo' => [[220, 25, 20], [260, 100, 100]], // Deep blue-purple, improved
    'violet' => [[240, 30, 20], [280, 100, 100]], // Purple variant, improved
    'fuchsia' => [[270, 30, 20], [310, 100, 100]], // Magenta-purple, improved
    'rose' => [[330, 30, 20], [360, 100, 100]], // Pink-red, improved
    'slate' => [[195, 5, 20], [235, 40, 85]], // Gray-blue, improved
    'zinc' => [[220, 0, 15], [260, 25, 80]], // Neutral gray, improved
]

/* 'colorsInterval' => [
    'red' => [[0, 47, 69], [15, 97, 100]], 
    'blue' => [[202, 51, 71], [232, 100, 100]],
    'green' => [[117, 48, 42], [167, 100, 100]], // Adjusted with larger tolerance
    'yellow' => [[35, 47, 74], [65, 97, 100]],
    'purple' => [[254, 4, 75], [284, 54, 100]],
    'pink' => [[312, 8, 73], [342, 58, 100]],
    'gray' => [[205, 0, 25], [235, 41, 75]],
    'black' => [[0, 0, 0], [360, 25, 25]],
    'white' => [[0, 0, 75], [360, 25, 100]],
    'emerald' => [[141, 27, 66], [171, 77, 100]],
    'orange' => [[16, 29, 74], [46, 79, 100]],
    'amber' => [[28, 61, 73], [58, 100, 100]],
    'teal' => [[159, 75, 48], [189, 100, 98]],
    'lime' => [[69, 75, 56], [99, 100, 100]],
    'cyan' => [[175, 75, 61], [205, 100, 100]],
    'sky' => [[184, 75, 71], [214, 100, 100]],
    'indigo' => [[226, 38, 75], [256, 88, 100]],
    'violet' => [[246, 43, 75], [276, 93, 100]],
    'fuchsia' => [[277, 46, 69], [307, 96, 100]],
    'rose' => [[335, 49, 71], [360, 99, 100]],
    'slate' => [[200, 3, 30], [230, 53, 80]],
    'zinc' => [[225, 0, 23], [255, 32, 73]],
] */


];
