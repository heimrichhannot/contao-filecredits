<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package filecredits
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

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