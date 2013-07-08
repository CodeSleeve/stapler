<?php

return [
	'options' => [
		'url' => '/system/:class/:attachment/:id_partition/:style/:filename',
		'default_url' => '/:attachment/:style/missing.png',
		'default_style' => 'original',
		'styles' => [],
		'keep_old_files' => false,
		'mode' => 0777
	]
];