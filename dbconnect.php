<<<<<<< HEAD
<?php
    try {
        $db = new PDO('mysql:dbname=mini_bbs; host=127.0.0.1;charset=utf8', 'bbs_user', 'joZ1sPow1triB+ocr6di'); //データソース名、ユーザー名、パスワードの順に書く
=======
<?php 
try {
        $db = new PDO('mysql:dbname=mini_bbs;host=127.0.0.1;charset=utf8', 'root','');
>>>>>>> e10f27e20df9ddc13f771ba39afad4e985f9a9aa
    } catch (PDOException $e) {
        echo 'DB接続エラー: ' . $e->getMessage();
    }
    ?>
