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


class FilesModel extends \FilesModel
{
	public static function findWithCopyright(array $arrOptions= [])
	{
		$t = static::$strTable;

		$arrColumns = ["$t.copyright <> ''"];

		return static::findBy($arrColumns, null, $arrOptions);
	}
	
}