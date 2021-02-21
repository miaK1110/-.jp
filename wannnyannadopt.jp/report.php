<?php
require('function.php');

debug('==============================================================');
debug('違反報告ページ');
debug('==============================================================');
debugLogStart();

//エラーメッセージ初期化
$err_msg = array();
//保護犬・猫のidを格納
$a_id = $_GET['a_id'];

debug('getの中身:' . print_r($_GET, true));
debug('$a_idの中身（掲載No）:' . $a_id, true);

if (!empty($_SESSION['user_id'])) {
  debug('ログインしているユーザーです');

  if ($_SESSION['login_limit'] < 60 * 60) {
    debug('セッション有効期限が１時間以内のユーザーなのでさらに１時間更新します');
    $sesLimit = 60 * 60;
    $_SESSION['login_limit'] = $sesLimit;
  }
  debug('session変数の内容:' . print_r($_SESSION, true));
  debug('POST送信の内容:' . print_r($_POST, true));

  if ($_POST) {

    debug('POST送信されました');
    debug('POST送信の内容:' . print_r($_POST, true));

    $name = $userInfo['family_name'] . $userInfo['given_name'];
    $email = $userInfo['email'];
    $to = 'test@test.com';
    $subject = $name . '様からの違反報告／掲載No.' . $a_id;
    $comment = filter_input(INPUT_POST, 'comment');

    debug('未入力チェック');

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
      debug('トップページへ移動します');
      $_SESSION['notice'] = SUC07;
      header("location: index.php");
      exit();
    }
  }
} else {
  debug('ログインしていないユーザーです');
  if ($_POST) {

    $name = filter_input(INPUT_POST, 'name');
    $email = filter_input(INPUT_POST, 'email');
    $to = 'test@test.com';
    $subject = $name . '様からの違反報告／掲載No.' . $a_id;
    $comment = filter_input(INPUT_POST, 'comment');
    debug($name);
    debug($email);
    debug($to);
    debug($subject);
    debug($comment);

    debug('未入力チェック');
    $err_msg['comment'] = emp($comment);
    if (empty(array_filter($err_msg))) {
      debug('バリデーションokです');

      sendEmail('FROM:' . $email, $to, $subject, $comment);
      debug('メールの宛元:' . $email);
      debug('メールの宛先:' . $to);
      debug('メールのサブジェクト:' . $subject);
      debug('メールの内容:' . $comment);
      debug('トップページへ移動します');
      $_SESSION['notice'] = SUC07;
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
<?php $siteTitle = '違反報告ページ';
require('head.php'); ?>

<!-- ヘッダータグ内 -->
<?php require('header.php'); ?>

<div class="notice">
  <p>規約違反が見られた場合、こちらから報告をお願いいたします。<BR>
    調査ののち対応させていただきます。</p>
</div>

<div class="form-container">
  <form class="form-common" method="post" action="">
    <label><span class="form-badge">任意</span>
      名前（匿名可）
      <input type="text" name="name" value="<?php if (!empty($_POST['name'])) echo sanitize($_POST['name']); ?>">
    </label>
    <label><span class="form-badge">任意</span>
      メールアドレス（返信をご希望される方）
      <input type="text" name="email" value="<?php if (!empty($_POST['name'])) echo sanitize($_POST['email']); ?>">
    </label>
    <label><span class="form-badge">必須</span>違反内容<BR><BR>
      <textarea name="comment" cols="30" rows="10" placeholder="<?php if (!empty($_POST['comment'])) echo sanitize($_POST['comment']); ?>"></textarea>
    </label>
    <BR>
    <div class="center">
      <input class="btn btn-pink" type="submit" value="送信"></input>
    </div>
  </form>
</div>
<!-- フッタータグ内 -->
<?php
require('footer.php');
?>