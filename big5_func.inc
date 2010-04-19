<?
/*
  程式 : big5 字串處理函數集
  檔名 : big5_func.inc
  作者 : Pigo Chu<pigo@ms5.url.com.tw>
  說明 :
	這些函數是以 PHP4 來處理 big5 字元
	任何人都可以自由散佈本程式
	寫這些程式是看見 LinuxFab 上討論區上很多人有中文問題才寫的
	我不能保證會發生什麼問題 , 若有 bug 請來信討論不要謾罵
	
  時間 : 2003/3/6
  版本 : 0.22
  

  PS 1 : 詳細安裝方式與使用方式請參考 readme.html 
  PS 2 : 本版本大幅改用 iconv 來處理字串 , 作者尚未很詳細的測試, 若有問題或 bug 請 Email 給我
  PS 3 : PHP 自從 4.3.0 之後已經正式支援big5字串 , 效果不錯 , 本函數集也許不會再做大幅更新了
  	因為有許多函數可以用 PHP 4.3.0 來做有相同效果 , 不過 PHP 4.3.0 尚未提供 addslashes 處理
  	雙位元字串...是可惜之處
  
*/


define("BIG5_FILE_DIR" , dirname(__FILE__) );



/* 程式開始 */
# error_reporting(E_ALL);
define("BIG5_UNICODE_START" , 0xa140);	// 目前中文轉 Unicocde 的 BIG5 字起始值
define("UTF16_FIRST_CHAR" ,chr(0xff).chr(0xfe));

// Big5 error handler function
function Big5ErrorHandler ($errno, $errstr, $errfile, $errline) {
  $debug = debug_backtrace();
  switch ($errno) {
  case E_USER_ERROR:
    echo "<br><b>Error: </b>$errstr in <b>".$debug[2][file] ."</b> on line " .$debug[2][line] ."<br>\n";
    exit(1);
    break;
  case E_USER_WARNING:
    echo "<br><b>Warring: </b>$errstr in <b>".$debug[2][file] ."</b> on line " .$debug[2][line] ."<br>\n";
    break;
  case E_USER_NOTICE:
    echo "<br><b>Notice: </b> [$errno] $errstr<br>\n";
    break;
    default:
    echo "Unkown error type: [$errno] $errstr<br>\n";
    break;
  }
}



if(function_exists("iconv"))
    include_once(BIG5_FILE_DIR . "/big5_func.iconv.php");
else
    include_once(BIG5_FILE_DIR . "/big5_func.default.php");


function big5_isBig5($c="")
{

  $bc  = hexdec(bin2hex($c));
  if
  (
     ($bc>=0xa440 && $bc<= 0xc67e) ||
     ($bc>=0xc940 && $bc<= 0xf9fe) ||
     ($bc>=0xa140 && $bc<= 0xa3fe) ||
     ($bc>=0xc6a1 && $bc<= 0xc8fe)
  ) return true;

  return false;
}

// 計算中文字筆劃
function big5_stroke($str="")
{

    $tab=@File(BIG5_FILE_DIR  ."/big5_stroke.tab");
    if(!$tab)
    {
        $error_handler = set_error_handler("Big5ErrorHandler");
        trigger_error ("big5_stroke() : Can not open file '" . BIG5_FILE_DIR . "/big5_stroke.tab'", E_USER_ERROR );
        restore_error_handler();

    }

    /* 讀取轉換表至陣列 $StrokeMapping */
    $i=0;
    while(list($key,$val)=Each($tab))
    {
        $StrokeMapping[$i] = split(" ",$val);
        $StrokeMapping[$i][1] = HexDec($StrokeMapping[$i][1]);
        $StrokeMapping[$i][2] = HexDec($StrokeMapping[$i][2]);
        $i++;
    }

    $s1 = substr($str,0,1);
    $s2 = substr($str,1,1);
    $s  = Hexdec(Bin2hex($s1.$s2));

    if( big5_isBig5($s1.$s2) )
    {
        for($i=0;$i<count($StrokeMapping);$i++)
             if($StrokeMapping[$i][1] <= $s && $StrokeMapping[$i][2] >= $s)
                 return $StrokeMapping[$i][0];
    }
    else

        return false;

}

function big5_chunk_split($str, $chunklen=76 , $end="\r\n")
{
   $len = big5_strlen($str);
   $tmp = array();
   for($i=0 ; $i<$len ; $i+=$chunklen)
      $tmp[] = big5_substr($str,$i,$chunklen) ;

   return implode( $end , $tmp);
}

function big5_strpos($haystack ,$needle ,$offset=0) 
{
    if($offset < 0 || !is_int($offset))
    {
        $error_handler = set_error_handler("Big5ErrorHandler");
        trigger_error ("big5_strpos(string haystack,string needle,int <b>offset=0</b>) : offset must >= 0 or ignore", E_USER_WARNING );
        restore_error_handler();
        return false;
    }
    $needle_len = big5_strlen($needle);
    $len =big5_strlen($haystack);
    for($i=$offset ; $i<$len ; $i++)
    {
        if(big5_substr($haystack,$i,$needle_len) == $needle)
            return $i;
    }
    return false;
}

function big5_strrpos($haystack , $needle)
{
    $first_char = "";
    if( is_int($needle) )
    {
        $hex = dechex($needle);
        $first_char = chr(hexdec(substr($hex,0,2)));
        if(strlen($hex)>2)
            $first_char .= chr(hexdec(substr($hex,2,2)));
    }
    else
        $first_char = big5_substr($needle,0,1);
    echo $first_char;
    $len =big5_strlen($haystack);
    for($i=$len ; $i>-1 ; $i--)
    {
         if(big5_substr($haystack,$i,1) == $first_char)
            return $i;
    }
    return false;
}
?>
