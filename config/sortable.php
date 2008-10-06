<?php
$config['query_string'] = 'orderby';

$config['templates'] = array(

	'NULL' => '<a href="{url}">{title}</a>',
	'ASC'  => '<a href="{url}">{title}</a> <img src="'.url::site('images/up.gif').'" />',
	'DESC' => '<a href="{url}">{title}</a> <img src="'.url::site('images/down.gif').'" />'
);


