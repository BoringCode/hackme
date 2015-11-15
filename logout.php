<?php
require("_inc/functions.php");

if ($auth->logged_in) $auth->logout();

header("Location: index.php");
?>