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
class YearView extends MonthView
{
    /**
     * Draws the year view
     *
     * @param $master_array
     * @param $getdate
     * @return string HTML output.
     */
    public function drawYear(&$master_array, $getdate): string
    {
        $this->_init($master_array);

        $page = Functions::getContent($this->conf['view.']['year.']['yearTemplate']);
        if ($page === '') {
            return '<h3>calendar: no template file found:</h3>' . $this->conf['view.']['year.']['yearTemplate'] . '<br />Please check your template record and add both cal items at "include static (from extension)"';
        }
        $array = [];
        return $this->finish($page, $array);
    }
}
