<?php
require('function.php');

debug('==============================================================');
debug('マイページ（保護者用）');
debug('==============================================================');
debugLogStart();

require('auth.php');

$userInfo = userInfoAll($_SESSION['user_id']);
debug('$userInfoの中身:' . print_r($userInfo, true));

if ($userInfo['user_role'] === '一般会員') {
  debug('一般会員なのでページ移動します');
  header("Location: mypage2.php");
  exit();
}

$dbAnimalData = getMyAnimalList($_SESSION['user_id']);
debug('$dbAnimalDataの中身:' . print_r($dbAnimalData, true));

$myMsgData = getMyMsg($_SESSION['user_id']);
debug('$myMsgDataの中身:' . print_r($myMsgData, true));

$applicantsInfo = userInfoAll($myMsgData['sender_id']);
debug('メッセージ送った人の情報:' . print_r($applicantsInfo, true));

debug('==============================================================');
debug('画面表示処理終了');
debug('==============================================================');
?>
<!-- headタグ内 -->
<?php $siteTitle = 'マイページ';
require('head.php'); ?>

<!-- ヘッダータグ内 -->
<?php require('header.php'); ?>

<!-- 変更／登録用のモーダル -->
<?php if (!empty($_SESSION['notice'])) {
  require('modal.php');
  unset($_SESSION['notice']);
  debug('$_SESSIONのnoticeがunsetされたかの確認用:' . print_r($_SESSION['notice'], true));
}
?>
<div class="mypage-grid">
  <section class="mypage-main">
    <div class="list-title">
      <h2>マイぺージ</h2>
    </div>
    <article class="mypage-board">
      <div class="mypage-head">メッセージ一覧</div>
      <div class="msg-card">
        <div class="main-comment">
          <?php
          if (!empty($myMsgData)) :
            foreach ($myMsgData as $key => $val) : ?>
              <?php if (!empty($val['msg'])) {
                $msg = array_shift($val['msg']);
              } ?>
              <ul>
                <a href="chat.php?m_id=<?php echo sanitize($val['id']); ?>">
                  <li><?php echo sanitize(date('Y年m月d日 H時i分', strtotime($msg['send_date']))); ?></li>
                  <li class="space-left">相手:<span class="bolder"><?php echo mb_substr(sanitize($val['sender_name']), 0, 8); ?></span></li>
                  <li class="space-left"><?php echo mb_substr(sanitize($msg['msg']), 0, 21); ?>
                  </li>
                </a>
              </ul>
            <?php endforeach; ?>
          <?php endif; ?>
          <?php if (empty($myMsgData)) echo '<p>メッセージが来たらここに表示します</p>';
          ?>
        </div>
      </div>
    </article>

    <article class="mypage-board">
      <div class="like-head">
        登録した保護犬・猫一覧（クリックで編集画面へ移動します）
      </div>
      <div class="like-card">
        <div class="like-main">
          <div class="main-pics">
            <!-- URLとsrcを動的にかえる。 -->
            <?php
            foreach ($dbAnimalData['data'] as $key => $val) :
            ?>
              <div class="pic-box">
                <a href="animalRegist.php<?php echo (!empty(appendGetParam())) ? appendGetParam() . '&a_id=' . $val['id'] : '?a_id=' . $val['id']; ?>"><img src="<?php echo showImgA($val['pic1'], $val['animal_type']); ?>" width="300" alt="<?php echo sanitize($val['name']); ?>" /></a>
                <p><?php echo sanitize($val['name']); ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </article>
  </section>

  <section class="mypage-side">
    <aside class="info-card-guardians">
      <span class="form-badge">登録情報</span>
      <div class="info-icon">
        <img src="<?php echo showImgH(sanitize($userInfo['pic'])); ?>" alt="person-icon" />
      </div>
      <div class="info-area">
        <ul>
          <li>ようこそ<?php echo sanitize($userInfo['nickname']); ?>さん</li>
          <li>会員種別<span class="info-span"><?php echo sanitize($userInfo['user_role']); ?></span></li>
          <li>ユーザーID<span class="info-span"><?php echo sanitize($userInfo['id']); ?></span></li>
          <li>登録日<span class="info-span"><?php echo sanitize(date('Y年n月j日', strtotime($userInfo['create_date']))); ?></span></li>
        </ul>
      </div>
      <div class="contents-area">
        <ul>
          <li><a href="profEdit.php">プロフィール変更</a></li>
          <li><a href="passChange.php">パスワード変更</a></li>
          <li><a href="delete.php">退会</a></li>
        </ul>
        <a class="btn btn-brown-sm" href="animalRegist.php">保護犬・猫の登録</a>
      </div>
    </aside>
  </section>
</div>
<!-- フッタータグ内 -->
<?php
require('footer.php');
?>