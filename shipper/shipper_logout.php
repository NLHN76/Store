<?php
session_start();
session_unset();
session_destroy();
header("Location: shipper_login.php");
exit;
