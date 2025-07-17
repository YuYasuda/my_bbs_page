<?php
    session_start();
    require('dbconnect.php');

    //htmlspecialcharsのショートカット
    function h($value){
        return htmlspecialchars($value,ENT_QUOTES);
    }

    //本文内のURLにリンクを設定します
    function makeLink($value) {
        // URLをリンク化
        $value = mb_ereg_replace(
            "(https?)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)",
            '<a href="\1\2" target="_blank" rel="noopener noreferrer">\1\2</a>',
            $value
        );
        
        // // @名前をリンク化（ユーザーのページに飛ぶリンクを生成
        // $value = preg_replace(
        //     '/@([a-zA-Z0-9_]+)/u', // 英数字やアンダースコアを名前と見なす
        //     '<a href="user.php?name=$1">@$1</a>',
        //     $value
        // );

        return $value;
    }

    //選択されてなかったらindexに戻る
    if(empty($_REQUEST['id'])){
        header('Location:index.php');exit();
    }

    // 投稿を取得する
$posts = $db->prepare('
    SELECT m.name,
    m.picture AS member_picture,
    p.picture AS post_picture,
    p.*,
    (SELECT COUNT(*) FROM goods g WHERE g.post_id = p.id) AS good_count
    FROM members m, posts p 
    WHERE m.id = p.member_id 
    AND p.id = ?
');
    // ここでIDが整数であることを確認する
    $postId = (int)$_REQUEST['id'];
    $posts->execute(array($postId));

    // 結果を取得
    $post = $posts->fetch(PDO::FETCH_ASSOC);




if (isset($_REQUEST['res']) && is_numeric($_REQUEST['res'])) {
    $res = $_REQUEST['res'];
    $replyId = (int)$res;

    // $replysの初期化が必要
    $replys = $db->prepare('
        SELECT m.name,
        m.picture AS member_picture,
        p.picture AS post_picture,
        p.*,
        (SELECT COUNT(*) FROM goods g WHERE g.post_id = p.id) AS good_count
        FROM members m, posts p 
        WHERE m.id = p.member_id 
        AND p.reply_post_id = ?
    ');
    $replys->execute(array($replyId));

    // 結果を取得
    $reply = $replys->fetch(PDO::FETCH_ASSOC);
}

        
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/style.css">
    <title>ひとこと掲示板</title>
</head>

<body>
<header>
    <?php require('header.php'); ?>
</header>

<main>
    <p>&laquo;<a href="index.php">TOPにもどる</a></p>

    <?php if ($post) : ?>

        <div class="msg">

            <!-- アイコン画像 -->
            <div class="mem_pic">
                <img src="member_picture/<?php echo h($post['member_picture']); ?>" width="48" height="48" alt="<?php echo h($post['name'],ENT_QUOTES); ?>" />
            </div>

            <!-- 名前 -->
            <span class="name">(<?php echo h($post['name']); ?>)</span>

            <!-- メッセージ内容 -->
            <p class = "content">
                <?php echo makeLink(nl2br(h($post['message']))); ?>
            </p>

            <!-- 投稿写真 -->
            <div class="post_pic">
                <?php if ($post['post_picture']): ?>
                <img src="post/post_picture/<?php echo h($post['post_picture']); ?>" width="300" height="200" alt="<?php echo h($post['post_picture'], ENT_QUOTES); ?>">
                <?php endif; ?>
            </div>

            <!-- 投稿日時 -->
            <div class="others">
                <a href="view.php?id=<?php echo h($post['id']); ?>"><?php echo h($post['created']); ?></a>
                <!-- 返信元のメッセージリンク -->
                <?php if($post['reply_post_id']>0): ?>
                <a href="view.php?id=<?php echo h($post['reply_post_id']); ?>">返信元のメッセージ</a>
                <?php endif; ?>
                <!-- ログインしていたら削除ボタンを表示 -->
                <?php if($login && $_SESSION['id']==$post['member_id']) : ?>
                    [<a href="delete.php?id=<?php echo h($post['id']); ?>" style="color:#F33;">削除</a>]
                <?php endif; ?>
                    <!-- ログインしていたら返信ボタンを表示 -->
                <?php if($login) {
                        echo "[<a href=\"post/index.php?res=" . h($post['id']) . "\">Re</a>]";
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
        
    <?php endif; ?>

    <?php $reply = null; ?>
    
    <?php if ($reply): ?>

<div class="rep_msg">
    <div class="rep_col"></div>

    <!-- アイコン画像 -->
    <div class="mem_pic">
        <img src="member_picture/<?php echo h($reply['member_picture']); ?>" width="48" height="48" alt="<?php echo h($reply['name'], ENT_QUOTES); ?>" />
    </div>

    <!-- 名前 -->
    <span class="name">(<?php echo h($reply['name']); ?>)</span>

    <!-- メッセージ内容 -->
    <p class="content">
        <?php echo makeLink(h($reply['message'])); ?>
    </p>

    <!-- 投稿写真 -->
    <div class="post_pic">
        <?php if ($reply['post_picture']): ?>
            <img src="post/post_picture/<?php echo h($reply['post_picture']); ?>" width="300" height="200" alt="<?php echo h($reply['post_picture'], ENT_QUOTES); ?>">
        <?php endif; ?>
    </div>

    <!-- 投稿日時 -->
    <div class="others">
        <a href="view.php?id=<?php echo h($reply['id']); ?>"><?php echo h($reply['created']); ?></a>
        <!-- 返信元のメッセージリンク -->
        <?php if ($reply['reply_post_id'] > 0): ?>
            <a href="view.php?id=<?php echo h($reply['reply_post_id']); ?>">返信元のメッセージ</a>
        <?php endif; ?>
        <!-- ログインしていたら削除ボタンを表示 -->
        <?php if ($login && $_SESSION['id'] == $reply['member_id']) : ?>
            [<a href="delete.php?id=<?php echo h($reply['id']); ?>" style="color:#F33;">削除</a>]
        <?php endif; ?>
        <!-- ログインしていたら返信ボタンを表示 -->
        <?php if ($login) {
            echo "[<a href=\"index.php?res=" . h($reply['id']) . "\">Re</a>]";
        } ?>
        <!-- Goodボタン -->
        <?php if ($login) : ?>
            <div class="good">
                <button class="good-button" data-post-id="<?php echo h($reply['id']); ?>">
                    <img src="<?php echo isset($goodStatus[$reply['id']]) ? 'img/good_on.png' : 'img/good_off.png'; ?>" alt="Good">
                    <span class="good-count"><?php echo h($reply['good_count']); ?></span>
                </button>
            </div>
        <?php endif; ?>
    </div>           
</div>
<?php endif; ?>
</main>
</body>
</html>
    