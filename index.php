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
  // uncomment config.xml if rolling fresh, good.xml is what I use for $WORK
  $xml = simplexml_load_file("config.xml");
  //$xml = simplexml_load_file("good.xml");

  // status bar at the top, time gets updated everytime it gets hit
  // to change the update frequency, edit
  //     config.xml config -> frontend -> refresh
  echo "<div id=\"top\"><div id=\"feed\">&nbsp;</div>".
       "<div id=\"stats\" class=\"category\">timeout: ".
       $xml->backend->timeout."s; update interval: ".
       ($xml->frontend->refresh/1000)."s; last updated: <span id=\"update\">".
       "</span> (server time)</div>";
  echo "<div id=\"key\" class=\"category\">key: <span class=\"item up\">up".
       "</span><span class=\"item down\">down</span></div></div>";

  // echos out a div for each element/device
  // sample:
  //   <div id="h172_20_10_1">172.20.10.1</div>
  //   h is prepended before the ip to satisfy an HTML rule requiring ids
  //   to start with a letter
 


  foreach($xml->devices as $action){
    //print_r($action);
    foreach($action->children() as $child){
      //  device
      //    ping
      //      switch
      //        main
      $name = $child->getName();
      echo "<div class=\"category\"><span id=\"header\">$name</span><br />\n";
      foreach($child->children() as $type){
        $name = $type->getName();
        echo "<div class=\"category inner1\"><span id=\"header\">$name</span><br />\n";
        foreach($type->children() as $loc){
          $name = $loc->getName();
          echo "<div class=\"category inner2\"><span id=\"header\">$name</span><br />\n";
          foreach($loc->children() as $end){
            $str = str_replace('.','_',$end->ip);

            if($child->getName() == "socket"){
              $js_str = $js_str."    $.getJSON(\"ping.php?socket=$end->ip:$end->port\",callBack);\n";
            }else{
              $js_str = $js_str."    $.getJSON(\"ping.php?ping=$end->ip\",callBack);\n";
            }


            if($xml->frontend->nameorip == "name"){
              if($child->getName() == "socket"){
                echo "<span class=\"item\" id=\"h".$str."_".$end->port."\" title=\"$end->ip\">$end->name:$end->port</span>\n";
              }else{
                echo "<span class=\"item\" id=\"h$str\" title=\"$end->ip\">$end->name</span>\n";
              }
            }elseif($xml->frontend->nameorip == "ip"){
              if($child->getName() == "socket"){
                echo "<span class=\"item\" id=\"h".$str."_".$end->port."\" title=\"$end->name\">$end->ip:$end->port</span>\n";
              }else{
                echo "<span class=\"item\" id=\"h$str\" title=\"$end->name\">$end->ip</span>\n";
              }
            }else{
              // if neither is chosen, both will be picked
              if($child->getName() == "socket"){
                echo "<span class=\"item\" id=\"h".$str."_".$end->port."\" title=\"$end->ip\">$end->name ($end->ip) $end->port</span>\n";
              }else{
                echo "<span class=\"item\" id=\"h$str\" title=\"$end->ip\">$end->name ($end->ip)</span>\n";
              }
            }


          }
          echo "</div><br />";
        }
        echo "</div><br />";
      }
      echo "</div><br />";
    }
    echo "</div><br />";
  } // end of foreach   

  /*
  foreach($xml->devices->children() as $device){
    $name = $device->getName();
    echo "<div class=\"category\"><span id=\"header\">$name</span><br />\n";
    foreach($device->children() as $end){
      $str = str_replace('.','_',$end->ip);
      if($xml->frontend->nameorip == "name"){
        if($end->getName() == "socket"){
          echo "<span class=\"item\" id=\"h".$str."_".$end->port."\" title=\"$end->ip\">$end->name:$end->port</span>\n";
        }else{
          echo "<span class=\"item\" id=\"h$str\" title=\"$end->ip\">$end->name</span>\n";
        }
      }elseif($xml->frontend->nameorip == "ip"){
        if($end->getName() == "socket"){
          echo "<span class=\"item\" id=\"h".$str."_".$end->port."\" title=\"$end->name\">$end->ip:$end->port</span>\n";
        }else{
          echo "<span class=\"item\" id=\"h$str\" title=\"$end->name\">$end->ip</span>\n";
        }
      }else{
        // if neither is chosen, both will be picked
        if($end->getName() == "socket"){
          echo "<span class=\"item\" id=\"h".$str."_".$end->port."\" title=\"$end->name\">$end->name - $end->ip:$end->port</span>\n";
        }else{
          echo "<span class=\"item\" id=\"h$str\">$end->name - $end->ip</span>\n";
        }
      }
      if($end->getName() == "socket"){
        $js_str = $js_str."    $.getJSON(\"ping.php?socket=$end->ip:$end->port\",callBack);\n";
      }else{
        $js_str = $js_str."    $.getJSON(\"ping.php?ping=$end->ip\",callBack);\n";
      }
    }
    echo "</div><br />";
  } // end of foreach    */

?>  
  
<script>


  function pinger(){
    // php'd string, compiled above
    // this makes things more versatile
    // 
    $("#update").load("ping.php?updateTime");
    <?php echo $js_str; ?>
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
        $("#feed").prepend($('#'+json.id).text()+" came <span class=\"feedup\">up</span> at <b>"+$('#update').text()+"</b>; ");
      }else{
        // it was nothing, now up, add up
        $("#"+json.id).toggleClass("up");
        $("#feed").prepend($('#'+json.id).text()+" is <span class=\"feedup\">up</span>; ");
      }
    }else if(json.res == "0"){
      if($("#"+json.id).hasClass("down")){ 
        // it was down and is now down, do nothing
      }else if($("#"+json.id).hasClass("up")){ 
        // it was down and is now up, get rid of up, add down
        // add an alert or something here, news feed???
        $("#"+json.id).toggleClass("up"); 
        $("#"+json.id).toggleClass("down");
        $("#feed").prepend($('#'+json.id).text()+" went <span class=\"feeddown\">down</span> at <b>"+$('#update').text()+"</b>; ");
      }else{
        // it was nothing, now down, add down
        $("#"+json.id).toggleClass("down");
        $("#feed").prepend($('#'+json.id).text()+" is <span class=\"feeddown\">down</span>; ");
      }
    }else{
      // this means my script failed and it's setting stuff to black
      $("#"+json.id).css("background-color","#000000"); 
      $("#"+json.id).css("color","#EEEEEE"); 
    }
  }


  pinger();
</script>
<div id="footer">source available on <a href="https://github.com/wcchandler/pinger">github</a></div><br />
</body>
</html>
