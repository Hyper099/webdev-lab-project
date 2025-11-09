<?php

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

define('GEMINI_API_KEY', 'AIzaSyD7VXjQ2VPazDuS45DMCs4JVBYCYelj1qw');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent');

define('BASE_URL', 'http://localhost/webdev_project/');

date_default_timezone_set('Asia/Kolkata');
?>
