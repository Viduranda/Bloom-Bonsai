<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

respond([
    'success' => true,
    'message' => 'Logged out successfully'
]);
