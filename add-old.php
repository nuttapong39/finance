<!DOCTYPE html>
<?php 
error_reporting(~E_NOTICE);
date_default_timezone_set('Asia/Bangkok');
include "connect_db.php"; 

@session_start();
$PID=$_SESSION["PID"];
$Names=$_SESSION["Names"];
$TypeUser=$_SESSION["TypeUser"];

if($PID== ""){
    $PID=$_GET["PID"];
}
if($Names == ""){
	$sql = "SELECT * FROM employee where PID = $PID";
	$result = $conn->query($sql);
	if ($result->num_row > 0){
		while($row = $result->fetch_assoc()) {
			$Names=$row["Names"];
			$TypeUser=$row["TypeUser"];
		}
	}
}
?>
<html>
<div>
		<?php include 'header.php'; ?>
	</div>
<head>
    <script>
                        if (top.location != location) {
                    top.location.href = document.location.href ;
                  }
                                $(function(){
                                        window.prettyPrint && prettyPrint();
                                        $('#dp1').datepicker({
                                                format: 'dd-mm-yyyy'
                                        });
                                        $('#dp2').datepicker();
                                        $('#dp3').datepicker();
                                        $('#dpYears').datepicker();
                                        $('#dpMonths').datepicker();

                                        var startDate = new Date(2012,1,20);
                                        var endDate = new Date(2012,1,25);
                                        $('#dp4').datepicker()
                                                .on('changeDate', function(ev){
                                                        if (ev.date.valueOf() > endDate.valueOf()){
                                                                $('#alert').show().find('strong').text('The start date can not be greater then the end date');
                                                        } else {
                                                                $('#alert').hide();
                                                                startDate = new Date(ev.date);
                                                                $('#startDate').text($('#dp4').data('date'));
                                                        }
                                                        $('#dp4').datepicker('hide');
                                                });
                                        $('#dp5').datepicker()
                                                .on('changeDate', function(ev){
                                                        if (ev.date.valueOf() < startDate.valueOf()){
                                                                $('#alert').show().find('strong').text('The end date can not be less then the start date');
                                                        } else {
                                                                $('#alert').hide();
                                                                endDate = new Date(ev.date);
                                                                $('#endDate').text($('#dp5').data('date'));
                                                        }
                                                        $('#dp5').datepicker('hide');
                                                });

                        // disabling dates
        var nowTemp = new Date();
        var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

        var checkin = $('#dpd1').datepicker({
          onRender: function(date) {
            return date.valueOf() < now.valueOf() ? 'disabled' : '';
          }
        }).on('changeDate', function(ev) {
          if (ev.date.valueOf() > checkout.date.valueOf()) {
            var newDate = new Date(ev.date)
            newDate.setDate(newDate.getDate() + 1);
            checkout.setValue(newDate);
          }
          checkin.hide();
          $('#dpd2')[0].focus();
        }).data('datepicker');
        var checkout = $('#dpd2').datepicker({
          onRender: function(date) {
            return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
          }
        }).on('changeDate', function(ev) {
          checkout.hide();
        }).data('datepicker');
		});
	</script>   
    
</head>
<body>
<div class="container">
<div class="row">
  		<ul class="list-group">
                    <li class="list-group-item">
                    <form class="form-horizontal" name="gis" method="get" action="<?php echo $_SERVER['SCRIPT_NAME'];?>">
                    <div class="panel panel-success"><p class="bg-success">&nbsp;<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> บันทึกใบลา</p>
                         <div class="form-group" align="right">
                			<label class="col-md-4" contorl-label >ประเภทการลา :</label>
							<div class="col-md-3"  align="left">
								<select class="form-control" name="Type_Leave" id="Type_Leave">
								<option>-- เลือกประเภทการลา --</option>
								<option value='1'>ลาพักผ่อน</option>
								<option value='2'>ลาป่วย</option>
								<option value='3'>ลากิจ</option>
								<option value='4'>ลาคลอด</option>
								<option value='5'>สาย</option>
								</select>
							</div>
						</div>

                        <div class="form-group" align="right">
                			<label class="col-md-4" contorl-label>ตั้งแต่วันที่ :</label>
							<div class="col-md-3"  align="left">
								<div class="well">
				                    <input class="span2"  data-date-format="dd/mm/yyyy" id="dp3" type="text">
				                </div>
							</div>
						</div>

						<div class="form-group" align="right">
                			<label class="col-md-4" contorl-label>ถึงวันที่ :</label>
							<div class="col-md-3"  align="left">
								<input type="text" class="form-control"  name="dateInput" id="dateInput" placeholder="วัน/เดือน/ปี เช่น 01/01/2564">	
							</div>
						</div>
                            
                        <div class="form-group">
                			<label class="col-md-4" contorl-label></label>
							<div class="col-md-1"  align="left">
								<button type="botton" class="btn btn-primary">ตกลง</button>
								<input type="hidden" name="PID" value="<?php echo $PID;?>">
							</div>
							<div class="col-md-1"  align="left">
								<button type="reset" class="btn btn-default" value="1">ยกเลิก</button>
							</div>
							<div class="col-md-4"  align="left">
								
							</div>
							
						</div>
                        </div><!-- /.panal 1 -->						
					</form>
</div>
</div>
<div class="menu">
<?php include 'footer.php';?>
</div>

</body>
</html>