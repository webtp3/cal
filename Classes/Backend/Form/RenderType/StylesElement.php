<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Cal\Backend\Form\RenderType;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class StylesElement
 */
class StylesElement extends AbstractFormElement
{
    /**
     * @return array
     */
    public function render(): array
    {
        ElementHelper::init();

        $result = $this->initializeResultArray();

        $params = $this->data['parameterArray']['fieldConf']['config']['parameters'];
        $part = $params['stylesFor'];

        $table = $this->data['tableName'];
        $row = $this->data['databaseRow'];

        $uid = $row['uid'];
        $pid = $row['pid'];

        $result['html'] = $this->getStyles($part, $table, $uid, $pid, $row[$part . 'style']);

        return $result;
    }

    /**
     * @param $part
     * @param $table
     * @param $uid
     * @param $pid
     * @param $value
     * @return string
     */
    protected function getStyles($part, $table, $uid, $pid, $value): string
    {
        $html = '<div class="cal-row">';

        $pageTSConf = BackendUtility::getPagesTSconfig($pid);

        if ($pageTSConf ['options.'] ['tx_cal_controller.'] [$part . 'Styles']) {
            $html .= '<select class="form-control form-control-adapt" name="data[' . $table . '][' . $uid . '][' . $part . 'style]">';
            $html .= '<option value=""></option>';

            $options = GeneralUtility::trimExplode(',', $pageTSConf ['options.'] ['tx_cal_controller.'] [$part . 'Styles'], 1);

            foreach ($options as $option) {
                $nameAndColor = GeneralUtility::trimExplode('=', $option, 1);
                $selected = '';
                if ($value === $nameAndColor [0]) {
                    $selected = ' selected="selected"';
                }
                $html .= '<option value="' . $nameAndColor [0] . '" style="background-color:' . $nameAndColor [1] . ';" ' . $selected . ' >' . $nameAndColor [0] . '</option>';
            }
            $html .= '</select>';
        } else {
            $html .= '<input class="input" maxlength="30" size="20" name="data[' . $table . '][' . $uid . '][' . $part . 'style]" value="' . $value . '">';
        }
        $html .= '</div>';

        return $html;
    }
}
