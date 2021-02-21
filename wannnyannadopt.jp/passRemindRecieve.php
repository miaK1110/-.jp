<?php
require('function.php');

debug('==============================================================');
debug('パスワード再発行ページ（送信用）');
debug('==============================================================');
debugLogStart();

if (empty($_SESSION['token'])) {
  debug('Tokenを持ってない方がこのページを訪れました');
  debug('パスワード再発行（送）に移動します');
  header("Location: passRemindSend.php");
  exit();
}

if (!empty($_POST['token'])) {

  //エラーメッセージ初期化
  $err_msg = array();

  $token = filter_input(INPUT_POST, 'token');
  $err_msg = array();
  $err_msg['token'] = emp($token);
  $err_msg['token'] = validAlphaNum($token);

  if (empty(array_filter($err_msg))) {

    if ($token !== $_SESSION['token']) {
      $err_msg['common'] = ERR01;
    }

    if (time() > $_SESSION['token_limit']) {
      $err_msg['common'] = ERR01;
    }

    $pass_new = makeToken();
    debug('再発行されたパスワード:' . $pass_new);

    try {
      $dbh = dbConnect();
      $sql = 'UPDATE users SET password = :password WHERE email = :email AND delete_flg = 0';
      $data = array(
        ':password' => password_hash($pass_new, PASSWORD_DEFAULT),
        ':email' => $_SESSION['email']
      );
      $stmt = queryPost($dbh, $sql, $data);
      //ここから
      if ($stmt) {
        debug('クエリ成功');

        //メールを送信します
        $from = "test@test.com";
        $to = $_SESSION['email'];
        $subject = "パスワードを再発行しました";
        $comment = <<<EOT
パスワードの再発行が完了しました。
新しいパスワード:{$pass_new}】
ログイン画面で新しいパスワードをご入力ください
EOT;

        sendEmail($from, $to, $subject, $comment);

        debug('メールの宛元:' . $email);
        debug('メールの宛先:' . $to);
        debug('メールのサブジェクト:' . $subject);
        debug('メールの内容:' . $comment);
        debug('新しいパスワード:' . $pass_new);

        session_unset();
        $_SESSION['notice'] = SUC10;
        debug('セッション変数の中身：' . print_r($_SESSION, true));
        debug('ログインページへ移動します');
        header("Location: login.php");
        exit();
      } else {
        debug('クエリに失敗しました');
        debug('SQLエラー' . print_r($stmt->errorInfo(), true));
      }
      //ここまでに問題あり
    } catch (Exception $e) {
      error_log('パスワード再発行（受）でエラー発生:' . $e->getMessage());
    }
  }
}

debug('セッションの中身:' . print_r($_SESSION, true));

debug('画面処理終了,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,');
?>
<!-- headタグ内 -->
<?php $siteTitle = 'パスワード再発行';
require('head.php'); ?>

<!-- ヘッダータグ内 -->
<?php require('header.php'); ?>

<div class="notice">
  <p>
    認証キーをご入力ください。<br />認証キーに間違いがなければ登録メールアドレス宛に<br />新しいパスワードをお送りいたします。
    パスワードはランダムな文字で作られますので再発行後は<br />マイページのパスワード変更からパスワードを変更することをおすすめいたします。
  </p>
</div>

<div class="form-container">
  <form method="post" action="" class="form-common">
    <div class="err-msg-common">
      <?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?>
    </div>
    <label class="<?php if (!empty($err_msg['token'])) echo 'err'; ?>"><span class="form-badge">必須</span>
      認証キー（8文字）
      <input type="text" name="token" value="<?php if (!empty($_POST['token'])) echo sanitize($_POST['token']); ?>" />
    </label>

    <div class="center">
      <input class="btn btn-brown" type="submit" value="再発行" />
    </div>
  </form>
</div>
<!-- フッタータグ内 -->
<?php
require('footer.php');
?>