<?php
require_once __DIR__ . '/bootstrap.php';
function require_login() { if (empty($_SESSION['user_id'])) { header('Location: index.php'); exit; } }
function current_user() { return empty($_SESSION['user_id']) ? ['id'=>0,'login'=>'','role'=>''] : ['id'=>(int)$_SESSION['user_id'],'login'=>$_SESSION['login']??'','role'=>$_SESSION['role']??'']; }