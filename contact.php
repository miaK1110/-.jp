<?php
require('function.php');

debug('==============================================================');
debug('お問い合わせページ');
debug('==============================================================');
debugLogStart();

$err_msg = array();



if (!empty($_SESSION['user_id'])) {
  debug('ログインしているユーザーです');

  if ($_SESSION['login_limit'] < 60 * 60) {
    debug('セッション有効期限が１時間以内のユーザーなのでさらに１時間更新します');
    $sesLimit = 60 * 60;
    $_SESSION['login_limit'] = $sesLimit;
  }
  debug('session変数の内容:' . print_r($_SESSION, true));
  //misa is beautiful;
  $userInfo = userInfoAll($_SESSION['user_id']);
  debug('userInfoの中身:' . print_r($userInfo, true));
  debug('POST送信の内容:' . print_r($_POST, true));

  if ($_POST) {

    debug('POST送信されました');
    debug('POST送信の内容:' . print_r($_POST, true));

    $name = $userInfo['family_name'] . $userInfo['given_name'];
    $email = $userInfo['email'];
    $to = 'test@test.com';
    $subject = $name . '様からのお問い合わせ';
    $comment = filter_input(INPUT_POST, 'comment');

    debug('未入力チェック');

    $err_msg['name'] = emp($name);
    $err_msg['email'] = emp($email);
    $err_msg['comment'] = emp($comment);
    debug('エラーメッセージの内容:' . print_r($err_msg, true));

    if (empty(array_filter($err_msg))) {

      debug('エラーメッセージの内容:' . print_r($err_msg, true));
      debug('バリデーションokです');

      sendEmail('FROM:' . $email, $to, $subject, $comment);
      debug('メールの宛元:' . $email);
      debug('メールの宛先:' . $to);
      debug('メールのサブジェクト:' . $subject);
      debug('メールの内容:' . $comment);
      debug('マイページへ移動します');
      $_SESSION['notice'] = SUC08;
      header("location: mypage.php");
      exit();
    }
  }
} else {
  debug('ログインしていないユーザーです');
  if ($_POST) {

    $name = filter_input(INPUT_POST, 'name');
    $email = filter_input(INPUT_POST, 'email');
    $to = 'test@test.com';
    $subject = $name . '様からのお問い合わせ';
    $comment = filter_input(INPUT_POST, 'comment');
    debug($name);
    debug($email);
    debug($to);
    debug($subject);
    debug($comment);

    debug('未入力チェック');
    $err_msg['name'] = emp($name);
    $err_msg['email'] = emp($email);
    $err_msg['comment'] = emp($comment);
    if (empty(array_filter($err_msg))) {
      debug('バリデーションokです');

      sendEmail('FROM:' . $email, $to, $subject, $comment);
      debug('メールの宛元:' . $email);
      debug('メールの宛先:' . $to);
      debug('メールのサブジェクト:' . $subject);
      debug('メールの内容:' . $comment);
      debug('トップページへ移動します');
      $_SESSION['notice'] = SUC08;
      header("location: index.php");
      exit();
    }
  }
}

debug('==============================================================');
debug('画面表示処理終了');
debug('==============================================================');
?>
<!-- headタグ内 -->
<?php $siteTitle = 'お問い合わせ';
require('head.php'); ?>

<!-- ヘッダータグ内 -->
<?php require('header.php'); ?>


<div class="notice">
  <p>
    お問い合わせが必要な方は下記のフォームを記入して<br />
    送信ボタンを押してください。入力していただいたメールアドレス宛に<br />
    返信させていただきます。
  </p>
</div>

<div class="form-container">
  <form class="form-common" method="post" action="">
    <div class="err-msg">
      <?php
      if (!empty($err_msg['name'])) echo $err_msg['name'];
      ?>
    </div>
    <label class="<?php if (!empty($err_msg['name'])) echo 'err'; ?>"><span class="form-badge">必須</span>
      名前（匿名可）
      <input type="text" name="name" value="<?php if (!empty($_SESSION['login_date'])) echo sanitize($userInfo['nickname']); ?><?php if (!empty($_POST['name'])) echo sanitize($_POST['name']); ?>" />
    </label>
    <div class="err-msg">
      <?php
      if (!empty($err_msg['email'])) echo $err_msg['email'];
      ?>
    </div>
    <label class="<?php if (!empty($err_msg['email'])) echo 'err'; ?>"><span class="form-badge">必須</span>
      メールアドレス
      <input type="text" name="email" value="<?php if (!empty($_SESSION['login_date'])) echo sanitize($userInfo['email']); ?><?php if (!empty($_POST['email'])) echo sanitize($_POST['email']); ?> " />
    </label>
    <div class="err-msg">
      <?php
      if (!empty($err_msg['comment'])) echo $err_msg['comment'];
      ?>
    </div>
    <label class="<?php if (!empty($err_msg['comment'])) echo 'err'; ?>"><span class="form-badge">必須</span>内容<br /><br />
      <textarea name="comment" cols="30" rows="10" placeholder="<?php if (!empty($_POST['comment'])) echo sanitize($_POST['comment']); ?>"></textarea>
    </label>
    <br />
    <div class="center">
      <input class="btn btn-brown" type="submit" value="送信" />
    </div>
  </form>
</div>
<!-- フッタータグ内 -->
<?php
require('footer.php');
?>