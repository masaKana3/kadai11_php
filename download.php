<?php
include("funcs.php");
$pdo = db_conn();

// CSVファイルのパス
$filename = "conditions.csv";
$export_csv_title = ["ID", "性別", "年代", "居住地", "症状の有無", "症状1", "症状2", "症状3", "症状4", "症状5", "症状6", "日時"];

$sql = "SELECT id, gender, generation, area, agree, condition1, condition2, condition3, condition4, condition5, condition6, date FROM survey1_con_table";

// ヘッダー行をSJIS-winに変換
$export_header = array_map(function($val) {
    return mb_convert_encoding($val, 'SJIS-win', 'UTF-8');
}, $export_csv_title);

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 一時的にファイル内容を保持するためのストリームを開く（メモリ上）
    $fp = fopen('php://memory', 'w');

    // ヘッダー行を書き込む（文字コードを SJIS-win に変換）
    $export_header = array_map(function($val) {
        return mb_convert_encoding($val, 'SJIS-win', 'UTF-8');
    }, $export_csv_title);
    fputcsv($fp, $export_header);

    // データ行を書き込む
    foreach ($results as $row) {
        $data = array_map(function($value) {
            return mb_convert_encoding($value, 'SJIS-win', 'UTF-8');
        }, $row);
        fputcsv($fp, $data);
    }

    // ストリームのポインタを先頭に戻す
    fseek($fp, 0);

    // HTTPヘッダーを設定（ダウンロードファイルとして扱う）
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // ファイルの内容を出力
    fpassthru($fp);
    fclose($fp);
    exit();
} catch (Exception $e) {
    echo "エラーが発生しました: " . $e->getMessage();
}

?>