<?php
if (session_status() == PHP_SESSION_NONE) {
   session_start();
}

define('GEMINI_API_KEY', 'AIzaSyChI4dK1j-k7E6nWlZog5057Wq5JRvlOM0');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent');
define('BASE_URL', 'http://localhost/webdev_project/');

date_default_timezone_set('Asia/Kolkata');
?>
