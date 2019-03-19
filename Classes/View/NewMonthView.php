<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\View;

use TYPO3\CMS\Cal\Controller\Calendar;
use TYPO3\CMS\Cal\Model\CalDate;
use TYPO3\CMS\Cal\Model\EventModel;
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
class NewMonthView extends NewTimeView
{
    protected $weeks;
    protected $maxWeeksInYear = 52;
    protected $monthStartWeekdayNum;
    protected $monthLength;

    /**
     * Constructor.
     * @param $month
     * @param $year
     */
    public function __construct($month, $year)
    {
        parent::__construct();
        $this->setMySubpart('MONTH_SUBPART');
        $this->setMonth(intval($month));
        $this->setYear(intval($year));
        $this->generateWeeks();
        $controller = &Registry::Registry('basic', 'controller');
        $controller->cache->set($month . '_' . $year, serialize($this), 'month');
    }

    /**
     * @param $month
     * @param $year
     * @return NewMonthView
     */
    public static function getMonthView($month, $year): self
    {
        $controller = &Registry::Registry('basic', 'controller');
        $controller->cache->get($month . '_' . $year);
        return new self($month, $year);
    }

    private function generateWeeks()
    {
        $date = new CalDate();
        $date->setDay(1);
        $date->setMonth($this->getMonth());
        $date->setYear($this->getYear());
        $this->monthStartWeekdayNum = $date->format('w');
        $this->monthLength = $date->getDaysInMonth();
        $monthEnd = Calendar::calculateEndMonthTime($date);

        $weekEnd = $monthEnd->getWeekOfYear();
        $newDate = Calendar::calculateStartWeekTime($date);

        $this->weeks = [];
        $weekNumber = $newDate->getWeekOfYear();

        if ($weekEnd === 1 && $this->getMonth() === 12) {
            do {
                if ($weekNumber === $weekEnd) {
                    $this->weeks[($newDate->getYear() + 1) . '_' . $weekNumber] = new NewWeekView(
                        $weekNumber,
                        $newDate->getYear() + 1,
                        $this->getMonth()
                    );
                } else {
                    $this->weeks[$newDate->getYear() . '_' . $weekNumber] = new NewWeekView(
                        $weekNumber,
                        $newDate->getYear(),
                        $this->getMonth()
                    );
                }
                $newDate->addSeconds(86400 * 7);
                $weekNumber = $newDate->getWeekOfYear();
                $weekNumberTmp = $weekNumber;
                if ($weekNumber !== $weekEnd) {
                    $weekNumberTmp = 0;
                }
            } while ($weekNumberTmp <= $weekEnd && $newDate->getYear() === $this->getYear());
        } elseif ($this->getMonth() === 1) {
            do {
                if ($weekNumber > 6) {
                    $this->weeks[$newDate->getYear() . '_' . $weekNumber] = new NewWeekView(
                        $weekNumber,
                        $newDate->getYear(),
                        $this->getMonth()
                    );
                } else {
                    $this->weeks[$this->getYear() . '_' . $weekNumber] = new NewWeekView(
                        $weekNumber,
                        $this->getYear(),
                        $this->getMonth()
                    );
                }
                $newDate->addSeconds(86400 * 7);
                $weekNumber = $newDate->getWeekOfYear();
            } while ($weekNumber <= $weekEnd && $newDate->getYear() === $this->getYear());
        } else {
            do {
                $this->weeks[$this->getYear() . '_' . $weekNumber] = new NewWeekView(
                    $weekNumber,
                    $newDate->getYear(),
                    $this->getMonth()
                );
                $newDate->addSeconds(86400 * 7);
                $weekNumber = $newDate->getWeekOfYear();
            } while ($weekNumber <= $weekEnd && $newDate->getYear() === $this->getYear());
        }
        $this->maxWeeksInYear = max($this->maxWeeksInYear, $weekNumber);
    }

    /**
     * @param EventModel $event
     */
    public function addEvent(&$event)
    {
        $eventStartWeek = $event->getStart()->getWeekOfYear();
        $eventEndWeek = $event->getEnd()->getWeekOfYear();
        $eventStartYear = $event->getStart()->year;
        $eventEndYear = $event->getEnd()->year;
        if (($eventStartWeek === 52 || $eventStartWeek === 53) && $event->getStart()->month === 1) {
            $eventStartYear--;
        }
        if (($eventEndWeek === 52 || $eventEndWeek === 53) && $event->getEnd()->month === 1) {
            $eventEndYear--;
        }
        if ($eventStartWeek === 1 && $event->getStart()->month === 12) {
            $eventStartYear++;
        }
        if ($eventEndWeek === 1 && $event->getEnd()->month === 12) {
            $eventEndYear++;
        }
        do {
            if ($this->weeks[$eventStartYear . '_' . $eventStartWeek]) {
                $this->weeks[$eventStartYear . '_' . $eventStartWeek]->addEvent($event);
            }
            $eventStartWeek++;
            if ($eventStartWeek > $this->maxWeeksInYear) {
                $eventStartWeek = 1;
                $eventStartYear++;
            }
        } while (!(($eventStartYear === $eventEndYear && $eventStartWeek > $eventEndWeek) || ($eventStartYear > $eventEndYear)));
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getWeeksMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $content = '';
        foreach ($this->weeks as $week) {
            $content .= $week->render($this->getTemplate());
        }
        $sims['###WEEKS###'] = $content;
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getWeekdaysMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->setMySubpart('MONTH_WEEKDAYS_SUBPART');
        if (DATE_CALC_BEGIN_WEEKDAY === 0) {
            $this->setMySubpart('SUNDAY_MONTH_WEEKDAYS_SUBPART');
        }
        $sims['###WEEKDAYS###'] = $this->render($this->getTemplate());
        $this->setMySubpart('MONTH_SUBPART');
    }

    /**
     * @param CalDate $dateObject
     */
    public function setSelected(&$dateObject)
    {
        if ($dateObject->year === $this->getYear() && $dateObject->month === $this->getMonth()) {
            $this->selected = true;

            $week = $this->weeks[$dateObject->year . '_' . $dateObject->getWeekOfYear()];
            if (is_object($week)) {
                $week->setSelected($dateObject);
            }
        }
    }

    /**
     * @param CalDate $dateObject
     */
    public function setCurrent(&$dateObject)
    {
        if ($dateObject->year === $this->getYear() && $dateObject->month === $this->getMonth()) {
            $this->current = true;

            $week = $this->weeks[$dateObject->year . '_' . $dateObject->getWeekOfYear()];
            if (is_object($week)) {
                $week->setCurrent($dateObject);
            }
        }
    }

    /**
     * @param $template
     * @param $sims
     * @param $rems
     * @param $wrapped
     * @param $view
     */
    public function getMonthTitleMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $current_month = new CalDate();
        $current_month->setMonth($this->getMonth());
        $current_month->setYear($this->getYear());
        $conf = &Registry::Registry('basic', 'conf');
        $sims['###MONTH_TITLE###'] = $current_month->format($conf['view.'][$view . '.']['dateFormatMonth']);
    }

    /**
     * @return bool
     */
    public function hasEvents(): bool
    {
        return !empty($this->getEvents()) || $this->getHasAlldayEvents();
    }
}
