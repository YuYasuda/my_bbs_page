<?php require('dbconnent.php');?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php 
if(isset($_REQUEST['id]']) && is_numeric($_REQUEST['id'])){
    $id=$_REQUEST['id'];
    $statement=$db->prepare('DELETE FROM members WHERE id=?');
    $statement->execute(array($id));
}
?>
<pre>
<p>会員登録を削除しました。</p>
</pre>
</body>
</html>