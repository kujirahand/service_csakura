<?php
// csakura path
$config = __DIR__."/config.php";
if (file_exists($config)) {
  include_once($config);
} else {
  define('CSAKURA', 'csakura');
}

// mml_idが指定されているか確認
if (!isset($_GET['mml_id']) || empty($_GET['mml_id'])) {
    die("Error: mml_id is required.");
}
// mml_idを取得
$mml_id = $_GET['mml_id'];
$nocache = isset($_GET['nocache']) ? intval($_GET['nocache']) : 0;

// ダウンロードURLを構築
$download_url = "https://sakuramml.com/mmlbbs6/post.php?action=download&mml_id=" . urlencode($mml_id);

// 保存先パスを設定
$mml_directory = __DIR__ . "/mmlbbs6-mml";
$midi_directory = __DIR__ . "/mmlbbs6-midi";

// 必要なディレクトリが存在しない場合は作成
if (!is_dir($mml_directory)) {
    mkdir($mml_directory, 0777, true);
}
if (!is_dir($midi_directory)) {
    mkdir($midi_directory, 0777, true);
}

$mmlpath = $mml_directory . "/" . $mml_id . ".mml";
$midipath = $midi_directory . "/" . $mml_id . ".mid";

if (file_exists($midipath) && $nocache == 0) {
    // すでにMIDIファイルが存在する場合はダウンロードせずにそのまま返す
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: audio/midi");
    header("Content-Disposition: attachment; filename=\"output.mid\"");
    readfile($midipath);
    exit;
}

// ファイルをダウンロードして保存
$file_content = file_get_contents($download_url);
if ($file_content === false) {
    die("Error: Failed to download MML file.");
}

if (file_put_contents($mmlpath, $file_content) === false) {
    die("Error: Failed to save MML file.");
}

// コマンドを実行
$csakura_path = CSAKURA;
$command = escapeshellcmd(
  "{$csakura_path} \"$mmlpath\" \"$midipath\"");
exec($command, $output, $return_var);

if ($return_var !== 0) {
    die("Error: Failed to execute csakura command.\n" . implode("\n", $output));
}

// CORS対応ヘッダーの設定
header("Access-Control-Allow-Origin: *");
header("Content-Type: audio/midi");
header("Content-Disposition: attachment; filename=\"output.mid\"");
readfile($midipath);






