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


class FileCreditPageModel extends \Model
{
	protected static $strTable = 'tl_filecredit_page';

	public static function deleteByPid($intPid)
	{
		// Delete all
		$intAffected = \Database::getInstance()->prepare("DELETE FROM " . static::$strTable . " WHERE pid=?")
			->execute($intPid)
			->affectedRows;

		return $intAffected;
	}

	public static function findByPidAndUrl($intPid, $strUrl, array $arrOptions = array())
	{
		$t = static::$strTable;

		$arrColumns = array("$t.pid=? AND $t.url=?");

		return static::findBy($arrColumns, array($intPid, $strUrl), $arrOptions);
	}

	public static function findByPidAndPageAndUrl($intPid, $intPage, $strUrl, array $arrOptions = array())
	{
		$t = static::$strTable;

		$arrColumns = array("$t.pid=? AND $t.page=? AND $t.url=?");

		return static::findBy($arrColumns, array($intPid, $intPage, $strUrl), $arrOptions);
	}

	public static function findPublishedByPids(array $arrPids, array $arrOptions = array())
	{
		$t = static::$strTable;
		$arrColumns = array("$t.pid IN(" . implode(',', array_map('intval', $arrPids)) . ")");

		if (!BE_USER_LOGGED_IN)
		{
			$time = \Date::floorToMinute();
			$arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
		}

		return static::findBy($arrColumns, array(), $arrOptions);
	}
}