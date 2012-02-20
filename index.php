<!DOCTYPE html>
<html>
<head>
  <title>pinger - Simple Pinging Webapp</title>
  <link rel="stylesheet" href="main.css" type="text/css" />
  <!-- <script src="jquery-latest.js"></script> -->
  <script src="http://code.jquery.com/jquery-latest.js"></script>
</head>
<body>

<?php
  // loads up config.xml as simplexml object
  $xml = simplexml_load_file("config.xml");

  // status bar at the top, time that gets updated every time would be nice
  echo "<div id=\"stats\" class=\"category\">update interval: ".
       ($xml->frontend->refresh/1000)."s; last updated: <span id=\"update\">".
       "</span> (server time)</div>";
  echo "<div id=\"key\" class=\"category\">key: <span class=\"item up\">up".
       "</span><span class=\"item down\">down</span></div>";
  //print_r($xml);
  // echos out a div for each element/device
  // sample:
  //   <div id="h172_20_10_1">172.20.10.1</div>
  //   h is prepended before the ip to as an HTML rule
  
  foreach($xml->devices->children() as $device){
    $name = $device->getName();
    echo "<div class=\"category\"><span id=\"header\">$name</span><br />\n";
    foreach($device->children() as $end){
      $str = str_replace('.','_',$end->ip);
      if($xml->frontend->nameorip == "name"){
        echo "<span class=\"item\" id=\"h$str\" title=\"$end->ip\">$end->name</span>\n";
      }elseif($xml->frontend->nameorip == "ip"){
        echo "<span class=\"item\" id=\"h$str\" title=\"$end->name\">$end->ip</span>\n";
      }else{
        // if neither is chosen, both will be picked
        echo "<span class=\"item\" id=\"h$str\">$end->name - $end->ip</span>\n";
      }
      $js_str = $js_str."    $.getJSON(\"ping.php?ping=$end->ip\",callBack);\n";
    }
    echo "</div><br />";
  }

?>  
  
<script>


  function pinger(){
    // php'd string, compiled above
    // this makes things more versatile
    // 
    <?php echo $js_str; ?>
    $("#update").load("ping.php?updateTime");
    setTimeout(pinger, <?php echo $xml->frontend->refresh ?>);
  }

  function callBack(json){
    if(json.res == "1"){
      if($("#"+json.id).hasClass("up")){ 
        // it was up and is now up, do nothing
      }else if($("#"+json.id).hasClass("down")){ 
        // it was down and is now up, get rid of down, add up
        $("#"+json.id).toggleClass("down"); 
        $("#"+json.id).toggleClass("up");
      }else{
        // it was nothing, now up, add up
        $("#"+json.id).toggleClass("up");
      }
    }else if(json.res == "0"){
      if($("#"+json.id).hasClass("down")){ 
        // it was down and is now down, do nothing
      }else if($("#"+json.id).hasClass("up")){ 
        // it was down and is now up, get rid of up, add down
        // add an alert or something here, news feed???
        $("#"+json.id).toggleClass("up"); 
        $("#"+json.id).toggleClass("down");
      }else{
        // it was nothing, now down, add down
        $("#"+json.id).toggleClass("down");
      }
    }else{
      // this means my script failed and it's setting stuff to black
      $("#"+json.id).css("background-color","#000000"); 
      $("#"+json.id).css("color","#EEEEEE"); 
    }
  }


  pinger();
</script>

</body>
</html>
