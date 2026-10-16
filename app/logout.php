<?php
session_start();
session_destroy();
header("Location: ../public/loginGL.php");
exit;
?>