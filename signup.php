<?php
require('function.php');

debug('==============================================================');
debug('新規会員登録ページ');
debug('==============================================================');
debugLogStart();

if ($_POST) {
  debug('POST送信されました');

  //エラーメッセージ初期化
  $err_msg = array();

  //post内容を変数に格納
  $role = filter_input(INPUT_POST, 'role');
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

  debug('POST情報:' . print_r($_POST, true));

  //未入力じゃないかチェック
  $err_msg['role'] = selectCheck($role);
  $err_msg['nickname'] = emp($nickname);
  $err_msg['email'] = emp($email);
  $err_msg['pass'] = emp($pass);
  $err_msg['pass_re'] = emp($pass_re);
  $err_msg['family_name'] = emp($family_name);
  $err_msg['given_name'] = emp($given_name);
  $err_msg['age'] = emp($age);
  $err_msg['job'] = selectCheck($job);
  $err_msg['phone'] = emp($phone);
  $err_msg['zip'] = emp($zip);
  $err_msg['address'] = emp($address);

  debug('エラーメッセージ:' . print_r($err_msg, true));

  //未入力じゃないなら文字数チェック
  if (empty(array_filter($err_msg))) {
    debug('未入力チェックをパスしました');
    //文字数チェック(max255文字)
    $err_msg['nickname'] = maxLen($nickname);
    $err_msg['email'] = maxLen($email);
    $err_msg['pass'] = maxLen($pass);
    $err_msg['pass_re'] = maxLen($pass_re);
    $err_msg['family_name'] = maxLen($family_name);
    $err_msg['given_name'] = maxLen($given_name);
    $err_msg['phone'] = maxLen($phone);
    $err_msg['zip'] = maxLen($zip);
    $err_msg['address'] = maxLen($address);

    debug('エラーメッセージ:' . print_r($err_msg, true));

    //文字数チェックokなら各種バリデーション
    if (empty(array_filter($err_msg))) {
      // $err_msg['nickname'] = validSymbol($nickname);
      $err_msg['email'] = validEmail($email);
      $err_msg['pass'] = validAlphaNum($pass);
      $err_msg['pass'] = minLen($pass);
      $err_msg['pass'] = minLen($pass);
      $err_msg['phone'] = validNum($phone);
      $err_msg['zip'] = validNum($zip);
      // $err_msg['address'] = validSymbol($address);

      debug('エラーメッセージ:' . print_r($err_msg, true));

      //上記okならパスワードとパスワード再入力の同値チェックとemail重複チェック
      if (empty(array_filter($err_msg))) {
        $err_msg['email'] = emailDupCheck($email);
        $err_msg['pass_re'] = validMatch($pass, $pass_re);

        debug('エラーメッセージ:' . print_r($err_msg, true));

        if (empty(array_filter($err_msg))) {
          debug('バリデーションokです');
          try {
            debug('会員情報をDBに登録します');
            $dbh = dbConnect();
            $sql = 'INSERT INTO users (user_role, nickname, email, password, family_name, given_name, age, job, phone, zip, address, create_date, login_time, update_date) VALUES (:role, :nickname, :email, :pass, :fName, :lName, :age, :job, :phone, :zip, :address, :create_date, :login_time, :update_date)';
            $data = array(
              ':role' => $role,
              ':nickname' => $nickname,
              ':email' => $email,
              ':pass' => password_hash($pass, PASSWORD_DEFAULT),
              ':fName' => $family_name,
              ':lName' => $given_name,
              ':age' => $age,
              ':job' => $job,
              ':phone' => $phone,
              ':zip' => $zip,
              ':address' => $address,
              ':create_date' => date('Y-m-d H:i:s'),
              ':login_time' => date('Y-m-d H:i:s'),
              ':update_date' => date('Y-m-d H:i:s')
            );
            $stmt = queryPost($dbh, $sql, $data);

            if ($stmt) {
              debug('会員情報をDBに登録しました');
              debug('セッション情報を更新します');
              $sesLimit = 60 * 60;
              $_SESSION['login_date'] = time();
              $_SESSION['login_limit'] = $sesLimit;
              $_SESSION['user_id'] = $dbh->lastInsertId();
              $_SESSION['notice'] = SUC11;

              debug('$_session：' . print_r($_SESSION, true));
              debug('マイページへ遷移します');
              header("Location: mypage.php");
              exit();
            } else {
              return false;
              debug('会員登録画面でSQLエラー発生');
              debug('SQLエラー' . print_r($stmt->errorInfo(), true));
            }
          } catch (Exception $e) {
            error_log('会員登録画面でpost情報の登録に失敗しました:' . $e->getMessage());
          }
        }
      }
    }
  }
}

debug('==============================================================');
debug('画面表示処理終了');
debug('==============================================================');
?>

<!-- headタグ内 -->
<?php $siteTitle = '会員登録ページ';
require('head.php'); ?>

<!-- ヘッダータグ内 -->
<?php require('header.php'); ?>

<div class="notice">
  <p>
    ご利用には会員登録が必要となります。<br />
    既に会員登録がお済みの方は<a href=""><span style="color: rgb(217, 187, 98)">こちら</span></a>からログインしてください。
  </p>
</div>

<div class="form-container">

  <form action="" method="post" class="form-common">
    <div class="err-msg-common">
      <?php
      if (!empty($err_msg['common'])) echo $err_msg['common'];
      ?>
    </div>
    <div class="err-msg">
      <?php
      if (!empty($err_msg['role'])) echo $err_msg['role'];
      ?>
    </div>
    <label class=""><span class="form-badge">必須</span>
      会員種別
      <span class="<?php if (!empty($err_msg['role'])) echo 'err'; ?>">
        <input type="radio" name="role" value="一般会員" <?php echo array_key_exists('role', $_POST) && $_POST['role'] == '一般会員' ? 'checked' : ''; ?> />一般会員
        <input type="radio" name="role" value="保護者" <?php echo array_key_exists('role', $_POST) && $_POST['role'] == '保護者' ? 'checked' : ''; ?> />保護者・保護団体
      </span>
    </label>

    <div class="err-msg">
      <?php
      if (!empty($err_msg['nickname'])) echo $err_msg['nickname'];
      ?>
    </div>
    <label class="<?php if (!empty($err_msg['nickname'])) echo 'err'; ?>"><span class="form-badge">必須</span>
      ニックネーム
      <input type="text" name="nickname" value="<?php if (!empty($_POST['nickname'])) echo $_POST['nickname']; ?>" class="<?php if (!empty($err_msg['nickname'])) echo 'err'; ?> " />
    </label>
    <div class="err-msg">
      <?php
      if (!empty($err_msg['email'])) echo $err_msg['email'];
      ?>
    </div>
    <label class="<?php if (!empty($err_msg['email'])) echo 'err'; ?>"><span class="form-badge">必須</span>
      メールアドレス
      <input type="text" name="email" value="<?php if (!empty($_POST['email'])) echo $_POST['email']; ?>" />
    </label>
    <div class="err-msg">
      <?php
      if (!empty($err_msg['pass'])) echo $err_msg['pass'];
      ?>
      <label style=" margin-top:20px;" class="<?php if (!empty($err_msg['given_name'])) echo 'err'; ?>"><span class=" form-badge">必須</span>
        パスワード（6文字以上）
        <input type="password" name="pass" value="<?php if (!empty($_POST['pass'])) echo $_POST['pass']; ?>" />

      </label>
    </div>
    <div class="err-msg">
      <?php
      if (!empty($err_msg['pass_re'])) echo $err_msg['pass_re'];
      ?>
    </div>
    <label class="<?php if (!empty($err_msg['given_name'])) echo 'err'; ?>"><span class="form-badge">必須</span>
      パスワード（確認用再入力）

      <input type="password" name="pass_re" value="<?php if (!empty($_POST['pass_re'])) echo $_POST['pass_re']; ?>" />

    </label>

    <div class="err-msg">
      <?php
      if (!empty($err_msg['family_name'])) echo $err_msg['family_name'];
      ?>
    </div>
    <label class="<?php if (!empty($err_msg['family_name'])) echo 'err'; ?>"><span class="form-badge">必須</span>
      苗字
      <input type="text" name="family_name" value="<?php if (!empty($_POST['family_name'])) echo $_POST['family_name']; ?>" />
      <div class="err-msg">
        <?php
        if (!empty($err_msg['given_name'])) echo $err_msg['given_name'];
        ?>
      </div>
      <label class="<?php if (!empty($err_msg['given_name'])) echo 'err'; ?>"><span class="form-badge">必須</span>
        名前
        <input type="text" name="given_name" value="<?php if (!empty($_POST['given_name'])) echo $_POST['given_name']; ?>" />
      </label>

      <div class="err-msg">
        <?php
        if (!empty($err_msg['age'])) echo $err_msg['age'];
        ?>
      </div>
      <label class="<?php if (!empty($err_msg['age'])) echo 'err'; ?>"><span class="form-badge">必須</span> 年齢<BR><BR>
        <input type="number" name="age" min="0" max="100" value="<?php if (!empty($_POST['age'])) echo $_POST['age']; ?>" />
      </label>
      <div class="err-msg">
        <?php
        if (!empty($err_msg['job'])) echo $err_msg['job'];
        ?>
      </div>
      <label class="<?php if (!is_null($err_msg['job'])) echo 'err'; ?>"> <span class="form-badge">必須</span>職業<BR><BR>
        <select name="job">
          <option value="" style='display:none;'>選択してください</option>
          <option value="公務員" <?php echo array_key_exists('job', $_POST) && $_POST['job'] == '公務員' ? 'selected' : ''; ?>>公務員</option>
          <option value="経営者・役員" <?php echo array_key_exists('job', $_POST) && $_POST['job'] == '経営者・役員' ? 'selected' : ''; ?>>経営者・役員</option>
          <option value="会社員" <?php echo array_key_exists('job', $_POST) && $_POST['job'] == '会社員' ? 'selected' : ''; ?>>会社員</option>
          <option value="自営業" <?php echo array_key_exists('job', $_POST) && $_POST['job'] == '自営業' ? 'selected' : ''; ?>>自営業</option>
          <option value="自由業" <?php echo array_key_exists('job', $_POST) && $_POST['job'] == '自由業' ? 'selected' : ''; ?>>自由業</option>
          <option value="専業主婦" <?php echo array_key_exists('job', $_POST) && $_POST['job'] == '専業主婦' ? 'selected' : ''; ?>>専業主婦</option>
          <option value="パート・アルバイト" <?php echo array_key_exists('job', $_POST) && $_POST['job'] == 'パート・アルバイト' ? 'selected' : ''; ?>>パート・アルバイト</option>
          <option value="学生" <?php echo array_key_exists('job', $_POST) && $_POST['job'] == '学生' ? 'selected' : ''; ?>>学生</option>
          <option value="その他" <?php echo array_key_exists('job', $_POST) && $_POST['job'] == 'その他' ? 'selected' : ''; ?>>その他</option>
        </select>
      </label>
      <div class="err-msg">
        <?php
        if (!empty($err_msg['phone'])) echo $err_msg['phone'];
        ?>
      </div>
      <label style=" margin-top:20px;" class="<?php if (!empty($err_msg['phone'])) echo 'err'; ?>"><span class="form-badge">必須</span>
        電話番号（ハイフンなしでご入力ください）
        <input type="text" name="phone" value="<?php if (!empty($_POST['phone'])) echo $_POST['phone']; ?>" />
      </label>
      <div class="err-msg">
        <?php
        if (!empty($err_msg['zip'])) echo $err_msg['zip'];
        ?>
      </div>
      <label class="<?php if (!empty($err_msg['zip'])) echo 'err'; ?>"><span class="form-badge">必須</span>
        郵便番号（ハイフンなしでご入力ください）
        <input type="text" name="zip" value="<?php if (!empty($_POST['zip'])) echo $_POST['zip']; ?>" />
      </label>
      <div class="err-msg">
        <?php
        if (!empty($err_msg['address'])) echo $err_msg['address'];
        ?>
      </div>
      <label class="<?php if (!empty($err_msg['address'])) echo 'err'; ?>"><span class="form-badge">必須</span>
        住所
        <input type="text" name="address" value="<?php if (!empty($_POST['address'])) echo $_POST['address']; ?>" />
      </label>

      <br />
      <div class="center">
        <input class="btn btn-brown" type="submit" value="会員登録" />
      </div>
  </form>
</div>
<!-- フッタータグ内 -->
<?php
require('footer.php');
?>