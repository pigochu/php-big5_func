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
	
  時間 : 2003/1/6
  版本 : 0.19
  

  PS 1 : 詳細安裝方式與使用方式請參考 readme.html 
  PS 2 : 本版本大幅改用 iconv 來處理字串 , 作者尚未很詳細的測試, 若有問題或 bug 請 Email 給我
  PS 3 : PHP 自從 4.3.0 之後已經正式支援big5字串 , 效果不錯 , 本函數集也許不會再做大幅更新了
  	因為有許多函數可以用 PHP 4.3.0 來做有相同效果 , 不過 PHP 4.3.0 尚未提供 addslashes 處理
  	雙位元字串...是可惜之處
  
*/

# 本函式庫存放的位置 , 很重要喔 , 請按照您實際放的位置來修改下面定義
define("BIG5_FILE_DIR" , "./big5_func/" );  



/* 程式開始 */
# error_reporting(E_ALL);
define("BIG5_UNICODE_START" , 0xa140);	// 目前中文轉 Unicocde 的 BIG5 字起始值
define("UTF16_FIRST_CHAR" ,chr(0xff).chr(0xfe));

if(function_exists("iconv"))
    include_once(BIG5_FILE_DIR . "/big5_func.iconv.php");
else
    include_once(BIG5_FILE_DIR . "/big5_func.default.php");

function big5_isBig5($c)
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
function big5_stroke($str)
{

    $tab=@File(BIG5_FILE_DIR  ."/big5_stroke.tab");
    if(!$tab)
    {
    	 echo "Can't Open file big5_stroke.tab, plz check define BIG5_FILE_DIR is valid";
    	 exit;
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
?>
