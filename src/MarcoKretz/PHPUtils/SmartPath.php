<?php

namespace MarcoKretz\PHPUtils;

/**
* This class utilizes DOMDocument and DOMXpath to extract
* information from HTML using XPATH.
*
* @author Marco Kretz <mk@marco-kretz.de>
*
* @link http://www.php.net/manual/en/class.domxpath.php
* @link http://www.php.net/manual/en/class.domdocument.php
*/
class SmartPath
{
    /**
     * Initialize SmartPath-Instance with DOMDocument.
     * Load "UTF8-fixed" HTML into DOMDocument.
     */
    public function __construct($html, $encoding = 'UTF-8')
    {
        $dom = DOMDocument::loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', $encoding));
        if (empty($dom)) {
            $this->valid = false;
        } else {
            $this->domXPath = new DOMXPath($dom);
            $this->valid = true;
        }
    }

    /**
     * Fire a normal XPATH query, return nodes as array.
     * If SmartPath-instance isn't valid, return FALSE.
     */
    public function queryXPath($expr)
    {
        return (($this->valid) ? iterator_to_array($this->domXPath->query($expr)) : false);
    }

    /**
     * Dynamic extract-function.
     *
     * Examples:
     * "Search for the $tag where the $attr contains $value and give me the $attrNeeded"
     * "Search for 'a' where 'href' contains 'exmaple.com' and give me the 'title'"
     * "Search for 'a' where 'text' contains 'Testlink' and give me the 'title'"
     *
     * If $attrNeeded stays blank, it will return an array of DOMNodes, DOMTexts, ...
     */
    public function nodeAttrContainsValue($tag, $attr, $value, $attrNeeded='')
    {
        if (!$this->valid) {
            return false;
        }

        $valuesFound = [];

        // Generate query based on $attr
        if ($attr === 'text') {
            $q = sprintf("//%s[contains(text(), '%s')]", $tag, $value);
        } else {
            $q = sprintf("//%s[contains(@%s, '%s')]", $tag, $attr, $value);
        }

        // Fire and extract data based on $attrNeeded
        if ($attrNeeded === 'text') {
            // Return array of strings
            $q .= "/text()";
            $elements = $this->domXPath->query($q);
            if (!empty($elements)) {
                foreach ($elements as $element) {
                    array_push($valuesFound, $element->wholeText);
                }
            }
        } elseif ($attrNeeded === '/text') {
            // Return array of strings
            $q .= "//text()";
            $elements = $this->domXPath->query($q);
            if (!empty($elements)) {
                foreach ($elements as $element) {
                    array_push($valuesFound, $element->wholeText);
                }
            }
        } elseif (empty($attrNeeded)) {
            // Return array of DOMElements
            $elements = $this->domXPath->query($q);
            if (!empty($elements)) {
                foreach ($elements as $element) {
                    array_push($valuesFound, $element);
                }
            }
        } else {
            // Return array of strings
            $q .= '/@' . $attrNeeded;
            $elements = $this->domXPath->query($q);
            if (!empty($elements)) {
                foreach ($elements as $element) {
                    array_push($valuesFound, $element->value);
                }
            }
        }

        return $valuesFound;
    }
}
