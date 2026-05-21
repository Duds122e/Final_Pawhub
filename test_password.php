<?php
$hash = '$2y$13$51WfAWZm1bWkBvsWMq1tTea5E3koeYMCuyDlftp85uL4hzDTJGERu';
$password = 'Admin@123';
$result = password_verify($password, $hash);
echo $result ? 'PASSWORD MATCHES' : 'PASSWORD DOES NOT MATCH';
