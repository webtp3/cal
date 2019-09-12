<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Cal\Backend\Form\RenderType;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RDateElement
 */
class RDateElement extends AbstractFormElement
{
    /**
     * @return array
     * @throws \TYPO3\CMS\Backend\Form\Exception
     */
    public function render(): array
    {
        ElementHelper::init();

        $result = $this->initializeResultArray();

        $table = $this->data['tableName'];
        $row = $this->data['databaseRow'];

        $uid = $row['uid'];

        $rdateType = $row['rdate_type'][0];
        $rdateValues = GeneralUtility::trimExplode(',', $row['rdate'], 1);

        // add for new empty line
        $rdateValues[] = '';

        $out = [];
        $out[] = '<div class="formengine-field-item t3js-formengine-field-item">';
        $out[] =   '<div class="form-wizards-wrap">';
        $out[] =       '<div class="form-wizards-element">';
        $out[] =           '<div class="form-control-wrap">';

        $jsDate = $GLOBALS ['TYPO3_CONF_VARS'] ['SYS'] ['USdateFormat'] ? '%m-%d-%Y' : '%d-%m-%Y';

        /** @var NodeFactory $nodefactory */
        $nodefactory = GeneralUtility::makeInstance(NodeFactory::class);

        $key = 0;
        foreach ($rdateValues as $value) {
            $formatedValue = '';
            $splittedPeriod = ['', ''];

            if ($value !== '') {
                $splittedPeriod = explode('/', $value);

                $m = [];
                preg_match('/(\d{4})(\d{2})(\d{2})(T(\d{2})(\d{2})(\d{2})Z)?/i', $splittedPeriod[0], $m);
                $formatedValue = sprintf('%02d-%02d-%02dT%02d:%02d:%02dZ', $m[1], $m[2], $m[3], $m[5], $m[6], $m[7]);
            }

            $config = [
                'tableName'      => 'tx_cal_event',
                'fieldName'      => 'rdate' . $key,
                'databaseRow'    => ['uid' => $uid],
                'renderType'     => 'inputDateTime',
                'parameterArray' => [
                    'itemFormElValue' => $formatedValue,
                    'itemFormElName'  => 'data[' . $table . '][' . $uid . '][rdate' . $key . ']',
                    'fieldConf'       => [
                        'config' => [
                            'eval' => 'XXX'
                        ]
                    ]
                ]
            ];

            if ($rdateType === 'date_time' || $rdateType === 'period') {
                $config['parameterArray']['fieldConf']['config']['eval'] = 'datetime';
                $datefield = $nodefactory->create($config)->render();

                $out [] = $datefield['html'];
            } else {
                $config['parameterArray']['fieldConf']['config']['eval'] = 'date';
                $datefield = $nodefactory->create($config)->render();

                $out [] = $datefield['html'];
            }

            if ($rdateType === 'period') {
                $periodArray = [];
                if (is_string($splittedPeriod [1])) {
                    preg_match('/P((\d+)Y)?((\d+)M)?((\d+)W)?((\d+)D)?T((\d+)H)?((\d+)M)?((\d+)S)?/', $splittedPeriod [1], $periodArray);
                }

                $out [] .= '<label class="t3js-formengine-label">' . $GLOBALS['LANG']->getLL('l_duration') . '</label>'
                    . '<div class="row">'
                    . '<div class="form-group t3js-formengine-validation-marker t3js-formengine-palette-field col-sm-4">'
                    . '<label class="t3js-formengine-label">' . $GLOBALS['LANG']->getLL('l_year') . '</label>'
                    . '<div class="formengine-field-item t3js-formengine-field-item"><div class="form-control-wrap" style="max-width: 192px">'
                    . '<input type="text" value="' . intval($periodArray [2]) . '" class="form-control rdateChanged" name="rdateYear' . $key . '" id="rdateYear' . $key . '"/>'
                    . '</div>'
                    . '</div>'
                    . '</div>'
                    . '<div class="form-group t3js-formengine-validation-marker t3js-formengine-palette-field col-sm-4">'
                    . '<label class="t3js-formengine-label">' . $GLOBALS['LANG']->getLL('l_month') . '</label>'
                    . '<div class="formengine-field-item t3js-formengine-field-item"><div class="form-control-wrap" style="max-width: 192px">'
                    . '<input type="text" value="' . intval($periodArray [4]) . '" class="form-control rdateChanged" name="rdateMonth' . $key . '" id="rdateMonth' . $key . '"/>'
                    . '</div>'
                    . '</div>'
                    . '</div>'
                    . '<div class="form-group t3js-formengine-validation-marker t3js-formengine-palette-field col-sm-4">'
                    . '<label class="t3js-formengine-label">' . $GLOBALS['LANG']->getLL('l_week') . '</label>'
                    . '<div class="formengine-field-item t3js-formengine-field-item"><div class="form-control-wrap" style="max-width: 192px">'
                    . '<input type="text" value="' . intval($periodArray [6]) . '" class="form-control rdateChanged" name="rdateWeek' . $key . '" id="rdateWeek' . $key . '"/>'
                    . '</div>'
                    . '</div>'
                    . '</div>'
                    . '</div>'
                    . '<div class="row">'
                    . '<div class="form-group t3js-formengine-validation-marker t3js-formengine-palette-field col-sm-4">'
                    . '<label class="t3js-formengine-label">' . $GLOBALS['LANG']->getLL('l_day') . '</label>'
                    . '<div class="formengine-field-item t3js-formengine-field-item"><div class="form-control-wrap" style="max-width: 192px">'
                    . '<input type="text" value="' . intval($periodArray [8]) . '" class="form-control rdateChanged" name="rdateDay' . $key . '" id="rdateDay' . $key . '"/>'
                    . '</div>'
                    . '</div>'
                    . '</div>'
                    . '<div class="form-group t3js-formengine-validation-marker t3js-formengine-palette-field col-sm-4">'
                    . '<label class="t3js-formengine-label">' . $GLOBALS['LANG']->getLL('l_hour') . '</label>'
                    . '<div class="formengine-field-item t3js-formengine-field-item"><div class="form-control-wrap" style="max-width: 192px">'
                    . '<input type="text" value="' . intval($periodArray [10]) . '" class="form-control rdateChanged" name="rdateHour' . $key . '" id="rdateHour' . $key . '"/>'
                    . '</div>'
                    . '</div>'
                    . '</div>'
                    . '<div class="form-group t3js-formengine-validation-marker t3js-formengine-palette-field col-sm-4">'
                    . '<label class="t3js-formengine-label">' . $GLOBALS['LANG']->getLL('l_minute') . '</label>'
                    . '<div class="formengine-field-item t3js-formengine-field-item"><div class="form-control-wrap" style="max-width: 192px">'
                    . '<input type="text" value="' . intval($periodArray [12]) . '" class="form-control rdateChanged" name="rdateMinute' . $key . '" id="rdateMinute' . $key . '"/>'
                    . '</div>'
                    . '</div>'
                    . '</div>'
                    . '</div>'
                ;
            }

            $key++;
        }

        $out [] = '<input type="hidden" name="data[' . $table . '][' . $uid . '][rdate]" id="data_' . $table . '_' . $uid . '_rdate" value="' . $row ['rdate'] . '" />';

        $out[] =           '</div>';
        $out[] =       '</div>';
        $out[] =   '</div>';
        $out[] = '</div>';

        $rDateCount = count($rdateValues);

        $callback = <<< EOJ
function(RDate) {
	window.rDate = new RDate('$jsDate', '$table', $uid, '$rdateType', $rDateCount);

	$(".rdateChanged").on("change", function() {
		window.rDate.rdateChanged();
	});
	$(document).on("formengine.dp.change", function() {
		window.rDate.rdateChanged();
	});
}
EOJ;

        $result['html'] = implode(LF, $out);
        $result['requireJsModules'][] = ['TYPO3/CMS/Cal/RDate' => $callback];

        return $result;
    }
}
