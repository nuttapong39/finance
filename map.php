<!doctype html>
<html>
    <head>
        <link rel="shortcut icon" type="image/x-icon" href="pic/gis-icon.jpg" />
        <title> ระบบGIS พิกัดหลังคาเรือน </title>
        <script src="https://maps.google.com/maps/api/js?key=AIzaSyDfeSc7kRuhm_txbcXq74HZ1YdMvvy9m9M&v=3.2&sensor=false&language=th&callback=initialize"
   async defer></script>
    </head>
    <body onload ='start()'>
        <?php
          $address= $_REQUEST['address'];
          $lat=$_REQUEST['lat'];
          $longs=$_REQUEST['longs'];
          $giss=$_REQUEST['giss'];
        ?>
        <div id='main' style='width:100; height:400px; '></div>
        <form class="form-horizontal" name="showmap">
        <input type="hidden" name="address" id="address" value="<?php echo $address; ?>"></input>
        <input type="hidden" name="lats" id="lats" value="<?php echo round($lat,6); ?>"></input>
        <input type="hidden" name="longs" id="longs" value="<?php echo round($longs,6); ?>"></input>
        <input type="hidden" name="giss" id="giss" value="<?php echo $giss; ?>"></input>
        </form>
        <script>
      function start(){
        var address = document.showmap.address.value;
        var lats = document.showmap.lats.value;
        var longs = document.showmap.longs.value;
        var giss = document.showmap.giss.value;
        var mapOptions = {
          zoom: 18,
          center: new google.maps.LatLng(lats, longs)
        };
          
        var maps = new google.maps.Map(document.getElementById("main"),mapOptions);
        
        var marker = new google.maps.Marker({
           position: new google.maps.LatLng(lats, longs),
           map: maps,
           title: giss,
           //icon: 'images/camping-icon.png',
        });

        var info = new google.maps.InfoWindow({
          content : '<div style="font-size: 14px" align ="center">'+address+'</div><br/><div style="font-size: 14px" align ="center">พิกัด: '+giss+'</div>'
        });

        google.maps.event.addListener(marker, 'click', function() {
          info.open(maps, marker);
        });

    }
    </script>
    </body>
</html>