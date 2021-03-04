<?php 
include 'tpcol.php';

header('Content-type:application/json;charset=utf-8');
//	Для меня
// 	lsnByGroup (
//	id группы,
//	1-7 день недели,
//	0 - зеленая/1 - красная недели)
ob_end_clean();
header("Connection: close");
ignore_user_abort(true); // just to be safe
ob_start();
// Исполняемый

echo("\n Расписание через ID группы \n\n");
var_dump(lsnByGroup(556,2));
echo("\n Студенты \n\n");
var_dump(getStudents());
echo("\n Экзамен через ID группы \n\n");
var_dump(getExams(556));
echo("\n Лекции учителя через ID учителя \n\n");
var_dump(lsnByTeacher(2614,3));
echo("\n Неделя \n\n");
echo(getWeekColor());
echo("\n Группо по названию!!!\n('ад21') \n");
var_dump(getGroupIdByName("ад21"));
echo("\n('в41') \n");
var_dump(getGroupIdByName("в41"));
echo("\n ('21-вп') \n");
var_dump(getGroupIdByName("21-вп"));
echo("\n Учителя \n\n");
var_dump(getTeacherToId());
echo("\n Группы \n\n");
var_dump(getGroupToId());
echo("\n Группы \n\n");
var_dump(getAdvGroupToId());


// Исполняемый конец
$size = ob_get_length();
header("Content-Length: $size");
ob_end_flush(); // Strange behaviour, will not work
flush(); // Unless both are called !
// Исполняемый после

error_log("Text user will never see");

// Исполняемый после конец
// Do processing here 
sleep(30);
