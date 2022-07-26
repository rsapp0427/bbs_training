<?php
//セッションの開始
session_start();

//関数の読み込み
require __DIR__ . '/../../lib/function.php';

//入力画面からの遷移か確認
if (isset($_SESSION['form'])) {
  $form = $_SESSION['form'];
} else {
  header('Location: index.php');

  //プログラムの終了
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $db = dbConnect();

  $stmt = $db->prepare('insert into members (name,email,password,picture) values(?,?,?,?)');
  if (!$stmt) {
    die($db->error);
  }

  $password = password_hash($form['password'], PASSWORD_DEFAULT);

  $stmt->bind_param('ssss', $form['name'], $form['email'], $password, $form['image']);
  $success = $stmt->execute();
  if (!$success) {
    die($db->error);
  }

  //セッションの情報を削除
  unset($_SESSION['form']);
  header('Location: thanks.php');
}

include __DIR__ . '/../../tpl/join/check.tpl.html';
