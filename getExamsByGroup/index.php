<?php
include "../tpcol.php";

echo(json_encode(getExams($_POST["groupid"])));