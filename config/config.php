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

/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['miscellaneous']['filecredit'] = 'HeimrichHannot\FileCredit\ModuleFileCredit';

/**
 * Back end modules
 */
array_insert(
	$GLOBALS['BE_MOD']['system'],
	1,
	array(
		'filecredit' => array
		(
			'tables' => array('tl_filecredit', 'tl_filecredit_page'),
			'icon'   => 'system/modules/filecredits/assets/img/icon.png',
			'sync'   => array('HeimrichHannot\FileCredit\Backend\FileCredit', 'sync'),
		),
	)
);

/**
 * Javascript
 */
if (TL_MODE == 'BE') {
	$GLOBALS['TL_JAVASCRIPT']['filecredits-be'] = 'system/modules/filecredits/assets/js/filecredits_be.js|static';
}


/**
 * Css
 */
if (TL_MODE == 'BE') {
	$GLOBALS['TL_CSS']['filecredits-be'] = 'system/modules/filecredits/assets/css/filecredits_be.css';
}


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['executeResize'][] = array('\HeimrichHannot\FileCredit\Hooks', 'executeResizeHook');

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_filecredit']      = 'HeimrichHannot\FileCredit\FileCreditModel';
$GLOBALS['TL_MODELS']['tl_filecredit_page'] = 'HeimrichHannot\FileCredit\FileCreditPageModel';

