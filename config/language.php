<?php
// Set the language
$pistarLanguage='english_uk';

// Default strings
include_once $_SERVER['DOCUMENT_ROOT']."/lang/english_uk.php";
// Load the language support
include_once $_SERVER['DOCUMENT_ROOT']."/lang/$pistarLanguage.php";
// Combine
$lang=array_merge($lang,$lang2);
?>
