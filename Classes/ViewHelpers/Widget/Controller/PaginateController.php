<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\ViewHelpers\Widget\Controller;

/**
 * This file is part of the "news" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use TYPO3\CMS\Cal\Model\CalendarDateTime;
use TYPO3\CMS\Cal\Model\EventModel;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Paginate controller to create the pagination.
 * Extended version from fluid core
 *
 */
class PaginateController extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetController
{

    /**
     * @var CalendarDateTime
     */
    public $starttime;

    /**
     * @var CalendarDateTime
     */
    public $endtime;

    /**
     * @var array
     */
    protected $configuration = [
        'itemsPerPage' => 10,
        'insertAbove' => false,
        'insertBelow' => true,
        'maximumNumberOfLinks' => 99,
        'templatePath' => ''
    ];

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    protected $objects;

    /**
     * @var int
     */
    protected $currentPage = 1;

    /**
     * @var string
     */
    protected $templatePath = '';

    /**
     * @var int
     */
    protected $numberOfPages = 1;

    /**
     * @var int
     */
    protected $maximumNumberOfLinks = 99;

    /** @var int */
    protected $initialOffset = 0;
    /** @var int */
    protected $initialLimit = 0;
    /** @var int */
    protected $recordId = 0;

    /**
     * Initialize the action and get correct configuration
     *
     */
    public function initializeAction()
    {
        $this->objects = $this->widgetConfiguration['objects'];
        \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule(
            $this->configuration,
            (array)$this->widgetConfiguration['configuration'],
            true
        );

        $itemsPerPage = (integer)$this->configuration['itemsPerPage'];
        if ($itemsPerPage === 0) {
            throw new \RuntimeException(
                'The itemsPerPage is 0 which is not allowed. Please also add "list.paginate.itemsPerPage" to the TS setting settings.overrideFlexformSettingsIfEmpty!',
                1400741142
            );
        }
        $this->starttime = GeneralUtility::makeInstance(CalendarDateTime::class,'@'.time())->setTimezone(new \DateTimeZone(date('T')));
        $this->endtime =  GeneralUtility::makeInstance(CalendarDateTime::class, '@'.time())->setTimezone(new \DateTimeZone(date('T')))->add(new \DateInterval('P365D'));

        $this->numberOfPages = (int)ceil( $this->getCount($this->objects) / $itemsPerPage);
        $this->maximumNumberOfLinks = (integer)$this->configuration['maximumNumberOfLinks'];
        if (isset($this->configuration['templatePath']) && !empty($this->configuration['templatePath'])) {
            $this->templatePath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($this->configuration['templatePath']);
        }

        if (isset($this->widgetConfiguration['initial']['offset'])) {
            $this->initialOffset = (int)$this->widgetConfiguration['initial']['offset'];
        }
        if (isset($this->widgetConfiguration['initial']['limit'])) {
            $this->initialLimit = (int)$this->widgetConfiguration['initial']['limit'];
        }
        if (isset($this->widgetConfiguration['initial']['recordId'])) {
            $this->recordId = (int)$this->widgetConfiguration['initial']['recordId'];
        }
    }
    /**
     * @return int
     */
    public function getCount($objects): int
    {
        $count = 0;
        if (count($objects)) {

            // parse the master_array for "valid" events of the current listView and reference them in a separate array that is used for rendering
            // use array keys for the loops, so that references can be used and less memory is needed :)
            $master_array_keys = array_keys($objects);

            foreach ($master_array_keys as $cal_time) {
                // create a reference
                $event_times = &$objects[$cal_time];
                if (is_array($event_times)) {
                    $event_times_keys = array_keys($event_times);
                    foreach ($event_times_keys as $a_key) {
                        $a = &$event_times[$a_key];
                        if (is_array($a)) {
                            $a_keys = array_keys($a);
                            foreach ($a_keys as $uid) {
                                $event = &$a[$uid];

                                if (!is_object($event)) {
                                    unset($objects[$cal_time][$event_times][$a_keys]);
                                    continue;
                                }
                                if ((int)$this->conf['view.'][$this->conf['view'] . '.']['hideStartedEvents'] === 1 && $event->getStart()->before($this->starttime)) {
                                    unset($objects[$cal_time][$event_times][$a_keys]);
                                    continue;
                                }

                                if ($event->getEnd()->before($this->starttime) || $event->getStart()->after($this->endtime)) {
                                   // unset($objects[$cal_time][$event_times][$a_keys]);
                                    continue;
                                }
                                $count++;
                            }
                        }
                    }
                }
            }
        }
        return $count;
    }

    /**
     * Main action
     *
     * @param int $currentPage
     */
    public function indexAction($currentPage = 1)
    {
        if((int)GeneralUtility::_GP('tx__')['@widget_0']["currentPage"]>1)$currentPage = (int)GeneralUtility::_GP('tx__')['@widget_0']["currentPage"];
        // set current page
        $this->currentPage = (integer)$currentPage;
        if ($this->currentPage < 1) {
            $this->currentPage = 1;
        }

        if ($this->currentPage > $this->numberOfPages) {
            // set $modifiedObjects to NULL if the page does not exist
            $modifiedObjects = null;
        } else {
            // modify query
            $itemsPerPage = (integer)$this->configuration['itemsPerPage'];
        //    $query = $this->objects->getQuery();
// parse the master_array for "valid" events of the current listView and reference them in a separate array that is used for rendering
            // use array keys for the loops, so that references can be used and less memory is needed :)
           // $count = $this->getCount($this->objects);

//            if ($this->currentPage === $this->numberOfPages && $this->initialLimit > 0) {
//                $difference = $this->initialLimit - ((integer)($itemsPerPage * ($this->currentPage - 1)));
//                if ($difference > 0) {
//                    $query->setLimit($difference);
//                } else {
//                    $query->setLimit($itemsPerPage);
//                }
//            } else {
//                $query->setLimit($itemsPerPage);
//            }

            if ($this->currentPage > 1) {
                $offset = (integer)($itemsPerPage * ($this->currentPage - 1));
                $offset += $this->initialOffset;
                $modifiedObjects = array_slice ( $this->objects , $offset , $itemsPerPage , true ) ;
//                $query->setOffset($offset);
            } else{
               // $query->setOffset($this->initialOffset);
                $modifiedObjects = array_slice ( $this->objects , $this->initialOffset , $itemsPerPage , true ) ;

            }
           // $modifiedObjects = $query->execute();
        }

        if ($this->currentPage > 1) {
            $pageLabel = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'paginate_overall',
                'news',
                [
                    $this->currentPage,
                    $this->numberOfPages
                ]
            );
            $titleAddition = ' - ' . trim($pageLabel, '.');

            $GLOBALS['TSFE']->page['title'] .= $titleAddition;

            $registerProperties = [
                'currentPage' => $this->currentPage,
                'numberOfPages' => $this->numberOfPages,
                'titleAddition' => $titleAddition,
            ];
            \TYPO3\CMS\Cal\Utility\Page::setRegisterProperties(
                implode(',', array_keys($registerProperties)),
                $registerProperties,
                'newsPagination'
            );
        }

        $this->view->assign('contentArguments', [
            $this->widgetConfiguration['as'] => $modifiedObjects
        ]);
        $this->view->assign('configuration', $this->configuration);
        $this->view->assign('recordId', $this->recordId);
        $this->view->assign('pageId', $this->getCurrentPageId());
        $this->view->assign('pagination', $this->buildPagination());

        if (!empty($this->templatePath)) {
            $this->view->setTemplatePathAndFilename($this->templatePath);
        }
    }

    /**
     * Returns an array with the keys "pages", "current", "numberOfPages", "nextPage" & "previousPage"
     *
     * @return array
     */
    protected function buildPagination()
    {
        $this->calculateDisplayRange();
        $pages = [];
        for ($i = $this->displayRangeStart; $i <= $this->displayRangeEnd; $i++) {
            $pages[] = ['number' => $i, 'isCurrent' => $i === $this->currentPage];
        }
        $pagination = [
            'pages' => $pages,
            'current' => $this->currentPage,
            'numberOfPages' => $this->numberOfPages,
            'displayRangeStart' => $this->displayRangeStart,
            'displayRangeEnd' => $this->displayRangeEnd,
            'hasLessPages' => $this->displayRangeStart > 2,
            'hasMorePages' => $this->displayRangeEnd + 1 < $this->numberOfPages
        ];
        if ($this->currentPage < $this->numberOfPages) {
            $pagination['nextPage'] = $this->currentPage + 1;
        }
        if ($this->currentPage > 1) {
            $pagination['previousPage'] = $this->currentPage - 1;
        }
        return $pagination;
    }

    /**
     * If a certain number of links should be displayed, adjust before and after
     * amounts accordingly.
     *
     */
    protected function calculateDisplayRange()
    {
        $maximumNumberOfLinks = $this->maximumNumberOfLinks;
        if ($maximumNumberOfLinks > $this->numberOfPages) {
            $maximumNumberOfLinks = $this->numberOfPages;
        }
        $delta = floor($maximumNumberOfLinks / 2);
        $this->displayRangeStart = $this->currentPage - $delta;
        $this->displayRangeEnd = $this->currentPage + $delta - ($maximumNumberOfLinks % 2 === 0 ? 1 : 0);
        if ($this->displayRangeStart < 1) {
            $this->displayRangeEnd -= $this->displayRangeStart - 1;
        }
        if ($this->displayRangeEnd > $this->numberOfPages) {
            $this->displayRangeStart -= $this->displayRangeEnd - $this->numberOfPages;
        }
        $this->displayRangeStart = (integer)max($this->displayRangeStart, 1);
        $this->displayRangeEnd = (integer)min($this->displayRangeEnd, $this->numberOfPages);
    }

    /**
     * @return int
     */
    protected function getCurrentPageId()
    {
        if (is_object($GLOBALS['TSFE'])) {
            return (int)$GLOBALS['TSFE']->id;
        }
        return 0;
    }
}
