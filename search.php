<?php 
require_one('connect_db.php');
mysql_select_db('finance');
$w=$_GET['term'];
$sql="SELECT TypesName FROM types where TypesName Like '{$w}%'";
$rs=mysql_query($sql);
$json=array();
while($row = mysql_fetch_assoc($rs)){
	$json[] = $row['TypesName'];
}
mysql_free_result($rs);
mysql_close($connect_db);
$json = json_encode($json);
echo $json;

?>