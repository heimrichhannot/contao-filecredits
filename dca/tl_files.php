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
	'inputType'               	=> 'tagsinput',
	'options_callback'			=> array('tl_files_filecredits', 'getCreditOptions'),
	'eval'						=> array('maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'long clr', 'helpwizard'=>true, 'freeInput' => true),
	'reference'					=> &$GLOBALS['TL_LANG']['tl_files'],
	'sql'                     	=> "varchar(255) NOT NULL default ''"
);

class tl_files_filecredits extends Backend
{

	public function getCreditOptions($dc)
	{
		$arrOptions = array();

		$objFileCredits =  \HeimrichHannot\FileCredit\FilesModel::findWithCopyright();

		if($objFileCredits === null)
		{
			return $arrOptions;
		}

		while($objFileCredits->next())
		{
			$arrOptions[$objFileCredits->copyright] = $objFileCredits->copyright;
		}

		return $arrOptions;
	}

}