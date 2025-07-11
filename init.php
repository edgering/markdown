<?php
/**
 *  Set proper markdown version
 *
 *  + run EDGERING enhancements
 *  +  - add support for nested images
 *  +  - add support for image alignment
 *  +  - add support for custom table classes
 * 
 *  @return function Markdown($text)
 * 
 */

require_once(__DIR__ . '/Michelf/MarkdownExtra.inc.php');
require_once(__DIR__ . '/Edgering/MarkdownEdgering.php');

define("MARKDOWN_VERSION",\Michelf\MarkdownEdgering::MARKDOWNLIB_VERSION);

function Markdown($text)
{
    $text = \Michelf\MarkdownEdgering::defaultTransform($text);

    $text = \Michelf\MarkdownExtra::defaultTransform($text);

    $text = \Michelf\MarkdownEdgering::defaultPostTransform($text);

    return $text;
}
