<?php
define('UKLOOLE', true);
if(session_status()===PHP_SESSION_NONE) session_start();
session_destroy();
header('Location: /admin/login.php');
exit;
