<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

/**
 * This class is a temporary store view helper for the Fluid templating engine.
 *
 * @version
 */
class Tx_Cal_ViewHelpers_TempStoreViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper
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
