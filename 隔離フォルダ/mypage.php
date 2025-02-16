<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0", shrink-to-fit="no">

     <link rel="stylesheet" href="css/style.css">

    <title>マイページ</title>
            
</head>
<body>
    <header>
        <?php
        require('dbconnect.php');
        require('header.html');
        $error = [];
        ?>
        <h1>マイページ</h1>
    </header>

    <main>
        <h2></h2>
<?php
session_start();

if(isset($_SESSION['id'])&& $_SESSION['time']+3600>time()){
    //ログインしている
    $_SESSION['time']=time();
    
    $members=$db->prepare('SELECT*FROM members WHERE id=?');
    $members->execute(array($_SESSION['id']));
    $member=$members->fetch();
    }else{
    //ログインしていない
    header('Location: login.php'); 
    exit();
    }
    
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

$posts=$db->prepare('SELECT m.name, m.picture, p.*FROM members m, posts p WHERE m.id=p.member_id AND p.member_id = ? ORDER BY p.created DESC LIMIT ?,5');
$posts->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);  //1番目に現在のidに固定して代入する
$posts->bindParam(2, $start, PDO::PARAM_INT);   //2番目に$startを代入する
$posts->execute();

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

<div style="text-align: right"><a href="logout.php">ログアウト</a></div>
<div style="text-align: right"><a href="taikai.php?id=<?php echo htmlspecialchars($_SESSION['id'], ENT_QUOTES, 'UTF-8'); ?>" onclick="return confirm('本当に退会しますか？');">退会する</a>
<?php
    foreach($posts as $post):
    ?>
    <div class="msg">
        <!-- プロフィール画像 -->
        <img src="member_picture/<?php echo h($member['picture'],ENT_QUOTES); ?>" width="48" height="48" alt="<?php echo h($post['name'],ENT_QUOTES); ?>" />
        <?php echo $member['name']. "さん、ようこそ！"; ?>

        <!-- 投稿内容 -->
        <p>
            <?php echo makeLink(h($post['message'])); ?>
            <span class="name">(<?php echo h($post['name'],ENT_QUOTES); ?>)</span>
            [<a href="index.php?res=<?php echo h($post['id'],ENT_QUOTES);?>">Re</a>]
            <!-- 編集ページへ移動 -->
            [<a href="modify.php?id=<?php echo h($post['id'],ENT_QUOTES); ?>">編集</a>]

            <!-- 投稿画像の表示 -->
             <?php if ($post['picture']): ?>
                <img src="post/post_picture/<?php echo h($post['picture'], ENT_QUOTES); ?>" alt = "投稿画像" width="100px" />
                <?php endif; ?>
        </p>
        <p class="day">
            <a href="view.php?id=<?php echo h($post['id'],ENT_QUOTES); ?>"><?php echo h($post['created'],ENT_QUOTES); ?></a>
            <?php
            if($post['reply_post_id']>0):
            ?>
            <a href="view.php?id=<?php echo h($post['reply_post_id'],ENT_QUOTES); ?>">返信元のメッセージ</a>
            <?php
            endif;
            ?>
            <?php
            if($_SESSION['id']==$post['member_id']):
            ?>
            [<a href="delete.php?id=<?php echo h($post['id']); ?>" style="color:F33">削除</a>]    
            <?php
            endif;
            ?>
        </p>    
    </div>
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
</div>

    </main>
</body>
</html>    