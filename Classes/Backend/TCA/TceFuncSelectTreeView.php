<?php
namespace TYPO3\CMS\Cal\Backend\TCA;

use TYPO3\CMS\Backend\Tree\View\AbstractTreeView;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

/**
 * This function displays a selector with nested categories.
 * The original code is borrowed from the extension "Digital Asset Management" (tx_dam) author: Rene© Fritz <r.fritz@colorcube.de>
 */

/**
 * extend class \TYPO3\CMS\Backend\Tree\View\AbstractTreeView to change function wrapTitle().
 */
class TceFuncSelectTreeView extends AbstractTreeView
{
    public $TCEforms_itemFormElName = '';
    public $TCEforms_nonSelectableItemsArray = [];

    public function __construct()
    {
        parent::__construct();

        $this->init();
    }

    /**
     * wraps the record titles in the tree with links or not depending on if they are in the TCEforms_nonSelectableItemsArray.
     *
     * @param $title string title
     * @param $v array  array with uid and title of the current item.
     * @param $bank int
     * @return string wrapped title
     */
    public function wrapTitle($title, $v, $bank = 0): string
    {
        if ($v ['uid'] > 0) {
            if (in_array($v ['uid'], $this->MOUNTS, true) || in_array(
                $v ['uid'],
                $this->TCEforms_nonSelectableItemsArray,
                true
            )) {
                return '<a href="#" title="' . $v ['title'] . '"><span style="color:#999;cursor:default;">' . $title . '</span></a>';
            }
            $aOnClick = 'setFormValueFromBrowseWin(\'' . $this->TCEforms_itemFormElName . '\',' . $v ['uid'] . ',\'' . addslashes($title) . '\'); return false;';
            return '<a href="#" onclick="' . htmlspecialchars($aOnClick) . '" title="' . htmlentities($v ['title']) . '">' . $title . '</a>';
        }
        return $title;
    }

    /**
     * Get icon for the row.
     * If $this->iconPath and $this->iconName is set, try to get icon based on those values.
     *
     * @param  array		Item row.
     * @return string tag.
     */
    public function getIcon($row): string
    {
        if (in_array($row ['uid'], $this->MOUNTS, true)) {
            $this->table = 'tx_cal_calendar';
        }
        $return = parent::getIcon($row);
        $this->table = 'tx_cal_category';
        return $return;
    }

    /**
     * Returns the root icon for a tree/mountpoint (defaults to the globe)
     *
     * @param  array		Record for root.
     * @return string image tag.
     */
    public function getRootIcon($rec): string
    {
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        return $iconFactory->getIcon('cal-pagetree-root', Icon::SIZE_SMALL)->render();
    }
}
