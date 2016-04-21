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

$dc['palettes']['filecredit'] =
	'{title_legend},name,headline,type;{reference_legend:hide},defineRoot;{credit_legend},creditsSortBy,creditsGroupBy,creditsPrefix;{image_legend:hide},imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$arrFields = array
(
	'creditsSortBy' => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['creditsSortBy'],
		'inputType'        => 'select',
		'options_callback' => array('\HeimrichHannot\FileCredit\FileCredit', 'getSortOptions'),
		'reference'        => &$GLOBALS['TL_LANG']['tl_module']['refs']['creditsSortBy'],
		'sql'              => "varchar(64) NOT NULL default ''",
		'eval'             => array('tl_class' => 'w50'),
	),
	'creditsGroupBy' => array
	(
		'label'            => &$GLOBALS['TL_LANG']['tl_module']['creditsGroupBy'],
		'inputType'        => 'select',
		'options_callback' => array('\HeimrichHannot\FileCredit\FileCredit', 'getGroupOptions'),
		'reference'        => &$GLOBALS['TL_LANG']['tl_module']['refs']['creditsGroupBy'],
		'sql'              => "varchar(64) NOT NULL default ''",
		'eval'             => array('tl_class' => 'w50', 'includeBlankOption' => true),
	),
	'creditsPrefix' => array
	(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['creditsPrefix'],
		'inputType' => 'text',
		'default'   => '&copy;&nbsp;',
		'sql'       => "varchar(255) NOT NULL default ''",
		'eval'      => array('tl_class' => 'w50', 'decodeEntities' => true),
	),
);

$dc['fields'] = array_merge($dc['fields'], $arrFields);

class tl_module_filecredits extends Backend
{
	public function getFileCredits()
	{
		$arrOptions = array();
		
		$objFiles = \FilesModel::findBy(array('copyright != ""'), "");

		if ($objFiles === null) {
			return $arrOptions;
		}
		
		$maxLength = 45;
		
		while ($objFiles->next()) {
			$strPath = $objFiles->path;
			
			$strLength = strlen($strPath);
			
			if ($strLength > $maxLength) {
				$strPathLeft  = substr($strPath, 0, ceil($maxLength / 7));
				$strPathRight = substr($strPath, ceil($strLength - $maxLength / (7 / 5)), $strLength);
				
				$strPath = $strPathLeft . '…' . $strPathRight;
			}
			
			$arrOptions[$objFiles->id] = $strPath;
		}
		
		return $arrOptions;
	}
}