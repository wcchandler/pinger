<!DOCTYPE html>
<html>
<head>
  <style>
    body{ font-size: 1.05em; font-family: Arial; color: #111; }
    div { background-color: #EEE; font-weight: bold; }
  </style>
  <!-- <script src="jquery-latest.js"></script> -->
  <script src="http://code.jquery.com/jquery-latest.js"></script>
</head>
<body>

<?php

  // loads up config.xml as simplexml object
  $xml = simplexml_load_file("config.xml");
  //print_r($xml);

  // echos out a div for each element/device
  // sample:
  //   <div id="h172_20_10_1">172.20.10.1</div>
  //   h is prepended before the ip to as an HTML rule
 
  foreach($xml->devices->switch as $device){
    $str = str_replace('.','_',$device->ip);
    echo "<div id=\"h$str\">$device->ip</div>\n";
  }

?>  
  
<script>


  function pinger(){
    <?php
    foreach($xml->devices->switch as $device){
      echo "    $.getJSON(\"ping.php?host=$device->ip\",callBack);\n";
      // this is used to add jQuery calls for each device to be updated
      // sample:
      //   $.getJSON("ping.php?host=172.20.1.80",callBack); 
    } ?>

    setTimeout(pinger, <?php echo $xml->frontend->refresh ?>);
  }

  function callBack(json){
    if(json.res == "1"){
      $("#"+json.id).css("background-color","#00CC00"); 
      $("#"+json.id).css("color","#111111"); 
    }else if(json.res == "0"){
      $("#"+json.id).css("background-color","#CC0000"); 
      $("#"+json.id).css("color","#EEEEEE"); 
    }else{
      $("#"+json.id).css("background-color","#666666"); 
      $("#"+json.id).css("color","#EEEEEE"); 
    }
  }


  pinger();
</script>

</body>
</html>
