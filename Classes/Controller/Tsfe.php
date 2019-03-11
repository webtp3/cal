<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Controller;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Tsfe
 */
class Tsfe extends TypoScriptFrontendController
{
    /**
     * @param mixed $code
     * @param string $header
     * @param string $reason
     */
    public function pageNotFoundHandler($code, $header = '', $reason = '')
    {
        // do nothing
    }

    /**
     * @param string $reason
     * @param string $header
     */
    public function pageNotFoundAndExit($reason = '', $header = '')
    {
        // do nothing
    }
}
