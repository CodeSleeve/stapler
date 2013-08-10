<?php

return [
	'options' => [
		'storage' 					=> 'filesystem',
		'url' 						=> '/system/:class/:attachment/:id_partition/:style/:filename',
		'path' 						=> ':laravel_root/public:url',
		'default_url' 				=> '/:attachment/:style/missing.png',
		'default_style' 			=> 'original',
		'styles' 					=> [],
		'keep_old_files' 			=> false,
		'override_file_permissions' => null
	]
];