<?php require('../dbconnect.php'); ?>
<?php 
session_start();
$error = [];

if (!empty($_POST)) {
    // エラーの項目確認
    if ($_POST['name'] == '') {
        $error['name'] = 'blank';
    }
    if ($_POST['email'] == '') {
        $error['email'] = 'blank';
    }
    if (strlen($_POST['password']) < 4) {
        $error['password'] = 'length';
    }
    if ($_POST['password'] == '') {
        $error['password'] = 'blank';
    }
    $file_name = $_FILES['image']['name'];
    if (!empty($file_name)) {
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)); // 拡張子を小文字で取得
        if ($ext != 'jpg' && $ext != 'gif' && $ext != 'png') {
            $error['image'] = 'type';
        }
    }

    // 重複アカウントのチェック
    if (empty($error)) {
        $member = $db->prepare('SELECT COUNT(*) AS cnt FROM members WHERE email=?');
        $member->execute(array($_POST['email']));
        $record = $member->fetch();
        if ($record['cnt'] > 0) {
            $error['email'] = 'duplicate';
        }
    }

    if (empty($error)) {
        // 画像をアップロードする
        if (!empty($file_name)) {
            $image = date('YmdHis') . $_FILES['image']['name'];
            move_uploaded_file($_FILES['image']['tmp_name'], '../member_picture/' . $image);
        } else {
            // 画像がアップロードされなかった場合はデフォルト画像を設定
            $image = 'default.png';
        }
        $_SESSION['join'] = $_POST;
        $_SESSION['join']['image'] = $image;
        header('Location: check.php');
        exit();
    }
}

// 書き直し
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'rewrite') {
    $_POST = $_SESSION['join'];
    $error['rewrite'] = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>会員登録</title>
</head>
<body>
    <header>
    <?php require('../header.php'); ?>
    </header>
    <h2>会員登録</h2>
    <main>
    <div class="signup">
    <p>次のフォームに必要事項をご記入ください。</p>
    <form action="" method="post" enctype="multipart/form-data">
        <dl>
            <dt>ニックネーム<span class="required">必須</span></dt>
            <dd>
                <input type="text" name="name" size="35" maxlength="255" value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES); ?>">
                <?php if (isset($error['name']) && $error['name'] === 'blank'): ?>
                <p class="error">* ニックネームを入力してください</p>
                <?php endif; ?>
            </dd>
            <dt>メールアドレス<span class="required">必須</span></dt>
            <dd>
                <input type="text" name="email" size="35" maxlength="255"  value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>">
                <?php if (isset($error['email']) && $error['email'] === 'blank'): ?>
                <p class="error">* メールアドレスを入力してください</p>
                <?php endif; ?>
                <?php if (isset($error['email']) && $error['email'] == 'duplicate'):  ?>
                <p class="error">* 指定されたメールアドレスはすでに登録されています</p>
                <?php endif ?>
            </dd>
            <dt>パスワード<span class="required">必須</span></dt>
            <dd>
                <input type="password" name="password" size="10" maxlength="20" value="<?php echo htmlspecialchars($_POST['password'] ?? '', ENT_QUOTES); ?>">
                <?php if (isset($error['password'])): ?>
                    <?php if ($error['password'] == 'blank'): ?>
                    <p class="error">* パスワードを入力してください</p>
                    <?php endif; ?>
                    <?php if ($error['password'] == 'length'): ?>
                    <p class="error">* パスワードは4文字以上で入力してください</p>
                    <?php endif; ?>
                <?php endif; ?>
            </dd>
            <dt>写真など</dt>
            <dd>
                <input type="file" name="image" size="35">
                <?php if (isset($error['image']) && $error['image'] === 'type'): ?>
                <p class="error">* 恐れ入りますが、画像を改めて指定してください</p>
                <?php endif; ?>
            </dd>
        </dl>
        <div><input type="submit" value="入力内容を確認する" class="button"></div>
    </form>
    </div>
    </main>
</body>
</html>