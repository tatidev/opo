<?php

$smtp_app_user = getenv('SMTP_APP_EMAIL');
$smtp_app_pass = getenv('SMTP_APP_PASSWORD');

$config['protocol'] = 'smtp';
$config['smtp_host'] = 'smtp.gmail.com';
$config['smtp_port'] = 587;
$config['smtp_user'] = $smtp_app_user;
$config['smtp_pass'] = $smtp_app_pass;
$config['smtp_crypto'] = 'tls';
$config['mailtype'] = 'html';
$config['charset'] = 'utf-8';
$config['wordwrap'] = TRUE;
$config['newline'] = "\r\n";
