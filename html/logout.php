<?php
//セッション開始
session_start();

//セッションの破棄
unset($_SESSION['id']);
unset($_SESSION['name']);

header('Location: login.php');

//プログラムの終了
exit();
