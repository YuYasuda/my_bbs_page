<?php
session_start();
require('dbconnect.php');

$response = [];
if (isset($_SESSION['id']) && isset($_POST['post_id'])) {
    $postId = $_POST['post_id'];
    $checkGood = $db->prepare('SELECT * FROM goods WHERE user_id = ? AND post_id = ?');
    $checkGood->execute([$_SESSION['id'], $postId]);
    
    if ($checkGood->rowCount() > 0) {
        // Goodを外す
        $deleteGood = $db->prepare('DELETE FROM goods WHERE user_id = ? AND post_id = ?');
        $deleteGood->execute([$_SESSION['id'], $postId]);
        $response['status'] = 'removed';
    } else {
        // Goodを追加
        $addGood = $db->prepare('INSERT INTO goods (post_id, user_id, created_at) VALUES (?, ?, NOW())');
        $addGood->execute([$postId, $_SESSION['id']]);
        $response['status'] = 'added';
    }
    // Goodのカウントを取得して返すことも可能
    $count = $db->prepare('SELECT COUNT(*) FROM goods WHERE post_id = ?');
    $count->execute([$postId]);
    $response['count'] = $count->fetchColumn();
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
