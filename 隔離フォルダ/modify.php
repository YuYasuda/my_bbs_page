<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0", >
    <link rel="stylesheet" href="css/style.css">
    <title>投稿の編集</title>
    <script>
        // アップロードする画像を表示するJavaScript
        function previewImage(input) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    // 画像タグのソースを変更する
                    document.getElementById('preview').src = e.target.result;
                };
                reader.readAsDataURL(file); // 画像読み込み
            }
        }
    </script>
</head>
<body>
    <header>
        <?php
            require('dbconnect.php');
            require('header.html');
            $error = [];
            
        ?>
        <h1>投稿を編集できます</h1>
    </header>

    <main>
<?php
require('dbconnect.php');
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: login.php'); 
    exit();
}

// 投稿IDを取得
if (isset($_GET['id'])) {
    $postId = $_GET['id'];

    // 投稿データを取得
    $post = $db->prepare('SELECT * FROM posts WHERE id = ?');
    $post->execute(array($postId));
    $postData = $post->fetch();

    // 投稿が存在しない場合はエラーページにリダイレクト
    if (!$postData) {
        header('Location: error.php'); 
        exit();
    }

    // 投稿者のチェック
    if ($postData['member_id'] != $_SESSION['id']) {
        header('Location: error.php'); 
        exit();
    }
} else {
    header('Location: index.php'); 
    exit();
}

// 画像変更処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'];
    $picture = $_FILES['picture'];

    // 画像がアップロードされた場合
    if ($picture['name']) {
        // 画像のアップロード処理（ここでは簡略化）
        $uploadPath = 'post/post_picture/';
        $fileExtension = pathinfo($picture['name'], PATHINFO_EXTENSION);    // ファイルの拡張子を取ってくる
        $fileName = date('YmdHis') . basename($picture['name']);    // 日時をファイル名の頭に追加
        
        // ファイルの拡張子が安全か確認する
        if (!in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif'])) {
            // 不正な拡張子はエラーにする
            $error['picture'] = '画像ファイルはjpg, jpeg, png, gifのいずれかでお願いします。';
        } else {
            move_uploaded_file($picture['tmp_name'], $uploadPath . $fileName);
        }
    } else {
        $fileName = $postData['picture']; // 既存の画像を保持
    }

    // データベースを更新
    $update = $db->prepare('UPDATE posts SET message = ?, picture = ? WHERE id = ?');
    $update->execute(array($message, $fileName, $postId));

    // リダイレクト
    header('Location: mypage.php');
    exit();
}
?>

    <form action="" method="post" enctype="multipart/form-data">
        <div>
            <label for="message">メッセージ:</label><br>
            <textarea name="message" id="message" required><?php echo htmlspecialchars($postData['message'], ENT_QUOTES); ?></textarea>
        </div>
        <div>
            <label for="picture">画像を変更:</label><br>
            <input type="file" name="picture" id="picture" onchange = "previewImage(this)">
            <?php if ($postData['picture']): ?>
                <img id = "preview" src="post/post_picture/<?php echo htmlspecialchars($postData['picture'], ENT_QUOTES); ?>" alt="現在の画像" width="100px">
            <?php endif; ?>
        </div>
        <div>
            <input type="submit" value="更新">
        </div>
    </form>
    </main>
</body>
</html>
