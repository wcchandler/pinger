<?php

if($_GET['host'] == ""){
  $host = "127.0.0.1";
}else{
  $host = $_GET['host'];
}
$out = trim(shell_exec('ping -n -q -c 1 -w 1 '.$host.' | grep received | awk \'{print $4}\''));

$id = str_replace('.','_',$host);

if(($out == "1") || ($out == "0")){
  echo json_encode(array("id"=>"h$id","res"=>"$out"));
}else{
  ## if it returns nothing, assume network is messed up
  echo json_encode(array("id"=>"h$id","res"=>"0"));
}

?>
