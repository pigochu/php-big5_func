<?
/*
  �{�� : big5 �r��B�z��ƶ�
  �ɦW : big5_func.inc
  �@�� : Pigo Chu<pigo@ms5.url.com.tw>
  ���� :
	�o�Ǩ�ƬO�H PHP4 �ӳB�z big5 �r��
	����H���i�H�ۥѴ��G���{��
	�g�o�ǵ{���O�ݨ� LinuxFab �W�Q�װϤW�ܦh�H��������D�~�g��
	�ڤ���O�ҷ|�o�ͤ�����D , �Y�� bug �ШӫH�Q�פ��n��|
	
  �ɶ� : 2003/1/6
  ���� : 0.19
  

  PS 1 : �ԲӦw�ˤ覡�P�ϥΤ覡�аѦ� readme.html 
  PS 2 : �������j�T��� iconv �ӳB�z�r�� , �@�̩|���ܸԲӪ�����, �Y�����D�� bug �� Email ����
  PS 3 : PHP �۱q 4.3.0 ����w�g�����䴩big5�r�� , �ĪG���� , ����ƶ��]�\���|�A���j�T��s�F
  	�]�����\�h��ƥi�H�� PHP 4.3.0 �Ӱ����ۦP�ĪG , ���L PHP 4.3.0 �|������ addslashes �B�z
  	���줸�r��...�O�i�����B
  
*/

# ���禡�w�s�񪺦�m , �ܭ��n�� , �Ы��ӱz��ک񪺦�m�ӭק�U���w�q
define("BIG5_FILE_DIR" , "./big5_func/" );  



/* �{���}�l */
# error_reporting(E_ALL);
define("BIG5_UNICODE_START" , 0xa140);	// �ثe������ Unicocde �� BIG5 �r�_�l��
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

// �p�⤤��r����
function big5_stroke($str)
{

    $tab=@File(BIG5_FILE_DIR  ."/big5_stroke.tab");
    if(!$tab)
    {
    	 echo "Can't Open file big5_stroke.tab, plz check define BIG5_FILE_DIR is valid";
    	 exit;
    }

    /* Ū���ഫ���ܰ}�C $StrokeMapping */
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