<?php
// login.phpのパスを設定
$loginPath = 'login.php';

// 同一フォルダ内にlogin.phpが存在するか確認
if (!file_exists($loginPath)) {
    // 存在しない場合は一つ上の階層のURLを設定
    $loginPath = '../login.php';
}
// login.phpのパスを設定
$loginPath = './post/index.php';

// 同一フォルダ内にlogin.phpが存在するか確認
if (!file_exists($loginPath)) {
    // 存在しない場合は一つ上の階層のURLを設定
    $loginPath = '../login.php';
}

// logout.phpのパスを設定
$logoutPath = 'logout.php';

// 同一フォルダ内にlogin.phpが存在するか確認
if (!file_exists($logoutPath)) {
    // 存在しない場合は一つ上の階層のURLを設定
    $logoutPath = '../logout.php';
}
// logout.phpのパスを設定
$logoutPath = './post/logout.php';

// 同一フォルダ内にlogin.phpが存在するか確認
if (!file_exists($logoutPath)) {
    // 存在しない場合は一つ上の階層のURLを設定
    $logoutPath = '../logout.php';
}

// 初期のリンクパスを設定
$post_index_link = '';

// 現在のファイルがpostフォルダ内か確認
if (strpos(__FILE__, '/post/') !== false) {
    // postフォルダ内の場合
    $post_index_link = 'index.php';
} elseif (is_dir('./post')) {
    // 同一フォルダにpostフォルダがある場合
    $post_index_link = './post/index.php';
} else {
    // どちらにも該当しない場合は../post/index.php
    $post_index_link = '../post/index.php';
}

// 初期のリンクパスを設定
$index_link = '';

// 現在のファイルがpost or joinフォルダ内か確認
if (strpos(__FILE__, '/post/') !== false || strpos(__FILE__, '/join/') !== false) {
    // フォルダ内の場合
    $index_link = '../index.php';
} else {
    $index_link = './index.php';
}
?>
<h1>ひとこと掲示板</h1>
<nav>
    <ul>
        <a href="<?php echo $index_link; ?>"><li>TOP</li></a>
        <a href="<?php echo $post_index_link; ?>"><li>投稿</li></a>
        <a href="<?php echo $loginPath; ?>"><li>ログイン</li></a>
        <a href="<?php echo $logoutPath; ?>"><li>ログアウト</li></a>
        <a href="#"><li>マイページ</li></a>
    </ul>
</nav>