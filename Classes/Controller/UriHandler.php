<?php

namespace TYPO3\CMS\Cal\Controller;

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
 * URI Handling class based on TYPO3s original t3lib::htmlmail class
 */
class UriHandler
{
    public $jumperURL_prefix = ''; // This is a prefix that will be added to all links in the mail. Example: 'http://www.mydomain.com/jump?userid=###FIELD_uid###&url='. if used, anything after url= is urlencoded.
    public $jumperURL_useId = 0; // If set, then the array-key of the urls are inserted instead of the url itself. Smart in order to reduce link-length
    public $mediaList = ''; // If set, this is a list of the media-files (index-keys to the array) that should be represented in the html-mail
    public $theParts = [];
    public $message = '';
    public $part = 0;
    public $image_fullpath_list = '';
    public $href_fullpath_list = '';

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Set the HTML variable without further processing
     *
     * @param string $html :
     *            HTML text to be handled
     */
    public function setHTML($html)
    {
        $this->theParts['html']['content'] = $html;
    }

    /**
     * Set the PATH variable
     *
     * @param string $path :
     *            path to be used
     */
    public function setPATH($path)
    {
        $this->theParts['html']['path'] = $path;
    }

    /**
     * Get the HTML variable
     *
     * @return string HTML text in its curren processing status
     */
    public function getHTML(): string
    {
        return $this->theParts['html']['content'];
    }

    /**
     *
     */
    public function modify()
    {
        $this->theParts['html']['content'] = str_replace(
            'PathDebug',
            $this->theParts['html']['path'],
            $this->theParts['html']['content']
        );
    }

    /**
     * Fetches the HTML-content from either url og local serverfile
     *
     * @param string $file :
     *            file to load
     * @return bool the data was fetched or not
     */
    public function fetchHTML($file): bool
    {
        // Fetches the content of the page
        $this->theParts['html']['content'] = $this->getUrl($file);
        if ($this->theParts['html']['content']) {
            $addr = $this->extParseUrl($file);
            $path = $addr['scheme'] ? $addr['scheme'] . '://' . $addr['host'] . ($addr['port'] ? ':' . $addr['port'] : '') . ($addr['filepath'] ?: '/') : $addr['filepath'];
            $this->theParts['html']['path'] = $path;
            return true;
        }
        return false;
    }

    /**
     * Fetches the mediafiles which are found by extractMediaLinks()
     */
    public function fetchHTMLMedia()
    {
        if (!is_array($this->theParts['html']['media']) || !count($this->theParts['html']['media'])) {
            return;
        }
        foreach ($this->theParts['html']['media'] as $key => $media) {
            // fetching the content and the mime-type
            $picdata = $this->getExtendedURL($this->theParts['html']['media'][$key]['absRef']);
            if (is_array($picdata)) {
                $this->theParts['html']['media'][$key]['content'] = $picdata['content'];
                $this->theParts['html']['media'][$key]['ctype'] = $picdata['content_type'];
            }
        }
    }

    /**
     * extracts all media-links from $this->theParts['html']['content']
     */
    public function extractMediaLinks()
    {
        $html_code = $this->theParts['html']['content'];
        $attribRegex = $this->tag_regex([
            'img',
            'table',
            'td',
            'tr',
            'body',
            'iframe',
            'script',
            'input',
            'embed'
        ]);

        // split the document by the beginning of the above tags
        $codepieces = preg_split($attribRegex, $html_code);
        $len = strlen($codepieces[0]);
        $pieces = count($codepieces);
        $reg = [];
        for ($i = 1; $i < $pieces; $i++) {
            $tag = strtolower(strtok(substr($html_code, $len + 1, 10), ' '));
            $len += strlen($tag) + strlen($codepieces[$i]) + 2;
            $attributes = $this->get_tag_attributes($reg[0]); // Fetches the attributes for the tag
            $imageData = [];

            // Finds the src or background attribute
            $imageData['ref'] = ($attributes['src'] ?: $attributes['background']);
            if ($imageData['ref']) {
                // find out if the value had quotes around it
                $imageData['quotes'] = (substr(
                    $codepieces[$i],
                    strpos($codepieces[$i], $imageData['ref']) - 1,
                    1
                    ) === '"') ? '"' : '';
                // subst_str is the string to look for, when substituting lateron
                $imageData['subst_str'] = $imageData['quotes'] . $imageData['ref'] . $imageData['quotes'];
                if (false === strpos(
                    $this->image_fullpath_list,
                    '|' . $imageData['subst_str'] . '|'
                )) {
                    $this->image_fullpath_list .= '|' . $imageData['subst_str'] . '|';
                    $imageData['absRef'] = $this->absRef($imageData['ref']);
                    $imageData['tag'] = $tag;
                    $imageData['use_jumpurl'] = $attributes['dmailerping'] ? 1 : 0;
                    $this->theParts['html']['media'][] = $imageData;
                }
            }
        }

        // Extracting stylesheets
        $attribRegex = $this->tag_regex([
            'link'
        ]);
        // Split the document by the beginning of the above tags
        $codepieces = preg_split($attribRegex, $html_code);
        $pieces = count($codepieces);
        for ($i = 1; $i < $pieces; $i++) {
            // fetches the attributes for the tag
            $attributes = $this->get_tag_attributes($reg[0]);
            $imageData = [];
            if ($attributes['href'] && strtolower($attributes['rel']) === 'stylesheet') {
                // Finds the src or background attribute
                $imageData['ref'] = $attributes['href'];
                // Finds out if the value had quotes around it
                $imageData['quotes'] = (substr(
                    $codepieces[$i],
                    strpos($codepieces[$i], $imageData['ref']) - 1,
                    1
                    ) === '"') ? '"' : '';
                // subst_str is the string to look for, when substituting lateron
                $imageData['subst_str'] = $imageData['quotes'] . $imageData['ref'] . $imageData['quotes'];
                if ($imageData['ref'] && false === strpos(
                    $this->image_fullpath_list,
                    '|' . $imageData['subst_str'] . '|'
                )) {
                    $this->image_fullpath_list .= '|' . $imageData['subst_str'] . '|';
                    $imageData['absRef'] = $this->absRef($imageData['ref']);
                    $this->theParts['html']['media'][] = $imageData;
                }
            }
        }

        // fixes javascript rollovers
        $codepieces = explode('.src', $html_code);
        $pieces = count($codepieces);
        $expr = '/^[^' . quotemeta('"') . quotemeta("'") . ']*/';
        for ($i = 1; $i < $pieces; $i++) {
            $temp = $codepieces[$i];
            $temp = trim(str_replace('=', '', trim($temp)));
            preg_match($expr, substr($temp, 1, strlen($temp)), $reg);
            $imageData['ref'] = $reg[0];
            $imageData['quotes'] = $temp[0];
            // subst_str is the string to look for, when substituting lateron
            $imageData['subst_str'] = $imageData['quotes'] . $imageData['ref'] . $imageData['quotes'];
            $theInfo = $this->split_fileref($imageData['ref']);

            switch ($theInfo['fileext']) {
                case 'gif':
                case 'jpeg':
                case 'jpg':
                    if ($imageData['ref'] && false === strpos(
                        $this->image_fullpath_list,
                        '|' . $imageData['subst_str'] . '|'
                    )) {
                        $this->image_fullpath_list .= '|' . $imageData['subst_str'] . '|';
                        $imageData['absRef'] = $this->absRef($imageData['ref']);
                        $this->theParts['html']['media'][] = $imageData;
                    }
                    break;
            }
        }
    }

    /**
     * extracts all hyperlinks from $this->theParts["html"]["content"]
     */
    public function extractHyperLinks()
    {
        $html_code = $this->theParts['html']['content'];
        $attribRegex = $this->tag_regex([
            'a',
            'form',
            'area'
        ]);
        $codepieces = preg_split($attribRegex, $html_code); // Splits the document by the beginning of the above tags
        $len = strlen($codepieces[0]);
        $pieces = count($codepieces);
        for ($i = 1; $i < $pieces; $i++) {
            $tag = strtolower(strtok(substr($html_code, $len + 1, 10), ' '));
            $len += strlen($tag) + strlen($codepieces[$i]) + 2;

            // Fetches the attributes for the tag
            $attributes = $this->get_tag_attributes('');
            $hrefData = [];
            $hrefData['ref'] = ($attributes['href'] ?: $hrefData['ref'] = $attributes['action']);
            if ($hrefData['ref']) {
                // Finds out if the value had quotes around it
                $hrefData['quotes'] = (substr(
                    $codepieces[$i],
                    strpos($codepieces[$i], $hrefData['ref']) - 1,
                    1
                    ) === '"') ? '"' : '';
                // subst_str is the string to look for, when substituting lateron
                $hrefData['subst_str'] = $hrefData['quotes'] . $hrefData['ref'] . $hrefData['quotes'];
                if (false === strpos($this->href_fullpath_list, '|' . $hrefData['subst_str'] . '|') && strpos(trim($hrefData['ref']), '#') !== 0) {
                    $this->href_fullpath_list .= '|' . $hrefData['subst_str'] . '|';
                    $hrefData['absRef'] = $this->absRef($hrefData['ref']);
                    $hrefData['tag'] = $tag;
                    $this->theParts['html']['hrefs'][] = $hrefData;
                }
            }
        }
        // Extracts TYPO3 specific links made by the openPic() JS function
        $codepieces = explode("onClick=\"openPic('", $html_code);
        $pieces = count($codepieces);
        for ($i = 1; $i < $pieces; $i++) {
            $showpic_linkArr = explode("'", $codepieces[$i]);
            $hrefData['ref'] = $showpic_linkArr[0];
            if ($hrefData['ref']) {
                $hrefData['quotes'] = "'";
                // subst_str is the string to look for, when substituting lateron
                $hrefData['subst_str'] = $hrefData['quotes'] . $hrefData['ref'] . $hrefData['quotes'];
                if (false === strpos($this->href_fullpath_list, '|' . $hrefData['subst_str'] . '|')) {
                    $this->href_fullpath_list .= '|' . $hrefData['subst_str'] . '|';
                    $hrefData['absRef'] = $this->absRef($hrefData['ref']);
                    $this->theParts['html']['hrefs'][] = $hrefData;
                }
            }
        }
    }

    /**
     * extracts all media-links from $this->theParts["html"]["content"]
     *
     * @return array array with information about each frame
     */
    public function extractFramesInfo(): array
    {
        $htmlCode = $this->theParts['html']['content'];
        $info = [];
        if (strpos(' ' . $htmlCode, '<frame ')) {
            $attribRegex = $this->tag_regex('frame');
            // Splits the document by the beginning of the above tags
            $codepieces = preg_split($attribRegex, $htmlCode, 1000000);
            $pieces = count($codepieces);
            for ($i = 1; $i < $pieces; $i++) {
                // Fetches the attributes for the tag
                $attributes = $this->get_tag_attributes('');
                $frame = [];
                $frame['src'] = $attributes['src'];
                $frame['name'] = $attributes['name'];
                $frame['absRef'] = $this->absRef($frame['src']);
                $info[] = $frame;
            }
        }
        return $info;
    }

    /**
     * This function substitutes the media-references in $this->theParts["html"]["content"]
     *
     * @param bool $absolute :
     *            TRUE, then the refs are substituted with http:// ref's indstead of Content-ID's (cid).
     */
    public function substMediaNamesInHTML($absolute)
    {
        if (is_array($this->theParts['html']['media'])) {
            foreach ($this->theParts['html']['media'] as $key => $val) {
                if ($this->jumperURL_prefix && $val['use_jumpurl']) {
                    $subst = $this->jumperURL_prefix . str_replace('%2F', '/', rawurlencode($val['absRef']));
                } else {
                    $subst = $absolute ? $val['absRef'] : 'cid:part' . $key . '.' . $this->messageid;
                }
                $this->theParts['html']['content'] = str_replace(
                    $val['subst_str'],
                    $val['quotes'] . $subst . $val['quotes'],
                    $this->theParts['html']['content']
                );
            }
        }
        if (!$absolute) {
            $this->fixRollOvers();
        }
    }

    /**
     * This function substitutes the hrefs in $this->theParts["html"]["content"]
     */
    public function substHREFsInHTML()
    {
        if (!is_array($this->theParts['html']['hrefs'])) {
            return;
        }
        foreach ($this->theParts['html']['hrefs'] as $key => $val) {
            // Form elements cannot use jumpurl!
            if ($this->jumperURL_prefix && $val['tag'] !== 'form') {
                if ($this->jumperURL_useId) {
                    $substVal = $this->jumperURL_prefix . $key;
                } else {
                    $substVal = $this->jumperURL_prefix . str_replace('%2F', '/', rawurlencode($val['absRef']));
                }
            } else {
                $substVal = $val['absRef'];
            }
            $this->theParts['html']['content'] = str_replace(
                $val['subst_str'],
                $val['quotes'] . $substVal . $val['quotes'],
                $this->theParts['html']['content']
            );
        }
    }

    /**
     * JavaScript rollOvers cannot support graphics inside of mail.
     * If these exists we must let them refer to the absolute url. By the way:
     * Roll-overs seems to work only on some mail-readers and so far I've seen it
     * work on Netscape 4 message-center (but not 4.5!!)
     */
    public function fixRollOvers()
    {
        $newContent = '';
        $items = explode('.src', $this->theParts['html']['content']);
        if (count($items) <= 1) {
            return;
        }

        foreach ($items as $key => $part) {
            $sub = substr($part, 0, 200);
            if (preg_match('/cid:part[^ "\']*/', $sub, $reg)) {
                // The position of the string
                $thePos = strpos($part, $reg[0]);
                // Finds the id of the media...
                preg_match('/cid:part([^\.]*).*/', $sub, $reg2);
                $theSubStr = $this->theParts['html']['media'][intval($reg2[1])]['absRef'];
                if ($thePos && $theSubStr) {
                    // ... and substitutes the javaScript rollover image with this instead
                    // If the path is NOT and url, the reference is set to nothing
                    if (!strpos(' ' . $theSubStr, 'http://')) {
                        $theSubStr = 'http://';
                    }
                    $part = substr($part, 0, $thePos) . $theSubStr . substr(
                        $part,
                        $thePos + strlen($reg[0]),
                        strlen($part)
                        );
                }
            }
            $newContent .= $part . ((($key + 1) !== count($items)) ? '.src' : '');
        }
        $this->theParts['html']['content'] = $newContent;
    }

    /**
     * reads the URL or file and determines the Content-type by either guessing or opening a connection to the host
     *
     * @param string $url :
     *            URL to get information of
     * @return mixed FALSE or the array with information
     */
    public function getExtendedURL($url)
    {
        $res = [];
        $res['content'] = $this->getUrl($url);
        if (!$res['content']) {
            return false;
        }
        $pathInfo = parse_url($url);
        $fileInfo = $this->split_fileref($pathInfo['path']);
        switch ($fileInfo['fileext']) {
            case 'gif':
            case 'png':
                $res['content_type'] = 'image/' . $fileInfo['fileext'];
                break;
            case 'jpg':
            case 'jpeg':
                $res['content_type'] = 'image/jpeg';
                break;
            case 'html':
            case 'htm':
                $res['content_type'] = 'text/html';
                break;
            case 'css':
                $res['content_type'] = 'text/css';
                break;
            case 'swf':
                $res['content_type'] = 'application/x-shockwave-flash';
                break;
            default:
                $res['content_type'] = $this->getMimeType($url);
        }
        return $res;
    }

    /**
     * reads a url or file
     *
     * @param string $url :
     *            URL to fetch
     * @return string content of the URL
     */
    public function getUrl($url): string
    {
        return GeneralUtility::getUrl($url);
    }

    /**
     * reads a url or file and strips the HTML-tags AND removes all
     * empty lines.
     * This is used to read plain-text out of a HTML-page
     *
     * @param string $url :
     *            URL to load
     * @return string
     */
    public function getStrippedURL($url): string
    {
        $content = '';
        if ($fd = fopen($url, 'rb')) {
            while (!feof($fd)) {
                $line = fgetss($fd, 5000);
                if (trim($line)) {
                    $content .= trim($line) . LF;
                }
            }
            fclose($fd);
        }
        return $content;
    }

    /**
     * This function returns the mime type of the file specified by the url
     *
     * @param string $url :
     *            url
     * @return string the mime type found in the header
     */
    public function getMimeType($url): string
    {
        $mimeType = '';
        $headers = trim(GeneralUtility::getUrl($url, 2));
        if ($headers) {
            $matches = [];
            if (preg_match('/(Content-Type:[\s]*)([a-zA-Z_0-9\/\-\.\+]*)([\s]|$)/', $headers, $matches)) {
                $mimeType = trim($matches[2]);
            }
        }
        return $mimeType;
    }

    /**
     * Returns the absolute address of a link.
     * This is based on
     * $this->theParts["html"]["path"] being the root-address
     *
     * @param string $ref :
     *            to use
     * @return string absolute address
     */
    public function absRef($ref): string
    {
        $ref = trim($ref);
        $info = parse_url($ref);
        if ($info['scheme']) {
            return $ref;
        }
        if (preg_match('/^\//', $ref)) {
            $addr = parse_url($this->theParts['html']['path']);
            return $addr['scheme'] . '://' . $addr['host'] . ($addr['port'] ? ':' . $addr['port'] : '') . $ref;
        }
        // If the reference is relative, the path is added, in order for us to fetch the content
        return $this->theParts['html']['path'] . $ref;
    }

    /**
     * Returns information about a file reference
     *
     * @param string $fileref :
     *            file to use
     * @return array filename, filebody, fileext
     */
    public function split_fileref($fileref): array
    {
        $info = [];
        if (preg_match('/(.*\/)(.*)$/', $fileref, $reg)) {
            $info['path'] = $reg[1];
            $info['file'] = $reg[2];
        } else {
            $info['path'] = '';
            $info['file'] = $fileref;
        }
        $reg = '';
        if (preg_match('/(.*)\.([^\.]*$)/', $info['file'], $reg)) {
            $info['filebody'] = $reg[1];
            $info['fileext'] = strtolower($reg[2]);
            $info['realFileext'] = $reg[2];
        } else {
            $info['filebody'] = $info['file'];
            $info['fileext'] = '';
        }
        return $info;
    }

    /**
     * Returns an array with file or url-information
     *
     * @param string $path :
     *            to check
     * @return array about the path / URL
     */
    public function extParseUrl($path): array
    {
        $res = parse_url($path);
        preg_match('/(.*\/)([^\/]*)$/', $res['path'], $reg);
        $res['filepath'] = $reg[1];
        $res['filename'] = $reg[2];
        return $res;
    }

    /**
     * Creates a regular expression out of a list of tags
     *
     * @param $tags
     * @return string regular expression
     */
    public function tag_regex($tags): string
    {
        $tags = (!is_array($tags) ? [
            $tags
        ] : $tags);
        $regexp = '/';
        $c = count($tags);
        foreach ($tags as $tag) {
            $c--;
            $regexp .= '<' . $tag . '[[:space:]]' . ($c ? '|' : '');
        }
        return $regexp . '/i';
    }

    /**
     * This function analyzes a HTML tag
     * If an attribute is empty (like OPTION) the value of that key is just empty.
     * Check it with is_set();
     *
     * @param string $tag :
     *            either like this "<TAG OPTION ATTRIB=VALUE>" or
     *            this " OPTION ATTRIB=VALUE>" which means you can omit the tag-name
     * @return array with attributes as keys in lower-case
     */
    public function get_tag_attributes($tag): array
    {
        $attributes = [];
        $tag = ltrim(preg_replace('/^<[^ ]*/', '', trim($tag)));
        $tagLen = strlen($tag);
        $safetyCounter = 100;
        // Find attribute
        while ($tag) {
            $value = '';
            $reg = preg_split('/[[:space:]=>]/', $tag, 2);
            $attrib = $reg[0];

            $tag = ltrim(substr($tag, strlen($attrib), $tagLen));
            if (strpos($tag, '=') === 0) {
                $tag = ltrim(substr($tag, 1, $tagLen));
                if (strpos($tag, '"') === 0) {
                    // Quotes around the value
                    $reg = explode('"', substr($tag, 1, $tagLen), 2);
                    $tag = ltrim($reg[1]);
                    $value = $reg[0];
                } else {
                    // No quotes around value
                    preg_match('/^([^[:space:]>]*)(.*)/', $tag, $reg);
                    $value = trim($reg[1]);
                    $tag = ltrim($reg[2]);
                    if (strpos($tag, '>') === 0) {
                        $tag = '';
                    }
                }
            }
            $attributes[strtolower($attrib)] = $value;
            $safetyCounter--;
            if ($safetyCounter < 0) {
                break;
            }
        }
        return $attributes;
    }
}
