<?php
<<<<<<< HEAD
session_start();
if (!isset($_SESSION['post'])) {
    $_SESSION['post'] = [];
}
require ('dbconnect.php');
$error = [];

if(isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
=======
    session_start();
    require ('../dbconnect.php');
    
    $_SESSION['post_message'] = [];
    $_SESSION['post_file'] = [];
    $error = [];

    //htmlspecialcharsのショートカット
    function h($value){
        return htmlspecialchars($value,ENT_QUOTES);
    }

    if (!empty($_POST)) {// エラーの項目確認
        //投稿画像の拡張子のエラー診断
        $file_name = $_FILES['image']['name'];
        if (!empty($file_name)) {
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)); // 拡張子を小文字で取得
            if ($ext != 'jpg' && $ext != 'gif' && $ext != 'png') {
                //拡張子が上記以外ならエラー変数のimage配列にtypeを入れる
                $error['image'] = 'type';
            }
        }
    }

    if(isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
>>>>>>> d3ef077 (Upload latest project files from プロジェクトD)
    //ログインしている
    $_SESSION['time'] = time();

    $members = $db->prepare('SELECT * FROM members WHERE id=?');
    $members->execute(array($_SESSION['id']));
    $member = $members->fetch();
<<<<<<< HEAD
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

=======
    } else {
    //ログインしてない
    header('Location: ../login.php'); exit();
    }

    //返信の場合
    $message = '';
    if(isset($_REQUEST['res'])){
    $response=$db->prepare('SELECT m.name, m.picture, p.*FROM members m,posts p WHERE m.id=p.member_id AND p.id=?');
    $response->execute(array($_REQUEST['res']));
    $table=$response->fetch();
    $message="@".$table['name'].' '.$table['message']."\n";
    }



    $fileName = isset($_FILES['image']['name']) ? $_FILES['image']['name'] : '';

    //投稿を記録する
    if (!empty($_POST)) {//投稿ボタンがされたとき

        if ($_POST['message'] != '' || !empty($_FILES['image']['name'])) {//メッセージもしくは画像が投稿されたとき

            if ($_POST['message'] != '' && !empty($_FILES['image']['name'])) {//メッセージと画像の投稿のとき

                if (empty($error)) {//拡張子にエラーがなかったら
                    if (isset($_REQUEST['res'])) {
                        //画像をアップロードする
                    $image = date('YmdHis'). $fileName;
                    move_uploaded_file($_FILES['image']['tmp_name'], './post_picture/'. $image);
                    $_SESSION['post_file'] = $_FILES;
                    $_SESSION['post_file']['image'] = $image;
                
                    //データベースに保存
                    $message = $db->prepare('INSERT INTO posts set member_id=?, message=?, reply_post_id=?, picture=?, created=NOW()');
                    $message->execute(array(
                        $member['id'],
                        $_POST['message'],//メッセージの保存
                        $_POST['reply_post_id'],
                        $_SESSION['post_file']['image']
                    ));
                    $_SESSION['response'] = '';
                    header('Location: ../index.php'); exit();
                    } else {
                    //画像をアップロードする
                    $image = date('YmdHis'). $fileName;
                    move_uploaded_file($_FILES['image']['tmp_name'], './post_picture/'. $image);
                    $_SESSION['post_file'] = $_FILES;
                    $_SESSION['post_file']['image'] = $image;
                
                    //データベースに保存
                    $message = $db->prepare('INSERT INTO posts set member_id=?, message=?, picture=?, created=NOW()');
                    $message->execute(array(
                        $member['id'],
                        $_POST['message'],//メッセージの保存
                        $_SESSION['post_file']['image']
                    ));
                    $_SESSION['response'] = '';
                    header('Location: ../index.php'); exit();
                }
                }
            }

            if ($_POST['message'] != '' && empty($_FILES['image']['name'])) {//メッセージのみの投稿のとき
                //データベースに保存
                if (isset($_REQUEST['res'])) {
                $message = $db->prepare('INSERT INTO posts set member_id=?, message=?, reply_post_id=?, picture=null, created=NOW()');
                $message->execute(array(
                    $member['id'],
                    $_POST['message'],
                    $_POST['reply_post_id'],
                ));
            } else {
                $message = $db->prepare('INSERT INTO posts set member_id=?, message=?, picture=null, created=NOW()');
                $message->execute(array(
                    $member['id'],
                    $_POST['message'],
                ));
            }
                $_SESSION['response'] = '';
                header('Location: ../index.php'); exit();
            }

            if (empty($_POST['message']) && !empty($_FILES['image']['name'])) {//画像のみの投稿のとき

                if (empty($error)) {
                    //画像をアップロードする
                    $image = date('YmdHis'). $fileName;
                    move_uploaded_file($_FILES['image']['tmp_name'], './post_picture/'. $image);
                    $_SESSION['post_file'] = $_FILES;
                    $_SESSION['post_file']['image'] = $image;

                    //データベースに保存
                    $message = $db->prepare('INSERT INTO posts set member_id=?, message=null, picture=?, created=NOW()');
                    $message->execute(array(
                        $member['id'],
                        $_SESSION['post_file']['image']
                    ));
                    $_SESSION['response'] = '';
                    header('Location: ../index.php'); exit();
                }
            }
        }
    }

>>>>>>> d3ef077 (Upload latest project files from プロジェクトD)
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>投稿</title>
<<<<<<< HEAD
</head>
<body>
    <form action="" method="post" enctype="multipart/form-data">
        <dl>
            <dt><?php echo htmlspecialchars($member['name'], ENT_QUOTES); ?>さん、メッセージをどうぞ</dt>
            <dd>
                <textarea name="message" cols="50" rows="5"></textarea>
=======
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <?php require('../header.php'); ?>
    </header>
    <h2>投稿</h2>
    <main>
    <form action="" method="post" enctype="multipart/form-data">
        <dl>
            <dt><?php echo h($member['name']); ?>さん、メッセージをどうぞ</dt>
            <dd>
                <textarea name="message" cols="50" rows="5"><?php if ($message) { echo h($message);} ?><?php echo h($_POST['message'] ?? ''); ?></textarea>
                <?php if(isset($_REQUEST['res'])) : ?>
                <input type="hidden" name="reply_post_id" value="<?php echo h($_REQUEST['res'], ENT_QUOTES); ?>">
                <?php endif ?>
>>>>>>> d3ef077 (Upload latest project files from プロジェクトD)
            </dd>
            <dt>写真の選択</dt>
            <dd>
                <input type="file" name="image" size="35">
<<<<<<< HEAD
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
=======
                <!-- 拡張子のエラーがあった場合 -->
                <?php if (isset($error['image']) && $error['image'] === 'type'): ?>
                <p class="error">* 無効なファイルタイプです。JPGまたはGIFまたはPNGの画像ファイルを選択してください</p>
                <?php endif; ?>
            </dd> 
        </dl>
        <div>
            <input type="submit" value="投稿する" class="button">
        </div>
    </form>
    </main>
>>>>>>> d3ef077 (Upload latest project files from プロジェクトD)
</body>
</html>