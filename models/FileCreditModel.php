<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package ${CARET}
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FileCredit;


class FileCreditModel extends \Model
{
	protected static $strTable = 'tl_filecredit';

	public static function findByUuid($strUuid, array $arrOptions = array())
	{
		$t = static::$strTable;

		// Convert UUIDs to binary
		if (\Validator::isStringUuid($strUuid))
		{
			$strUuid = \String::uuidToBin($strUuid);
		}

		$arrColumns = array("$t.uuid=UNHEX(?)");

		return static::findOneBy($arrColumns, array(bin2hex($strUuid)), $arrOptions);
	}

	public static function findByUuidAndPidAndUrl($strUuid, $intPid, $strUrl, array $arrOptions = array())
	{
		$t = static::$strTable;

		// Convert UUIDs to binary
		if (\Validator::isStringUuid($strUuid))
		{
			$strUuid = \String::uuidToBin($strUuid);
		}

		$arrColumns = array("$t.uuid=UNHEX(?) AND $t.pid=? AND $t.url=?");

		return static::findBy($arrColumns, array(bin2hex($strUuid), $intPid, $strUrl), $arrOptions);
	}

	public static function findByPublished(array $arrOptions=array())
	{
		$t = static::$strTable;

		if (!BE_USER_LOGGED_IN)
		{
			$time = \Date::floorToMinute();
			$arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
		}

		return static::findBy($arrColumns, array(), $arrOptions);
	}
}