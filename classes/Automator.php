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
     * @param array $pages Limit page ids that should be purged
     * @param int $time
     */
    public static function purgeFileCreditTables(array $pages = [], int $time = 0)
    {
        $objDatabase = \Database::getInstance();

        // clean up credits that were indexed in history from unsupported page type
        $objDatabase->execute("DELETE tl_filecredit_page FROM tl_filecredit_page INNER JOIN tl_page ON tl_filecredit_page.page = tl_page.id WHERE tl_page.type IN ('root', 'redirect', 'forward', 'logout')");

        if (empty($pages)) {
            // Truncate the tables
            $objDatabase->execute("DELETE FROM tl_filecredit WHERE author = 0");

            $objCredits = $objDatabase->execute("SELECT id FROM tl_filecredit WHERE author = 0");

            if ($objCredits->numRows > 0) {
                $objDatabase->execute("DELETE FROM tl_filecredit_page WHERE id IN(" . implode(',', array_map('intval', $objCredits->fetchEach('id'))) . ")");
            }


            $objEmptyCredits = $objDatabase->prepare("SELECT tl_filecredit.id FROM tl_filecredit INNER JOIN tl_filecredit_page ON tl_filecredit.id = tl_filecredit_page.pid WHERE tl_filecredit.author=0 AND tl_filecredit_page.id IS NULL)");

            // delete credits without pages
            if ($objEmptyCredits->numRows > 0) {
                $objDatabase->execute("DELETE FROM tl_filecredit WHERE id IN(" . implode(',', array_map('intval', $objEmptyCredits->fetchEach('id'))) . ")");
            }

            // Add a log entry
            \System::log('Purged the filecredit tables, except manual created credits.', __METHOD__, TL_CRON);

            return;
        }

        $objCreditPages = $objDatabase->prepare("SELECT tl_filecredit.id as creditId, tl_filecredit_page.id as pageId FROM tl_filecredit_page LEFT JOIN tl_filecredit ON tl_filecredit.id = tl_filecredit_page.pid WHERE (tl_filecredit.author=0 OR tl_filecredit.id IS NULL) AND tl_filecredit_page.tstamp != ? AND tl_filecredit_page.page IN(" . implode(',', array_map('intval', $pages)) . ")")->execute($time);

        if ($objCreditPages->numRows > 0) {
            $objDatabase->execute("DELETE FROM tl_filecredit_page WHERE id IN(" . implode(',', array_map('intval', $objCreditPages->fetchEach('pageId'))) . ")");
        }

        $objEmptyCredits = $objDatabase->execute("SELECT tl_filecredit.id FROM tl_filecredit INNER JOIN tl_filecredit_page ON tl_filecredit.id = tl_filecredit_page.pid WHERE tl_filecredit.author=0 AND tl_filecredit_page.id IS NULL");

        // delete credits without pages
        if ($objEmptyCredits->numRows > 0) {
            $objDatabase->execute("DELETE FROM tl_filecredit WHERE id IN(" . implode(',', array_map('intval', $objEmptyCredits->fetchEach('id'))) . ")");
        }

        // Add a log entry
        \System::log('Purged the filecredit table with credits for page IDS:' . implode(",", $pages) . ' only.', __METHOD__, TL_CRON);
    }

}