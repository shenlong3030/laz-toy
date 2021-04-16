<?php

// $caller can be: __FUNCTION__ , __FILE__ , __LINE__
function myecho($val, $caller="") {
    echo "<br>";
    if(!empty($caller)) {
        echo $caller, "() ";
    }
    echo $val;
}

function debug_log($val) {
    if($_COOKIE["debug"]){
        echo "<br>";
        var_dump($val);
    }
}


function is_url($url) {
    $path = parse_url($url, PHP_URL_PATH);
    $encoded_path = array_map('urlencode', explode('/', $path));
    $url = str_replace($path, implode('/', $encoded_path), $url);

    return filter_var($url, FILTER_VALIDATE_URL) ? true : false;
}

function is_blank($val) {
    return empty($val) && !is_numeric($val);
}

function htmlLinkImage($url, $width=100, $height=100) {
    return '<a target="_blank" href="'.$url.'"><img width='.$width.' height='.$height.' src="'.$url.'"></img></a>';
}

function htmlLinkImages($urls, $width=100, $height=100) {
    $output = "";
    $urls = array_filter($urls);
    foreach($urls as $url) {
        $output .= '<a target="_blank" href="'.$url.'"><img width='.$width.' height='.$height.' src="'.$url.'"></img></a>';
    }
    return $output;
}

function getValue($list, $index) {
    if(isset($list[$index])) {
        return $list[$index];
    } else {
        return "";
    }
}

function val($input, $defaultvalue = 0) {
    return isset($input) ? $input : $defaultvalue;
}

function vn_to_str ($str){
    $unicode = array(
     
    'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
     
    'd'=>'đ',
     
    'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
     
    'i'=>'í|ì|ỉ|ĩ|ị',
     
    'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
     
    'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
     
    'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
     
    'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
     
    'D'=>'Đ',
     
    'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
     
    'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
     
    'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
     
    'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
     
    'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',

    '.'=>'\s'
     
    );
     
    foreach($unicode as $nonUnicode=>$uni){ 
        $str = preg_replace("/($uni)/i", $nonUnicode, $str);
    }

    // remove non-alphabet letter , keep "_", "."
    $str = preg_replace("/[^0-9a-zA-Z_\.]/", "", $str);
    return $str;
}

function vn_urlencode ( $str ) {
    $str = vn_to_str($str);
    
     # convert characters > 255 into HTML entities
     $convmap = array( 0xFF, 0x2FFFF, 0, 0xFFFF );
     $str = mb_encode_numericentity( $str, $convmap, "UTF-8");

     # escape HTML entities, so they are not urlencoded
     $str = preg_replace( '/&#([0-9a-fA-F]{2,5});/i', 'mark\\1mark', $str );
     $str = urlencode($str);

     # now convert escaped entities into unicode url syntax
     $str = preg_replace( '/mark([0-9a-fA-F]{2,5})mark/i', '%u\\1', $str );
     return $str;
}

function pre_process_sku($val) {
    return trim($val);
}

function pre_process_skus($list) {
    return array_map("trim", $list);
}

function make_short_sku($sku) {
    $dict = array(
    'COMBO' => 'CB',

    'TRONG.SUOT' => 'TRONG',
    
    'OP.DEO' => 'OD',
    'OP.LUNG' => 'OL',
    'CHONG.SOC' => 'CS',
    'CUONG.LUC' => 'CL',
    'BAO.DA' => 'BD',
    'DUONG.KINH.' => '',

    'IPHONE' => 'IP', 
    'SAMSUNG' =>'SAM',
    'HUAWEI' =>'HW',
    'REDMI' => 'RM',
    'ASUS.ZENFONE' => 'ZEN',
    'ZENFONE' => 'ZEN',
    'MOTOROLA' => 'MOTO',
    'NOKIA'=>'NK',
    'XIAOMI'=>'XM',
    'OPPO'=>'OP',
    'REALME'=>'RL',
    'HONOR'=>'HN',
    'VIVO'=>'VO',
    'VSMART'=>'VS',
    '.PLUS'=>'P',
    'GALAXY.WATCH.'=>'G.W',
    '\.\.\.\.'=>'',
    '\.\.\.'=>'',
    '\.\.'=>'.'
    );

    foreach($dict as $name=>$shortname){
        $sku = preg_replace("/($name)/i", $shortname, $sku);
    }

    return $sku;
}

function make_short_order_variation($order_variation) {
    $dict = array(
    'Nhóm màu:' => '',
    'Compatibility by Model:' => '',
    'AIS Lava' => '',
    '\.\.\.' => '',
    );

    foreach($dict as $name=>$shortname){
        $order_variation = preg_replace("/($name)/i", $shortname, $order_variation);
    }

    if(strlen($order_variation)) {
        $order_variation = "Lựa chọn: " . $order_variation;
    }

    return $order_variation;
}

/*
 $skuprefix : required
 $group, $color, $model, $kiotId : optional
*/
function generateSku($skuprefix, $group, $model, $color, $kiotid) {
    $kiotid = trim($kiotid);
    $newSku = $skuprefix;
    if(substr($newSku, -2) != "__") { // split prefix
        $newSku .= '__';
    }

    if($group) {
        $newSku .= vn_urlencode($group) . "__";
    }

    if($model) {
        if($model != "...") {
            $newSku .= vn_urlencode($model) . ".";
        }
    } 

    if($color) {
        if($color != "...") {
            $newSku .= vn_urlencode($color) . ".";
        }
    }

    $newSku = make_short_sku($newSku);

    if($kiotid) {
        $newSku .= $kiotid . ".";
    } 

    $time = substr(time(), -4);
    $newSku .= "V" . $time;
    return $newSku;
}

function generateSku1($skuprefix, $group, $attributes, $kiotid) {
    $skuprefix = trim($skuprefix, "_");
    $kiotid = trim($kiotid);
    $attributes = array_map("trim", $attributes);

    $parts = array();
    $parts[] = $skuprefix;
    $parts[] = $group;

    $details = array();
    $details = array_merge($details, $attributes);
    $details[] = $kiotid;
    $details = array_filter($details); // remove empty string
    $detailStr = implode('.', $details);
    $detailStr = vn_to_str($detailStr);
    $detailStr = make_short_sku($detailStr);

    // remove first duplicate word
    // SAM.SAM.A50  ==> SAM.A50
    $pattern = '/\b(\w+)\b\s*\.\s*(?=.*\1)/';
    $detailStr = preg_replace($pattern, "", $detailStr);
    $parts[] = $detailStr;

    $parts = array_filter($parts); // remove empty string
    $newSku = implode("__", $parts);
    $newSku = vn_to_str($newSku);
    $newSku = make_short_sku($newSku);
    return $newSku;
}

/**
 * Reposition an array element by its key.
 *
 * @param array      $array The array being reordered.
 * @param string|int $key They key of the element you want to reposition.
 * @param int        $order The position in the array you want to move the element to. (0 is first)
 *
 * @throws \Exception
 */
function repositionArrayElement(array &$array, $key, int $order): void
{
    if(($a = array_search($key, array_keys($array))) === false){
        throw new \Exception("The {$key} cannot be found in the given array.");
    }
    $p1 = array_splice($array, $a, 1);
    $p2 = array_splice($array, 0, $order);
    $array = array_merge($p2, $p1, $array);
}

?>