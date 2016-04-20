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


class Automator extends \System
{

	/**
	 * Purge the search tables
	 */
	public static function purgeFileCreditTables()
	{
		$objDatabase = \Database::getInstance();

		// Truncate the tables
		$objDatabase->execute("TRUNCATE TABLE tl_filecredit");
		$objDatabase->execute("TRUNCATE TABLE tl_filecredit_page");

		// Add a log entry
		\System::log('Purged the filecredit tables', __METHOD__, TL_CRON);
	}
	
}