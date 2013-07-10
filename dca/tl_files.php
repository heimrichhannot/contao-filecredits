<?php

$dc = &$GLOBALS['TL_DCA']['tl_files'];

$dc['palettes']['default'] = str_replace('name', 'name,copyright', $dc['palettes']['default']);


$dc['fields']['copyright'] = array
(
	'label'                   	=> &$GLOBALS['TL_LANG']['tl_files']['copyright'],
	'inputType'               	=> 'text',
	'options'					=> array('fotolia', 'pixelio', 'shutterstock', 'istockphoto'),
	'eval'						=> array('maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'long clr', 'helpwizard'=>true),
	'reference'					=> &$GLOBALS['TL_LANG']['tl_files'],
	'sql'                     	=> "varchar(255) NOT NULL default ''"
);