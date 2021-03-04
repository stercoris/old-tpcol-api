<?php 

include "../tpcol.php";

echo(json_encode(lsnByGroup($_POST["groupid"],$_POST["day"])));