<?php
require_once __DIR__ . '/../includes/init.php';
logout_user();
set_flash('success', 'Employee logged out.');
redirect('employee/login.php');
