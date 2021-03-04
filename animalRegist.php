<?php
require('function.php');

debug('==============================================================');
debug('保護犬・猫登録/編集ページ');
debug('==============================================================');
debugLogStart();

require('auth.php');
debug('セッション変数の中身:' . print_r($_SESSION, true));

$u_id = userInfoAll($_SESSION['user_id']);
debug('ユーザー情報' . print_r($u_id, true));

//一般会員の人が万が一このページに来たら、マイページへ戻す
if ($u_id['user_role'] !== "保護者") {
  $_SESSION['notice'] = ERR04;
  header("Location: mypage.php");
  exit();
}

$a_id = (!empty($_GET['a_id'])) ? $_GET['a_id'] : '';
debug('保護犬・猫のID：' . $a_id);
//登録済みなら情報を入れる
$dbFormData = (!empty($a_id)) ? animalInfoAll($a_id) : '';
// 新規登録画面か編集画面か判別するためのフラグ
$edit_flg = (empty($dbFormData)) ? false : true;


debug('取得した情報の中身:' . print_r($dbFormData, true));

// //不正なパラメータの場合マイページへ
// if (!empty($a_id) && empty($dbFormData)) {
//   debug('GETパラメータのIDが違います。マイページへ遷移します。');
//   header("Location:mypage.php"); //マイページへ
// }

if ($_POST) {
  debug('POST送信されました');

  //POST送信されたらPOST内容を変数に格納
  $animal_type = filter_input(INPUT_POST, 'animal_type');
  $name = filter_input(INPUT_POST, 'name');
  $gender = filter_input(INPUT_POST, 'gender');
  $age = filter_input(INPUT_POST, 'age');
  $area = filter_input(INPUT_POST, 'area');
  $comment = filter_input(INPUT_POST, 'comment');
  $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'], 'pic1') : '';
  $pic2 = (!empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'], 'pic2') : '';
  $pic3 = (!empty($_FILES['pic3']['name'])) ? uploadImg($_FILES['pic3'], 'pic3') : '';
  $delete_flg = filter_input(INPUT_POST, 'delete_flg');

  debug('画像があれば画像をフォルダに移動します');

  debug('$_FILESの中身:' . print_r($_FILES, true));
  debug('POST情報:' . print_r($_POST, true));

  // 登録情報更新の場合はDBの情報と入力情報が異なる場合にバリデーションを行う
  if (empty($dbFormData)) {
    //未入力チェック
    $err_msg['animal_type'] = selectCheck($animal_type);
    $err_msg['name'] = emp($name);
    $err_msg['gender'] = selectCheck($gender);
    $err_msg['age'] = selectCheck($age);
    $err_msg['area'] = selectCheck($area);
    $err_msg['comment'] = emp($comment);
    //譲渡済みならflgが1になる
    // $err_msg['delete_flg'] = selectCheck($delete_flg);

    debug('デリートフラグの中身:' . print_r($delete_flg, true));

    if (empty(array_filter($err_msg))) {

      //文字数チェック
      $err_msg['name'] = maxLen($name);
      $err_msg['comment'] = maxLen($comment, $len = 500);

      if (!empty($dbFormData['name']) && $dbFormData['name'] !== $name) {
        //未入力と文字数チェック
        $err_msg['name'] = emp($name);
        $err_msg['name'] = maxLen($name);
      }
      if (!empty($dbFormData['comment']) && $dbFormData['comment'] !== $comment) {
        $err_msg['comment'] = maxLen($comment, $len = 500);
        $err_msg['comment'] = emp($comment);
      }
    }

    debug('エラーメッセージ:' . print_r($err_msg, true));
    if (empty(array_filter($err_msg))) {
      debug('バリデーションokです');
      try {

        if ($edit_flg === false) {
          debug('保護犬・猫の情報をDBに登録します');
          $dbh = dbConnect();
          $sql = 'INSERT INTO animals (guardians_id, 	animal_type, name, gender, age, area, comment, pic1, pic2, pic3, delete_flg, create_date, update_date) VALUES (:guardians_id, :animal_type, :name, :gender, :age, :area, :comment, :pic1, :pic2, :pic3, :delete_flg, :create_date, :update_date)';
          $data = array(
            ':guardians_id' => $_SESSION['user_id'],
            ':animal_type' => $animal_type,
            ':name' => $name,
            ':gender' => $gender,
            ':age' => $age,
            ':area' => $area,
            ':comment' => $comment,
            ':pic1' => $pic1,
            ':pic2' => $pic2,
            ':pic3' => $pic3,
            ':delete_flg' => $delete_flg,
            ':create_date' => date('Y-m-d H:i:s'),
            ':update_date' => date('Y-m-d H:i:s')
          );
          $stmt = queryPost($dbh, $sql, $data);

          if ($stmt) {
            debug('保護犬・猫情報をDBに登録しました');
            $_SESSION['notice'] = SUC05;
            debug('マイページへ移動します');
            header("Location: mypage.php");
            exit();
          } else {
            return false;
            debug('保護犬・猫登録画面でSQLエラー発生');
            debug('SQLエラー' . print_r($stmt->errorInfo(), true));
          }
        }
        if ($edit_flg === true) {
          debug('保護犬・猫の情報をDBに編集します');
          $dbh = dbConnect();
          $sql = 'UPDATE animals SET guardians_id = :guardians_id, 	animal_type = :animal_type, name = :name, gender = :gender, age = :age, area = :area, comment = :comment, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3, delete_flg = 0, create_date = :create_date, update_date = :update_date';
          $data = array(
            ':guardians_id' => $_SESSION['user_id'],
            ':animal_type' => $animal_type,
            ':name' => $name,
            ':gender' => $gender,
            ':age' => $age,
            ':area' => $area,
            ':comment' => $comment,
            ':pic1' => $pic1,
            ':pic2' => $pic2,
            ':pic3' => $pic3,
            ':delete_flg' => $delete_flg,
            ':create_date' => date('Y-m-d H:i:s'),
            ':update_date' => date('Y-m-d H:i:s')
          );
          $stmt = queryPost($dbh, $sql, $data);
          if ($stmt) {
            debug('保護犬・猫情報の情報を更新しました');
            $_SESSION['notice'] = SUC06;
            debug('マイページへ移動します');
            header("Location: mypage.php");
            exit();
          } else {
            return false;
            debug('保護犬・猫の情報更新でSQLエラー発生');
            debug('SQLエラー' . print_r($stmt->errorInfo(), true));
          }
        }
      } catch (Exception $e) {
        error_log('保護犬・猫の登録画面でPOST情報の登録/更新に失敗しました:' . $e->getMessage());
      }
    }
  }
}


debug('==============================================================');
debug('画面表示処理終了');
debug('==============================================================');
?>

<!-- headタグ内 -->
<?php $siteTitle = '登録/編集';
require('head.php'); ?>

<!-- ヘッダータグ内 -->
<?php require('header.php'); ?>

<div class="notice">
  <?php echo (!$edit_flg) ? '  <p>
    こちらから保護犬・保護猫の登録ができます。
  </p>' : '  <p>
  　　こちらから保護犬・保護猫の編集ができます。
</p>'; ?>
</div>


<div class="form-container">
  <form method="post" action="" enctype="multipart/form-data" class="form">
    <div class="form-animals">
      <div class="err-msg-common">
        <?php
        if (!empty($err_msg['common'])) echo $err_msg['common'];
        ?>
      </div>
      <div class="err-msg">
        <?php
        if (!empty($err_msg['animal_type'])) echo $err_msg['animal_type'];
        ?>
      </div>
      <label class="<?php if (!is_null($err_msg['animal_type'])) echo 'err'; ?>"><span class="form-badge">必須</span>
        種別
        <span class="<?php if (!is_null($err_msg['animal_type'])) echo 'err'; ?>">
          <select name="animal_type">
            <option value="" style='display:none;'>選択してください</option>
            <option value="小型犬" <?php if (getFormData('animal_type') === '小型犬') echo 'selected'; ?>>小型犬</option>
            <option value="中型犬" <?php if (getFormData('animal_type') === '中型犬') echo 'selected'; ?>>中型犬</option>
            <option value="大型犬" <?php if (getFormData('animal_type') === '大型犬') echo 'selected'; ?>>大型犬</option>
            <option value="猫" <?php if (getFormData('animal_type') === '猫') echo 'selected'; ?>>猫</option>
          </select>
      </label>
      <div class="err-msg">
        <?php
        if (!empty($err_msg['name'])) echo $err_msg['name'];
        ?>
      </div>
      <label class="<?php if (!empty($err_msg['name'])) echo 'err'; ?>"><span class="form-badge">必須</span>
        名前
        <input type="text" name="name" value="<?php echo getFormData('name'); ?>" />
      </label>
      <div class="err-msg">
        <?php
        if (!empty($err_msg['gender'])) echo $err_msg['gender'];
        ?>
      </div>
      <label class="<?php if (!is_null($err_msg['gender'])) echo 'err'; ?>"><span class="form-badge">必須</span>
        性別<BR>
        <input type="radio" name="gender" value="オス" <?php if (getFormData('gender') === 'オス') echo 'checked'; ?> />オス
        <input type="radio" name="gender" value="メス" <?php if (getFormData('gender') === 'メス') echo 'checked'; ?> />メス
      </label>
      <div class="err-msg">
        <?php
        if (!empty($err_msg['age'])) echo $err_msg['age'];
        ?>
      </div>
      <label class="<?php if (!is_null($err_msg['age'])) echo 'err'; ?>"><span class="form-badge">必須</span>
        　年齢
        <select name="age">
          <option value="0" style='display:none;'>選択してください</option>
          <option value="0～1歳" <?php if (getFormData('age') == '0～1歳') echo 'selected'; ?>>0～1歳</option>
          <option value="1～7歳" <?php if (getFormData('age') == '1～7歳') echo 'selected'; ?>>1～7歳</option>
          <option value="7～12歳" <?php if (getFormData('age') == '7～12歳') echo 'selected'; ?>>7～12歳</option>
          <option value="12歳～" <?php if (getFormData('age') == '12歳～') echo 'selected'; ?>>12歳～</option>
        </select>
      </label>
      <div class="err-msg">
        <?php
        if (!empty($err_msg['area'])) echo $err_msg['area'];
        ?>
      </div>
      <label class="<?php if (!is_null($err_msg['area'])) echo 'err'; ?>"><span class="form-badge">必須</span>
        募集地域
        <select name="area">
          <option value="" style='display:none;'>選択してください</option>
          <option value="北海道" <?php if (getFormData('area') == '北海道') echo 'selected'; ?>>北海道</option>
          <option value="東北" <?php if (getFormData('area') == '東北') echo 'selected'; ?>>東北</option>
          <option value="関東" <?php if (getFormData('area') == '関東') echo 'selected'; ?>>関東</option>
          <option value="北陸" <?php if (getFormData('area') == '北陸') echo 'selected'; ?>>北陸</option>
          <option value="東海" <?php if (getFormData('area') == '東海') echo 'selected'; ?>>東海</option>
          <option value="近畿" <?php if (getFormData('area') == '近畿') echo 'selected'; ?>>近畿</option>
          <option value="中国" <?php if (getFormData('area') == '中国') echo 'selected'; ?>>中国</option>
          <option value="四国" <?php if (getFormData('area') == '四国') echo 'selected'; ?>>四国</option>
          <option value="九州" <?php if (getFormData('area') == '九州') echo 'selected'; ?>>九州</option>
          <option value="沖縄" <?php if (getFormData('area') == '沖縄') echo 'selected'; ?>>沖縄</option>
        </select>
      </label>
      <div class="err-msg">
        <?php
        if (!empty($err_msg['comment'])) echo $err_msg['comment'];
        ?>
      </div>
      <label class="<?php if (!empty($err_msg['comment'])) echo 'err'; ?>"><span class="form-badge">必須</span>
        詳細
        <textarea name="comment" id="js-count" cols="30" rows="10" style="height: 150px" placeholder="<?php echo getFormData('comment'); ?>"></textarea>
      </label>
      <p class="counter-text"><span id="js-count-view">0</span>/500文字</p>

      <span class="form-badge">任意</span><span style="color: #707070; font-weight: bolder;">画像（3枚まで）</span><BR><BR>
      <div class="pics-wrapper">
        <div class="err-msg">
          <?php
          if (!empty($err_msg['pic1'])) echo $err_msg['pic1'];
          ?>
        </div>

        <label class="area-drop <?php if (!empty($err_msg['pic1'])) echo 'err'; ?>">
          <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
          <input type="file" name="pic1" class="input-file" style="display: none;">
          <!-- 　あとで、画像が登録済みなら表示するといったことをする -->
          <img src="<?php echo getFormData('pic1'); ?>" alt="" class="prev-img" style="<?php if (empty($dbFormData['pic1'])) echo 'display:none;' ?>">
          <?php if (empty($dbFormData['pic1']))
            echo 'ドラッグ＆ドロップ';
          ?>
        </label>
        <div class="err-msg">
          <?php
          if (!empty($err_msg['pic2'])) echo $err_msg['pic2'];
          ?>
        </div>
        <label class="area-drop <?php if (!empty($err_msg['pic2'])) echo 'err'; ?>">
          <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
          <input type="file" name="pic2" class="input-file" style="display:none;">
          <img src="<?php echo getFormData('pic2'); ?>" alt="" class="prev-img" style="<?php if (empty($dbFormData['pic2'])) echo 'display:none;' ?>">
          <?php if (empty($dbFormData['pic2']))
            echo 'ドラッグ＆ドロップ';
          ?>
        </label>
        <div class="err-msg">
          <?php
          if (!empty($err_msg['pic3'])) echo $err_msg['pic3'];
          ?>
        </div>
        <label class="area-drop <?php if (!empty($err_msg['pic3'])) echo 'err'; ?>">
          <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
          <input type="file" name="pic3" class="input-file" style="display:none;">
          <img src="<?php echo getFormData('pic3'); ?>" alt="" class="prev-img" style="<?php if (empty($dbFormData['pic3'])) echo 'display:none;' ?>">
          <?php if (empty($dbFormData['pic3']))
            echo 'ドラッグ＆ドロップ';
          ?>
        </label>

      </div>


      <label class="<?php if (!empty($err_msg['delete_flg'])) echo 'err'; ?>"><span class="form-badge">必須</span>
        <input type="radio" name="delete_flg" value="0" <?php if ((int)getFormData('delete_flg') == '0') echo 'checked'; ?> />募集中
        <span class="<?php if (!empty($err_msg['delete_flg'])) echo 'err'; ?>">
          <input type="radio" name="delete_flg" value="1" <?php if ((int)getFormData('delete_flg') == '1') echo 'checked'; ?> />譲渡済み
        </span>
      </label>

      <div class="center">
        <input type="submit" class="btn btn-brown" value="<?php echo (!$edit_flg) ? '登録する' : '更新する'; ?>" />
      </div>
  </form>
</div>
</div>
</div>
<!-- フッタータグ内 -->
<?php
require('footer.php');
?>