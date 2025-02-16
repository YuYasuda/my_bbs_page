<?php
session_start();
if (!isset($_SESSION['post'])) {
    $_SESSION['post'] = [];
}
require ('dbconnect.php');
$error = [];

if(isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
    //ログインしている
    $_SESSION['time'] = time();

    $members = $db->prepare('SELECT * FROM members WHERE id=?');
    $members->execute(array($_SESSION['id']));
    $member = $members->fetch();
} else {
    //ログインしてない
    header('Location: ../login.php'); exit();
}

//投稿を記録する
if (!empty($_POST)) {

    //画像のチェック機能
    $fileName = $_FILES['image']['name'];
    if (!empty($fileName)) {
        $ext = substr($fileName, -3);
        if ($ext != 'jpg' && $ext != 'gif') {
            $error['image'] = '無効なファイルタイプです。JPGまたはGIFの画像をアップロードしてください。';
        }
    }

    if (empty($error)) {
        //画像をアップロードする
        $image = date('YmdHis'). $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], 'post_picture/'. $image);
        $_SESSION['post']['image'] = $image;
    }


    if ($_POST['message'] != '' || $_FILES['image'] != '') {
        $message = $db->prepare('INSERT INTO posts set member_id=?, message=?, picture=?, created=NOW()');
        $message->execute(array(
            $member['id'],
            $_POST['message'],
            $_SESSION['post']['image']
        ));

        header('Location: index.php'); exit();
    }
}

//投稿を取得する
$posts = $db->query('SELECT m.name, m.picture AS member_picture, p.picture AS post_picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC');

if ($posts->rowCount() == 0) {
    echo '投稿がありません。';
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>投稿</title>
</head>
<body>
    <form action="" method="post" enctype="multipart/form-data">
        <dl>
            <dt><?php echo htmlspecialchars($member['name'], ENT_QUOTES); ?>さん、メッセージをどうぞ</dt>
            <dd>
                <textarea name="message" cols="50" rows="5"></textarea>
            </dd>
            <dt>写真の選択</dt>
            <dd>
                <input type="file" name="image" size="35">
            </dd> 
        </dl>
        <div>
            <input type="submit" value="投稿する">
        </div>
    </form>

<?php
foreach ($posts as $post):
?>
    <div class="msg">
        <img src="member_picture/<?php echo htmlspecialchars($post['member_picture'], ENT_QUOTES); ?>" width="48" height="48" alt="<?php echo htmlspecialchars($post['name'], ENT_QUOTES); ?>">
        <img src="post_picture/<?php echo htmlspecialchars($post['post_picture'], ENT_QUOTES); ?>" width="300" height="200" alt="<?php echo htmlspecialchars($post['post_picture'], ENT_QUOTES); ?>">

        <p><?php echo htmlspecialchars($post['message'], ENT_QUOTES); ?><span class="name">(<?php echo htmlspecialchars($post['name'], ENT_QUOTES); ?>)</span></p>
        <p class="day"><?php echo htmlspecialchars($post['created'], ENT_QUOTES); ?></p>
    </div>
<?php
endforeach;
?>
</body>
</html>