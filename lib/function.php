<?php

//データベースに接続する関数
function dbConnect()
{
  $db = new mysqli('localhost', 'root', '', 'bbs_training');

  if (!$db) {
    die($db->error);
  }

  return $db;
}

//xss対策
function h($value)
{
  return htmlspecialchars($value, ENT_QUOTES);
}
