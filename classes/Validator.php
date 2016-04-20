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
	
}