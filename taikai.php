<?php require('dbconnect.php');
session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
    <title>退会</title>
</head>
<body>
    <h1>退会<h1>
    <?php    
    //セッションの情報を削除
    $_SESSION=array();
     if(ini_get("session.use_cookies")){
     $params=session_get_cookie_params();
     setcookie(session_name(),'',time()-42000,
     $params["path"],$params["domain"],
     $params["secure"],$params["httponly"]
     );
    }
    
    //Cookie情報も削除
    setcookie('email','',time()-3600);
    setcookie('password','',time()-3600);

    //データベースからメンバーの情報を削除
    if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
    $del = $db->prepare('DELETE FROM members WHERE id = ?');
    $del->execute(array($id));
    }
    ?>
    <pre>
    <p>会員登録を削除しました。</p>
    </pre>
</body>
</html>