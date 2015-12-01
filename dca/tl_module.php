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

$dc = &$GLOBALS['TL_DCA']['tl_module'];

$dc['palettes']['filecredit'] = '{title_legend},name,headline,type;{reference_legend:hide},defineRoot;{credit_legend},selectedCredits;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$arrFields = array
(
	'selectedCredits' => array
	(
		'label'										=> &$GLOBALS['TL_LANG']['tl_module']['selectedCredits'],
		'inputType'									=> 'multiColumnWizard',
		'exclude' 		=> true,
		'eval' 			=> array
		(
			'columnFields' => array
			(
				'file' => array
				(
					'label'								=> &$GLOBALS['TL_LANG']['tl_module']['selectedCredits']['file'],
					'exclude'							=> true,
					'inputType'							=> 'select',
					'options_callback'					=> array('tl_module_filecredits', 'getFileCredits'),
					'eval' 								=> array('style'=>'width:280px', 'chosen' => true, 'includeBlankOption' => true)
				),
				'usage' => array
				(
					'label'								=> &$GLOBALS['TL_LANG']['tl_module']['selectedCredits']['usage'],
					'exclude'							=> true,
					'inputType'						=> 'text',
					'eval'								=> array('style' => 'width:300px', 'includeBlankOption'=>true, 'chosen'=>true)
				),
			)
		),
	'sql'												=> "blob NULL"
	)
);

$dc['fields'] = array_merge($dc['fields'], $arrFields);

class tl_module_filecredits extends Backend
{
	public function getFileCredits()
	{
		$arrOptions = array();
		
		$objFiles = \FilesModel::findBy(array('copyright != ""'), "");

		if($objFiles === null) return $arrOptions;
		
		$maxLength = 45;
		
		while($objFiles->next())
		{
			$strPath = $objFiles->path;
			
			$strLength = strlen($strPath);
			
			if($strLength > $maxLength)
			{
				$strPathLeft = substr($strPath, 0, ceil($maxLength / 7));
				$strPathRight = substr($strPath, ceil($strLength - $maxLength / (7/5)), $strLength);
				
				$strPath = $strPathLeft . '…' . $strPathRight; 
			}
			
			$arrOptions[$objFiles->id] = $strPath;
		}
		
		return $arrOptions;
	}
}