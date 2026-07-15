<?php
session_start();
session_unset();
session_destroy();
header("Location: login/shipper_login.php");
exit;