<?php
// login.phpのパスを設定
$login_path = 'login.php';

// 同一フォルダ内にlogin.phpが存在するか確認
if (!file_exists($login_path)) {
    // 存在しない場合は一つ上の階層のURLを設定
    $login_path = '../login.php';
}

// logout.phpのパスを設定
$logout_path = 'logout.php';

// 同一フォルダ内にlogout.phpが存在するか確認
if (!file_exists($logout_path)) {
    // 存在しない場合は一つ上の階層のURLを設定
    $logout_path = '../logout.php';
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

// 現在のURLのパスを取得
$current_path = $_SERVER['SCRIPT_NAME'];

// 現在のファイルがpost or joinフォルダ内か確認
if (strpos($current_path, '/post/') !== false || strpos($current_path, '/join/') !== false) {
    // フォルダ内の場合
    $index_link = '../index.php';
} else {
    $index_link = './index.php';
}

// mypage.phpのパスを設定
$mypage_path = 'mypage.php';

// 同一フォルダ内にmypage.phpが存在するか確認
if (!file_exists($mypage_path)) {
    // 存在しない場合は一つ上の階層のURLを設定
    $mypage_path = '../mypage.php';
}

// ログイン判定
if(isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
   $login = true;
} else {
    $login = false;
}
?>
<h1>ひとこと掲示板</h1>
<nav>
    <ul>
        <a href="<?php echo $index_link; ?>"><li>TOP</li></a>
        <a href="<?php echo $post_index_link; ?>"><li>投稿</li></a>
        <?php 
        if ($login) {
        echo '<a href="' . $logout_path . '"><li>ログアウト</li></a>';
        } else {
            echo '<a href="' . $login_path . '"><li>ログイン</li></a>';
        }
        ?>
        <a href="<?php echo $mypage_path; ?>"><li>マイページ</li></a>
    </ul>
</nav>