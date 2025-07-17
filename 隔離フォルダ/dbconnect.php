<?php
    try {
        $db = new PDO('mysql:dbname=mini_bbs; host=127.0.0.1;charset=utf8', 'root', ''); //データソース名、ユーザー名、パスワードの順に書く
    } catch (PDOException $e) {
        echo 'DB接続エラー: ' . $e->getMessage();
    }
?>