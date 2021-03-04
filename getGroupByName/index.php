<?php

include "../tpcol.php";

echo(json_encode(getGroupIdByName($_POST["fname"])));