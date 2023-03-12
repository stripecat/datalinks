<?php

if (!function_exists('apache_response_headers')) {
    function apache_response_headers () {
        $arh = array();
        $headers = headers_list();
        foreach ($headers as $header) {
            $header = explode(":", $header);
            $arh[array_shift($header)] = trim(implode(":", $header));
        }
        return $arh;
    }
}

$datastream="";
$datastream.= $_SERVER['REMOTE_ADDR'];


ob_end_flush();
$headersraw=apache_request_headers();


foreach ($headersraw as $name => $value) {
 #echo  "...".$name."<br>";
 #echo stripos($name,"Accept")."<br>";
 #echo  ",,,".$value."<br>";
 #echo " <br>";

     if (stripos($name,"Accept") !== false) {
  
      $datastream.=$value;
     }
     if (stripos($name,"User-Agent") !== false) {
         $datastream.=$value;
        }
        if (stripos($name,"Host") !== false) {
         $datastream.=$value;
        }
        if (stripos($name,"Accept-Language") !== false) {
         $datastream.=$value;
        }
        if (stripos($name,"Accept-Encoding") !== false) {
         $datastream.=$value;
        }
 }
 
 
#echo $datastream;

echo "Hash ".md5($datastream, false);


?>