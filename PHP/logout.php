<?php
session_start();


$_SESSION = [];
session_unset();
session_destroy();

session_start();
session_regenerate_id(true);


header("Location: ../Login-Signup/login.html");
exit();
?>
