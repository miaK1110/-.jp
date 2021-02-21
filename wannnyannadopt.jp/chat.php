<?php
require('function.php');

debug('==============================================================');
debug('掲示板ページ');
debug('==============================================================');
debugLogStart();

require('auth.php');
debug('セッション変数の中身:' . print_r($_SESSION, true));

$m_id = '';
$chatInfo = '';
$animalInfo = '';
$guardiansInfo = '';
$senderInfo = '';

$m_id = $_GET['m_id'];

$chatInfo = getMsgAndBoard($m_id);
//保護犬・猫の情報
$animalInfo = animalInfoAll($chatInfo['animal_id']);
//掲載者の情報
$guardiansInfo = userInfoAll($chatInfo['guardians_id']);
//里親を申し出た人の情報
$applicantsInfo = userInfoAll($chatInfo['sender_id']);
//メッセージを送った人の情報
$senderInfo = userInfoAll($_SESSION['user_id']);
// $msgInfo = $chatInfo['']

debug('$m_idの中身:' . $m_id);
debug('掲示板やその他の情報:' . print_r($chatInfo, true));
debug('メッセージ内容の情報:' . print_r($chatInfo['data'], true));
debug('保護犬・猫の情報:' . print_r($animalInfo, true));
debug('保護者の情報:' . print_r($guardiansInfo, true));
debug('里親を申し出た人の情報:' . print_r($applicantsInfo, true));
debug('メッセージを送った人の情報:' . print_r($senderInfo, true));


//情報を取得できていないならトップページへ戻す
if (!empty($_GET['m_id']) && empty($senderInfo) && empty($guardiansInfo)) {
  debug('エラー発生。トップページへ移動します');
  $_SESSION['notice'] = ERR04;
  header("Location: index.php");
  exit();
}

if (!empty($_POST['add'])) {
  debug('メッセージ送信されました');
  debug('postの内容:' . print_r($_POST, true));
  debug('$_FILESの内容' . print_r($_FILES, true));

  $msg = filter_input(INPUT_POST, 'msg');
  $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'], 'pic1') : '';
  $pic2 = (!empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'], 'pic2') : '';
  $pic3 = (!empty($_FILES['pic3']['name'])) ? uploadImg($_FILES['pic3'], 'pic3') : '';

  $err_msg['msg'] = emp($msg);

  debug('エラーメッセージの内容:' . print_r($err_msg, true));

  if (empty(array_shift($err_msg))) {

    try {
      $dbh = dbConnect();
      $sql = 'INSERT INTO message (board_id, sender_id, sender_name, sender_pic, msg, pic1, pic2, pic3, delete_flg, send_date, create_date, update_date) VALUES (:b_id, :s_id, :s_name, :s_pic, :msg, :pic1, :pic2, :pic3, :delete_flg, :send_date, :create_date, :update_date)';
      $data = array(
        ':b_id' => $m_id,
        ':s_id' => $_SESSION['user_id'],
        ':s_name' => $senderInfo['nickname'],
        ':s_pic' => $senderInfo['pic'],
        ':msg' => $msg,
        ':pic1' => $pic1,
        ':pic2' => $pic2,
        ':pic3' => $pic3,
        ':delete_flg' => 0,
        ':send_date' => date('Y-m-d H:i:s'),
        ':create_date' => date('Y-m-d H:i:s'),
        ':update_date' => date('Y-m-d H:i:s')
      );
      $stmt = queryPost($dbh, $sql, $data);
      if ($stmt) {
        debug('クエリ成功。ページをリダイレクトします');
        header("Location: chat.php?m_id={$m_id}");
        exit();
      } else {
        return false;
        debug('メッセージ挿入クエリ失敗');
        debug('SQLエラー' . print_r($stmt->errorInfo(), true));
      }
    } catch (Exception $e) {
      debug('メッセージ挿入でエラー発生:' . $e->getMessage());
    }
  }
}



if (!empty($_POST['msg_delete'])) {
  debug('消去するボタンが押されました');
  debug('postの内容:' . print_r($_POST, true));

  foreach ($chatInfo['data'] as $key => $val) {

    $msg_id = $val['id'];
    debug('$chatInfo[id]:' . print_r($msg_id));
    try {
      $dbh = dbConnect();
      $sql = 'DELETE FROM message WHERE id=:msg_id';
      $data = array(':msg_id' => $msg_id);
      $stmt = queryPost($dbh, $sql, $data);
      if ($stmt) {
        debug('クエリ成功。ページをリダイレクトします');
        header("Location: chat.php?m_id={$m_id}");
        exit();
      } else {
        return false;
        debug('メッセージ挿入クエリ失敗');
        debug('SQLエラー' . print_r($stmt->errorInfo(), true));
      }
    } catch (Exception $e) {
      debug('エラー発生:' . $e->getMessage());
    }
  }
}

if (!empty($_POST['update'])) {
  debug('譲渡決定ボタンが押されました');
  try {
    $dbh = dbConnect();
    $sql = 'UPDATE animals SET delete_flg = 1 WHERE id = :a_id';
    $data = array(':a_id' => $chatInfo['animal_id']);
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('クエリ成功です');
      $_SESSION['notice'] = SUC09;
      header("Location: mypage.php");
      exit();
    } else {
      debug('譲渡決定のクエリ失敗');
      debug('SQLエラー' . print_r($stmt->errorInfo(), true));
    }
  } catch (Exception $e) {
    debug('譲渡決定でエラー発生:' . $e->getMessage());
  }
}

debug('==============================================================');
debug('画面表示処理終了');
debug('==============================================================');
?>
<!-- headタグ内 -->
<?php $siteTitle = '掲示板';
require('head.php'); ?>

<!-- ヘッダータグ内 -->
<?php require('header.php'); ?>

<!-- モーダル -->
<?php if (!empty($_SESSION['notice'])) {
  require('modal.php');
  unset($_SESSION['notice']);
  debug('$_SESSIONのnoticeがunsetされたかの確認用:' . print_r($_SESSION['notice'], true));
}
?>

<div class="chat-grid">
  <section class="chat-main">
    <div class="list-title">
      <h2>メッセージ一覧</h2>
    </div>

    <article class="chat-board">

      <?php
      if (!empty($chatInfo)) :
        foreach ($chatInfo['data'] as $key => $val) :
      ?>
          <div class="msg-head"><?php echo sanitize(date('Y年n月j日H時i分', strtotime($val['send_date']))); ?> 名前:<?php echo sanitize($val['sender_name']); ?></div>
          <div class="msg-card">
            <div class="person-icon">
              <img src="<?php echo showImgH($val['sender_pic']); ?>" alt="person-icon" />
            </div>
            <div class="msg-comment">
              <div class="main-comment">
                <?php echo sanitize($val['msg']); ?>
                <div class="main-pics">

                  <a href="<?php echo sanitize($val['pic1']); ?>" data-lightbox="group"><img src="<?php echo sanitize($val['pic1']); ?>" width="300" style="<?php if (empty($val['pic1'])) echo 'display:none;'; ?>" /></a>

                  <a href="<?php echo sanitize($val['pic2']); ?>" data-lightbox="group"><img src="<?php echo sanitize($val['pic2']); ?>" width="300" style="<?php if (empty($val['pic2'])) echo 'display:none;'; ?>" /></a>

                  <a href="<?php echo sanitize($val['pic3']); ?>" data-lightbox="group"><img src="<?php echo sanitize($val['pic3']); ?>" width="300" style="<?php if (empty($val['pic3'])) echo 'display:none;'; ?>" /></a>

                </div>
              </div>
              <form method="post" action="">
                <input type="hidden" name="delete" value="delete" />
                <input type="submit" name="msg_delete" value="消去する" style="<?php if ($_SESSION['user_id'] !== $val['sender_id']) echo 'display:none;' ?>'" class="btn btn-brown-xsm">
              </form>
            </div>
          </div>


        <?php endforeach; ?>
      <?php endif; ?>
    </article>
    <div class="list-title">
      <h2>メッセージ送信</h2>
    </div>
    <form class="form-chat" method="post" action="" enctype="multipart/form-data">
      <div class="err-msg">
        <?php
        if (!empty($err_msg['msg'])) echo $err_msg['msg'];
        ?>
      </div>
      <input type="hidden" name="add" value="add" />
      <textarea name="msg" placeholder="ここにメッセージを書いてください。空文字や写真のみは送信できません。"></textarea>
      <div class="pics-wrapper-chat">
        <label class="area-drop">
          <input type="hidden" name="MAX_FILE_SIZE" value="3145728" />
          <input type="file" name="pic1" class="input-file" style="display: none" />

          <img src="" alt="" class="prev-img" style="display: none" />
          ドラッグ＆ドロップ
        </label>

        <label class="area-drop">
          <input type="hidden" name="MAX_FILE_SIZE" value="3145728" />
          <input type="file" name="pic2" class="input-file" style="display: none" />
          <img src="" alt="" class="prev-img" style="display: none" />
          ドラッグ＆ドロップ
        </label>

        <label class="area-drop">
          <input type="hidden" name="MAX_FILE_SIZE" value="3145728" />
          <input type="file" name="pic3" class="input-file" style="display: none" />
          <img src="" alt="" class="prev-img" style="display: none" />
          ドラッグ＆ドロップ
        </label>
        <div class="btn-ajust">
          <input name="send" type="submit" class="btn btn-brown" value="メッセージを送信" />
        </div>
      </div>
    </form>
  </section>

  <section class="chat-side">
    <div class="chat-side-wrapper">
      <aside class="info-card">
        <span class="form-badge"><?php if ($senderInfo['user_role'] === '一般会員') echo '保護者情報'; ?><?php if ($senderInfo['user_role'] === '保護者') echo '里親候補者情報'; ?></span>
        <?php if ($senderInfo['user_role'] === '一般会員') : ?>
          <div class="icon">
            <img src="<?php echo showImgH($guardiansInfo['pic']); ?>" alt="person-icon" />
          </div>
          <div class="info-area">
            <ul>
              <li>候補者<span class="info-span"><?php echo sanitize($guardiansInfo['nickname']); ?></span>さん</li>
              <li>会員種別<span class="info-span"><?php echo sanitize($guardiansInfo['user_role']); ?></span></li>
              <li>ユーザーID<span class="info-span"><?php echo sanitize($guardiansInfo['id']); ?></span></li>
            </ul>
          <?php endif; ?>
          <?php if ($senderInfo['user_role'] === '保護者') : ?>
            <div class="icon">
              <img src="<?php echo showImgH($applicantsInfo['pic']); ?>" alt="person-icon" />
            </div>
            <div class="info-area">
              <ul>
                <li>候補者<span class="info-span"><?php echo sanitize($applicantsInfo['nickname']); ?></span>さん</li>
                <li>会員種別<span class="info-span"><?php echo sanitize($applicantsInfo['user_role']); ?></span></li>
                <li>ユーザーID<span class="info-span"><?php echo sanitize($applicantsInfo['id']); ?></span></li>
                <li>職業<span class="info-span"><?php echo sanitize($applicantsInfo['job']); ?></span></li>
                <li>年齢<span class="info-span"><?php echo sanitize($applicantsInfo['age']); ?></span>歳</li>
              </ul>
              <p>
                <?php echo $applicantsInfo['comment']; ?>
              <p>
              <?php endif; ?>
            </div>
      </aside>

      <div class="info-card">
        <span class="form-badge">保護犬・保護猫情報</span><br />
        <div class="icon">
          <img src="<?php echo sanitize($animalInfo['pic1']); ?>" alt="animal-icon" />
        </div>
        <div class="info-area">
          <ul>
            <li>名前<span class="info-span"><?php echo sanitize($animalInfo['name']); ?></span></li>
            <li>種別<span class="info-span"><?php echo sanitize($animalInfo['animal_type']); ?></span></li>
            <li>性別<span class="info-span"><?php echo sanitize($animalInfo['gender']); ?></span></li>
            <li>年齢<span class="info-span"><?php echo sanitize($animalInfo['age']); ?></span></li>
            <li>対象地域<span class="info-span"><?php echo sanitize($animalInfo['area']); ?></span>地方</li>
          </ul>
        </div>
      </div>

      <div class="center">
        <form method="post" action="">
          <input type="hidden" name="update" value="update" />
          <input name="flg" type="submit" class="btn btn-brown-sm" value="譲渡決定にする" style="<?php if ($senderInfo['user_role'] !== "保護者" || $animalInfo['delete_flg'] == 1) echo 'display:none;' ?>" />
        </form>
      </div>
    </div>
  </section>
</div>
<!-- フッタータグ内 -->
<?php
require('footer.php');
?>