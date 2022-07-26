<?php
// SDKの読み込み
require __DIR__ . '/../../vendor/autoload.php';

//s3クライアント読み込み
use Aws\S3\S3Client;

//envファイルの読み込み
Dotenv\Dotenv::createImmutable(__DIR__)->load();

//セッションの開始
session_start();

//関数の読み込み
require __DIR__ . '/../../lib/function.php';

//確認画面からの遷移かチェック
if (isset($_GET['action']) && $_GET['action'] === 'rewrite' && isset($_SESSION['form'])) {
  $form = $_SESSION['form'];
} else {
  //変数定義
  $form = [
    'name' => '',
    'email' => '',
    'password' => '',
  ];
}
$error = [];

//フォームが送信されたら
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  //フォームの入力値を取得
  $form['name'] = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
  $form['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  $form['password'] = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
  $image = $_FILES['image'];

  //エラーチェック
  if ($form['name'] === '') {
    $error['name'] = 'blank';
  }

  if ($form['email'] === '') {
    $error['email'] = 'blank';
  } else {
    $db = dbConnect();

    $stmt = $db->prepare('select count(*) from members where email=?');
    if (!$stmt) {
      die($db->error);
    }

    $stmt->bind_param('s', $form['email']);
    $success = $stmt->execute();
    if (!$success) {
      die($db->error);
    }

    $stmt->bind_result($cnt);
    $stmt->fetch();

    if ($cnt > 0) {
      $error['email'] = 'duplicate';
    }
  }

  if ($form['password'] === '') {
    $error['password'] = 'blank';
  } else if (strlen($form['password']) < 4) {
    $error['password'] = 'length';
  }

  if ($image['name'] !== '') {
    if ($image['error'] === 0) {
      $type = mime_content_type($image['tmp_name']);
      if ($type !== 'image/png' && $type !== 'image/jpeg') {
        $error['image'] = 'type';
      }
    } else {
      $error['image'] = 'failed';
    }
  }

  //エラーがなければ
  if (empty($error)) {
    $_SESSION['form'] = $form;

    if ($image['name'] !== '') {
      $filename = 'member_picture/' . date('YmdHis') . '_' . $image['name'];

      //S3clientのインスタンス生成(各項目の説明は後述)
      $s3client = S3Client::factory([
        'credentials' => [
          'key' => $_ENV['AWS_ACCESS_KEY_ID'],
          'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
        ],
        'region' => 'ap-northeast-1',
        'version' => 'latest',
      ]);

      //バケット名を指定
      $bucket = $_ENV['S3_BUCKET_NAME'];
      //アップロードするファイルを用意
      $upImage = fopen($image['tmp_name'], 'rb');

      //画像のアップロード(各項目の説明は後述)
      $result = $s3client->putObject([
        'Bucket' => $bucket,
        'Key' => $filename,
        'Body' => $upImage,
        'ContentType' => mime_content_type($image['tmp_name']),
      ]);

      //s3バケットのドメイン
      $s3_path = 'training-bucket-bbs-image.s3.ap-northeast-1.amazonaws.com';

      //cfのドメイン
      $cf_path = 'image.aws-and-rsapp.com';

      //読み取り用のパスを返す
      $path = str_replace($s3_path, $cf_path, $result['ObjectURL']);

      $_SESSION['form']['image'] = $path;
    } else {
      $_SESSION['form']['image'] = '';
    }

    header('Location: check.php');

    //プログラムの終了
    exit();
  }
}

//テンプレートの読み込み
include __DIR__ . '/../../tpl/join/index.tpl.html';
