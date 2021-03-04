$(function () {
  var $favourite = $('.btn-favourite'), //いいねボタンセレクタ
    animalFavouriteId; //保護犬・猫ID
  $favourite.on('click', function (e) {
    if (login_flg === true) {
      e.stopPropagation();
      var $this = $(this);
      //カスタム属性(animalid)から保護犬・猫ID取得
      animalFavouriteId = $this.parents('.post').data('animalid');
      console.log(animalFavouriteId);
      $.ajax({
        type: 'POST',
        url: 'ajaxFavourite.php', //post送信を受けとるphpファイル
        data: { animalId: animalFavouriteId }, //{キー:投稿ID}
      })
        .done(function () {
          console.log('Ajax Success');

          $this.children('i').toggleClass('far'); //空洞ハート
          // いいね押した時のスタイル
          $this.children('i').toggleClass('fas'); //塗りつぶしハート
          $this.children('i').toggleClass('active');
          $this.toggleClass('active');
        })
        .fail(function () {
          console.log('Ajax Error');
        });
    } else {
      window.location.href = 'login.php';
    }
  });
});
