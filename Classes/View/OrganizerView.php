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
use TYPO3\CMS\Cal\Model\Organizer;
use TYPO3\CMS\Cal\Utility\Functions;

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 */
class OrganizerView extends BaseView
{
    /**
     * Draws a organizer.
     *
     * @param Organizer $organizer        The organizer to be drawn.
     * @param array $relatedEvents
     * @return string HTML output.
     */
    public function drawOrganizer($organizer, $relatedEvents = []): string
    {
        $this->_init($relatedEvents);
        $page = Functions::getContent($this->conf['view.']['organizer.']['organizerTemplate']);
        if ($page === '') {
            return '<h3>calendar: no organizer template file found:</h3>' . $this->conf['view.']['organizer.']['organizerTemplate'];
        }
        if (is_object($organizer)) {
            $rems['###ORGANIZER###'] = $organizer->renderOrganizer();
            if ((int)$this->conf['view.']['event.']['substitutePageTitle'] === 1) {
                $GLOBALS['TSFE']->page['title'] = $organizer->getName();
                $GLOBALS['TSFE']->indexedDocTitle = $organizer->getName();
            }
        } else {
            $rems['###ORGANIZER###'] = $this->cObj->stdWrap(
                $this->controller->pi_getLL('l_no_organizer_results'),
                $this->conf['view.']['organizer.']['noOrganizerFound_stdWrap.']
            );
        }

        return $this->finish($page, $rems);
    }
}
