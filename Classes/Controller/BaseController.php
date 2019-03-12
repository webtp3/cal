<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Controller;

use TYPO3\CMS\Cal\Utility\Registry;

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
class BaseController
{
    public $cObj;
    public $local_cObj;
    public $conf;
    public $rightsObj;
    public $controller;
    public $prefixId = 'tx_cal_controller';

    public function __construct()
    {
        $this->cObj = &Registry::Registry('basic', 'cobj');
        $this->local_cObj = &Registry::Registry('basic', 'local_cobj');
        $this->controller = &Registry::Registry('basic', 'controller');
        $this->conf = &Registry::Registry('basic', 'conf');
        $this->rightsObj = &Registry::Registry('basic', 'rightscontroller');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return get_class($this);
    }
}
