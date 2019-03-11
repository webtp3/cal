<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

/**
 * Module 'Indexer' for the 'cal' extension.
 *
 */
$MCONF ['name'] = 'tools_calrecurrencegenerator';

$MCONF ['access'] = 'admin';
// MCONF["script"]="index.php";
$MCONF ['script'] = '_DISPATCH';

$MLANG ['default'] ['tabs_images'] ['tab'] = 'icon_tx_cal_indexer.gif';
$MLANG ['default'] ['ll_ref'] = 'LLL:EXT:cal/Resources/Private/Language/locallang_indexer_mod.xlf';

$GLOBALS ['LANG']->includeLLFile('EXT:cal/Resources/Private/Language/locallang_indexer.xlf');

$GLOBALS ['BE_USER']->modAccess($MCONF, 1); // This checks permissions and exits if the users has no permission for entry.
                               // DEFAULT initialization of a module [END]

// Make instance:
$SOBE = new \TYPO3\CMS\Cal\Backend\Modul\CalIndexerOld();
$SOBE->init();

$SOBE->main();
$SOBE->printContent();
