<?php
require('function.php');

debug('==============================================================');
debug('パスワード再発行ページ（送信用）');
debug('==============================================================');
debugLogStart();

$err_msg = array();

if (!empty($_POST)) {
  debug('POST送信されました');

  $email = filter_input(INPUT_POST, 'email');
  debug('POSTされたEmailの情報:' . $email);

  $err_msg['email'] = emp($email);
  $err_msg['email'] = validEmail($email);

  if (empty(array_filter($err_msg))) {
    debug('バリデーションチェックokです');
    try {
      $dbh = dbConnect();
      $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
      $data = array(
        ':email' => $email
      );
      $stmt = queryPost($dbh, $sql, $data);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($result) {
        debug('クエリ成功');

        $token = makeToken();
        debug('Tokenの中身:' . $token);

        $from = 'test@test.com';
        $to = $email;
        $subject = 'パスワードの認証キー発行';
        $comment = <<<EOT
パスワード再発行のための認証キー（8文字）を入力画面でご入力ください。
認証キー入力画面
http://localhost:8888/webservice_practice07/passRemindRecieve.php
【認証キー:{$token}】
※認証キーの有効期限は30分です。
EOT;

        sendEmail($from, $to, $subject, $comment);

        debug('メールの宛元:' . $email);
        debug('メールの宛先:' . $to);
        debug('メールのサブジェクト:' . $subject);
        debug('メールの内容:' . $comment);

        //セッションに情報をつめる
        $_SESSION['token'] = $token;
        $_SESSION['email'] = $email;
        $_SESSION['token_limit'] = time() + 60 * 30;

        debug('パスワード再発行用のセッション変数の中身:' . print_r($_SESSION, true));
        debug('パスワード再発行（受）に移動します');
        header("Location: passRemindRecieve.php");
        exit();
      } else {
        debug('クエリ失敗');
        debug('SQLエラー' . print_r($stmt->errorInfo(), true));
      }
    } catch (Exception $e) {
      error_log('パスワード再発行画面でエラー発生:' . $e->getMessage());
      $err_msg['common'] = ERR01;
    }
  }
}

debug('==============================================================');
debug('画面表示処理終了');
debug('==============================================================');
?>
<!-- headタグ内 -->
<?php $siteTitle = 'パスワード再発行';
require('head.php'); ?>

<!-- ヘッダータグ内 -->
<?php require('header.php'); ?>


<div class="notice">
  <p>
    ご入力していただいたメールアドレス宛にパスワード再発行するための<br />
    認証キーつきメールをお送りいたします。
  </p>
</div>

<div class="form-container">
  <form method="post" action="" class="form-common">
    <div class="err-msg-common">
      <?php
      if (!empty($err_msg['common'])) echo sanitize($err_msg['common']);
      ?>
    </div>
    <label><span class="form-badge">必須</span>
      登録メールアドレス
      <input type="text" name="email" value="<?php if (!empty($_POST['email'])) echo sanitize($_POST['email']); ?>" />
    </label>

    <div class="center">
      <input class="btn btn-brown" type="submit" value="メール送信" />
    </div>
  </form>
</div>
<!-- フッタータグ内 -->
<?php
require('footer.php');
?>