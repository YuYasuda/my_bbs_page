<?php
    session_start();
    require ('../dbconnect.php');
    
    $_SESSION['post_message'] = [];
    $_SESSION['post_file'] = [];
    $error = [];

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
    //ログインしている
    $_SESSION['time'] = time();

    $members = $db->prepare('SELECT * FROM members WHERE id=?');
    $members->execute(array($_SESSION['id']));
    $member = $members->fetch();
    } else {
    //ログインしてない
    header('Location: ../login.php'); exit();
    }

    // 返信先の"@名前"と"返信するメッセージ" をセッションから$response_messageに代入
    if (isset($_SESSION['response'])) {
        $response_message = $_SESSION['response'];
        unset($_SESSION['response']); //代入後はセッションをクリア
    } else {
        $response_message = '';//セッションに何もなかったら空欄にする
    }

    $fileName = isset($_FILES['image']['name']) ? $_FILES['image']['name'] : '';

    //投稿を記録する
    if (!empty($_POST)) {//投稿ボタンがされたとき

        if ($_POST['message'] != '' || !empty($_FILES['image']['name'])) {//メッセージもしくは画像が投稿されたとき

            if ($_POST['message'] != '' && !empty($_FILES['image']['name'])) {//メッセージと画像の投稿のとき

                if (empty($error)) {//拡張子にエラーがなかったら
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

            if ($_POST['message'] != '' && empty($_FILES['image']['name'])) {//メッセージのみの投稿のとき
                //データベースに保存
                $message = $db->prepare('INSERT INTO posts set member_id=?, message=?, picture=null, created=NOW()');
                $message->execute(array(
                    $member['id'],
                    $_POST['message'],
                ));
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
        <?php require('../header.php'); ?>
    </header>
    <h2>投稿</h2>
    <main>
    <form action="" method="post" enctype="multipart/form-data">
        <dl>
            <dt><?php echo htmlspecialchars($member['name'], ENT_QUOTES); ?>さん、メッセージをどうぞ</dt>
            <dd>
                <textarea name="message" cols="50" rows="5"><?php echo htmlspecialchars($response_message, ENT_QUOTES); ?><?php echo htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES); ?></textarea>
            </dd>
            <dt>写真の選択</dt>
            <dd>
                <input type="file" name="image" size="35">
                <!-- 拡張子のエラーがあった場合 -->
                <?php if (isset($error['image']) && $error['image'] === 'type'): ?>
                <p class="error">* 無効なファイルタイプです。JPGまたはGIFまたはPNGの画像ファイルを選択してください</p>
                <?php endif; ?>
            </dd> 
        </dl>
        <div>
            <input type="submit" value="投稿する">
        </div>
    </form>

    </main>
</body>
</html>