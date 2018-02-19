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

        $ids = $objDatabase->execute("SELECT id FROM tl_filecredit WHERE author = 0")->fetchEach('id');

        // Truncate the tables
        $objDatabase->execute("DELETE FROM tl_filecredit WHERE author = 0");
        $objDatabase->execute("DELETE FROM tl_filecredit_page WHERE id IN(" . implode(',', $ids) . ")");

        // Add a log entry
        \System::log('Purged the filecredit tables, except manual created credits.', __METHOD__, TL_CRON);
    }

}