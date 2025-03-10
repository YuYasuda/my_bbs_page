<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
    <title>ログアウト</title>
</head>
<body>
    <?php
    session_start();

    //セッションの情報を削除
    $_SESSION=array();
    if(ini_get("session.use_cookies")){
        $params=session_get_cookie_params();
        setcookie(session_name(),'',time()-42000,
        $params["path"],$params["domain"],
        $params["secure"],$params["httponly"]
        );
    }
    session_destroy();

    //Cookie情報も削除
    setcookie('email','',time()-3600);
    setcookie('password','',time()-3600);

    header('Location: index.php');
    exit();
    ?>

</body>
</html>