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
//投稿の画像を取得する
$posts = $db->query('SELECT m.name, m.picture AS member_picture, p.picture AS post_picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC');

//最終ページを取得する
$counts=$db->query('SELECT COUNT(*) AS cnt FROM posts');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt']/5);
$page = min($page,$maxPage);

$start = ($page-1)*5;

$posts = $db->prepare('
    SELECT m.name, m.picture, p.*, COUNT(g.id) AS good_count
    FROM members m
    JOIN posts p ON m.id = p.member_id
    LEFT JOIN goods g ON p.id = g.post_id
    GROUP BY p.id
    ORDER BY p.created DESC
    LIMIT ?, 5
');
// $posts=$db->prepare('SELECT m.name, m.picture, p.*FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT ?,5');
$posts->bindParam(1,$start, PDO::PARAM_INT);
$posts->execute();
//返信の場合
if(isset($_REQUEST['res'])){
    $response=$db->prepare('SELECT m.name, m.picture, p.*FROM members m,posts p WHERE m.id=p.member_id AND p.id=? ORDER BY p.created DESC');
    $response->execute(array($_REQUEST['res']));
    $table=$response->fetch();
    $message="@".$table['name'].''.$table['message'];
}
//htmlspecialcharsのショートカット
function h($value){
    return htmlspecialchars($value,ENT_QUOTES);
}
//本文内のURLにリンクを設定します
function makeLink($value){
    return mb_ereg_replace("(https?)(://[[:alnum]\+\$\;\?\.%,!#~*/:@&=_-]+)",
    '<a href="\1\2">\1\2</a>',$value);
}

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
            <!-- ログインしていたら投稿ボタン表示 -->
            <?php if($login) {
                echo "<a href=\"post/index.php\"><button>投稿</button></a>";
            } else {
            echo "<p>投稿するには、<a href=\"login.php\">ログイン</a>が必要です。</p>";
            } ?>
            <?php foreach($posts as $post): ?>
            <div class="msg">
                <img src="member_picture/<?php echo h($post['picture'],ENT_QUOTES); ?>" width="48" height="48" alt="<?php echo h($post['name'],ENT_QUOTES); ?>" />
                <p><?php echo makeLink(h($post['message'])); ?>
                    <span class="name">(<?php echo h($post['name'],ENT_QUOTES); ?>)</span>
                    <!-- ログインしていたら返信ボタンとgoodボタン表示 -->
                    <?php if($login) {
                        echo "[<a href=\"index.php?res=" . h($post['id'],ENT_QUOTES) . "\">Re</a>]";
                        echo "<button class=\"good-button\" data-post-id=\"" . h($post['id'], ENT_QUOTES) . "\">Good</button>";
                    } ?>
                    <!-- Good数を表示 -->
                    <span class="good-count"><?php echo h($post['good_count'], ENT_QUOTES); ?></span>
                </p>
                <p class="day">
                    <a href="view.php?id=<?php echo h($post['id'],ENT_QUOTES); ?>"><?php echo h($post['created'],ENT_QUOTES); ?></a>
                    <?php if($post['reply_post_id']>0): ?>
                    <a href="view.php?id=<?php echo h($post['reply_post_id'],ENT_QUOTES); ?>">返信元のメッセージ</a>
                    <?php endif; ?>
                    <!-- ログインしていたら削除ボタン表示 -->
                    <?php if($login) {
                        if($_SESSION['id']==$post['member_id']) {
                            echo "[<a href=\"delete.php?id=" . h($post['id']) . "\" style=\"color:#F33;\">削除</a>]";
                        }
                    } ?>
                </p>            
            </div>
            <?php
            endforeach;
            ?>
        </main>
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
        <script>
document.querySelectorAll('.good-button').forEach(button => {
    button.addEventListener('click', function() {
        const postId = this.dataset.postId;

        fetch('good.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ post_id: postId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Goodしました！');
            } else if (data.status === 'already_got') {
                alert('すでにGoodを押しています。');
            } else if (data.status === 'error') {
                alert('エラー: ' + data.message);
            }
        });
    });
});
</script>
    </body>
</html>
