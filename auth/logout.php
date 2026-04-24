<?php
session_start();
session_unset();
session_destroy();
header("Location: login.php?cleared=1");
exit;
?>

