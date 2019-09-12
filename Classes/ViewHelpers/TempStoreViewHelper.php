<?php
/**
 * This file is part of the TYPO3 extension Calendar Base (cal).
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 extension Calendar Base (cal) project - inspiring people to share!
 */
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * This class is a temporary store view helper for the Fluid templating engine.
 *
 * @version
 */
class Tx_Cal_ViewHelpers_TempStoreViewHelper extends AbstractViewHelper
{

    /**
     * @var array
     */
    private static $store = [];

    /**
     * Renders some classic dummy content: Lorem Ipsum...
     *
     * @param string $key The key
     * @param object $set The object to set
     * @param object $get The object to set
     * @return object If $get is defined an object will be returned (if found in the store)
     */
    public function render($key = null, $set = null, $get = null)
    {
        if ($key != null && $set != null) {
            self::$store[$key] = $set;
        } elseif ($get != null) {
            return self::$store[$get];
        }
    }
}
