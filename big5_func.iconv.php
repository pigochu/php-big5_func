<?php
/* big5 function use iconv */


// 此版本已經移除 , 改用查表法比較適合
//function big5_addslashes($str) {
//    return iconv("UTF-8" , BIG5_ENCODER ,
//                 addslashes(iconv(BIG5_ENCODER,"UTF-8" , $str)) );
//}

function big5_addcslashes($str,$charlist) {
    return iconv("UTF-8" , BIG5_ENCODER ,
                 addcslashes(iconv(BIG5_ENCODER,"UTF-8" , $str),$charlist) );
}
// function big5_stripslashes($str) {
//    return iconv("UTF-8" , BIG5_ENCODER ,
//                 stripslashes(iconv(BIG5_ENCODER,"UTF-8" , $str)) );
// }

function big5_stripcslashes($str) {
    return iconv("UTF-8" , BIG5_ENCODER ,
                 stripcslashes(iconv(BIG5_ENCODER,"UTF-8" , $str)) );

}
function big5_strtolower($str) {
    return iconv("UTF-8" , BIG5_ENCODER ,
                 strtolower(iconv(BIG5_ENCODER,"UTF-8" , $str)) );

}
function big5_strtoupper($str) {
    return iconv("UTF-8" , BIG5_ENCODER ,
                 strtoupper(iconv(BIG5_ENCODER,"UTF-8" , $str)) );
}
function big5_str_replace($search , $replace, $subject)
{
    $replace = substr(iconv(BIG5_ENCODER,"UTF-16",$replace) , 2);
    $search = substr(iconv(BIG5_ENCODER,"UTF-16",$search) ,2);
    $subject = iconv(BIG5_ENCODER,"UTF-16",$subject);
    $subject = str_replace( $search , $replace , $subject);
    return iconv("UTF-16" , BIG5_ENCODER , $subject);
}


function big5_strlen($str)
{

    if(BIG5_SUPPORT_MB)
        return mb_strlen ( $str ,BIG5_ENCODER );
    else {
        $len = strlen(iconv(BIG5_ENCODER,"UTF-16",$str))/2 - 1;
        if($len<=0) $len = 0;
        return  $len;
    }
}

function big5_substr($str,$start,$len=0)
{
    if(BIG5_SUPPORT_MB)
	return mb_substr( $str , $start , $len ,BIG5_ENCODER);
    if(!$len) $len = strlen($str);
    if($start < 0) $start = big5_strlen($str)+$start;
    return iconv("UTF-16",BIG5_ENCODER,substr(iconv(BIG5_ENCODER,"UTF-16",$str),($start+1)*2,$len*2));
}






function big5_deunicode($str)
{
    $regs = array();
    $tmp  = array();
    $tmp_big5 = array();
    $replace_arr = array();
    preg_match_all ("/&#[0-9]{1,5};/", $str, $regs);

    $tmp = array_values(array_unique($regs[0]));
    $len = count($tmp);
    for($i=0 ; $i<$len; $i++)
    {
    	$s = sprintf("%04X",(int)str_replace( array(";" , "&#") , "", $tmp[$i]));
    	$tmp_big5[$i] = iconv("UTF-16",BIG5_ENCODER,UTF16_FIRST_CHAR. Chr( hexdec( substr($s,2,2))) . Chr( hexdec( substr($s,0,2))));
    }
    return str_replace($tmp,$tmp_big5, $str) ;
}

function big5_unicode($str)
{
    $str = iconv(BIG5_ENCODER,"UTF-16",$str);
    for($i=2 ; $i< strlen($str) ; $i+=2)
        $tmp .= "&#".sprintf("%05d",hexdec(bin2hex($str[$i] . $str[$i+1]))) . ";";
    return $tmp;
}

function big5_utf8_encode($str)
{
    return iconv(BIG5_ENCODER,"UTF-8",$str);
}

function big5_utf8_decode($str)
{
    return iconv("UTF-8",BIG5_ENCODER,$str);
}

function big5_utf16_encode($str)
{
    return iconv(BIG5_ENCODER,"UTF-16",$str);
}


function big5_utf16_decode($str)
{
    return iconv("UTF-16",BIG5_ENCODER,$str);
}

?>