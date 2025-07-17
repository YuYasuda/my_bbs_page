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
}else{
//ログインしていない場合は閲覧のみ
unset($_SESSION['id']);
}
// //投稿を記録する
// if (!empty($_POST)) {
//     if (!empty($_POST['message'])) {
//         $replyPostId = isset($_POST['reply_post_id']) ? $_POST['reply_post_id'] : null;

//         // 投稿をデータベースに記録する
//         $message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, reply_post_id=?, created=NOW()');
//         $message->execute(array(
//             $member['id'],
//             $_POST['message'],
//             $replyPostId 
//         ));

//         // 投稿後にリダイレクト
//         header('Location: index.php');
//         exit();
//     }
// }

//投稿を取得する
$posts=$db->query('SELECT m.name, m.picture, p.*FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC');
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

$posts=$db->prepare('SELECT m.name, m.picture, p.*FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT ?,5');
$posts->bindParam(1,$start, PDO::PARAM_INT);
$posts->execute();


//返信の場合
if(isset($_REQUEST['res'])){
    $response=$db->prepare('SELECT m.name, m.picture, p.*FROM members m,posts p WHERE m.id=p.member_id AND p.id=? ORDER BY p.created DESC');
    $response->execute(array($_REQUEST['res']));
    $table=$response->fetch();
    $message="@".$table['name'].' '.$table['message'];
    $_SESSION['response'] = $message;
    header('Location: ./post/index.php'); exit();
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
        <main>
            <div style="text-align: right"><a href="logout.php">ログアウト</a></div>
            <?php if(isset($_SESSION['id'])): ?>
            <?php else: ?>
            <p>投稿するには、<a href="login.php">ログイン</a>が必要です。</p>
            <?php endif; ?>

            <?php //投稿の画像を取得する
            $posts = $db->query('SELECT m.name, m.picture AS member_picture, p.picture AS post_picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC'); ?>

            <?php foreach($posts as $post): ?>
            <div class="msg">
                <!-- アイコン画像 -->
                <img src="member_picture/<?php echo h($post['member_picture'],ENT_QUOTES); ?>" width="48" height="48" alt="<?php echo h($post['name'],ENT_QUOTES); ?>" />
                <!-- 名前 -->
                <span class="name">(<?php echo h($post['name'],ENT_QUOTES); ?>)</span><br>
                <!-- メッセージ内容 -->
                <p><?php echo makeLink(h($post['message'])); ?></p><br>
                <!-- 投稿写真 -->
                <?php if ($post['post_picture']): ?>
                    <img src="post/post_picture/<?php echo h($post['post_picture'], ENT_QUOTES); ?>" width="300" height="200" alt="<?php echo h($post['post_picture'], ENT_QUOTES); ?>">
                <?php endif; ?><br>
                <!-- 投稿日時 -->
                <p class="day">
                    <a href="view.php?id=<?php echo h($post['id'],ENT_QUOTES); ?>"><?php echo h($post['created'],ENT_QUOTES); ?></a>
                <!-- 返信元のメッセージリンク     -->
                    <?php if($post['reply_post_id']>0): ?>
                    <a href="view.php?id=<?php echo h($post['reply_post_id'],ENT_QUOTES); ?>">返信元のメッセージ</a>
                    <?php endif; ?>
                <!-- 返信ボタン -->
                <?php if(isset($_SESSION['id'])): ?>
                    [<a href="index.php?res=<?php echo h($post['id'],ENT_QUOTES);?>">Re</a>]
                <?php endif; ?>
                </p>
            </div>
            <?php endforeach; ?>

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
    </body>
</html>
