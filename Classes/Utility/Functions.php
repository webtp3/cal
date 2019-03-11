<?php

/*
 * This file is part of the web-tp3/cal.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace TYPO3\CMS\Cal\Utility;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This is a collection of many useful functions
 *
 */
class Functions
{
    /*
     * Expands a path if it includes EXT: shorthand. @param		string		The path to be expanded. @return					The expanded path.
     */
    public static function expandPath($path)
    {
        if (! strcmp(substr($path, 0, 4), 'EXT:')) {
            list($extKey, $script) = explode('/', substr($path, 4), 2);
            if ($extKey && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($extKey)) {
                $extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extKey);
                $path = substr($extPath, strlen(PATH_site)) . $script;
            }
        }

        return $path;
    }
    public static function clearCache()
    {
        if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 7005000) {
            $pageCache = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache('cache_pages');
            $pageCache->flushByTag('cal');
        } else {
            // only use cachingFramework if initialized and configured in TYPO3
            if (\TYPO3\CMS\Core\Cache\Cache::isCachingFrameworkInitialized() && TYPO3_UseCachingFramework && is_object($GLOBALS ['typo3CacheManager'])) {
                $pageCache = $GLOBALS ['typo3CacheManager']->getCache('cache_pages');
                $pageCache->flushByTag('cal');
            }
        }
    }
    public static function &getNotificationService()
    {
        $key = 'tx_default_notification';
        $serviceChain = '';
        /* Loop over all services providign the specified service type and subtype */
        while (is_object($notificationService = GeneralUtility::makeInstanceService('cal_view', 'notify', $serviceChain))) {
            $serviceChain .= ',' . $notificationService->getServiceKey();
            /* If the key of the current service matches what we're looking for, return the object */
            if ($key == $notificationService->getServiceKey()) {
                return $notificationService;
            }
        }
    }
    public static function &getReminderService()
    {
        $key = 'tx_default_reminder';
        $serviceChain = '';

        /* Loop over all services providign the specified service type and subtype */
        while (is_object($reminderService = GeneralUtility::makeInstanceService('cal_view', 'remind', $serviceChain))) {
            $serviceChain .= ',' . $reminderService->getServiceKey();
            /* If the key of the current service matches what we're looking for, return the object */
            if ($key == $reminderService->getServiceKey()) {
                return $reminderService;
            }
        }
    }
    public static function &getEventService()
    {
        $key = 'tx_cal_phpicalendar';
        $serviceChain = '';
        /* Loop over all services providign the specified service type and subtype */
        while (is_object($eventService = GeneralUtility::makeInstanceService('cal_event_model', 'event', $serviceChain))) {
            $serviceChain .= ',' . $eventService->getServiceKey();
            /* If the key of the current service matches what we're looking for, return the object */
            if ($key == $eventService->getServiceKey()) {
                return $eventService;
            }
        }
    }

    // get used charset
    public static function getCharset()
    {
        if ($GLOBALS ['TYPO3_CONF_VARS'] ['BE'] ['forceCharset']) { // First priority: forceCharset! If set, this will be authoritative!
            $charset = $GLOBALS ['TYPO3_CONF_VARS'] ['BE'] ['forceCharset'];
        } elseif (is_object($GLOBALS ['LANG'])) {
            $charset = $GLOBALS ['LANG']->charSet; // If "LANG" is around, that will hold the current charset
        } else {
            $charset = 'utf-8'; // THIS is just a hopeful guess!
        }

        return $charset;
    }
    public static function getOrderBy($table)
    {
        if (isset($GLOBALS['TCA'] [$table] ['ctrl'] ['default_sortby'])) {
            $orderBy = str_replace('ORDER BY ', '', $GLOBALS['TCA'] [$table] ['ctrl'] ['default_sortby']);
        } elseif (isset($GLOBALS['TCA'] [$table] ['ctrl'] ['sortby'])) {
            $orderBy = $GLOBALS['TCA'] [$table] ['ctrl'] ['sortby'];
        }

        return $orderBy;
    }
    public static function getmicrotime()
    {
        list($asec, $sec) = explode(' ', microtime());
        return date('H:m:s', intval($sec)) . ' ' . $asec;
    }
    public static function strtotimeOffset($unixtime)
    {
        $zone = intval(date('O', $unixtime)) / 100;
        return $zone * 60 * 60;
    }
    public static function getFormatStringFromConf($conf)
    {
        $dateFormatArray = [];
        $dateFormatArray [$conf ['dateConfig.'] ['dayPosition']] = '%d';
        $dateFormatArray [$conf ['dateConfig.'] ['monthPosition']] = '%m';
        $dateFormatArray [$conf ['dateConfig.'] ['yearPosition']] = '%Y';
        $format = $dateFormatArray [0] . $conf ['dateConfig.'] ['splitSymbol'] . $dateFormatArray [1] . $conf ['dateConfig.'] ['splitSymbol'] . $dateFormatArray [2];
        return $format;
    }
    public static function getYmdFromDateString($conf, $string)
    {
        // yyyy.mm.dd or dd.mm.yyyy or mm.dd.yyyy
        $stringArray = explode($conf ['dateConfig.'] ['splitSymbol'], $string);
        $ymdString = $stringArray [$conf ['dateConfig.'] ['yearPosition']] . $stringArray [$conf ['dateConfig.'] ['monthPosition']] . $stringArray [$conf ['dateConfig.'] ['dayPosition']];
        return $ymdString;
    }

    // returns true if $str begins with $sub
    public static function beginsWith($str, $sub)
    {
        return substr($str, 0, strlen($sub)) == $sub;
    }

    // return tru if $str ends with $sub
    public static function endsWith($str, $sub)
    {
        return substr($str, strlen($str) - strlen($sub)) == $sub;
    }

    // function that provides the same functionality like substituteMarkerArrayCached - but not cached, which is far better in case of cal
    public static function substituteMarkerArrayNotCached($content, $markContentArray = [], $subpartContentArray = [], $wrappedSubpartContentArray = [])
    {
        $cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic', 'cobj');
        // return $cObj->substituteMarkerArrayCached($content,$markContentArray,$subpartContentArray,$wrappedSubpartContentArray);

        // If not arrays then set them
        if (! is_array($markContentArray)) {
            $markContentArray = [];
        } // Plain markers
        if (! is_array($subpartContentArray)) {
            $subpartContentArray = [];
        } // Subparts being directly substituted
        if (! is_array($wrappedSubpartContentArray)) {
            $wrappedSubpartContentArray = [];
        } // Subparts being wrapped
        // Finding keys and check hash:
        $sPkeys = array_keys($subpartContentArray);
        $wPkeys = array_keys($wrappedSubpartContentArray);

        // Finding subparts and substituting them with the subpart as a marker
        foreach ($sPkeys as $key => $sPK) {
            $content = $cObj->substituteSubpart($content, $sPK, $subpartContentArray [$sPK]);
        }

        // Finding subparts and wrapping them with markers
        foreach ($wPkeys as $key => $wPK) {
            if (is_array($wrappedSubpartContentArray [$wPK])) {
                $parts = &$wrappedSubpartContentArray [$wPK];
            } else {
                $parts = explode('|', $wrappedSubpartContentArray [$wPK]);
            }
            $content = $cObj->substituteSubpart($content, $wPK, $parts);
        }

        return $cObj->substituteMarkerArray($content, $markContentArray);
    }

    /**
     * Removes potential XSS code from an input string.
     * Copied from typo3/contrib/RemoveXSS/RemoveXSS.php in TYPO3 trunk.
     *
     * @param
     *        	string		Input string
     * @param
     *        	string		replaceString for inserting in keywords (which destroyes the tags)
     * @return string string with potential XSS code removed
     *
     * @todo Once TYPO3 4.3 is released and required by cal, remove this method.
     */
    public static function removeXSS($val, $replaceString = '<x>')
    {
        // don't use empty $replaceString because then no XSS-remove will be done
        if ($replaceString == '') {
            $replaceString = '<x>';
        }
        // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
        // this prevents some character re-spacing such as <java\0script>
        // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
        $val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x19])/', '', $val);

        // straight replacements, the user should never need these since they're normal characters
        // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
        $search = '/&#[xX]0{0,8}(21|22|23|24|25|26|27|28|29|2a|2b|2d|2f|30|31|32|33|34|35|36|37|38|39|3a|3b|3d|3f|40|41|42|43|44|45|46|47|48|49|4a|4b|4c|4d|4e|4f|50|51|52|53|54|55|56|57|58|59|5a|5b|5c|5d|5e|5f|60|61|62|63|64|65|66|67|68|69|6a|6b|6c|6d|6e|6f|70|71|72|73|74|75|76|77|78|79|7a|7b|7c|7d|7e);?/i';
        // $val = preg_replace($search, "chr(hexdec('\\1'))", $val);
        // mixed preg_replace_callback ( mixed $pattern , callable $callback , mixed $subject [, int $limit = -1 [, int &$count ]] )
        $val = preg_replace_callback($search, function ($value) {
            return chr(hexdec($value));
        }, $val);
        $search = '/&#0{0,8}(33|34|35|36|37|38|39|40|41|42|43|45|47|48|49|50|51|52|53|54|55|56|57|58|59|61|63|64|65|66|67|68|69|70|71|72|73|74|75|76|77|78|79|80|81|82|83|84|85|86|87|88|89|90|91|92|93|94|95|96|97|98|99|100|101|102|103|104|105|106|107|108|109|110|111|112|113|114|115|116|117|118|119|120|121|122|123|124|125|126);?/i';
        // $val = preg_replace($search, "chr('\\1')", $val);
        $val = preg_replace_callback($search, function ($value) {
            return chr(hexdec($value));
        }, $val);

        // now the only remaining whitespace attacks are \t, \n, and \r
        $ra1 = [
                'javascript',
                'vbscript',
                'expression',
                'applet',
                'meta',
                'xml',
                'blink',
                'link',
                'style',
                'script',
                'embed',
                'object',
                'iframe',
                'frame',
                'frameset',
                'ilayer',
                'layer',
                'bgsound',
                'title',
                'base',
                'onabort',
                'onactivate',
                'onafterprint',
                'onafterupdate',
                'onbeforeactivate',
                'onbeforecopy',
                'onbeforecut',
                'onbeforedeactivate',
                'onbeforeeditfocus',
                'onbeforepaste',
                'onbeforeprint',
                'onbeforeunload',
                'onbeforeupdate',
                'onblur',
                'onbounce',
                'oncellchange',
                'onchange',
                'onclick',
                'oncontextmenu',
                'oncontrolselect',
                'oncopy',
                'oncut',
                'ondataavailable',
                'ondatasetchanged',
                'ondatasetcomplete',
                'ondblclick',
                'ondeactivate',
                'ondrag',
                'ondragend',
                'ondragenter',
                'ondragleave',
                'ondragover',
                'ondragstart',
                'ondrop',
                'onerror',
                'onerrorupdate',
                'onfilterchange',
                'onfinish',
                'onfocus',
                'onfocusin',
                'onfocusout',
                'onhelp',
                'onkeydown',
                'onkeypress',
                'onkeyup',
                'onlayoutcomplete',
                'onload',
                'onlosecapture',
                'onmousedown',
                'onmouseenter',
                'onmouseleave',
                'onmousemove',
                'onmouseout',
                'onmouseover',
                'onmouseup',
                'onmousewheel',
                'onmove',
                'onmoveend',
                'onmovestart',
                'onpaste',
                'onpropertychange',
                'onreadystatechange',
                'onreset',
                'onresize',
                'onresizeend',
                'onresizestart',
                'onrowenter',
                'onrowexit',
                'onrowsdelete',
                'onrowsinserted',
                'onscroll',
                'onselect',
                'onselectionchange',
                'onselectstart',
                'onstart',
                'onstop',
                'onsubmit',
                'onunload'
        ];
        $ra_tag = [
                'applet',
                'meta',
                'xml',
                'blink',
                'link',
                'style',
                'script',
                'embed',
                'object',
                'iframe',
                'frame',
                'frameset',
                'ilayer',
                'layer',
                'bgsound',
                'title',
                'base'
        ];
        $ra_attribute = [
                'style',
                'onabort',
                'onactivate',
                'onafterprint',
                'onafterupdate',
                'onbeforeactivate',
                'onbeforecopy',
                'onbeforecut',
                'onbeforedeactivate',
                'onbeforeeditfocus',
                'onbeforepaste',
                'onbeforeprint',
                'onbeforeunload',
                'onbeforeupdate',
                'onblur',
                'onbounce',
                'oncellchange',
                'onchange',
                'onclick',
                'oncontextmenu',
                'oncontrolselect',
                'oncopy',
                'oncut',
                'ondataavailable',
                'ondatasetchanged',
                'ondatasetcomplete',
                'ondblclick',
                'ondeactivate',
                'ondrag',
                'ondragend',
                'ondragenter',
                'ondragleave',
                'ondragover',
                'ondragstart',
                'ondrop',
                'onerror',
                'onerrorupdate',
                'onfilterchange',
                'onfinish',
                'onfocus',
                'onfocusin',
                'onfocusout',
                'onhelp',
                'onkeydown',
                'onkeypress',
                'onkeyup',
                'onlayoutcomplete',
                'onload',
                'onlosecapture',
                'onmousedown',
                'onmouseenter',
                'onmouseleave',
                'onmousemove',
                'onmouseout',
                'onmouseover',
                'onmouseup',
                'onmousewheel',
                'onmove',
                'onmoveend',
                'onmovestart',
                'onpaste',
                'onpropertychange',
                'onreadystatechange',
                'onreset',
                'onresize',
                'onresizeend',
                'onresizestart',
                'onrowenter',
                'onrowexit',
                'onrowsdelete',
                'onrowsinserted',
                'onscroll',
                'onselect',
                'onselectionchange',
                'onselectstart',
                'onstart',
                'onstop',
                'onsubmit',
                'onunload'
        ];
        $ra_protocol = [
                'javascript',
                'vbscript',
                'expression'
        ];

        // remove the potential &#xxx; stuff for testing
        $val2 = preg_replace('/(&#[xX]?0{0,8}(9|10|13|a|b);)*\s*/i', '', $val);
        $ra = [];

        foreach ($ra1 as $ra1word) {
            // stripos is faster than the regular expressions used later
            // and because the words we're looking for only have chars < 0x80
            // we can use the non-multibyte safe version
            if (stripos($val2, $ra1word) !== false) {
                // keep list of potential words that were found
                if (in_array($ra1word, $ra_protocol)) {
                    $ra [] = [
                            $ra1word,
                            'ra_protocol'
                    ];
                }
                if (in_array($ra1word, $ra_tag)) {
                    $ra [] = [
                            $ra1word,
                            'ra_tag'
                    ];
                }
                if (in_array($ra1word, $ra_attribute)) {
                    $ra [] = [
                            $ra1word,
                            'ra_attribute'
                    ];
                }
                // some keywords appear in more than one array
                // these get multiple entries in $ra, each with the appropriate type
            }
        }
        // only process potential words
        if (count($ra) > 0) {
            // keep replacing as long as the previous round replaced something
            $found = true;
            while ($found == true) {
                $val_before = $val;
                for ($i = 0; $i < count($ra); $i ++) {
                    $pattern = '';
                    for ($j = 0; $j < strlen($ra [$i] [0]); $j ++) {
                        if ($j > 0) {
                            $pattern .= '((&#[xX]0{0,8}([9ab]);)|(&#0{0,8}(9|10|13);)|\s)*';
                        }
                        $pattern .= $ra [$i] [0] [$j];
                    }
                    // handle each type a little different (extra conditions to prevent false positives a bit better)
                    switch ($ra [$i] [1]) {
                        case 'ra_protocol':
                            // these take the form of e.g. 'javascript:'
                            $pattern .= '((&#[xX]0{0,8}([9ab]);)|(&#0{0,8}(9|10|13);)|\s)*(?=:)';
                            break;
                        case 'ra_tag':
                            // these take the form of e.g. '<SCRIPT[^\da-z] ....';
                            $pattern = '(?<=<)' . $pattern . '((&#[xX]0{0,8}([9ab]);)|(&#0{0,8}(9|10|13);)|\s)*(?=[^\da-z])';
                            break;
                        case 'ra_attribute':
                            // these take the form of e.g. 'onload=' Beware that a lot of characters are allowed
                            // between the attribute and the equal sign!
                            $pattern .= '[\s\!\#\$\%\&\(\)\*\~\+\-\_\.\,\:\;\?\@\[\/\|\\\\\]\^\`]*(?==)';
                            break;
                    }
                    $pattern = '/' . $pattern . '/i';
                    // add in <x> to nerf the tag
                    $replacement = substr_replace($ra [$i] [0], $replaceString, 2, 0);
                    // filter out the hex tags
                    $val = preg_replace($pattern, $replacement, $val);
                    if ($val_before == $val) {
                        // no replacements were made, so exit the loop
                        $found = false;
                    }
                }
            }
        }

        return $val;
    }

    /*
     * Sets up a hook in the $className PHP file with the specified name. @param	string	The class name. @param	string	The name of the hook. @return	array	The array of objects implementing this hoook.
     */
    public static function getHookObjectsArray($className, $hookName, $modulePath = 'controller')
    {
        $hookObjectsArr = [];
        if (is_array($GLOBALS ['TYPO3_CONF_VARS'] [TYPO3_MODE] ['EXTCONF'] ['ext/cal/' . $modulePath . '/class.' . $className . '.php'] [$hookName])) {
            foreach ($GLOBALS ['TYPO3_CONF_VARS'] [TYPO3_MODE] ['EXTCONF'] ['ext/cal/' . $modulePath . '/class.' . $className . '.php'] [$hookName] as $classRef) {
                if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8000000) {
                    $hookObjectsArr [] = GeneralUtility::makeInstance($classRef);
                } else {
                    $hookObjectsArr [] = GeneralUtility::getUserObj($classRef);
                }
            }
        }

        return $hookObjectsArr;
    }

    /*
     * Executes the specified function for each item in the array of hook objects. @param	array	The array of hook objects. @param	string	The name of the function to execute. @return	none
     */
    public static function executeHookObjectsFunction($hookObjectsArray, $function, &$parentObject, &$params)
    {
        foreach ($hookObjectsArray as $hookObj) {
            if (method_exists($hookObj, $function)) {
                $hookObj->$function($parentObject, $params);
            }
        }
    }

    /**
     * Returns a Classname and allows various parameter to be passed to the constructor.
     *
     *
     * @param
     *        	string		className
     * @return object reference to the object
     *
     * @todo Once TYPO3 4.3 is released and required by cal, remove this method and replace calls to it with GeneralUtility::makeInstance.
     */
    public static function &makeInstance($className)
    {
        $constructorArguments = func_get_args();
        return call_user_func_array([
                'TYPO3\\CMS\\Core\\Utility\\GeneralUtility',
                'makeInstance'
        ], $constructorArguments);
    }
    public static function removeEmptyLines($string)
    {
        return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string);
    }
    public static function getMonthNames($type)
    {
        $monthNames = [];
        for ($i = 0; $i < 12; $i ++) {
            $monthNames [] = strftime($type, ($i * 2592000) + 1000000);
        }
        return $monthNames;
    }
    public static function getWeekdayNames($type)
    {
        $weekdayNames = [];
        for ($i = 3; $i < 10; $i ++) {
            $weekdayNames [] = strftime($type, ($i * 86400));
        }
        return $weekdayNames;
    }
    public static function getDayByWeek($year, $week, $weekday)
    {
        $date = new \TYPO3\CMS\Cal\Model\CalDate($year . '0101');
        $date->setTZbyID('UTC');

        $offset = $weekday - $date->format('%w');

        // correct weekday
        $date->addSeconds($offset * 86400);

        $oldYearWeek = ($date->getWeekOfYear() > 1) ? '0' : '1';

        // correct week
        $date->addSeconds((($week - $oldYearWeek) * 7) * 86400);

        return $date->format('%Y%m%d');
    }
    public static function createErrorMessage($error, $note)
    {
        return '<div class="error"><h2>Calendar Base Error</h2><p class="message"><strong>Message:</strong> ' . $error . '</p><p class="note"><strong>Note:</strong> ' . $note . '</p></div>';
    }
    public static function replaceLineFeed($string)
    {
        return str_replace([
                "\n\r",
                "\r\n",
                "\r",
                "\n"
        ], [
                '\n',
                '\n',
                '\n',
                '\n'
        ], $string);
    }

    /**
     * Wrapper to replace relative links with absolute ones for notifications
     *
     * @param string $html:
     *        	code thak can potentially have relative links that need to be fixed
     * @return string code with absolute links
     */
    public static function fixURI($html)
    {
        $uriHandler = GeneralUtility::makeInstance('TYPO3\\CMS\\Cal\\Controller\\UriHandler');
        $uriHandler->setHTML($html);
        $uriHandler->setPATH('http://' . GeneralUtility::getHostname(1) . '/');

        $uriHandler->extractMediaLinks();
        $uriHandler->extractHyperLinks();
        $uriHandler->fetchHTMLMedia();
        $uriHandler->substMediaNamesInHTML(0); // 0 = relative
        $uriHandler->substHREFsInHTML();

        return $uriHandler->getHTML();
    }

    /**
     * Returns a plain-array representation of the typoscript-setup
     *
     * @param array $conf
     * @return array
     */
    public static function getTsSetupAsPlainArray(&$conf)
    {

        /** @var TypoScriptService $typoScriptService */
        $typoScriptService = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Service\\TypoScriptService');
        return $typoScriptService->convertTypoScriptArrayToPlainArray($conf);
    }

    public static function getContent($path)
    {
        if (self::beginsWith($path, '/')) {
            $absPath = $path;
        } else {
            $absPath = $GLOBALS['TSFE']->tmpl->getFileName($path);
        }
        return file_get_contents($absPath);
    }

    /**
     *
     * @param array $conf
     * @param array $eventArray
     */
    public static function getIcsUid($conf, $eventArray)
    {
        return $conf ['view.'] ['ics.'] ['eventUidPrefix'] . '_' . $eventArray ['calendar_id'] . '_' . $eventArray ['uid'];
    }
}
