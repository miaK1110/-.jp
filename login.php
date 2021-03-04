<?php
require('function.php');


debug('==============================================================');
debug('ログインページ');
debug('==============================================================');
debugLogStart();

require('auth.php');

if ($_POST) {
  debug('POST送信されました');
  debug('バリデーションを行います');

  $email = filter_input(INPUT_POST, 'email');
  $pass = filter_input(INPUT_POST, 'pass');
  $pass_save = (!empty(filter_input(INPUT_POST, 'pass_save'))) ? true : false;

  debug('POSTの情報:' . print_r($_POST, true));

  //未入力チェック
  $err_msg['email'] = emp($email);
  $err_msg['pass'] = emp($pass);

  $err_msg['email'] = maxLen($email);
  $err_msg['email'] = validEmail($email);
  $err_msg['pass'] = validPass($pass);

  if ($err_msg['email'] === null && $err_msg['pass'] === null) {
    debug('バリデーションチェックokです');
    try {
      $dbh = dbConnect();
      $sql = 'SELECT password, id FROM users WHERE email = :email';
      $data = array(
        ':email' => $email
      );
      $stmt = queryPost($dbh, $sql, $data);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      if (!empty($result) && password_verify($pass, array_shift($result))) {
        debug('パスワード照合okです');
        debug('セッションに情報を詰めます');
        $_SESSION['user_id'] = $result['id'];
        $sesLimit = 60 * 60;
        $_SESSION['login_date'] = time();
        if ($pass_save) {
          $_SESSION['login_limit'] = $sesLimit * 24 * 30;
        } else {
          $_SESSION['login_limit'] = $sesLimit;
        }
        debug('セッション変数の中身:' . print_r($_SESSION, true));
        debug('マイページへ移動します');
        header("Location: mypage.php");
        exit();
      } else {
        debug('メールアドレスまたはパスワードが違います');
        $err_msg['common'] = 'メールアドレスまたはパスワードが違います';
      }
    } catch (Exception $e) {
      error_log('ログイン画面でエラー発生:' . $e->getMessage());
      $err_msg['common'] = ERR01;
    }
  }
}
debug('==============================================================');
debug('画面表示処理終了');
debug('==============================================================');
?>
<!-- headタグ内 -->
<?php $siteTitle = 'ログイン';
require('head.php'); ?>

<!-- ヘッダータグ内 -->
<?php require('header.php'); ?>


<div class="notice">
  <p>各種サービスをご利用いただくにはログインが必要です。</p>
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
      if (!empty($err_msg['email'])) echo $err_msg['email'];
      ?>
    </div>
    <label class="<?php if (!empty($err_msg['given_name'])) echo 'err'; ?>"><span class="form-badge">必須</span>
      メールアドレス
      <input type="text" name="email" value="<?php if (!empty($_POST['email'])) echo $_POST['email']; ?>" />
    </label>
    <div class="err-msg">
      <?php
      if (!empty($err_msg['pass'])) echo $err_msg['pass'];
      ?>
    </div>
    <label style=" margin-top:20px;" class="<?php if (!empty($err_msg['given_name'])) echo 'err'; ?>"><span class=" form-badge">必須</span>
      パスワード
      <input type="password" name="pass" value="<?php if (!empty($_POST['pass'])) echo $_POST['pass']; ?>" />
    </label>


    <label class="center">
      <input type="checkbox" name="pass_save" />ログイン状態を保持する
    </label>
    <br />
    <div class="center">
      <input class="btn btn-brown" type="submit" value="ログイン" />
    </div>
  </form>
</div>

<div class="notice">
  <a href="passRemaindSend.php">パスワードをお忘れですか？（パスワード再発行）</a>
  <br />
  <a href="signup.php">会員登録がまだの方はこちら</a>
</div>
<!-- フッタータグ内 -->
<?php
require('footer.php');
?>