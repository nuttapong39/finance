
<?php
  include "connect_db.php";
    $OfficeId = $_REQUEST['OfficeId'];
    $OfficeName = $_REQUEST['OfficeName'];
    $Department = $_REQUEST['Department'];
    $Work = $_REQUEST['Work'];
    $No = $_REQUEST['No'];
    $Tombol = $_REQUEST['Tombol'];
    $District = $_REQUEST['District'];
    $Province = $_REQUEST['Province'];
    $Postcode = $_REQUEST['Postcode'];
    $BookNo = $_REQUEST['BookNo'];
    $BookNoDept = $_REQUEST['BookNoDept'];
    $Tel = $_REQUEST['Tel'];
    $Finance = $_REQUEST['Finance'];
    $Manager = $_REQUEST['Manager'];
    $Parcel = $_REQUEST['Parcel'];
    $HParcel = $_REQUEST['HParcel'];
    $Director = $_REQUEST['Director'];
    $PID = $_REQUEST['PID'];

    //$result_save = mysql_query($conn,$sql_save);
    
    $sql_edit= "UPDATE `office` SET `OfficeName`='$OfficeName',`Department`='$Department',`Work`='$Work',`No`='$No',`Tombol`='$Tombol',`District`='$District',`Province`='$Province',`Postcode`='$Postcode',`BookNo`='$BookNo',`BookNoDept`='$BookNoDept',`Tel`='$Tel',`Finance`='$Finance',`Manager`='$Manager',`Parcel`='$Parcel',`HParcel`='$HParcel',`Director`='$Director',`PID`='$PID' WHERE OfficeId='$OfficeId'";
    if($conn->query($sql_edit)){
      header("location:config1.php");
      //header("location:accounting.php");
    }
  ?>