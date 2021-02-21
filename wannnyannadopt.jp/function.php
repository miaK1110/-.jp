<?php
//=========================================
//エラーログの設定
//=========================================
ini_set('log_errors', 'On');
ini_set('error_log', 'php.log');
//=========================================
//デバッグ用設定
//=========================================
//開発中のみtrue、それ以外はfalseにする
$debug_flg = true;
//デバッグログ用の関数
function debug($str)
{
  global $debug_flg;
  if ($debug_flg === true) {
    error_log('デバッグ:' . $str);
  } else {
    return false;
  }
}

//=========================================
//セッション用の設定
//=========================================
session_save_path("C:/MAMP/bin/php/var");
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
ini_set('session_cookie_lifetime', 60 * 60 * 24 * 30);
session_start();
session_regenerate_id();

//=========================================
//画面処理開始時用の関数
//=========================================
function debugLogStart()
{
  debug('==============================================================');
  debug('画面表示処理開始');
  debug('==============================================================');
  debug('SessionID:' . session_id());
  debug('SessionIDの配列の中身:' . print_r($_SESSION, true));
  debug('Time：' . time());
  if (!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])) {
    debug('Login time limit：' . ($_SESSION['login_date'] + $_SESSION['login_limit']));
  } else {
    return false;
  }
}
//=========================================
//エラーメッセージ用定数
//=========================================
define('MSG01', '入力必須の項目です');
define('MSG02', '文字が長すぎます');
define('MSG03', 'Emailの形式でご入力ください');
define('MSG04', '半角英数字のみご利用いただけます');
define('MSG05', '6文字以上で入力してください');
define('MSG06', 'そのメールアドレスは既に登録されています');
define('MSG07', 'パスワードとパスワード（再入力）が合っていません');
define('MSG08', '半角数字のみご利用いただけます');
define('MSG09', '選択してください');
define('MSG10', 'パスワードが違います');
define('MSG11', '今お使いのパスワードと新しいパスワードが同じです');
define('MSG12', '記号はご利用いただけません');
define('SUC01', 'パスワードを変更しました');
define('SUC02', 'プロフィールを変更しました');
define('SUC03', '退会しました');
define('SUC04', 'このチャットページでは掲載者に質問や里親の申し出ができます');
define('SUC05', '掲載完了です');
define('SUC06', '編集完了です');
define('SUC07', '違反報告が送信されました');
define('SUC08', 'お問い合わせが送信されました');
define('SUC09', '譲渡決定しました！おめでとうございます！');
define('SUC10', 'メールアドレス宛に新しいパスワードを送信しました。ログイン画面で新しいパスワードを使いログインしていただいた後、パスワード変更ページでお好きなパスワードに変更できます');
define('SUC11', '会員登録が完了しました。プロフィール編集から自己紹介をしてください');
define('ERR01', 'エラーが発生しました。しばらく経ってからもう一度お試しください');
define('ERR02', 'メールアドレスまたはパスワードが違います');
define('ERR03', '自分に質問・譲渡の申し込みをすることはできません');
define('ERR04', '問題が発生したのでトップページに戻りました');

//=========================================
//各種バリデーション
//=========================================
//未入力チェックのバリデーション
function emp($str)
{
  if (empty($str)) {
    return MSG01;
  } else {
    return null;
  }
}
//文字の長さチェック(defaultはmax255文字)
function maxLen($str, $len = 255)
{
  if (mb_strlen($str) >= $len) {
    return MSG02;
  } else {
    return null;
  }
}
//Emailの形式チェック
function validEmail($str)
{
  if (!filter_var($str, FILTER_VALIDATE_EMAIL)) {
    return MSG03;
  } else {
    return null;
  }
}
//Emailの重複チェック
function emailDupCheck($email)
{
  try {
    $dbh = dbConnect();
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);
    $stmt = queryPost($dbh, $sql, $data);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty(array_shift($result))) {
      debug('登録済みのEmailが使われました');
      return MSG06;
    } else {
      debug('Email重複checkをパスしました');
    }
  } catch (Exception $e) {
    error_log('Email重複チェック関数でエラーが発生しました:' . $e->getMessage());
  }
}
//半角英数字かチェック(空文字×)
function validAlphaNum($str)
{
  if (!preg_match("/^[0-9a-zA-Z]+$/", $str)) {
    return MSG04;
  } else {
    return null;
  }
}
//最小文字数チェック（パスワード用）
function minLen($str, $len = 6)
{
  if (mb_strlen($str) > $len) {
    return MSG05;
  } else {
    return null;
  }
}
//同値チェック。パスワード再確認用
function validMatch($str1, $str2)
{
  if ($str1 !== $str2) {
    return MSG07;
  } else {
    return null;
  }
}
//半角数字かチェック(空文字×)
function validNum($str)
{
  if (!preg_match("/^[0-9]+$/", $str)) {
    return MSG08;
  } else {
    return null;
  }
}
//セレクトボックスが選択されているかチェック
function selectCheck($str)
{

  if (!isset($str)) {
    return MSG09;
  } else {
    return null;
  }
}
//パスワードチェック用の関数
function validPass($str)
{
  validAlphaNum($str);
  minLen($str);
  maxLen($str);
}
//=========================================
//DB接続用・クエリ実行用の関数
//=========================================
//DB接続用関数
function dbConnect()
{
  $dsn = 'mysql:dbname=animaladopt;host=localhost;port=8889;chars=utf8mb4';
  $user = 'root';
  $password = 'root';
  $options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}
//クエリ実行用の関数
function queryPost($dbh, $sql, $data)
{
  $stmt = $dbh->prepare($sql);
  $stmt->execute($data);

  if ($stmt) {
    debug('クエリ成功');
    return $stmt;
  } else {
    debug('クエリ失敗');
    $err_msg['common'] = MSG07;
    return false;
  }
}
//ユーザー情報の全てをIDを条件にDBから取得
function userInfoAll($user_id)
{
  debug('userInfo関数を呼び出します');

  try {
    $dbh = dbConnect();
    $sql = 'SELECT id, user_role, nickname, email, password, family_name, given_name, age, job, phone, zip, address, comment, pic, create_date, login_time, update_date FROM users WHERE id = :user_id AND delete_flg = 0';
    $data = array(
      ':user_id' => $user_id
    );
    $stmt = queryPost($dbh, $sql, $data);
    if ($stmt) {
      $stmt = $stmt->fetch(PDO::FETCH_ASSOC);
      return $stmt;
    } else {
      debug('クエリ失敗');
      debug('SQLエラー' . print_r($stmt->errorInfo(), true));
      return false;
    }
  } catch (Exception $e) {
    error_log('userInfoAll関数でエラー発生:' . $e->getMessage());
  }
}
//保護犬・猫情報を取得する関数
function animalInfoAll($a_id)
{
  debug('保護犬・猫の情報を取得します');
  debug('保護犬・保護猫のid:' . $a_id);
  try {
    $dbh = dbConnect();
    $sql = 'SELECT guardians_id, animal_type, name, gender, age, area, comment, pic1, pic2, pic3, delete_flg, create_date, update_date FROM animals WHERE id = :a_id';
    $data = array(
      ':a_id' => $a_id
    );
    $stmt = queryPost($dbh, $sql, $data);
    if ($stmt) {
      $stmt = $stmt->fetch(PDO::FETCH_ASSOC);
      return $stmt;
    } else {
      debug('クエリ失敗');
      debug('SQLエラー' . print_r($stmt->errorInfo(), true));
      return false;
    }
  } catch (Exception $e) {
    error_log('保護犬・猫取得関数でエラー発生:' . $e->getMessage());
  }
}
//保護犬・猫＋掲載者情報を取得する関数
function animalAndGuardianInfo($a_id)
{
  debug('保護犬・猫と掲載者の情報を取得します');
  debug('保護犬・猫のID' . $a_id);
  try {
    $dbh = dbConnect();
    $sql = 'SELECT a.id AS animal_id, a.guardians_id, a.animal_type, a.name, a.gender, a.age AS animal_age, a.area AS animal_area, a.comment AS animal_comment, a.pic1, a.pic2, a.pic3, a.create_date, a.update_date, u.id, u.user_role, u.nickname, u.email, u.family_name, u.given_name, u.age, u.job, u.phone, u.zip, u.address, u.comment, u.pic FROM animals AS a LEFT JOIN users as u ON a.guardians_id = u.id WHERE a.id = :a_id';
    $data = array(':a_id' => $a_id);
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      $stmt = $stmt->fetch(PDO::FETCH_ASSOC);
      return $stmt;
    } else {
      debug('クエリ失敗');
      debug('SQLエラー' . print_r($stmt->errorInfo(), true));
    }
  } catch (Exception $e) {
    error_log('animalAndGuardiansInfo関数でエラー発生' . $e->getMessage());
  }
}
//トップページ用(全部のレコードを表示)
function getAnimalList($currentMinNum = 1, $search, $a_type, $gender, $age, $area, $span = 21)
{
  debug('getAnimalList関数で数を取得します');

  try {
    $dbh = dbConnect();
    //数を数えて総数/21でページ数を出す為のsql
    $sql = 'SELECT id FROM animals ';
    if (!empty($search)) {
      $sql .= "WHERE MATCH(animal_type,name,gender,age,area,comment)  AGAINST('" . $search . "')";
    }
    if (!empty($a_type) || !empty($gender) && $gender != 'すべて' || !empty($age) || !empty($area)) {
      $sql .= 'WHERE';
      if (!empty($a_type)) $sql .= ' animal_type =' . ' "' . $a_type . '"';
      if (!empty($gender) && $gender === "オス" || $gender === "メス") {
        if (!empty($a_type)) {
          $sql .= ' AND gender =' . '"' . $gender . '"';
        } else {
          $sql .= ' gender =' . '"' . $gender . '"';
        }
      }
      if (!empty($age)) {
        if (!empty($a_type) || !empty($gender) && $gender != 'すべて') {
          $sql .= ' AND age =' . '"' . $age . '"';
        } else {
          $sql .= ' age =' . '"' . $age . '"';
        }
      }
      if (!empty($area)) {
        if (!empty($a_type) || !empty($gender) && $gender != 'すべて' || !empty($age)) {
          $sql .= ' AND area =' . '"' . $area . '"';
        } else {
          $sql .= ' area =' . '"' . $area . '"';
        }
      }
    }
    debug('sql:' . print_r($sql, true));
    $data = array();
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount();
    $rst['total_page'] = ceil($rst['total'] / $span);
    if (!$stmt) {
      debug('SQLエラー' . print_r($stmt->errorInfo(), true));
      return false;
    }

    // ページング用
    $sql = 'SELECT * FROM animals ';
    if (!empty($search)) {
      $sql .= "WHERE MATCH(animal_type,name,gender,age,area,comment)  AGAINST('" . $search . "')";
    }
    if (!empty($a_type) || !empty($gender) && $gender != 'すべて'  || !empty($age) || !empty($area)) {
      $sql .= 'WHERE';
      if (!empty($a_type)) $sql .= ' animal_type =' . ' "' . $a_type . '"';
      if (!empty($gender) && $gender === "オス" || $gender === "メス") {
        if (!empty($a_type)) {
          $sql .= ' AND gender =' . '"' . $gender . '"';
        } else {
          $sql .= ' gender =' . '"' . $gender . '"';
        }
      }
      if (!empty($age)) {
        if (!empty($a_type) || !empty($gender) && $gender != 'すべて') {
          $sql .= ' AND age =' . '"' . $age . '"';
        } else {
          $sql .= ' age =' . '"' . $age . '"';
        }
      }
      if (!empty($area)) {
        if (!empty($a_type) || !empty($gender) && $gender != 'すべて' || !empty($age)) {
          $sql .= ' AND area =' . '"' . $area . '"';
        } else {
          $sql .= ' area =' . '"' . $area . '"';
        }
      }
    }
    $sql .= ' LIMIT ' . $span . ' OFFSET ' . $currentMinNum;
    $data = array();
    debug('SQL：' . $sql);
    $stmt = queryPost($dbh, $sql, $data);
    if ($stmt) {
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    } else {
      return false;
      debug('SQLエラー' . print_r($stmt->errorInfo(), true));
    }
  } catch (Exception $e) {
    error_log('getAnimalListでエラー発生:' . $e->getMessage());
  }
}
//トップページ用（最新6件の掲載を表示,掲載中の物のみ）
function getNewAnimalSix()
{

  try {
    $dbh = dbConnect();
    $sql = 'select * from ( select * from animals WHERE delete_flg = 0 order by create_date desc limit 6 ) as A order by create_date';
    $data = array();

    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    } else {
      return false;
      debug('SQLエラー' . print_r($stmt->errorInfo(), true));
    }
  } catch (Exception $e) {
    debug('getAnimalSix関数でエラー発生:' . $e->getMessage());
  }
}
//ユーザーが登録した保護犬・猫を取得
function getMyAnimalList($u_id)
{
  debug('getMyAnimalListで' . $u_id . 'さんが登録した保護犬・猫を取得します');

  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM animals WHERE guardians_id = :u_id';
    $data = array(':u_id' => $u_id);

    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    } else {
      return false;
      debug('SQLエラー' . print_r($stmt->errorInfo(), true));
    }
  } catch (Exception $e) {
    debug('getMyAnimalList関数でエラー発生:' . $e->getMessage());
  }
}
//掲示板データを取得
function getMsgAndBoard($m_id)
{
  debug('掲示板のデータを取得します');
  debug('掲示板のID:' . $m_id);
  try {
    $dbh = dbConnect();
    $sql = 'SELECT id, animal_id, guardians_id, sender_id, delete_flg, create_date, update_date FROM board WHERE id = :m_id';
    $data = array(':m_id' => $m_id);
    $stmt = queryPost($dbh, $sql, $data);
    $rst = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($rst) {
      // SQL文作成
      $sql = 'SELECT id, board_id, sender_id, sender_name, sender_pic, msg, pic1, pic2, pic3, delete_flg, send_date, create_date FROM message WHERE board_id = :id AND delete_flg = 0 ORDER BY send_date DESC';
      $data = array(':id' => $m_id);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    } else {
      debug('SQLエラー' . print_r($stmt->errorInfo(), true));
      return false;
    }
  } catch (Exception $e) {
    debug('getMsgAndBoard関数でエラー発生:' . $e->getMessage());
  }
}
//関わりがある掲示板の情報を取得する関数
function getMyMsg($u_id)
{
  debug('getMyMsg関数を開始します');
  try {
    // DBへ接続
    $dbh = dbConnect();

    // まず、掲示板レコード取得
    // SQL文作成
    $sql = 'SELECT * FROM board AS b WHERE b.guardians_id = :id OR b.sender_id = :id AND b.delete_flg = 0';
    $data = array(':id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst = $stmt->fetchAll();
    debug('rstの中身です:' . print_r($rst, true));

    if (!empty($rst)) {
      foreach ($rst as $key => $val) {
        // SQL文作成
        $sql = 'SELECT * FROM message WHERE board_id = :id AND delete_flg = 0 ORDER BY send_date DESC';
        $data = array(':id' => $val['id']);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $rst[$key]['msg'] = $stmt->fetchAll();
      }
    }

    if ($stmt) {
      // クエリ結果の全データを返却
      return $rst;
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//お気に入り登録機能用のログインしているかチェックする関数
function isLogin()
{
  //ログインしている状態
  if (!empty($_SESSION['login_date'])) {
    debug('ログイン済みユーザーです');

    //現在日時が最終ログイン日時＋有効期限を超えていた場合
    if (($_SESSION['login_date'] + $_SESSION['login_limit']) < time()) {
      debug('ログイン有効期限オーバーです');

      //セッションを消去
      session_destroy();
      return false;
    } else {
      debug('ログイン有効期限以内です');
      return true;
    }
  } else {
    debug('未ログインユーザーです');
    return false;
  }
}

//お気に入り登録されているかを取得する関数
function isFavourite($u_id, $a_id)
{
  // debug('いいねした情報があるかの確認');
  // debug('ユーザーID' . $u_id);
  // debug('保護犬・猫のID：' . $a_id);

  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM favourite WHERE animal_id = :a_id AND user_id = :u_id';
    $data = array(':u_id' => $u_id, ':a_id' => $a_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt->rowCount()) {
      // debug('お気に入り登録されています');
      return true;
    } else {
      // debug('お気に入り登録されていません');
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
function getMyFavouriteList($u_id)
{
  debug('お気に入り登録した情報を取得します');

  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM favourite AS f LEFT JOIN animals AS a ON f.animal_id = a.id WHERE user_id = :u_id AND f.delete_flg = 0 ORDER BY f.update_date DESC';
    $data = array(':u_id' => $u_id);
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      $rst = $stmt->fetchAll();
      return $rst;
    } else {
      debug('SQLエラー' . print_r($stmt->errorInfo(), true));
      return false;
    }
  } catch (Exception $e) {
    error_log('getMyFavouriteList関数でエラーが発生:' . $e->getMessage());
  }
}
//=========================================
//メール送信用関数
//=========================================
function sendEmail($to, $from, $subject, $comment)
{
  if (!empty($to) && !empty($comment)) {
    mb_language("Japanese");
    mb_internal_encoding("utf-8");
    $result = mb_send_mail($to, $subject, $comment, $from);
    if ($result) {
      debug('メール送信完了しました');
    } else {
      debug('メール送信できませんでした');
    }
  }
}
//==========================================
//画像アップロード用の関数
//==========================================
function uploadImg($file)
{
  debug('画像をアップロードします');
  if (isset($file['error']) && is_int($file['error'])) {
    try {
      switch ($file['error']) {
        case UPLOAD_ERR_OK:
          break;
        case UPLOAD_ERR_NO_FILE:
          throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          throw new RuntimeException('ファイルサイズが大きすぎます');
        default:
          throw new RuntimeException('その他のエラーが発生しました');
      }

      $type = @exif_imagetype($file['tmp_name']);

      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
        throw new RuntimeException('画像の形式が未対応です');
      }
      $path = 'uploads/' . sha1_file($file['tmp_name']) . image_type_to_extension($type);
      if ($file['error'] === 0) {
        move_uploaded_file($file['tmp_name'], $path);
        chmod($path, 0644);
        debug('ファイルを正常にアップロードしました');
        debug('ファイルパス：' . $path);
        return $path;
      } elseif (!move_uploaded_file($file['tmp_name'], $path)) {
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
    } catch (Exception $e) {
      error_log('uploadImg関数でエラーが発生しました:' . $e->getMessage());
      $err_msg['common'] = ERR01;
    }
  }
}
//=========================================
//その他
//=========================================
//サニタイズ用関数
function sanitize($str)
{
  return htmlspecialchars($str, ENT_QUOTES);
}
// フォーム入力保持用関数
//getの値を使うなら第二パラメータをtrueにする
function getFormData($str, $flg = false)
{
  if ($flg) {
    $method = $_GET;
  } else {
    $method = $_POST;
  }
  global $dbFormData;
  // ユーザーデータがある
  if (!empty($dbFormData)) {
    //フォームのエラーがある
    if (!empty($err_msg[$str])) {
      //POSTにデータがある
      if (isset($method[$str])) {
        return sanitize($method[$str]);
      } else {
        //その他
        return sanitize($dbFormData[$str]);
      }
    } else {
      //POSTにデータがあり、DBの情報と違う
      if (isset($method[$str]) && $method[$str] !== $dbFormData[$str]) {
        return sanitize($method[$str]);
      } else {
        return sanitize($dbFormData[$str]);
      }
    }
  } else {
    if (isset($method[$str])) {
      return sanitize($method[$str]);
    }
  }
}

//パスワード再発行用の認証キーをつくる関数
function makeToken($len = 8)
{
  $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
  $str = '';
  for ($i = 0; $i < $len; ++$i) {
    $str .= $chars[mt_rand(0, 61)];
  }
  return $str;
}
function appendGetParam($arr_del_key = array())
{
  //$_GETがemptyじゃないなら
  if (!empty($_GET)) {
    $str = '?';
    foreach ($_GET as $key => $val) {

      if (!in_array($key, $arr_del_key, true)) {
        debug(print_r($key, true) . print_r($val, true));
        $str .= $key . '=' . $val . '&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}
//画像が登録されてない時にサンプル画像を表示する用の関数（動物）
function showImgA($path, $key)
{
  if (empty($path) && $key == '猫') {
    $path = 'img/sample.neko.png';
    return $path;
  } else if (empty($path) && $key != '猫') {
    $path = 'img/sample.inu.png';
    return $path;
  } else {
    $path = sanitize($path);
    return $path;
  }
}
//画像が登録されてない時にサンプル画像を表示する用の関数（人）
function showImgH($path)
{
  if (empty($path)) {
    $path = 'img/sample.hituji.png';
    return $path;
  } else {
    $path = sanitize($path);
    return $path;
  }
}
