<?php

include("../config/database.php");

$q=$_GET['q'];

$result=mysqli_query($conn,"
SELECT * FROM books
WHERE title LIKE '%$q%'
LIMIT 10
");

while($row=mysqli_fetch_assoc($result)){

echo "<p>".$row['title']." by ".$row['author']."</p>";

}