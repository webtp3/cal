<?php

namespace TYPO3\CMS\Cal\TreeProvider;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

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
 * this class displays a tree selector with nested tt_news categories.
 */
class TreeHelperElement extends AbstractFormElement
{
    /**
     * @param $fName
     * @param $mode
     * @param $allowed
     * @param $itemArray
     * @param string $selector
     * @param array $params
     * @param string $onFocus
     * @param string $table
     * @param string $field
     * @param string $uid
     * @param array $config
     * @return mixed
     */
    public function getDbFileIcon(
        $fName,
        $mode,
        $allowed,
        $itemArray,
        $selector = '',
        $params = [],
        $onFocus = '',
        $table = '',
        $field = '',
        $uid = '',
        $config = []
    ) {
        return $this->dbFileIcons(
            $fName,
            $mode,
            $allowed,
            $itemArray,
            $selector,
            $params,
            $onFocus,
            $table,
            $field,
            $uid,
            $config
        );
    }

    /**
     * @param $itemKinds
     * @param $wizConf
     * @param $table
     * @param $row
     * @param $field
     * @param $PA
     * @param $itemName
     * @param $specConf
     * @param bool $RTE
     * @return mixed
     */
    public function getRenderWizards(
        $itemKinds,
        $wizConf,
        $table,
        $row,
        $field,
        $PA,
        $itemName,
        $specConf,
        $RTE = false
    ) {
        return $this->renderWizards($itemKinds, $wizConf, $table, $row, $field, $PA, $itemName, $specConf, $RTE);
    }

    /**
     * Handler for single nodes
     *
     * @return array As defined in initializeResultArray() of AbstractNode
     */
    public function render()
    {
        // TODO: Implement render() method.
    }
}
