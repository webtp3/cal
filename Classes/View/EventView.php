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
use TYPO3\CMS\Cal\Model\EventModel;
use TYPO3\CMS\Cal\Utility\Functions;

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 */
class EventView extends BaseView
{
    /**
     * Draws a single event.
     *
     * @param EventModel $event object to be drawn.
     * @param $getdate integer of the event
     * @param array $relatedEvents
     * @return string HTML output.
     */
    public function drawEvent(&$event, $getdate, $relatedEvents = []): string
    {
        $this->_init($relatedEvents);

        if ((int)$this->conf['activateFluid'] === 1) {
            return $this->renderWithFluid($event);
        }

        $page = Functions::getContent($this->conf['view.']['event.']['eventTemplate']);
        if ($page === '') {
            return '<h3>calendar: no template file found:</h3>' . $this->conf['view.']['event.']['eventTemplate'];
        }
        if ($event === null) {
            $rems['###EVENT###'] = $this->cObj->cObjGetSingle(
                $this->conf['view.']['event.']['event.']['noEventFound'],
                $this->conf['view.']['event.']['event.']['noEventFound.']
            );
        } elseif ($this->conf['preview']) {
            $rems['###EVENT###'] = $event->renderEventPreview();
        } else {
            $rems['###EVENT###'] = $event->renderEvent();
            if ((int)$this->conf['view.']['event.']['substitutePageTitle'] === 1) {
                $GLOBALS['TSFE']->page['title'] = $event->getTitle();
                $GLOBALS['TSFE']->indexedDocTitle = $event->getTitle();
            }
        }

        return $this->finish($page, $rems);
    }
}
