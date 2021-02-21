<?php
//共通変数・関数の読み込み
require('function.php');

debug('==============================================================');
debug('トップページ');
debug('==============================================================');
debugLogStart();


//現在のページ数
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;
//ngram全文検索用
$search = (!empty($_GET['search'])) ? sanitize(($_GET['search'])) : '';
// 種別検索用
$a_type = (!empty($_GET['a_type'])) ? $_GET['a_type'] : '';
// 性別別検索用
$gender = (!empty($_GET['gender'])) ? $_GET['gender'] : '';
//年齢別検索用
$age = (!empty($_GET['age'])) ? $_GET['age'] : '';
//地域別検索用
$area = (!empty($_GET['area'])) ? $_GET['area'] : '';
//表示件数
$listspan = 20;
// 現在の表示レコード先頭を算出
$currentMinNum = ($currentPageNum - 1) * $listspan;
//// DBから保護犬・猫のデータを取得
$dbAnimalData = getAnimalList($currentMinNum, $search, $a_type, $gender, $age, $area);
//toppageの新着レコード６件を取得
$dbAnimalDataSix = getNewAnimalSix();

// debug('現在のページ：' . $currentPageNum);
// debug('保護犬・猫のDBデータ：' . print_r($dbAnimalData, true));
// debug('最新の保護犬・猫のDBデータ：' . print_r($dbAnimalDataSix, true));

//ページ数が合っているかチェック
if (!is_int((int)$currentPageNum)) {
  debug('不正なパラメータが入りました。トップページへ移動します');
  header("Location: index.php");
  exit();
}
debug('==============================================================');
debug('画面表示処理終了');
debug('==============================================================');
?>

<!-- ヘッドタグ内 -->
<?php $siteTitle = 'トップ';
require('head.php'); ?>

<!-- ヘッダータグ内 -->
<?php require('header.php'); ?>

<section class="top-title">
  <h1>
    新しい家族を待っている犬猫たちを幸せに。<br />
    そんな思いから生まれた里親募集情報サイトです。
  </h1>
  <img class="top-dog-img" src="img/top-dog-img.png" alt="top-dog-img" />
</section>

<!-- お問い合わせ・違反報告・退会後用モーダル -->
<?php if (!empty($_SESSION['notice'])) {
  require('modal.php');
  unset($_SESSION['notice']);
  debug('$_SESSIONのnoticeがunsetされたかの確認:' . print_r($_SESSION['notice'], true));
} ?>

<!-- window幅を80%に、中央寄せ -->
<div id="contents-wrapper">
  　
  <div class="grid">
    <article class="main">
      <!-- getに何も入ってない時のみ新着６件を表示 -->
      <?php if (empty($_GET)) {
        require('newAnimalList.php');
      }
      ?>

      <div class="animal-sum">
        <div class="animal-num">
          <span class="num"><?php echo (!empty($dbAnimalData['data'])) ? $currentMinNum + 1 : 0; ?></span> - <span class="num"><?php echo $currentMinNum + count($dbAnimalData['data']); ?></span>件 /
          <span class="num"><?php echo sanitize($dbAnimalData['total']); ?></span>件中
        </div>
      </div>

      <?php
      require('animalList.php');
      ?>

    </article>
    <section class="side">
      <aside class="side-wrapper">
        <!-- ngram全文検索のフォーム -->
        <div class="form-toppage">
          <form class="search-form" method="get" action="">
            <input id="search-box" name="search" type="text" placeholder="<?php if (empty($search)) {
                                                                            echo 'キーワードで検索';
                                                                          } elseif (!empty($search)) {
                                                                            echo getFormData('search', $flg = true);
                                                                          } ?>" />
            <input id="search-btn" type="submit" value="検索" />
          </form>
          <!-- 種別・性別・年齢・対象地域のフォーム -->
          <form method="get" action="">
            <div class="select">
              <h5>種別</h5>
              <select name="a_type">
                <option value="0" style='display:none;'>選択してください</option>
                <option value="小型犬" <?php if ($a_type == "小型犬") {
                                      echo 'selected';
                                    } ?>>小型犬</option>
                <option value="中型犬" <?php if ($a_type === "中型犬") {
                                      echo 'selected';
                                    } ?>>中型犬</option>
                <option value="大型犬" <?php if ($a_type === "大型犬") {
                                      echo 'selected';
                                    } ?>>大型犬</option>
                <option value="猫" <?php if ($a_type === "猫") {
                                    echo 'selected';
                                  } ?>>猫</option>
              </select>
            </div>

            <div class="radio-gender">
              <h5>性別</h5>
              <label><input type="radio" name="gender" value="すべて" <?php if ($gender === 'すべて') echo 'checked'; ?> />すべて</label>
              <label><input type="radio" name="gender" value="オス" <?php if ($gender === 'オス') echo 'checked'; ?> />オス</label>
              <label><input type="radio" name="gender" value="メス" <?php if ($gender === 'メス') echo 'checked'; ?> />メス</label>
            </div>

            <div class="select">
              <h5>年齢</h5>
              <select name="age">
                <option value="" style='display:none;'>選択してください</option>
                <option value="0～1歳" <?php if ($age == '0～1歳') echo 'selected'; ?>>0～1歳</option>
                <option value="1～7歳" <?php if ($age == '1～7歳') echo 'selected'; ?>>1～7歳</option>
                <option value="7～12歳" <?php if ($age == '7～12歳') echo 'selected'; ?>>7～12歳</option>
                <option value="12歳～" <?php if ($age == '12歳～') echo 'selected'; ?>>12歳～</option>
              </select>
            </div>

            <div class="select">
              <h5>対象地域</h5>
              <select name="area">
                <option value="" style='display:none;'>選択してください</option>
                <option value="北海道" <?php if ($area == '北海道') echo 'selected'; ?>>北海道</option>
                <option value="東北" <?php if ($area == '東北') echo 'selected'; ?>>東北</option>
                <option value="関東" <?php if ($area == '関東') echo 'selected'; ?>>関東</option>
                <option value="北陸" <?php if ($area == '北陸') echo 'selected'; ?>>北陸</option>
                <option value="東海" <?php if ($area == '東海') echo 'selected'; ?>>東海</option>
                <option value="近畿" <?php if ($area == '近畿') echo 'selected'; ?>>近畿</option>
                <option value="中国" <?php if ($area == '中国') echo 'selected'; ?>>中国</option>
                <option value="四国" <?php if ($area == '四国') echo 'selected'; ?>>四国</option>
                <option value="九州" <?php if ($area == '九州') echo 'selected'; ?>>九州</option>
                <option value="沖縄" <?php if ($area == '沖縄') echo 'selected'; ?>>沖縄</option>
              </select>
            </div>
            <div class="searchByConditions">
              <input class="btn btn-brown-sm" type="submit" value="条件を絞って検索" />
            </div>
          </form>
        </div>
      </aside>
    </section>
  </div>
</div>

<!-- ページネーション -->

<div class="pagination">
  <ul class="pagination-list">
    <?php
    $pageColNum = 5;
    $totalPageNum = $dbAnimalData['total_page'];

    debug('トータルページ数' . $totalPageNum);
    // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
    if ($currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum) {
      $minPageNum = $currentPageNum - 4;
      $maxPageNum = $currentPageNum;
      // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
    } elseif ($currentPageNum == ($totalPageNum - 1) && $totalPageNum >= $pageColNum) {
      $minPageNum = $currentPageNum - 3;
      $maxPageNum = $currentPageNum + 1;
      // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
    } elseif ($currentPageNum == 2 && $totalPageNum >= $pageColNum) {
      $minPageNum = $currentPageNum - 1;
      $maxPageNum = $currentPageNum + 3;
      // 現ページが1の場合は左に何も出さない。右に５個出す。
    } elseif ($currentPageNum == 1 && $totalPageNum >= $pageColNum) {
      $minPageNum = $currentPageNum;
      $maxPageNum = 5;
      // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
    } elseif ($totalPageNum < $pageColNum) {
      $minPageNum = 1;
      $maxPageNum = $totalPageNum;
      // それ以外は左に２個出す。
    } else {
      $minPageNum = $currentPageNum - 2;
      $maxPageNum = $currentPageNum + 2;
    }
    ?>
    <?php if ($currentPageNum != 1) : ?>
      <li class="list-item"><a href="?p=1">&lt;</a></li>
    <?php endif; ?>
    <?php
    for ($i = $minPageNum; $i <= $maxPageNum; $i++) :
    ?>
      <li class="list-item <?php if ($currentPageNum == $i) echo 'active'; ?>"><a href="?p=<?php echo $i; ?>"><?php echo $i; ?></a></li>
    <?php
    endfor;
    ?>
    <?php if ($currentPageNum != $maxPageNum) : ?>
      <li class="list-item"><a href="?p=<?php echo $maxPageNum; ?>">&gt;</a></li>
    <?php endif; ?>
  </ul>
</div>
<!-- フッタータグ内 -->
<?php
require('footer.php');
?>