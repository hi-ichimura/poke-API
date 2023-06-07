<?php

// file_get_contentsの結果をキャッシュしつつ返す
function getCacheContents($url, $cachePath, $cacheLimit = 86400) {
    if(file_exists($cachePath) && filemtime($cachePath) + $cacheLimit > time()) {
      // キャッシュ有効期間内なのでキャッシュの内容を返す
      return file_get_contents($cachePath);
    } else {
      // キャッシュがないか、期限切れなので取得しなおす
      $data = file_get_contents($url);
      file_put_contents($cachePath, $data, LOCK_EX); // キャッシュに保存
      return $data;
    }
}

/** PokeAPI のデータを取得する(id=1から50のポケモンのデータ) */
$url = 'https://pokeapi.co/api/v2/pokemon/?limit=50&offset=0';
$replace0 = str_replace('https://','',$url);
$replace0 = str_replace('/','',$replace0);
$replace0 = str_replace('?','',$replace0);
$response = getCacheContents($url,"./cache1/{$replace0}");
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
$replace = str_replace('https://','',$value['url']);
$replace = str_replace('/','',$replace);
$response1 = getCacheContents($value['url'],"./cache2/{$replace}");
//$response1 = file_get_contents($value['url']);
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

print("<pre>");
/*日本語名*/
$url2 = 'https://pokeapi.co/api/v2/language/11';
$replace1 = str_replace('https://','',$url2);
$replace1 = str_replace('/','',$replace1);
$response2 = getCacheContents($url2,"./cache3/{$replace1}");

// レスポンスデータは JSON 形式なので、デコードして連想配列にする
$data2 = json_decode($response2, true);

//$array_name2 = [];
    //var_dump($data2['names']);

//foreach($data2['results'] as $key => $value){
        //var_dump($value);
        
        //$array_name[] =$value['name'];
        //var_dump($value['url']);
//}
print("</pre>");




/*Mainテンプレートの読み込み*/
$file_name_main = "POKE-zukan.tmpl";
$file_handler_main = fopen($file_name_main, "r");
$tmpl_main = fread($file_handler_main, filesize($file_name_main));

fclose($file_handler_main);

$result_tmpl = [];
$k = 0;
for($i=0; $i<count($array_name); $i++){
    
    /*Cardテンプレートの読み込み*/
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


if(!isset($_POST['kensu'])){
    $ken = '10';
}else{
    $ken = $_POST['kensu'];
}
define('MAX', (int)$ken);//マックス表示件数
$result_num = count($result_tmpl);
$max_page = ceil($result_num / MAX);
//echo $max_page;
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

?>
