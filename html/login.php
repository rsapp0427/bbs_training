<?php
//セッションの開始
session_start();

//関数の読み込み
require __DIR__ . '/../lib/function.php';

//変数の定義
$email = '';
$password = '';
$error = [];

//フォームが送信されたら
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  //フォームの値を取得
  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);

  //エラーチェック
  if ($email === '' || $password === '') {
    $error['login'] = 'blank';
  } else {
    $db = dbConnect();

    $stmt = $db->prepare('select id, name, password from members where email=?');
    if (!$stmt) {
      die($db->error);
    }

    $stmt->bind_param('s', $email);
    $success = $stmt->execute();
    if (!$success) {
      die($db->error);
    }

    $stmt->bind_result($id, $name, $hash);
    $stmt->fetch();

    //パスワードチェック
    if (password_verify($password, $hash)) {
      $_SESSION['id'] = $id;
      $_SESSION['name'] = $name;

      header('Location: index.php');
      //プログラムの終了
      exit();
    } else {
      $error['login'] = 'failed';
    }
  }
}

//テンプレートの読み込み
include __DIR__ . '/../tpl/login.tpl.html';
