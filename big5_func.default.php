<?php
/* big5 function default */



class Big5FileCache {
    var $Big5Cache = array();
    /* 開檔 */
    function OpenFile($file_name)
    {
        $md5index = substr(md5($file_name),0,8);
        if ( @array_key_exists($md5index , $this->Big5Cache) )
        {
            return $md5index;
        }	
        else
        {
            $fp =  @fopen($file_name,"rb");
            if($fp)
            {
                $this->Big5Cache[$md5index]->fp   = $fp;
                $this->Big5Cache[$md5index]->size = filesize($file_name);
                $this->Big5Cache[$md5index]->isCache   = false;;
                return $md5index;
            }
            else return false;
        }
    }
    function &ReadFile($fp , $unserialize=false)
    {
    	
        if ( @array_key_exists($fp , $this->Big5Cache) && $this->Big5Cache[$fp]->isCache )
        {
            if($unserialize)
                return $this->Big5Cache[$fp]->usdata;
            else
                return $this->Big5Cache[$fp]->data;
        }
        else
        {

            $this->Big5Cache[$fp]->data = @fread( $this->Big5Cache[$fp]->fp ,  $this->Big5Cache[$fp]->size );
            $this->Big5Cache[$fp]->isCache = true;
            if($unserialize)
            {
            	$this->Big5Cache[$fp]->usdata = unserialize($this->Big5Cache[$fp]->data);
                return $this->Big5Cache[$fp]->usdata;
            }
            else
                return $this->Big5Cache[$fp]->data;
        }
    }
}

$Big5FileCacheObj =new Big5FileCache();
    
function big5_global_func($str,$func,$op1="")
{
   $return_str = "";
   $len = strlen($str);
   for($i=0; $i<$len; $i++)
   {
       $s1 = substr($str,$i,1);
       $s2 = substr($str,$i+1,1);
       if(big5_isBig5($s1.$s2)) {
           $return_str.= $s1.$s2;
           $i++;
       }
       else
       {

           switch($func)
           {
               case "addslashes":
                   $return_str.= addslashes($s1);
                   break;
               case "stripslashes":
                   if($s1 == "\\")
                   {  
                       $s2 = substr($str,$i+1,1);
                       if($s2  == "\\")
                           $return_str .= "\\";
                       else
                           $return_str .=$s2 ;
                       $i++;
                   }
                   else $return_str .=$s1;
                   break;
               case "stripcslashes":
                   if($s1 == "\\")
                   {  
                       $s2 = substr($str,$i+1,1);
                       if(
                          $s2  == "0" || $s2  == "a" ||
                          $s2  == "b" || $s2  == "f" ||
                          $s2  == "n" || $s2  == "r" ||
                          $s2  == "t" || $s2  == "v"
                       )
                           $return_str .= stripcslashes($s1.$s2);
                       else
                           $return_str .=$s2 ;
                       $i++;
                   }
                   else $return_str .=$s1;
                   break;
               case "addcslashes":
                   $return_str.= addcslashes($s1,$op1);
                   break;
               case "strtolower":
                   $return_str.= strtolower($s1);
                   break;
               case "strtoupper":
                   $return_str.= strtoupper($s1);           
                   break;
           }
       }
   }
   

   return $return_str;
}

// 此版本已經移除
//function big5_addslashes($str) {
//   return big5_global_func($str,"addslashes");
//}
function big5_addcslashes($str,$charlist) {
   return big5_global_func($str,"addcslashes",$charlist);
}
// function big5_stripslashes($str) {
//   return big5_global_func($str,"stripslashes");
//}
function big5_stripcslashes($str) {
   return big5_global_func($str,"stripcslashes");
}
function big5_strtolower($str) {
   return big5_global_func($str,"strtolower");
}
function big5_strtoupper($str) {
   return big5_global_func($str,"strtoupper");
}
function big5_str_replace($search , $replace, $subject)
{

   $len = strlen($subject);
   $search_len = strlen($search);
   for($i=0; $i<$len; $i++)
   {
       $s1 = substr($subject,$i,1);
       $s2 = substr($subject,$i+1,1);
       if(big5_isBig5($s1.$s2))
       {
           $first_str = $s1.$s2;
           if($first_str == substr($search,0,2))
           {
               if( substr($subject,$i,$search_len) == $search )
               {
                   $return_str .= $replace;
                   $i+=$search_len-1;
               }
               else 
               {
                   $return_str .= $first_str;
                   $i++;
               }
           }
           else
           {
               $return_str .= $first_str;
               $i++;
           }
       }
       else
       {
           $first_str = $s1;
           if($first_str == substr($search,0,1))
           {
               if( substr($subject,$i,$search_len) == $search )
               {
                   $return_str .=  $replace;
                   $i+=$search_len-1;
               }
               else $return_str .= $first_str;
           }
           else $return_str .= $first_str;
       }
   }
   return $return_str;
}


function big5_strlen($str)
{
    if(BIG5_SUPPORT_MB)
        return mb_strlen ( $str ,BIG5_ENCODER );
    else {
        $return_len = 0;
        $len = strlen($str);
        for($i=0; $i<$len; $i++)
        {
            if(big5_isBig5( substr($str,$i,2)))
                ++$i;
            $return_len++;
        }
        return $return_len;
    }
}

function big5_substr($str,$start,$len=0)
{
    if(BIG5_SUPPORT_MB)
	return mb_substr( $str , $start , $len ,BIG5_ENCODER);
   $offset = 1; # 字元的指標
   if(!$len) $len = strlen($str);
   $str_len = strlen($str);
   $big5_len = big5_strlen($str);
   if($start < 0) $start = $big5_len+$start;
   if($len<0) $len = $big5_len+$len-$start;
  
   $return_str = "";
   for($i=0; $i< $str_len ; $i++)
   {
     if($offset-$start-1 >= $len)
           break;
     $ch = substr($str, $i , 2);
     if($offset>$start)
     {
       if( big5_isBig5($ch) )
       {
               $return_str .= $ch;
               $i++;
       }
       else
       	   $return_str .= $ch[0];

     }
     else if( big5_isBig5($ch) )
         $i++;

     $offset++;
   }  
   return $return_str;
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
    	$tmp_big5[$i] = big5_utf16_decode(UTF16_FIRST_CHAR. Chr( hexdec( substr($s,2,2))) . Chr( hexdec( substr($s,0,2))) );
    }
    return str_replace($tmp,$tmp_big5, $str) ;
}

function big5_unicode($str)
{

    global $Big5FileCacheObj;
    $fp = $Big5FileCacheObj->OpenFile(BIG5_FILE_DIR  ."/big5_unicode.tab");
    if($fp)
        $tab = & $Big5FileCacheObj->ReadFile($fp);

    else
    {
        $error_handler = set_error_handler("Big5ErrorHandler");
        trigger_error ("big5_unicode() : Can not open file '" . BIG5_FILE_DIR . "/big5_unicode.tab'", E_USER_ERROR );
        restore_error_handler();
    }

    $len = strlen($str);
    for($i=0; $i< $len; $i++)
    {

      if(big5_isBig5($str[$i].$str[($i+1)]))
      {
          $a1 = @Ord($str[$i]);
          $a2 = @Ord($str[$i+1]);
          $ret_str .= "&#" . sprintf("%05d",hexdec(bin2hex(substr($tab,($a1*256 + $a2 - BIG5_UNICODE_START)*2,2))) ) .";";
          $i++;
      }
      else 
      {
          $s = $str[$i];
          $ret_str .= "&#" . sprintf("%05d",Hexdec(bin2hex($s))) . ";";
      }
    }  

    return $ret_str;
}

function big5_utf8_encode($str)
{
    global $Big5FileCacheObj;
    $fp = $Big5FileCacheObj->OpenFile(BIG5_FILE_DIR  ."/big5_utf8.tab");
    if($fp)
        $tab = & $Big5FileCacheObj->ReadFile($fp);

    else
    {
        $error_handler = set_error_handler("Big5ErrorHandler");
        trigger_error ("big5_utf8_encode() : Can not open file '" . BIG5_FILE_DIR . "/big5_utf8.tab'", E_USER_ERROR );
        restore_error_handler();
    }


    $len = strlen($str);

    for($i=0; $i< $len; $i++)
    {
      if(big5_isBig5( $str[$i].$str[$i+1] ))
      {
          $a1 = Ord(substr($str,$i,1));
          $a2 = Ord(substr($str,$i+1,1));
          $ret_str .= substr($tab,($a1*256 + $a2 - BIG5_UNICODE_START)*3,3);
          $i++;
      }
      else 
          $ret_str .= utf8_encode($str[$i]);
    }  
    return $ret_str;
}



function big5_utf8_decode($str)
{
    
    global $Big5FileCacheObj;
    $fp = $Big5FileCacheObj->OpenFile(BIG5_FILE_DIR  ."/utf8_big5.tab");
    if($fp)
    {
        $tab = &$Big5FileCacheObj->ReadFile($fp,true);

    }
    else
    {
        $error_handler = set_error_handler("Big5ErrorHandler");
        trigger_error ("big5_utf8_decode() : Can not open file '" . BIG5_FILE_DIR . "/utf8_big5.tab'", E_USER_ERROR );
        restore_error_handler();
    }

    $len = strlen($str);
    for($i=0 ; $i<$len ; $i++)
    {
    	$check = Ord($str[$i]);
        if( $check >> 7 == 0)
            $ret_str .= chr($check);

        else if ( $check>>5 == 6 ) // AscII > 127 的特殊符號
        {
            $ret_str .= $tab[bin2hex($str[$i].$str[$i+1])];
            $i++;
        }
        else if ( $check>> 4 == 0xe)
        {
             $ret_str .= $tab[bin2hex($str[$i].$str[$i+1].$str[$i+2])];
            $i+=2;
        }

    }
   return $ret_str;
}

function big5_utf16_encode($str)
{
       
    global $Big5FileCacheObj;
    $fp = $Big5FileCacheObj->OpenFile(BIG5_FILE_DIR  ."/big5_unicode.tab");
    if($fp)
        $tab = & $Big5FileCacheObj->ReadFile($fp);

    else
    {
        $error_handler = set_error_handler("Big5ErrorHandler");
        trigger_error ("big5_utf16_encode() : Can not open file '" . BIG5_FILE_DIR . "/big5_unicode.tab'", E_USER_ERROR );
        restore_error_handler();
    }

    $len = strlen($str);
    $ret_str = UTF16_FIRST_CHAR;
    for($i=0; $i< $len; $i++)
    {
      if(big5_isBig5(substr($str,$i,2)))
      {
          $a1 = @Ord($str[$i]);
          $a2 = @Ord($str[$i+1]);
      	  $idx = ($a1*256 + $a2 - BIG5_UNICODE_START)*2;
          $ret_str .=substr($tab,$idx+1,1) . substr($tab,$idx,1);
          ++$i;
      }
      else 
          $ret_str .= $str[$i] .chr(0);
    }  

    return $ret_str;
}


function big5_utf16_decode($str)
{

    // 讀取 utf16 轉 big5 表格
    global $Big5FileCacheObj;
    $fp = $Big5FileCacheObj->OpenFile(BIG5_FILE_DIR  ."/utf16_big5.tab");
    if($fp)
    {
        $tab = &$Big5FileCacheObj->ReadFile($fp,true);

    }
    else
    {
        $error_handler = set_error_handler("Big5ErrorHandler");
        trigger_error ("big5_utf16_decode() : Can not open file '" . BIG5_FILE_DIR . "/utf16_big5.tab'", E_USER_ERROR );
        restore_error_handler();
    }


    $len = strlen($str);
    for($i=0 ; $i<$len ; $i+=2)
    {
        if( !Ord($str[$i+1]) )
          $ret_str .= $str[$i];
        else
          $ret_str .= $tab[bin2hex( $str[$i+1] .$str[$i] )];
    }
   return $ret_str;
}

?>