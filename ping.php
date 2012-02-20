<?php

if(isset($_GET['updateTime'])){
  echo date('g:i:s a');
  return 0;
}

if(isset($_GET['ping'])){
  // if this is ever noticably slower, i'll pass it stuff when called
  $xml = simplexml_load_file("config.xml");
  if($_GET['ping'] == ""){
    $host = "127.0.0.1";
  }else{
    $host = $_GET['ping'];
  }
  $out = trim(shell_exec('ping -n -q -c 1 -w '.$xml->backend->timeout
              .' '.$host.' | grep received | awk \'{print $4}\''));
  $id = str_replace('.','_',$host);

  if(($out == "1") || ($out == "0")){
    echo json_encode(array("id"=>"h$id","res"=>"$out"));
  }else{
    ## if it returns nothing, assume network is messed up
    echo json_encode(array("id"=>"h$id","res"=>"0"));
  }
}

?>
