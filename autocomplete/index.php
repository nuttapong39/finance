<?php
include_once 'connect.php';
$sql = "SELECT * FROM types" ;
$query = $conn->query($sql);
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>itoffside.com::AJAX</title>
        <link type="text/css" rel="stylesheet" href="jquery.autocomplete.css" />
        <script type="text/javascript" src="jquery-1.11.2.min.js"></script>
        <script type="text/javascript" src="jquery.autocomplete.js"></script>
        <script type="text/javascript">
            var states = [
<?php
$province = "";
while ($result = $query->fetch_assoc()) {
    $province .= "'" . $result['TypesName'] . "',";
}
echo rtrim($province, ",");
?>
            ];
            $(function () {
                $("input").autocomplete({
                    source: [states]
                });
            });
        </script>
        <style>
            .xdsoft_autocomplete_dropdown{
                padding: 10px;
            }
        </style>
    </head>
    <body style="margin-top: 30px;margin-left: 40%;">
        <form name="searchform" action="#" method="POST">
            <input type="text" name="states" value="" style="border: 1px solid #cccccc; height: 30px;width: 300px;padding: 5px;"/>
        </form>
    </body>
</html>
