<?php
require('function.php');

debug('==============================================================');
debug('退会ページ');
debug('==============================================================');
debugLogStart();

require('auth.php');

//postされたら
if (!empty($_POST)) {
  debug('POST送信があります。');

  $err_msg = array();

  try {
    $dbh = dbConnect();
    $sql = 'UPDATE users SET delete_flg = 1 WHERE id = :u_id';
    $data = array(':u_id' => $_SESSION['user_id']);
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      session_destroy();
      $_SESSION['notice'] = SUC03;
      debug('セッション変数の中身：' . print_r($_SESSION, true));
      debug('トップページへ遷移します。');
      header("Location: index.php");
    } else {
      debug('クエリが失敗しました。');
      $err_msg['common'] = ERR01;
      debug('SQLエラー' . print_r($stmt->errorInfo(), true));
    }
  } catch (Exception $e) {
    error_log('退会ページでエラー発生:' . $e->getMessage());
    $err_msg['common'] = ERR01;
  }
}
debug('==============================================================');
debug('画面表示処理終了');
debug('==============================================================');
?>
<!-- headタグ内 -->
<?php $siteTitle = '退会ページ';
require('head.php'); ?>

<!-- ヘッダータグ内 -->
<?php require('header.php'); ?>

<p>退会ボタンを押すと退会できます。</p>

<form action="" method="post" class="form-delete">
  <?php
  if (!empty($err_msg['common'])) echo $err_msg['common'];
  ?>
  </div>
  <input type="submit" class="btn btn-pink" value="退会する" name="submit">
</form>
<!--フッタータグ内 -->
<?php
require('footer.php');
?>