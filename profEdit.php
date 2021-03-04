<?php
require('function.php');


debug('==============================================================');
debug('プロフィール変更ページ');
debug('==============================================================');
debugLogStart();

require('auth.php');

$dbFormData = userInfoAll($_SESSION['user_id']);

debug('取得したユーザー情報:' . print_r($_SESSION['user_id'], true));
debug('取得したユーザー情報:' . print_r($dbFormData, true));

if ($_POST) {

  //エラーメッセージ初期化
  $err_msg = array();

  debug('post送信されました');

  $nickname = filter_input(INPUT_POST, 'nickname');
  $email = filter_input(INPUT_POST, 'email');
  $pass = filter_input(INPUT_POST, 'pass');
  $pass_re = filter_input(INPUT_POST, 'pass_re');
  $family_name = filter_input(INPUT_POST, 'family_name');
  $given_name = filter_input(INPUT_POST, 'given_name');
  $age = filter_input(INPUT_POST, 'age');
  $job = filter_input(INPUT_POST, 'job');
  $phone = filter_input(INPUT_POST, 'phone');
  $zip = filter_input(INPUT_POST, 'zip');
  $address = filter_input(INPUT_POST, 'address');
  $comment = filter_input(INPUT_POST, 'comment');
  //ファイルまだアップロードされてないならアップロード
  $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : '';
  //画像を登録していないが既にDBに登録されている場合、DBのパスを入れる。
  $pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;

  debug('POST情報:' . print_r($_POST, true));
  debug('FILE情報:' . $_FILES, true);

  if ($dbFormData['nickname'] !== $nickname) {
    $err_msg['nickname'] = emp($nickname);
    $err_msg['nickname'] = maxLen($nickname);
    debug('ここまでできてる１');
  }
  if ($dbFormData['email'] !== $email) {
    $err_msg['email'] = emp($email);
    $err_msg['email'] = maxLen($email);
    $err_msg['email'] = validEmail($email);
    $err_msg['email'] = emailDupCheck($email);
    debug('ここまでできてる２');
  }
  if ($dbFormData['family_name'] !== $family_name) {
    $err_msg['family_name'] = emp($family_name);
    $err_msg['family_name'] = maxLen($family_name);
    debug('ここまでできてる３');
  }
  if ($dbFormData['given_name'] !== $given_name) {
    $err_msg['given_name'] = emp($given_name);
    $err_msg['given_name'] = maxLen($given_name);
    debug('ここまでできてる４');
  }
  if ($dbFormData['age'] !== $age) {
    $err_msg['age'] = emp($age);
    $err_msg['age'] = validNum($age);
    debug('ここまでできてる５');
  }
  if ($dbFormData['job'] !== $job) {
    $err_msg['job'] = selectCheck($job);
  }
  if ($dbFormData['phone'] !== $phone) {
    $err_msg['phone'] = emp($phone);
    $err_msg['phone'] = maxLen($phone);
    $err_msg['phone'] = validNum($phone);
  }
  if ($dbFormData['zip'] !== $zip) {
    $err_msg['zip'] = emp($zip);
    $err_msg['zip'] = maxLen($zip);
    $err_msg['zip'] = validNum($zip);
  }
  if ($dbFormData['address'] !== $address) {
    $err_msg['address'] = emp($address);
    $err_msg['address'] = maxLen($address);
  }
  if (!empty($dbFormData['comment']) && $dbFormData['comment'] !== $comment) {
    $err_msg['comment'] = maxLen($address, $len = 500);
  }
  debug('エラーメッセージ:' . print_r($err_msg, true));
  if (empty(array_filter($err_msg))) {
    debug('バリデーションokです');
    try {
      $dbh = dbConnect();
      $sql = 'UPDATE users SET nickname = :nickname, email = :email, family_name = :fName, given_name = :GName, age = :age, job = :job, phone = :phone, zip = :zip, address = :address, comment = :comment, pic= :pic WHERE id = :id';
      $data = array(
        ':nickname' => $nickname,
        ':email' => $email,
        ':fName' => $family_name,
        ':GName' => $given_name,
        ':age' => $age,
        ':job' => $job,
        ':phone' => $phone,
        ':zip' => $zip,
        ':address' => $address,
        ':comment' => $comment,
        ':pic' => $pic,
        ':id' => $_SESSION['user_id']
      );
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
        debug('プロフィールを変更しました');
        $_SESSION['notice'] = SUC02;
        debug('セッション情報' . print_r($_SESSION, true));
        debug('マイページへ遷移します');
        header("Location: mypage.php");
        exit();
      } else {
        return false;
        debug('プロフィール編集画面でSQLエラー発生');
        debug('SQLエラー' . print_r($stmt->errorInfo(), true));
      }
    } catch (Exception $e) {
      error_log('プロフィール編集画面でエラー発生:' . $e->getMessage());
    }
  }
}

debug('==============================================================');
debug('画面表示処理終了');
debug('==============================================================');
?>
<!-- headタグ内 -->
<?php require('head.php'); ?>

<!-- ヘッダータグ内 -->
<?php $siteTitle = 'プロフィール編集ページ';
require('header.php'); ?>


<div class="notice">
  <p>こちらからプロフィールの編集ができます。</p>
</div>

<div class="form-container">
  <form class="form-common" method="post" action="" enctype="multipart/form-data">
    <div class="err-msg-common">
      <?php
      if (!empty($err_msg['common'])) echo $err_msg['common'];
      ?>
    </div>
    <div class="err-msg">
      <?php
      if (!empty($err_msg['nickname'])) echo $err_msg['nickname'];
      ?>
    </div>
    <label class="<?php if (!empty($err_msg['nickname'])) echo 'err'; ?>"><span class="form-badge">必須</span>
      ニックネーム
      <input type="text" name="nickname" value="<?php echo getFormData('nickname'); ?>" />
    </label>
    <div class="err-msg">
      <?php
      if (!empty($err_msg['email'])) echo $err_msg['email'];
      ?>
    </div>
    <label class="<?php if (!empty($err_msg['email'])) echo 'err'; ?>"><span class="form-badge">必須</span>
      メールアドレス
      <input type="text" name="email" value="<?php echo getFormData('email'); ?>" />
    </label>
    <div class="err-msg">
      <?php
      if (!empty($err_msg['family_name'])) echo $err_msg['family_name'];
      ?>
    </div>
    <label class="<?php if (!empty($err_msg['family_name'])) echo 'err'; ?>"><span class="form-badge">必須</span>
      苗字
      <input type="text" name="family_name" value="<?php echo getFormData('family_name'); ?>" />
    </label>
    <div class="err-msg">
      <?php
      if (!empty($err_msg['given_name'])) echo $err_msg['given_name'];
      ?>
    </div>
    <label class="<?php if (!empty($err_msg['given_name'])) echo 'err'; ?>"><span class="form-badge">必須</span>
      名前
      <input type="text" name="given_name" value="<?php echo getFormData('given_name'); ?>" />
    </label>
    <div class="err-msg">
      <?php
      if (!empty($err_msg['age'])) echo $err_msg['age'];
      ?>
    </div>
    <label class="<?php if (!empty($err_msg['age'])) echo 'err'; ?>"><span class="form-badge">必須</span> 年齢<br /><br />
      <input type="number" name="age" min="0" max="100" value="<?php echo getFormData('age'); ?>" />
      <?php debug('ageの入力保持確認:' . getFormData('age')); ?>
    </label>
    <div class="err-msg">
      <?php
      if (!empty($err_msg['job'])) echo $err_msg['job'];
      ?>
    </div>
    <label class="<?php if (!is_null($err_msg['job'])) echo 'err'; ?>"><span class="form-badge">必須</span>職業<br /><br />
      <select name="job">
        <option value="" style='display:none;'>選択してください</option>
        <option value="公務員" <?php if (getFormData('job') == '公務員') echo 'selected'; ?>>公務員</option>
        <option value="経営者・役員" <?php if (getFormData('job') == '経営者・役員') echo 'selected'; ?>>経営者・役員</option>
        <option value="会社員" <?php if (getFormData('job') == '会社員') echo 'selected'; ?>>会社員</option>
        <option value="自営業" <?php if (getFormData('job') == '自営業') echo 'selected'; ?>>自営業</option>
        <option value="自由業" <?php if (getFormData('job') == '自由業') echo 'selected'; ?>>自由業</option>
        <option value="専業主婦" <?php if (getFormData('job') == '専業主婦') echo 'selected'; ?>>専業主婦</option>
        <option value="パート・アルバイト" <?php if (getFormData('job') == 'パート・アルバイト') echo 'selected'; ?>>パート・アルバイト</option>
        <option value="学生" <?php if (getFormData('job') == '学生') echo 'selected'; ?>>学生</option>
        <option value="その他" <?php if (getFormData('job') == 'その他') echo 'selected'; ?>>その他</option>
      </select>
    </label>
    <div class="err-msg">
      <?php
      if (!empty($err_msg['phone'])) echo $err_msg['phone'];
      ?>
      <label class="<?php if (!empty($err_msg['phone'])) echo 'err'; ?>"><span class=" form-badge">必須</span>
        電話番号
        <input type="text" name="phone" value="<?php echo getFormData('phone'); ?>" />
      </label>
      <div class="err-msg">
        <?php
        if (!empty($err_msg['zip'])) echo $err_msg['zip'];
        ?>
      </div>
      <label><span class="form-badge">必須</span>
        郵便番号
        <input type="text" name="zip" value="<?php echo getFormData('zip'); ?>" />
      </label>
      <div class="err-msg">
        <?php
        if (!empty($err_msg['address'])) echo $err_msg['address'];
        ?>
      </div>
      <label class="<?php if (!empty($err_msg['address'])) echo 'err'; ?>"><span class="form-badge">必須</span>
        住所
        <input type="text" name="address" value="<?php echo getFormData('address'); ?>" />
      </label>
      <div class="err-msg">
        <?php
        if (!empty($err_msg['comment'])) echo $err_msg['comment'];
        ?>
      </div>
      <label class="<?php if (!empty($err_msg['comment'])) echo 'err'; ?>"><span class="form-badge">任意</span>
        コメント
        <textarea name="comment" id="js-count" cols="30" rows="10" style="height: 150px"><?php echo getFormData('comment'); ?></textarea>
      </label>
      <p class="counter-text"><span id="js-count-view">0</span>/500文字</p>
      <span class="form-badge">任意</span><span style="color: #707070; font-weight: bolder">プロフィール画像</span><br /><br />
      <label class="area-drop" style="
  position: relative;
  height: 9.35rem;
  width: 9.35rem;
  line-height: 9.35rem;
  background: white;
  margin: 0 auto;
  margin-top: 1.5rem;
  margin-bottom: 1.5rem;
  padding: 1.5rem 0.5rem;
          ">
        <input type="hidden" name="MAX_FILE_SIZE" value="3145728" />
        <input type="file" name="pic" class="input-file" style="display: none" />
        <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img" style="position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    height: auto;
    width: auto;
    max-width: 100%;
    max-height: 100%;
    margin: auto;
    <?php if (empty(getFormData('pic'))) echo 'display:none;' ?>" />
        <?php if (empty(getFormData('pic'))) echo 'ドラッグ＆ドロップ'; ?>
      </label>

      <br />
      <div class="center">
        <input class="btn btn-brown" type="submit" value="会員情報編集" />
      </div>
  </form>
</div>
</div>
<!-- フッタータグ内 -->
<?php
require('footer.php');
?>