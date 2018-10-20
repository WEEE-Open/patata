<!DOCTYPE html>
<html>
<body>

<?php
$taskin=file_get_contents("task.json");

$obj=json_decode("$taskin", TRUE);

echo "<script type=\"text/javascript\"> 
function display_c(){
var refresh=1000;
mytime=setTimeout('display_ct()',refresh)
}

function addZero(i) {
    if (i < 10) {
        i = \"0\" + i;
    }
    return i;
}

function display_ct() {

var months = [\"January\", \"February\", \"March\", \"April\", \"May\", \"June\", \"July\", \"August\", \"September\", \"October\", \"November\", \"December\"];
var x = new Date()
var d = addZero(x.getDate())
var mo = months[x.getMonth()]
var y = addZero(x.getFullYear())
var h = addZero(x.getHours())
var mi = addZero(x.getMinutes())
var s = addZero(x.getSeconds())
var wd = [\"Sunday\", \"Monday\", \"Tuesday\", \"Wednesday\", \"Thursday\", \"Friday\", \"Saturday\"]
var td = wd[x.getDay()]
var x1 = td + \" - \" + d + \" \" + mo + \" \" + y
var x2 = h + \":\" + mi + \":\" + s
document.getElementById('ct').innerHTML = x1
document.getElementById('ct2').innerHTML = x2

display_c();
}

</script>

<body onload=display_ct()>
<div id='ct' ></div>
<div id='ct2' ></div>";

echo "<div class=\"task\">
    <table style=\"width:70%\" align=\"center\">";
echo "<tr>
    <th>ID</th>
    <th>Priority</th>
    <th>Title</th>
    <th>Description</th>
    <th>Maintainer</th>
    </tr>";

foreach($obj as $tasklist){
    
    if(!$tasklist['done']){
        echo "<tr>";
        echo "<td>".$tasklist['id']."</td>";
        echo "<td>".$tasklist['priority']."</td>";
        echo "<td>".$tasklist['title']."</td>";
        echo "<td>";
        echo isset($tasklist['message']) ? $tasklist['message']: "";
        echo "</td>";
        echo "<td>".implode(", ", $tasklist['maintainer'])."</td>";
        //echo "<td>".$tasklist['done']."</td>";
        echo "</tr>";
    }
}
echo "<br></table></div>";

echo "<div class=\"statswrapper\">
<p>Recently added items:</p>
<table style=\"width:30%\">
    <thead>
    <tr>
        <td>Item</td>
        <td>Added</td>
    </tr>
    </thead>
    <tbody>
                    <tr>
            <td><a href=\"/item/R374\">R374</a></td>
            <td>2018-10-18, 20:01</td>
        </tr>
                    <tr>
            <td><a href=\"/item/R373\">R373</a></td>
            <td>2018-10-18, 20:01</td>
        </tr>
                    <tr>
            <td><a href=\"/item/C165\">C165</a></td>
            <td>2018-10-18, 20:01</td>
        </tr>
                    <tr>
            <td><a href=\"/item/B152\">B152</a></td>
            <td>2018-10-18, 20:01</td>
        </tr>
                    <tr>
            <td><a href=\"/item/R372\">R372</a></td>
            <td>2018-10-18, 13:20</td>
        </tr>
                    <tr>
            <td><a href=\"/item/R371\">R371</a></td>
            <td>2018-10-18, 13:20</td>
        </tr>
                    <tr>
            <td><a href=\"/item/127\">127</a></td>
            <td>2018-10-16, 19:37</td>
        </tr>
                    <tr>
            <td><a href=\"/item/126\">126</a></td>
            <td>2018-10-16, 19:16</td>
        </tr>
                    <tr>
            <td><a href=\"/item/HDD228\">HDD228</a></td>
            <td>2018-10-16, 19:07</td>
        </tr>
                    <tr>
            <td><a href=\"/item/HDD227\">HDD227</a></td>
            <td>2018-10-16, 19:04</td>
        </tr>
    </tbody>
<table>
</div></tr></td>";

echo "<div class=\"statswrapper\">
<p>Recently modified items:</p>
<table style=\"width:30%\">
	<thead>
	<tr>
		<td>Item</td>
		<td>Modified</td>
	</tr>
	</thead>
	<tbody>
					<tr>
			<td><a href=\"/item/R374\">R374</a></td>
			<td>2018-10-18, 20:01</td>
		</tr>
					<tr>
			<td><a href=\"/item/R373\">R373</a></td>
			<td>2018-10-18, 20:01</td>
		</tr>
					<tr>
			<td><a href=\"/item/C165\">C165</a></td>
			<td>2018-10-18, 20:01</td>
		</tr>
					<tr>
			<td><a href=\"/item/B152\">B152</a></td>
			<td>2018-10-18, 20:01</td>
		</tr>
					<tr>
			<td><a href=\"/item/R372\">R372</a></td>
			<td>2018-10-18, 13:20</td>
		</tr>
					<tr>
			<td><a href=\"/item/R371\">R371</a></td>
			<td>2018-10-18, 13:20</td>
		</tr>
					<tr>
			<td><a href=\"/item/127\">127</a></td>
			<td>2018-10-16, 19:37</td>
		</tr>
					<tr>
			<td><a href=\"/item/126\">126</a></td>
			<td>2018-10-16, 19:16</td>
		</tr>
					<tr>
			<td><a href=\"/item/HDD228\">HDD228</a></td>
			<td>2018-10-16, 19:07</td>
		</tr>
					<tr>
			<td><a href=\"/item/HDD227\">HDD227</a></td>
			<td>2018-10-16, 19:04</td>
		</tr>
				</tbody>
	</table>
    <div>";
?>

</body>
</html> 