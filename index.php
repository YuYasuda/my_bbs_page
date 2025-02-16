<?php

$message = '';

session_start();
require('dbconnect.php');

if(isset($_SESSION['id'])&& $_SESSION['time']+3600>time()){
//ログインしている
$_SESSION['time']=time();

$members=$db->prepare('SELECT*FROM members WHERE id=?');
$members->execute(array($_SESSION['id']));
$member=$members->fetch();
$login = 'true'; //ログイン判定フラグon
}else{
//ログインしていない場合は閲覧のみ
$login = 'false'; //ログイン判定フラグoff
}

//投稿を取得する
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
if ($page == ''){
    $page = 1;
}
$page = max($page,1);

//最終ページを取得する
$counts=$db->query('SELECT COUNT(*) AS cnt FROM posts');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt']/5);
$page = min($page,$maxPage);

$start = ($page-1)*5;

// 投稿を取得する
$posts = $db->prepare('
    SELECT m.name, m.picture AS member_picture, p.picture AS post_picture, p.*, 
    (SELECT COUNT(*) FROM goods g WHERE g.post_id = p.id) AS good_count 
    FROM members m, posts p 
    WHERE m.id = p.member_id 
    ORDER BY p.created DESC 
    LIMIT ?, 5
');
$posts->bindParam(1, $start, PDO::PARAM_INT);
$posts->execute();

//htmlspecialcharsのショートカット
function h($value){
    return htmlspecialchars($value,ENT_QUOTES);
}
//本文内のURLにリンクを設定します
function makeLink($value) {
    return mb_ereg_replace(
        "(https?)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)",
        '<a href="\1\2" target="_blank" rel="noopener noreferrer">\1\2</a>',
        $value
    );
}
// good
$goodStatus = [];
if ($login == 'true') {
    $userGoods = $db->prepare('SELECT post_id FROM goods WHERE user_id = ?');
    $userGoods->execute([$_SESSION['id']]);
    $goodPosts = $userGoods->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($goodPosts as $goodPost) {
        $goodStatus[$goodPost] = true;
    }
}
// goodここまで
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>ひとこと掲示板</title>
        <link rel = "stylesheet" href="css/style.css">
    </head>
    <body>
    <header>
    <?php require('header.php'); ?>
    </header>
        <main>
            <?php foreach($posts as $post): ?>
            <div class="msg">
                <!-- アイコン画像 -->
                <div class="mem_pic">
                    <img src="member_picture/<?php echo h($post['member_picture'],ENT_QUOTES); ?>" width="48" height="48" alt="<?php echo h($post['name'],ENT_QUOTES); ?>" />
                </div>
                <!-- 名前 -->
                <span class="name">(<?php echo h($post['name'],ENT_QUOTES); ?>)</span>
                <!-- メッセージ内容 -->
                <p class = "content">
                    <?php echo makeLink(nl2br(h($post['message']))); ?>
                </p>
                <!-- 投稿写真 -->
                <div class="post_pic">
                    <?php if ($post['post_picture']): ?>
                    <img src="post/post_picture/<?php echo h($post['post_picture'], ENT_QUOTES); ?>" width="300" height="200" alt="<?php echo h($post['post_picture'], ENT_QUOTES); ?>">
                    <?php endif; ?>
                </div>
                <!-- 投稿日時 -->
                <div class="others">
                    <span><a href="view.php?id=<?php echo h($post['id'],ENT_QUOTES); ?>"><?php echo h($post['created'],ENT_QUOTES); ?></a></span>
                    <!-- 返信元のメッセージリンク -->
                    <?php if($post['reply_post_id']>0): ?>
                    <span><a href="view.php?id=<?php echo h($post['reply_post_id'],ENT_QUOTES); ?>">返信元のメッセージ</a></span>
                    <?php endif; ?>
                    <!-- ログインしていたら削除ボタンを表示 -->
                    <?php if($login && $_SESSION['id']==$post['member_id']) : ?>
                       <span> [<a href="delete.php?id=<?php echo h($post['id'],ENT_QUOTES); ?>" style="color:#F33;">削除</a>]</span>
                    <?php endif; ?>
                     <!-- ログインしていたら返信ボタンを表示 -->
                    <?php if($login) {
                            echo "<span>[<a href=\"post/index.php?res=" . h($post['id'],ENT_QUOTES) . "\">Re</a>]</span>";
                        } 
                    ?>
                    <!-- Goodボタン -->
                    <?php if($login) : ?>
                    <div class="good">
                    <button class="good-button" data-post-id="<?php echo h($post['id']); ?>">
                        <img src="<?php echo isset($goodStatus[$post['id']]) ? 'img/good_on.png' : 'img/good_off.png'; ?>" alt="Good">
                        <span class="good-count"><?php echo h($post['good_count']); ?></span>
                    </button>
                    </div>
                    <?php endif; ?>
                </div>      
            </div>
            <hr>
            <?php
            endforeach;
            ?>
        <ul class="paging">
            <?php
            if ($page >1){
            ?>
            <li><a href="index.php?page=<?php print($page - 1); ?>">前のページへ</a></li>
            <?php
            }else{
            ?>
            <li>前のページへ</li>
            <?php
            }
            if($page < $maxPage){
            ?>
            <li><a href="index.php?page=<?php print($page + 1); ?>">次のページへ</a></li>    
            <?php
            }else{
            ?>
            <li>次のページへ</li>
            <?php
            }
            ?>
        </ul>
        </main>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.good-button').on('click', function() {
        var button = $(this);
        var postId = button.data('post-id');

        $.ajax({
            type: 'POST',
            url: 'good.php',
            data: { post_id: postId },
            success: function(response) {
                // ボタンの状態を更新
                // 例: クリックされた時にGoodの状態を切り替える
                if (button.find('img').attr('src') === 'img/good_on.png') {
                    button.find('img').attr('src', 'img/good_off.png');
                    // カウントを減らす
                    var count = parseInt(button.find('.good-count').text());
                    button.find('.good-count').text(count - 1);
                } else {
                    button.find('img').attr('src', 'img/good_on.png');
                    // カウントを増やす
                    var count = parseInt(button.find('.good-count').text());
                    button.find('.good-count').text(count + 1);
                }
            },
            error: function() {
                alert('Error occurred while updating Good status.');
            }
        });
    });
});
</script>
    </body>
</html>
