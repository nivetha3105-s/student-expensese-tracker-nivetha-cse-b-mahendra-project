<?php
require_once 'config/database.php';
redirect(is_logged_in() ? '/dashboard.php' : '/login.php');
