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

namespace HeimrichHannot\FileCredit;


class Hooks extends \Controller
{

	public function generatePageHook($objPage, $objLayout, $objPageRegular)
	{
		if(Validator::isRebuildFileCreditRequest())
		{
			FileCredit::cleanupByCurrentRequest();
		}
	}

	public function executeResizeHook($objImage)
	{
		if(TL_MODE == 'BE') return false; // do not return a string to not interrupt Image::executeResize

		$objFile = \FilesModel::findByPath($objImage->getOriginalPath());

		if($objFile !== null)
		{
			FileCreditIndex::indexFile($objFile);
		}

		return false; // do not return a string to not interrupt Image::executeResize
	}

}