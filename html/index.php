<?php

//セッション開始
session_start();

//関数の読み込み
require __DIR__ . '/../lib/function.php';

//ログイン確認
if (isset($_SESSION['id']) && isset($_SESSION['name'])) {
  $id = $_SESSION['id'];
  $name = $_SESSION['name'];
} else {
  header('Location: login.php');

  //プログラムの終了
  exit();
}

//変数の定義
$error = [];

//フォームが送信されたら
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS);

  if ($message === '') {
    $error['message'] = 'failed';
  } else {
    $db = dbConnect();

    $stmt = $db->prepare('insert into posts (message, member_id) values(?, ?)');
    if (!$stmt) {
      die($db->error);
    }

    $stmt->bind_param('si', $message, $id);
    $success = $stmt->execute();
    if (!$success) {
      die($db->error);
    }

    //フォームの再送信防止
    header('Location: index.php');

    //プログラムの終了
    exit();
  }
}

$db = dbConnect();

$stmt = $db->prepare('select p.id, p.member_id, p.message, p.created, m.name, m.picture from posts p, members m where p.member_id=m.id order by id desc');
if (!$stmt) {
  die($db->error);
}

$success = $stmt->execute();
if (!$success) {
  die($db->error);
}

$stmt->bind_result($post_id, $member_id, $post_message, $created, $member_name, $picture);

$postItem = [];
$postList = [];

while ($stmt->fetch()) {
  $postItem['post_id'] = $post_id;
  $postItem['member_id'] = $member_id;
  $postItem['message'] = $post_message;
  $postItem['created'] = $created;
  $postItem['name'] = $member_name;
  $postItem['picture'] = $picture;

  $postList[] = $postItem;
}

//テンプレートの読み込み
include __DIR__ . '/../tpl/index.tpl.html';
