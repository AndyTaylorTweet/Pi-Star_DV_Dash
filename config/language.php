<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/config/security_headers.php');
setSecurityHeaders();

// Set the language
$pistarLanguage='english_uk';
include_once $_SERVER['DOCUMENT_ROOT']."/lang/$pistarLanguage.php";
?>
