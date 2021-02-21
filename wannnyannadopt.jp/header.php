<body>
  <header class="header">
    <a href="index.php">
      <div class="top-logo">
        <span class="top-logo orange">わんにゃん</span><span class="top-logo grey">あどぷと.jp</span>
        <span class="top-logo icon"><img src="img/top-logo-neko-icon.png" alt="neko-icon" /></span>
      </div>
    </a>

    <nav id="global-nav">
      <?php if (empty($_SESSION['user_id'])) : ?>
        <ul>
          <li><a href="login.php">ログイン</a></li>
          <li><a href="signup.php">新規登録</a></li>
          <li><a href="contact.php">お問い合わせ</a></li>
        </ul>
      <?php endif; ?>
      <?php if (!empty($_SESSION['user_id'])) : ?>
        <ul>
          <li><a href="mypage.php">マイページ</a></li>
          <li><a href="logout.php">ログアウト</a></li>
          <li><a href="contact.php">お問い合わせ</a></li>
        </ul>
      <?php endif; ?>
    </nav>
  </header>