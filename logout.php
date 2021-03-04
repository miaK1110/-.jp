<?php
require('function.php');


debug('==============================================================');
debug('ログアウトページ');
debug('==============================================================');
debugLogStart();

require('auth.php');

debug('ログアウトします');
session_start();
$_SESSION = array();
session_destroy();

if (empty($_SESSION)) {
  debug('マイページへ遷移します');
  header("Location: index.php");
  exit();
} else {
  return false;
}

debug('==============================================================');
debug('画面表示処理終了');
debug('==============================================================');
