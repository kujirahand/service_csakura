<?php
$config = __DIR__."/config.php";
if (file_exists($config)) {
    include_once($config);
} else {
    define('CSAKURA', 'csakura');
}

function get_midi_url($id) {
    $base_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
    $base_url .= dirname($_SERVER['SCRIPT_NAME']);
    return rtrim($base_url, '/') . "/midi/$id.mid";
}

// データベース接続
function connectDatabase() {
    $path = __DIR__."/db/history.db";
    $db = new SQLite3($path);
    $db->exec("CREATE TABLE IF NOT EXISTS music (id INTEGER PRIMARY KEY AUTOINCREMENT)");
    return $db;
}

// IDを生成
function createNewID($db) {
    $db->exec("INSERT INTO music DEFAULT VALUES");
    return $db->lastInsertRowID();
}

// メイン処理
$mml = empty($_POST['mml']) ? "" : $_POST['mml'];
if (trim($mml) !== "") {
    $mml_text = $mml;

    // データベース接続
    $db = connectDatabase();
    // IDを取得
    $id = createNewID($db);
    // MMLファイルの保存
    $mml_filename = __DIR__."/mml/$id.mml";
    file_put_contents($mml_filename, $mml_text);

    // csakuraで変換
    $mid_filename = __DIR__."/midi/$id.mid";
    $csakura_path = CSAKURA;
    $command = escapeshellcmd("{$csakura_path} \"$mml_filename\" \"$mid_filename\"");
    exec($command, $output, $return_var);

    if ($return_var === 0 && file_exists($mid_filename)) {
        $path = get_midi_url($id);
        echo "ok; $id; $path";
    } else {
        // エラー処理
        header('Content-Type: text/plain');
        echo "error; $output";
    }
    exit;
}

// ポストがない場合、投稿フォームを表示
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MML to MIDI Converter</title>
</head>
<body>
    <h1>MML to MIDI Converter</h1>
    <form method="post">
        <label for="mml">MMLテキストを入力してください:</label><br>
        <textarea id="mml" name="mml" rows="10" cols="50"></textarea><br>
        <button type="submit">変換</button>
    </form>
</body>
</html>


