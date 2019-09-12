<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Cal\Backend\Form\RenderType;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

/**
 * Class ByDayElement
 */
class ByDayElement extends AbstractFormElement
{
    /**
     * @return array
     */
    public function render(): array
    {
        ElementHelper::init();

        $result = $this->initializeResultArray();

        $table = $this->data['tableName'];
        $row = $this->data['databaseRow'];

        $uid = $row['uid'];
        $html = '';
        $callback = '';

        $startDay = ElementHelper::getWeekStartDay($row['pid']);

        switch ($row['freq'][0]) {
            case 'week':
                $html = $this->byDay_checkbox($startDay);
                $callback = <<< EOJ
function(ByDayUI) {
	window.byDay = new ByDayUI('byday-container', 'data[$table][$uid][byday]', 'cal-row');
	$(function() { byDay.load(); });
}
EOJ;
                break;
            case 'month':
                $dayRow = $this->getByDayRow($startDay, ElementHelper::getEveryMonthText());
                $html = $this->byDay_select();
                $callback = <<< EOJ
function(ByDayUI) {
	window.byDay = new ByDayUI('byday-container', 'data[$table][$uid][byday]', 'cal-row', '$dayRow');
	$(function() { byDay.load(); });
}
EOJ;
                break;
            case 'year':
                $dayRow = $this->getByDayRow($startDay, ElementHelper::getSelectedMonthText());
                $html = $this->byDay_select();
                $callback = <<< EOJ
function(ByDayUI) {
	window.byDay = new ByDayUI('byday-container', 'data[$table][$uid][byday]', 'cal-row', '$dayRow');
	$(function() { byDay.load(); });
}
EOJ;
                break;
        }

        $out = [];

        $out [] = $html;
        $out [] = '<input type="hidden" name="data[' . $table . '][' . $uid . '][byday]" id="data[' . $table . '][' . $uid . '][byday]" value="' . $row ['byday'] . '" />';

        $result['html'] = implode("\n", $out);
        $result['requireJsModules'][] = ['TYPO3/CMS/Cal/RecurUI/ByDayUI' => $callback];

        return $result;
    }

    /**
     * @param $startDay
     * @param $endString
     * @return mixed
     */
    public function getByDayRow($startDay, $endString)
    {
        $html = '<div class="cal-row">';

        $html .= '<select class="form-control count" style="width: 48px; display: inline;" onchange="byDay.save()">';
        $html .= '<option value="" />';
        foreach (ElementHelper::getCountsArray() as $value => $label) {
            $html .= '<option value="' . $value . '">' . $label . '</option>';
        }
        $html .= '</select>&nbsp;&nbsp;';

        $html .= '<select class="form-control day" style="width: 48px; display: inline;" onchange="byDay.save()">';
        $html .= '<option value="" />';

        foreach (ElementHelper::getWeekdaysArray($startDay) as $value => $label) {
            $html .= '<option value="' . $value . '">' . $label . '</option>';
        }
        $html .= '</select>';

        $html .= ' ' . $endString;
        $html .= '<a id="garbage" href="#" onclick="byDay.removeRecurrence(this);">' . ElementHelper::getGarbageIcon() . '</a>';
        $html .= '</div>';

        return ElementHelper::removeNewlines($html);
    }

    /**
     * @param $startDay
     * @return string
     */
    public function byDay_checkbox($startDay): string
    {
        $out = [];

        $out [] = '<div id="byday-container" style="margin-bottom: 5px;">';

        foreach (ElementHelper::getWeekdaysArray($startDay) as $value => $label) {
            $name = 'byday_' . $value;

            $out [] = '<div class="cal-row">';
            $out [] = '<input style="padding: 0; margin: 0;" type="checkbox" name="' . $name . '" value="' . $value . '" onchange="byDay.save();"/>';
            $out [] = '<label style="padding-left: 2px;" for="' . $name . '">' . $label . '</label>';
            $out [] = '</div>';
        }
        $out [] = '</div>';

        return implode(LF, $out);
    }

    /**
     * @return string
     */
    public function byDay_select(): string
    {
        $out = [];

        $out [] = '<div id="byday-container"></div>';
        $out [] = '<div style="padding: 5px 0"><a href="javascript:byDay.addRecurrence();">' . ElementHelper::getNewIcon() .
            $GLOBALS['LANG']->getLL('tx_cal_event.add_recurrence') . '</a></div>';

        return implode(LF, $out);
    }
}
