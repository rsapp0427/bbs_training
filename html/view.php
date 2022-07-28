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

  $stmt = $db->prepare('select p.id, p.message, p.member_id, p.created ,m.name, m.picture from posts p, members m where p.id=? and p.member_id=m.id');
  if (!$stmt) {
    die($db->error);
  }

  $stmt->bind_param('i', $post_id);
  $success = $stmt->execute();
  if (!$success) {
    die($db->error);
  }

  $stmt->bind_result($postId, $message, $memberId, $created, $memberName, $picture);
  $stmt->fetch();

  $postItem = [
    'post_id' => $postId,
    'message' => $message,
    'member_id' => $memberId,
    'created' => $created,
    'name' => $memberName,
    'picture' => $picture,
  ];

  //連想配列から変数を作成
  extract($postItem);
}

//テンプレートの読み込み
include __DIR__ . '/../tpl/view.tpl.html';
