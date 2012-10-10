<?php

set_include_path(implode(PATH_SEPARATOR, array(
	dirname(__DIR__) . '/libraries',
	get_include_path()
)));

require_once dirname(__DIR__) . '/vendor/autoload.php';
