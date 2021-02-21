<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('===========================================================');
debug('Ajax');
debug('===========================================================');
debugLogStart();

// postがある場合
if (isset($_POST['animalId']) && isLogin()) {
  $a_id = $_POST['animalId'];
  debug('post送信があります');
  debug('$a_id' . print_r($a_id, true));

  try {
    //DB接続
    $dbh = dbConnect();
    // favouriteテーブルからIDとユーザーIDが一致したレコードを取得するSQL文
    $sql = 'SELECT * FROM favourite WHERE animal_id = :a_id AND user_id = :u_id';
    $data = array(':a_id' => $a_id, 'u_id' => $_SESSION['user_id']);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $resultCount = $stmt->rowCount();
    // レコードが1件でもある場合
    if (!empty($resultCount)) {
      // レコードを削除する
      $sql = 'DELETE FROM favourite WHERE animal_id = :a_id AND user_id = :u_id';
      $data = array(':a_id' => $a_id, ':u_id' => $_SESSION['user_id']);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      debug('お気に入り登録を解除します');
    } else {
      // レコードを挿入する
      $sql = 'INSERT INTO favourite (animal_id, user_id, create_date) VALUES (:a_id, :u_id, :date)';
      $data = array(':a_id' => $a_id, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      debug('お気に入り登録します');
    }
  } catch (Exception $e) {
    error_log('お気に入り登録機能でエラー発生：' . $e->getMessage());
  }
}
debug('Ajax処理終了 =================================================');
