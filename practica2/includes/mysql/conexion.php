<?php
require_once __DIR__ . '/../config.php';

$conn = mysqli_connect(BD_HOST, BD_USER, BD_PASS, BD_NAME);
if (!$conn) die("Error BD: " . mysqli_connect_error());

echo "BD OK";