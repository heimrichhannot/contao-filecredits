<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FileCredit;


class Validator extends \Validator
{

	public static function isRebuildFileCreditRequest()
	{
		return (strpos($_SERVER['HTTP_REFERER'], 'main.php?act=index&do=filecredit&key=sync') !== false);
	}


	public static function isRequestAlias($strRequest, $strAlias)
	{
		$blnCheck = true;

		if(FileCredit::isIndexSuspended())
		{
			return false;
		}

		if($strRequest != $strAlias)
		{
			$blnCheck = false;
		}

		if((\Config::get('useAutoItem') && isset($_GET['auto_item'])))
		{
			if(is_array($GLOBALS['TL_AUTO_ITEM']) && is_array($_GET) && is_array($GLOBALS['TL_AUTO_ITEM']))
			{
				$arrSet = array_intersect(array_keys($_GET), $GLOBALS['TL_AUTO_ITEM']);

				if(!empty($arrSet))
				{
					$blnCheck = true;
				}
			}
		}

		return $blnCheck;
	}
}