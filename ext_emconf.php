<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

$EM_CONF [$_EXTKEY] = [
        'title' => 'Calendar Base',
        'description' => 'A calendar combining all the functions of the existing calendar extensions plus adding some new features. It is based on the ical standard',
        'category' => 'plugin',
        'shy' => 0,
        'version' => '2.1.5',
        'loadOrder' => '',
        'state' => 'stable',
        'uploadfolder' => 0,
        'createDirs' => 'uploads/tx_cal/pics,uploads/tx_cal/ics,uploads/tx_cal/media',
        'clearCacheOnLoad' => 0,
        'author' => 'Mario Matzulla, Jeff Segars, Franz Koch, Thomas Kowtsch',
        'author_email' => 'mario@matzullas.de, jeff@webempoweredchurch.org, franz.koch@elements-net.de, typo3@thomas-kowtsch.de',
        'author_company' => '',
        'constraints' => [
                'depends' => [
                        'typo3' => '6.1.0-8.9.99'
                ],
                'suggests' => [
                        'css_styled_content' => '6.1.0-8.9.99',
                        'wec_map' => '',
                        'tt_address' => ''
                ]
        ]
];
