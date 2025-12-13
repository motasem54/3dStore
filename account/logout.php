<?php
require_once '../includes/store-init.php';
session_destroy();
redirect('/account/login.php');