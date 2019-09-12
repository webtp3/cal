<?php

namespace TYPO3\CMS\Cal\View;

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
use TYPO3\CMS\Cal\Utility\Functions;

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 */
class MonthView extends BaseView
{
    /**
     * Looks for month markers.
     *
     * @param array $master_array array to be drawn.
     * @param int $getdate integer of the event
     * @return string HTML output.
     */
    public function drawMonth(&$master_array, $getdate): string
    {
        // Resetting viewarray, to make sure we always get the current events
        $this->viewarray = false;
        $this->_init($master_array);
        $page = '';
        if ($this->conf['view.']['month.']['monthMakeMiniCal']) {
            $incFile = $GLOBALS['TSFE']->tmpl->getFileName($this->conf['view.']['month.']['monthMiniTemplate']);
            if ($incFile !== null && file_exists(PATH_site . $incFile)) {
                $page = Functions::getContent($this->conf['view.']['month.']['monthMiniTemplate']);
            }

            if ($page === '') {
                $page = $this->conf['view.']['month.']['monthMiniTemplate'];
                if (!preg_match('/###([A-Z0-9_|+-]*)###/', $page)) {
                    return '<h3>calendar: no template file found:</h3>' . $this->conf['view.']['month.']['monthMiniTemplate'] . '<br />Please check your template record and add both cal items at "include static (from extension)"';
                }
            }
        } else {
            $page = Functions::getContent($this->conf['view.']['month.']['monthTemplate']);
            if ($page === '') {
                return '<h3>calendar: no template file found:</h3>' . $this->conf['view.']['month.']['monthTemplate'] . '<br />Please check your template record and add both cal items at "include static (from extension)"';
            }
        }

        $rems = [];
        return $this->finish($page, $rems);
    }
}
