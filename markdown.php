<?php

/**
 *  Set proper Markdown version
 *
 *  @return function Markdown($text)
 * 
 */

if (function_exists('Markdown')) {
    return;
}

if (version_compare(PHP_VERSION, '7.4.0','<')) {
    require_once(__DIR__ . '/Michelf/previous/markdown-edgering-1.2.4.php');
} else {    
    require_once(__DIR__ . '/init.php');
}
