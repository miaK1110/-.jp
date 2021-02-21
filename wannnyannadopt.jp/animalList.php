<div class="list-title">
  <h3>保護犬・保護猫一覧</h3>
</div>

<div class="animal-list">

  <?php
  foreach ($dbAnimalData['data'] as $key => $val) :
    $a_id = $val['id'];
  ?>
    <!-- 記事一覧 -->
    <div class="article-card">
      <a href="animalPage.php<?php echo (!empty(appendGetParam())) ? appendGetParam() . '&a_id=' . $a_id : '?a_id=' . $a_id; ?>">
        <div class="article-img">
          <img src="<?php echo showImgA($val['pic1'], $val['animal_type']); ?>" alt="<?php echo sanitize($val['name']); ?>" />
        </div>

        <div class="article-head">
          <ul>
            <li><?php echo sanitize($val['name']); ?></li>
          </ul>
        </div>
        <div class="article-type">
          <ul>
            <li><?php if ($val['gender'] === 'オス') echo '<p style="color:#8ea9e4;">♂</p>'; ?><?php if ($val['gender'] === 'メス') echo '<p style="color:#f3a49b;">♀</p>'; ?>
            </li>
            <li>/</li>
            <li><?php echo sanitize($val['age']); ?></li>
            <ul>
        </div>
        <div class="article-comment">
          <p>
            <?php echo sanitize(mb_strimwidth($val['comment'], 0, 110, '…', 'UTF-8')); ?>
          </p>
        </div>
      </a>

      <!-- $login_flgをscript.jsに渡すための記述 -->
      <?php $login_flg = !empty($_SESSION['user_id']); ?>
      <script>
        var login_flg = ('<?php echo $login_flg; ?>' == 1);
        console.log(login_flg);
      </script>

      <!-- お気に入りボタン -->
      <section class="post" data-animalid="<?php echo sanitize($val['id']); ?>">
        <div class="btn btn--orange btn-c btn-favourite <?php if (isFavourite($_SESSION['user_id'], $a_id)) echo 'active'; ?>">
          <!-- 自分がいいねした保護犬・猫にはハートのスタイルを常に保持する -->
          <i class="fa-heart fa-lg px-16
        <?php
        if (isFavourite($_SESSION['user_id'], $a_id)) { //お気に入り登録を押したらハートが塗りつぶされる
          echo ' active fas';
        } else { //お気に入り登録を取り消したらハートのスタイルが取り消される
          echo ' far';
        }; ?>"></i>お気に入り登録
        </div>
    </div>
    </section>
  <?php endforeach; ?>
</div>