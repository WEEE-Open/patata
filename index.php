<!DOCTYPE html>
<html>
<body>

<?php
$taskin=file_get_contents("task.json");

$obj=json_decode("$taskin", TRUE);

echo "<table style=\"width:100%\">";
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
        // echo "<td>".$tasklist['done']."</td>";
        echo "</tr>";
    }
}
?>

</body>
</html> 