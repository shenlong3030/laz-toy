<?php

// $caller can be: __FUNCTION__ , __FILE__ , __LINE__
function myecho($val, $caller="") {
    echo "<br>";
    if(!empty($caller)) {
        echo $caller, "() ";
    }
    echo $val;
}

function myvar_dump($val) {
    echo "<br>";
    var_dump($val);
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
     
    'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ'
     
    );
     
    foreach($unicode as $nonUnicode=>$uni){
     
    $str = preg_replace("/($uni)/i", $nonUnicode, $str);
     
    }
    $str = str_replace(' ','.',$str);
    
    // remove non-alphabet letter , keep "_", "."
    $str = preg_replace("/[^0-9a-zA-Z_\.]/", "", $str);
    $str = preg_replace("[\.\.]", ".", $str);
     
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

    'IPHONE' => 'IP', 
    
    'SAMSUNG' =>'SS',
     
    'HUAWEI' =>'HW',

    'REDMI' => 'RM',

    'ASUS' => '',
    'ZENFONE' => 'ZEN',

    'MOTOROLA' => 'MOTO',
     
    'NOKIA'=>'NK'
    );

    foreach($dict as $name=>$shortname){
        $sku = preg_replace("/($name)/i", $shortname, $sku);
    }

    return $sku;
}

?>