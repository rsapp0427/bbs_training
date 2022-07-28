<?php
//セッションの開始
session_start();

//関数の読み込み
require __DIR__ . '/../lib/function.php';

//ログインチェック
if (isset($_SESSION['id']) && isset($_SESSION['name'])) {
  $id = $_SESSION['id'];
  $name = $_SESSION['name'];
} else {
  header('Location: login.php');

  //プログラムの終了
  exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $post_id = $_GET['id'];

  $db = dbConnect();

  $stmt = $db->prepare('delete from posts where id=? and member_id=? limit 1');
  if (!$stmt) {
    die($db->error);
  }

  $stmt->bind_param('ii', $post_id, $id);
  $success = $stmt->execute();
  if (!$success) {
    die($db->error);
  }
}

header('Location: index.php');

//プログラムの終了
exit();
