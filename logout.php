<?php
// logout.php
require_once 'Auth.php';

$auth = new Auth();
$auth->logout();

header("Location: index.php");
exit();
?>
