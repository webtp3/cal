<?php

$EM_CONF['cal'] = [
    'title' => 'Calendar Base',
    'description' => 'A calendar combining all the functions of the existing calendar extensions plus adding some new features. It is based on the ical standard',
    'category' => 'plugin',
    'version' => '2.0.0-dev',
    'state' => 'stable',
    'createDirs' => 'uploads/tx_cal/pics,uploads/tx_cal/ics,uploads/tx_cal/media',
    'author' => 'Mario Matzulla, Jeff Segars, Franz Koch, Thomas Kowtsch',
    'author_email' => 'mario@matzullas.de, jeff@webempoweredchurch.org, franz.koch@elements-net.de, typo3@thomas-kowtsch.de',
    'constraints' => [
        'depends' => [
            'typo3' => '8.9.99 - 10.9.99'
        ],
        'suggests' => []
    ]
];
