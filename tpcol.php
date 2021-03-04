<?php



// Id группы, День недели 1 - 7, цвет недели (0 - красная, 1 - зеленая, -1/ничего - автоматическое определение группы)
function lsnByGroup($groupid, $day, $week = -1)
{
    if ($week == -1) {
        $week = getWeekColor();
    }
    $url = 'http://www.tpcol.ru/asu/timetablestud.php?f=1';
    $data = array('group' => $groupid, 'day' => $day, 'week' => $week);
    $doc = getDoc($url, $data);
    $xpath = new DOMXpath($doc);
    $les_arr = array();
    $rasp = true; // расписание на сегодня есть 
    $zamtood = true; // замены на сегодня есть 
    foreach ($xpath->query("/html/body/table//tr[1]/td[2]/table[2]//tr[1]/td[2]/table//tr/td/table//tr/td[@class='head3']/text()") as $element) {
        $alert = $element->nodeValue;
        if (strpos($alert, "расписания нет!") !== false) {
            $rasp = false;
        } else if (strpos($alert, "нет") !== false and strpos($alert, "сегодня") !== false) {
            $zamtood = false;
        }
    }
    if ($rasp == false and $zamtood == false)
        $zamtood = 3;
    else if (($rasp == false and $zamtood == true) or ($rasp == true and $zamtood == false))
        $zamtood = 4;
    else if ($zamtood == true and $rasp == true)
        $zamtood = 5;
    $today = getTable($doc, 3, 2);
    $zamToday = getTable($doc, $zamtood, 3);
    $zamTomorrow = getTable($doc, $zamtood + 2, 3);
    if (date("w") == strval($day))
        $response = array_replace($today, $zamToday);
    else if (date("w") == strval($day - 1))
        $response = array_replace($today, $zamTomorrow);
    else
        $response = $today;

    $pari = array();
    foreach ($response as $npari => $para) {
        array_push($pari, ['id' => $npari, "title" => $para]);
    }
    return ($pari);
}

// Id учителя, День недели 1 - 7, цвет недели (0 - красная, 1 - зеленая, -1/ничего - автоматическое определение группы)
function lsnByTeacher($teacherid, $day = -1, $week = -1)
{
    if ($week == -1) {
        $week = getWeekColor();
    }
    if ($day == -1) {
        $day = date("w");
    }
    $url = 'http://www.tpcol.ru/asu/timetableprep.php?f=1';
    $data = array('fio' => $teacherid, 'day' => $day, 'week' => $week);
    $doc = getDoc($url, $data);
    $xpath = new DOMXpath($doc);
    $les_arr = array();
    $rasp = true; // расписание на сегодня есть 
    $zamtood = true; // замены на сегодня есть 
    foreach ($xpath->query("/html/body/table//tr[1]/td[2]/table[2]//tr[1]/td[2]/table//tr/td/table//tr/td[@class='head3']/text()") as $element) {
        $alert = $element->nodeValue;
        if (strpos($alert, "расписания нет!") !== false) {
            $rasp = false;
        } else if (strpos($alert, "нет") !== false and strpos($alert, "сегодня") !== false) {
            $zamtood = false;
        }
    }
    if ($rasp == false and $zamtood == false)
        $zamtood = 3;
    else if (($rasp == false and $zamtood == true) or ($rasp == true and $zamtood == false))
        $zamtood = 4;
    else if ($zamtood == true and $rasp == true)
        $zamtood = 5;
    $today = getTable($doc, 3, 2);
    $zamToday = getTable($doc, $zamtood, 3);
    $zamTomorrow = getTable($doc, $zamtood + 2, 3);
    if (date("w") == strval($day))
        $response = array_replace($today, $zamToday);
    else if (date("w") == strval($day - 1))
        $response = array_replace($today, $zamTomorrow);
    else
        $response = $today;
    return ($response);
}
//Принимает документ, смещение.Возращает массив-лист [пара№ -> занятие]
function getTable($doc, $table, $col)
{
    $xpath = new DOMXpath($doc);
    $ids = array();
    foreach ($xpath->query("/html/body/table//tr[1]/td[2]/table[2]//tr[1]/td[2]/table//tr/td/table[$table]//tr/td[2]/table//tr[1]/td[2]/table//tr[@class='ttext']/td[1]/text()") as $id) {
        array_push($ids, trim($id->nodeValue, " \t\n\r\0\x0B\xC2\xA0"));
    }
    $lesns = array();
    foreach ($xpath->query("/html/body/table//tr[1]/td[2]/table[2]//tr[1]/td[2]/table//tr/td/table[$table]//tr/td[2]/table//tr[1]/td[2]/table//tr[@class='ttext']/td[$col]/text()") as $les) {
        if ($les->nodeValue != " ") {
            array_push($lesns, trim($les->nodeValue, " \t\n\r\0\x0B\xC2\xA0"));
        }
    }
    $idToLes = array_combine($ids, $lesns);
    return ($idToLes);
}
// Id группы
function getExams($groupid)
{
    $url = 'http://www.tpcol.ru/asu/exams.php?f=0';
    $data = array('group' => $groupid);
    $doc = getDoc($url, $data);
    $exams = [];
    $xpath = new DOMXpath($doc);
    $curarr = 0;
    foreach ($xpath->query("/html/body/table//tr[1]/td[2]/table[2]//tr[1]/td[2]/table//tr/td/table[2]//text()") as $i => $element) {
        if ($i > 4) {
            $blankless = trim($element->nodeValue, "\t\n\r\0\x0B\xC2\xA0");
            if ($i % 4 == 1)
                $exams[$curarr] = ["date" => $blankless];
            elseif ($i % 4 == 2)
                $exams[$curarr] += ["les" => preg_replace("/\s+/", " ", $blankless)];
            elseif ($i % 4 == 3)
                $exams[$curarr] += ["teacher" => $blankless];
            elseif ($i % 4 == 0)
                $exams[$curarr++] += ["cab" => $blankless];
        }
    }
    return ($exams);
}
// Возвращает цвет недели
function getWeekColor()
{
    $url = 'http://www.tpcol.ru/asu/timetablestud.php?f=0';
    $data = array();
    $doc = getDoc($url, $data);
    $xpath = new DOMXpath($doc);
    $element = $xpath->query("/html/body/table//tr[1]/td[2]/table[2]//tr[1]/td[2]/table//tr/td/table//tr[2]/td/table//tr/td[2]/font/text()");
    $week = trim($element->item(0)->nodeValue, "\t\n\r\0\x0B\xC2\xA0");
    $today = date("w");
    if ($week == "КРАСНАЯ неделя") {
        if ($today == 7)
            return (1);
        return (0);
    } else {
        if ($today == 7)
            return (0);
        return (1);
    }
}

// Возвращает словарь "Имя группы => её ид"
function getGroupToId()
{
    $url = 'http://www.tpcol.ru/asu/timetablestud.php?f=0';
    $data = array();
    $doc = getDoc($url, $data);
    $xpath = new DOMXpath($doc);
    $grouptoid = array();

    foreach ($xpath->query("/html/body/table//tr[1]/td[2]/table[2]//tr[1]/td[2]/table//tr/td/table//tr[2]/td/table//tr/td[2]/form/table//tr[1]/td[2]/select/option") as $element) {
        $groupname = $element->nodeValue;
        $id = $xpath->query("@value", $element)->item(0)->nodeValue;
        $grouptoid += [trim($groupname, " ") => $id];
    }
    return ($grouptoid);
}
// Возвращает словарь "префикс группы => [постфикс группы => id],"
function getAdvGroupToId()
{
    $url = 'http://www.tpcol.ru/asu/timetablestud.php?f=0';
    $data = array();
    $doc = getDoc($url, $data);
    $xpath = new DOMXpath($doc);
    $groupstoid = array();

    foreach ($xpath->query("/html/body/table//tr[1]/td[2]/table[2]//tr[1]/td[2]/table//tr/td/table//tr[2]/td/table//tr/td[2]/form/table//tr[1]/td[2]/select/option") as $element) {
        $groupname = $element->nodeValue;
        $groupname = trim($groupname, " ");
        $groupre = preg_split('[-]', $groupname)[0];
        $groupost = preg_split('[-]', $groupname)[1];
        $id = $xpath->query("@value", $element)->item(0)->nodeValue;
        if ($groupstoid[$groupre])
            $groupstoid[$groupre] += [$groupost => $id];
        else
            $groupstoid[$groupre] = [$groupost => $id];
    }
    return ($groupstoid);
}

// Возвращает словарь "['id' => его ид,'secname' => фамилия,'name' => имя]"
function getTeacherToId()
{
    $url = 'http://www.tpcol.ru/asu/timetableprep.php?f=0';
    $data = array();
    $doc = getDoc($url, $data);
    $xpath = new DOMXpath($doc);
    $teachertoid = array();

    foreach ($xpath->query("/html/body/table//tr[1]/td[2]/table[2]//tr[1]/td[2]/table//tr/td/table//tr[2]/td/table//tr/td[2]/form/table//tr[1]/td[2]/select/option") as $element) {
        $fullteachername = $element->nodeValue;
        $secname = preg_split('/[\s]/', $fullteachername)[0];
        $name = preg_split('/[\s]/', $fullteachername)[1];
        $id = $xpath->query("@value", $element)->item(0)->nodeValue;
        array_push($teachertoid, ["id" => $id, "secname" => $secname, "name" => $name]);
    }
    return ($teachertoid);
}

// Возвращает словарь "имя группы => её ид"
function getGroupIdByName($fname)
{
    $fname = mb_strtolower($fname);
    $groups = getGroupToId();

    $possible_groups = [];
    foreach ($groups as $name => $id) {
        $groupname = mb_strtolower($name);
        $groupre = mb_strtolower(preg_split('[-]', $groupname)[0]);
        $groupost = mb_strtolower(preg_split('[-]', $groupname)[1]);
        if (strpos($fname, $groupre) !== false and strpos($fname, $groupost) !== false)
            array_push($possible_groups, ["name" => $name, "id" =>  $id]);
    }

    $max = 0;

    foreach ($possible_groups as $possible_group) {
        if (strlen($possible_group["name"]) > $max) {
            $group = $possible_group;
            $max = strlen($possible_group["name"]);
        }
    }
    return ($group);
}

function getDoc($url, $args)
{
    $doc = new DOMDocument();

    libxml_use_internal_errors(true);
    // use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($args)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) { /* Handle error */
    }
    //$result = mb_convert_encoding($result, "UTF-8", "cp1251");
    $doc->loadHTML($result);
    return ($doc);
}

function getStudents()
{
    $conn = mysqli_connect("127.0.0.1", "root", "5aSETIMo44hiHukA8O5ew1Xewu1ARa", "smartcollege");
    $res = $conn->query("SELECT * FROM students");
    if ($res != null) {
        $res->data_seek(0);
        while ($row = $res->fetch_assoc()) {
            $res->data_seek(0);
            $studs = array();
            while ($row = $res->fetch_assoc()) {
                array_push($studs, ['name' => $row['name'], 'id' => $row['id'], 'groupid' => $row['group']]);
            }
        }
        return ($studs);
    }
}
