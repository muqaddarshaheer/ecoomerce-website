<?php
require_once __DIR__ . '/../includes/init.php';
logout_user();
set_flash('success', 'Admin logged out.');
redirect('admin/login.php');
