<?php
    session_start();
    require ('../dbconnect.php');
    $error = [];
    $_SESSION['post_message'] = [];
    $_SESSION['post_file'] = [];


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

    //画像エラーチェック
    $fileName = isset($_FILES['image']['name']) ? $_FILES['image']['name'] : '';
    $error['image'] = '';
    if (!empty($file_name)) {
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)); // 拡張子を小文字で取得
        if ($ext != 'jpg' && $ext != 'gif' && $ext != 'png') {
            $error['image'] = 'type';
        }
    }



    //投稿を記録する
    if (!empty($_POST)) {

        if ($_POST['message'] != '' || $_FILES['image'] != '') {

            if($_POST['message'] != '' && $_FILES['image'] != '') {
                
                // //画像のチェック機能
                // $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                // if ($ext != 'jpg' && $ext != 'gif' && $ext != 'png') {
                //     header('Location: ./error_post_picture.php'); exit(); 
                // }

                //画像をアップロードする
                $image = date('YmdHis'). $fileName;
                move_uploaded_file($_FILES['image']['tmp_name'], './post_picture/'. $image);
                $_SESSION['post_file'] = $_FILES;
                $_SESSION['post_file']['image'] = $image;
                
                //データベースに保存
                $message = $db->prepare('INSERT INTO posts set member_id=?, message=?, picture=?, created=NOW()');
                $message->execute(array(
                    $member['id'],
                    $_POST['message'],
                    $_SESSION['post_file']['image']
                ));
                header('Location: ../index.php'); exit();
            }

            if($_POST['message'] != '' && $_FILES['image'] == '') {
                //データベースに保存
                $message = $db->prepare('INSERT INTO posts set member_id=?, message=?, picture=null, created=NOW()');
                $message->execute(array(
                    $member['id'],
                    $_POST['message'],
                ));
                header('Location: ../index.php'); exit();
            }

            if($_POST['message'] == '' && $_FILES['image'] != '') {
                
                // //画像のチェック機能
                // $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                // if ($ext != 'jpg' && $ext != 'gif' && $ext != 'png') {
                //     header('Location: ./error_post_picture.php'); exit(); 
                // }

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
                header('Location: ../index.php'); exit();
            }
        }
    }

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>投稿</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        ヘッダー
    </header>
    <form action="" method="post" enctype="multipart/form-data">
        <dl>
            <dt><?php echo htmlspecialchars($member['name'], ENT_QUOTES); ?>さん、メッセージをどうぞ</dt>
            <dd>
                <textarea name="message" cols="50" rows="5"><?php echo isset($_SESSION['response']) ? htmlspecialchars($_SESSION['response'], ENT_QUOTES) : ''; ?></textarea>
            </dd>
            <dt>写真のアップロード</dt>
            <dd>
                <input type="file" name="image" size="35">
                <?php if ($error['image'] == 'type'): ?>
                    <p class="error">* 写真は「.jpg」または「.gif」または「.png」の画像を指定してください</p>
                <?php endif; ?>
            </dd> 
        </dl>
        <div>
            <input type="submit" value="投稿する">
        </div>
    </form>


</body>
</html>