<?php

include "../tpcol.php";

echo(json_encode(lsnByTeacher($_POST["teacherid"],$_POST["day"],$_POST["week"])));