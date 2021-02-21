<?php
require('function.php');

debug('==============================================================');
debug('保護猫・猫掲載ページ');
debug('==============================================================');
debugLogStart();



$a_id = (!empty($_GET['a_id'])) ? $_GET['a_id'] : '';
$animalInfo = animalAndGuardianInfo($a_id);
debug('$animalInfoの中身:' . print_r($animalInfo, true));


//不正な値が入っているかチェック
if (empty($animalInfo)) {
  error_log('指定ページに不正な値が入りました。トップページへ移動します');
  $_SESSION['notice'] = ERR04;
  header("Location:index.php");
  exit();
}


if (!empty($_POST)) {

  debug('post送信されました');
  require('auth.php');

  $userInfo = userInfoAll($_SESSION['user_id']);
  debug('$userInfoの中身' . print_r($userInfo, true));

  if ($animalInfo['guardians_id'] === $_SESSION['user_id']) {
    debug('掲載者がボタンを押しました');
    $_SESSION['notice'] = ERR03;
    header("Location: mypage.php");
    exit();
  }

  try {
    $dbh = dbConnect();
    $sql = 'INSERT INTO board (animal_id, guardians_id, guardians_name, sender_id, sender_name, create_date, update_date) VALUES (:a_id, :g_id, :g_name, :s_id, :s_name, :create_date, :update_date)';
    $data = array(
      ':a_id' => $a_id,
      ':g_id' => $animalInfo['guardians_id'],
      //保護者のニックネーム
      ':g_name' => $animalInfo['nickname'],
      ':s_id' => $_SESSION['user_id'],
      ':s_name' => $userInfo['nickname'],
      ':create_date' => date('Y-m-d H:i:s'),
      ':update_date' => date('Y-m-d H:i:s')
    );
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      $_SESSION['notice'] = SUC04;
      debug('掲示板に移動します');
      debug(print_r($stmt), true);
      header("Location: chat.php?m_id=" . $dbh->lastInsertId());
      exit();
    }
  } catch (Exception $e) {
    error_log('保護犬・猫掲載ページでエラー発生:' . $e->getMessage());
  }
}
debug('画面処理終了,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,');
?>

<!-- headタグ内 -->
<?php $siteTitle = '掲載ページ';
require('head.php'); ?>

<!-- ヘッダータグ内 -->
<?php require('header.php'); ?>

<div class="animalpage-grid">
  <section class="animalpage-main">
    <article class="animalpage-pics">
      <div class="animalpage-head">掲載No.<?php echo sanitize($animalInfo['animal_id']); ?>／
        <a href="report.php?a_id=<?php echo sanitize($animalInfo['id']); ?>">違反報告をする</a>
      </div>
      <div class="pic-left">
        <a href="<?php echo showImgA($animalInfo['pic1'], $animalInfo['animal_type']); ?>" data-lightbox="group"><img src="<?php echo showImgA($animalInfo['pic1'], $animalInfo['animal_type']); ?>" width="300" /></a>
      </div>

      <div class="pic-right">
        <div class="pic-box">
          <a href="<?php echo showImgA($animalInfo['pic2'], $animalInfo['animal_type']); ?>" data-lightbox="group"><img src="<?php echo showImgA($animalInfo['pic2'], $animalInfo['animal_type']); ?>" style="<?php if (empty($animalInfo['pic2'])) echo 'display:none;' ?>" width="300" /></a>

          <a href="<?php echo showImgA($animalInfo['pic3'], $animalInfo['animal_type']); ?>" data-lightbox="group"><img src="<?php echo showImgA($animalInfo['pic3'], $animalInfo['animal_type']); ?>" width="300" style="<?php if (empty($animalInfo['pic3'])) echo 'display:none;' ?>" /></a>
        </div>
      </div>
    </article>
    <div class="animalpage-head">基本情報</div>
    <section class="animal-info">
      <div class="animal-info-main">
        <ul>
          <li>
            <h5>名前：<span><?php echo sanitize($animalInfo['name']); ?></span></h5>
          </li>
          <li>
            <h5>種別：<span><?php echo sanitize($animalInfo['animal_type']); ?> </span></h5>
          </li>
          <li>
            <h5>性別：<span><?php echo sanitize($animalInfo['gender']); ?></span></h5>
          </li>
          <li>
            <h5>年齢：<span><?php echo sanitize($animalInfo['animal_age']); ?></span></h5>
          </li>
          <li>
            <h5>対象地域：<span><?php echo sanitize($animalInfo['animal_area']); ?></span></h5>
          </li>
          <li>
            <h5>掲載日：<span><?php echo sanitize(date('Y年n月j日', strtotime($animalInfo['create_date']))); ?></span></h5>
          </li>
        </ul>
      </div>

      <div class="animal-info-comment">
        <h5>コメント</h5>
        <p>
          <?php echo sanitize($animalInfo['animal_comment']); ?>
        </p>
      </div>
    </section>
    <a href="index.php<?php echo appendGetParam(array('a_id')); ?>">一覧に戻る</a>
  </section>

  <section class="animalpage-side">
    <aside class="info-card">
      <span class="form-badge">掲載者情報</span>
      <div class="icon">
        <img src="<?php echo showImgH($animalInfo['pic']); ?>" alt="person-icon" />
      </div>
      <div class="info-area">
        <ul>
          <li>名前<span class="info-span"><?php echo sanitize($animalInfo['nickname']); ?></span></li>
          <li>会員種別<span class="info-span">保護者</span></li>
          <li>ユーザーID<span class="info-span"><?php echo sanitize($animalInfo['guardians_id']); ?></span></li>
        </ul>
      </div>
      <div class="comment">
        <h5>ユーザーコメント</h5>
        <p>
          保護猫の活動を個人で行っています。テキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキスト
        </p>
      </div>
    </aside>
    <div class="center">
      <form method="post" action="">
        <input type="submit" name="submit" class="btn btn-brown-sm" value="里親を申し出る・質問" style="<?php if ($animalInfo['user_role'] === "保護者") echo 'display:none;' ?>">
      </form>
    </div>
  </section>
</div>
<!-- フッタータグ内 -->
<?php
require('footer.php');
?>