<?php
debug('==============================================================');
debug('ログイン認証');
debug('==============================================================');
if (!empty($_SESSION['login_date'])) {
  debug('ログインしているユーザーです');


  if ($_SESSION['login_date'] + $_SESSION['login_limit'] < time()) {
    debug('ユーザーの有効期限が切れています');
    debug('セッションを切ります');
    session_destroy();
    header("Location: login.php");
    exit();
  } else {
    //有効期限以内なら
    debug('このユーザーはセッション有効期限内です');
    $_SESSION['login_date'] = time();

    if (basename($_SERVER['PHP_SELF']) === 'login.php') {
      debug('マイページへ移動します');
      header("Location: mypage.php"); //マイページへ
      exit();
    }
  }
} else {
  debug('ログインしていないユーザーです');
  if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header("Location:login.php"); //ログインページへ
    exit();
  }
}
