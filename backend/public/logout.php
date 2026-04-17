<?php
require_once __DIR__ . '/lib/session.php';
logout_user();
header('Location: index.php');
