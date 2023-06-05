<?php
/* PokeAPI のデータを取得する(URL末尾の数字はポケモン図鑑のID) 
$url = 'https://pokeapi.co/api/v2/pokemon/1/';
$response = file_get_contents($url);
// レスポンスデータは JSON 形式なので、デコードして連想配列にする
$data = json_decode($response, true);
print("<pre>");
var_dump($data['name']); // 名前
$name="";
$name = $data['name'];
$img = "";
var_dump($data['sprites']['front_default']); // 正面向きのイメージ
$img = $data['sprites']['front_default'];
var_dump($data['height']); // たかさ
var_dump($data['weight']); // おもさ
print("</pre>");
*/

/** PokeAPI のデータを取得する(id=11から20のポケモンのデータ) */
$url = 'https://pokeapi.co/api/v2/pokemon/?limit=50&offset=0';
$response = file_get_contents($url);
// レスポンスデータは JSON 形式なので、デコードして連想配列にする
$data = json_decode($response, true);
// 取得結果をループさせてポケモンの名前を表示する

$array_img = [];
$array_name = [];
$array_height = [];
$array_weight = [];
$array_type = [];

print("<pre>");
foreach($data['results'] as $key => $value){
//var_dump($value);

$array_name[] =$value['name'];

$response1 = file_get_contents($value['url']);
$data1 = json_decode($response1, true);
$array_img[] = $data1['sprites']['front_default'];
$array_height[] =$data1['height'];
$array_weight[] =$data1['weight'];

$array_type[] =$data1['types'][0]['type']["name"];

}
print("</pre>");

print("<pre>");
//var_dump($array_type);
print("</pre>");


/*テンプレートの読み込み*/
$file_name_main = "POKE-zukan.tmpl";
$file_handler_main = fopen($file_name_main, "r");
$tmpl_main = fread($file_handler_main, filesize($file_name_main));

fclose($file_handler_main);

$result_tmpl = [];
$k = 0;
for($i=0; $i<count($array_name); $i++){
    
    /*テンプレートの読み込み*/
    $file_name = "POKE-zukan-card.tmpl";
    $file_handler = fopen($file_name, "r");
    $tmpl = fread($file_handler, filesize($file_name));


    fclose($file_handler);
    $tmpl=str_replace("!poke_img!", $array_img[$i], $tmpl);//画像の代入

    $tmpl=str_replace("!poke_name!", $array_name[$i], $tmpl);//名前の代入

    //$result_type = implode('', $array_type[$i]);
    //$result_height = implode('', $array_height[$i]);
    //$result_weight = implode('', $array_weight[$i]);

    $tmpl=str_replace("!poke_type!", $array_type[$i], $tmpl);//タイプの代入

    $tmpl=str_replace("!poke_height!", (string)$array_height[$i], $tmpl);//高さの代入

    $tmpl=str_replace("!poke_weight!", (string)$array_weight[$i], $tmpl);//重さの代入

    $result_tmpl[] = $tmpl;
}

define('MAX','10');//マックスは10件表示
$result_num = count($result_tmpl);
$max_page = ceil($result_num / MAX);
 
if(!isset($_GET['page_id'])){//page_idがセットされていないとき
    $now = 1;
}else{
    $now = $_GET['page_id'];
}
 
$start_no = ((int)$now - 1) * MAX;//データのスタート位置
//echo $start_no;
 
$disp_data = array_slice($result_tmpl, $start_no, MAX, true);
 
    $result = implode('',$disp_data );
    $tmpl_main=str_replace("!poke_list!", $result, $tmpl_main);
    echo $tmpl_main;
 
echo '全件数'. $result_num. '件'. '　'; // 全データ数の表示です。
 
if($now > 1){ // リンクをつけるかの判定
    echo "<a href='POKE-zukan.php?page_id='.($now - 1).''>前へ</a>";
} else {
    echo '前へ'. '　';
}
 
for($i = 1; $i <= $max_page; $i++){//ページ番号の表示（リンク）
    if ($i == $now) {
        echo $now. '　'; 
    } else {
        echo "<a href='POKE-zukan.php?page_id=$i'> $i </a>";
    }
}
 
if($now < $max_page){ // リンクをつけるかの判定
    echo "<a href='POKE-zukan.php?page_id=",$now + 1,"'>次へ</a>";
} else {
    echo '次へ';
}







/*
print("<pre>");
//var_dump($result_tmpl);
print("</pre>");

$result = implode('',$result_tmpl );
$tmpl_main=str_replace("!poke_list!", $result, $tmpl_main);
echo $tmpl_main;
*/
?>
