<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

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
use TYPO3\CMS\Cal\Model\LocationModel;
use TYPO3\CMS\Cal\Utility\Functions;

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 */
class LocationView extends BaseView
{
    /**
     * Draws a location.
     *
     * @param LocationModel $location       The location to be drawn.
     * @param array $relatedEvents
     * @return string HTML output.
     */
    public function drawLocation($location, $relatedEvents = []): string
    {
        $this->_init($relatedEvents);
        $page = Functions::getContent($this->conf['view.']['location.']['locationTemplate']);
        if ($page === '') {
            return $this->createErrorMessage(
                'No location template file found at: >' . $this->conf['view.']['location.']['locationTemplate'] . '<.',
                'Please make sure the path is correct and that you included the static template and double-check the path using the Typoscript Object Browser.'
            );
        }
        $rems = [];
        if (is_object($location)) {
            $rems['###LOCATION###'] = $location->renderLocation();
            if ($this->conf['view.']['location.']['substitutePageTitle'] === 1) {
                $GLOBALS['TSFE']->page['title'] = $location->getName();
                $GLOBALS['TSFE']->indexedDocTitle = $location->getName();
            }
        } else {
            $rems['###LOCATION###'] = $this->cObj->cObjGetSingle(
                $this->conf['view.']['location.']['noLocationFound'],
                $this->conf['view.']['location.']['noLocationFound.']
            );
        }
        return $this->finish($page, $rems);
    }
}
