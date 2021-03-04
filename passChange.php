<?php
require('function.php');

debug('==============================================================');
debug('パスワード変更ページ');
debug('==============================================================');
debugLogStart();

require('auth.php');

$userInfo = userInfoAll($_SESSION['user_id']);
debug('userInfoの中身:' . print_r($userInfo, true));

if ($_POST) {
  debug('post送信されました');

  $pass_old = filter_input(INPUT_POST, 'pass_old');
  $pass_new = filter_input(INPUT_POST, 'pass_new_re');
  $pass_new_re = filter_input(INPUT_POST, 'pass_new_re');

  debug('postの中身:' . print_r($_POST, true));

  emp($pass_old, 'pass_old');
  emp($pass_new, 'pass_new');
  emp($pass_new_re, 'pass_new_re');

  if (empty($err_msg)) {
    debug('未入力チェックokです');
    validPass($pass_old, 'pass_old');
    validPass($pass_new, 'pass_new');
    validMatch($pass_new, $pass_new_re, 'pass_new_re');

    if (!password_verify($pass_old, $userInfo['password'])) {
      $err_msg['pass_old'] = MSG10;
      debug('古いパスワードがＤＢデータと一致しません');
    }
    if ($pass_old === $pass_new) {
      $err_msg['pass_new'] = MSG11;
      debug('古いパスワードと新しいパスワードが同じです');
    }

    if (empty($err_msg)) {
      debug('バリデーションokです');


      try {
        $dbh = dbConnect();
        $sql = 'UPDATE users SET password=:password WHERE id=:id';
        $data = array(
          ':password' => password_hash($pass_new, PASSWORD_DEFAULT),
          ':id' => $_SESSION['user_id']
        );
        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
          debug('パスワードを変更しました');

          $_SESSION['notice'] = SUC01;

          //メール送信する
          $username = $userInfo['family_name'] . $userInfo['given_name'];
          $from = 'miii0327@icloud.com';
          $to = $userInfo['email'];
          $subject = 'パスワードを変更しました';
          $comment = <<<EOT
{$username}様、いつもご利用いただきありがとうございます。
パスワードが正常に変更されました。
EOT;
          sendEmail($from, $to, $subject, $comment);
          debug('メールの宛元:' . $from);
          debug('メールの宛先:' . $to);
          debug('メールのサブジェクト:' . $subject);
          debug('メールの内容:' . $comment);
          debug('マイページへ移動します');
          header("location: mypage.php");
          exit();
        } else {
          debug('パスワード変更画面でSQLエラー発生');
          debug('SQLエラー' . print_r($stmt->errorInfo(), true));
          return false;
        }
      } catch (Exception $e) {
        error_log('パスワード変更画面でエラー発生:' . $e->getMessage());
        $err_msg['common'] = ERR01;
      }
    }
  }
}

debug('画面処理終了,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,');
?>
<!-- headタグ内 -->
<?php $siteTitle = 'パスワード変更';
require('head.php'); ?>

<!-- ヘッダータグ内 -->
<?php require('header.php'); ?>

<div class="notice">
  <p>
    下記のフォームを入力後<br />
    パスワード変更ボタンを押してください
  </p>
</div>

<div class="form-container">
  <form class="form-common" method="post" action="">
    <div class="err-msg-common">
      <?php
      if (!empty($err_msg['common'])) echo $err_msg['common'];
      ?>
    </div>
    <div class="err-msg">
      <?php
      if (!empty($err_msg['pass_old'])) echo $err_msg['pass_old'];
      ?>
    </div>
    <label class="<?php if (!empty($err_msg['pass_old'])) echo 'err'; ?>"><span class="form-badge">必須</span>
      今お使いのパスワード
      <input type="password" name="pass_old" value="<?php if (!empty($_POST['pass_old'])) echo $_POST['pass_old']; ?>" />
    </label>
    <div class="err-msg">
      <?php
      if (!empty($err_msg['pass_new'])) echo $err_msg['pass_new'];
      ?>
    </div>
    <label class="<?php if (!empty($err_msg['pass_new'])) echo 'err'; ?>"><span class="form-badge">必須</span>
      変更後のパスワード
      <input type="password" name="pass_new" value="<?php if (!empty($_POST['pass_new'])) echo $_POST['pass_new']; ?>" />
    </label>
    <div class="err-msg">
      <?php
      if (!empty($err_msg['pass_new_re'])) echo $err_msg['pass_new_re'];
      ?>
    </div>
    <label class="<?php if (!empty($err_msg['pass_new_re'])) echo 'err'; ?>"><span class="form-badge">必須</span>
      変更後のパスワード（再入力確認用）
      <input type="password" name="pass_new_re" value="<?php if (!empty($_POST['pass_new_re'])) echo $_POST['pass_new_re']; ?>" />
    </label>

    <br />
    <div class="center">
      <input class="btn btn-brown" type="submit" value="パスワード変更" />
    </div>
  </form>
</div>
<!-- フッタータグ内 -->
<?php
require('footer.php');
?>