<?php

use common\helpers\Html;

$mail = mailparse_msg_create();
mailparse_msg_parse($mail,$model->message);
$struct = mailparse_msg_get_structure($mail);

if ($type) {
	foreach ($struct as $st) {
		$section = mailparse_msg_get_part($mail, $st);
		$info = mailparse_msg_get_part_data($section);

		if ($info['content-type'] == 'text/'.$type) {
			ob_start();
			mailparse_msg_extract_part($section, $model->message);
			$contents = ob_get_contents();
			ob_end_clean();
			echo ($type == 'plain') ? nl2br($contents) : $contents;
		}
	}
}
else {
	echo Html::tag('pre', Html::encode($model->message));
}
