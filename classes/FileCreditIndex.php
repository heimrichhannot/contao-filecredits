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


use Contao\Database;
use Contao\PageModel;
use HeimrichHannot\Request\Request;

class FileCreditIndex extends \Controller
{

    public static function doIndex()
    {
        global $objPage;

        // Index page if there is no back end user
        if ($objPage->type == 'regular' && !BE_USER_LOGGED_IN) {
            // Index protected pages if enabled
            if ((!FE_USER_LOGGED_IN && !$objPage->protected)) {
                // Do not index the page if certain parameters are set
                if (!empty($_GET)) {
                    foreach (array_keys($_GET) as $key) {
                        if (in_array($key, $GLOBALS['TL_NOINDEX_KEYS']) || strncmp($key, 'page_', 5) === 0) {
                            return false;
                        }
                    }
                }

                return true;
            }
        }

        return false;
    }

    public static function addFile($objFile, int $time)
    {
        $checksum = md5(preg_replace('/ +/', ' ', strip_tags($objFile->copyright)));

        $objModel = FileCreditModel::findByUuid($objFile->uuid);

        // do not index again if copyright did not change, but add the current page
        if ($objModel !== null && $checksum == $objModel->checksum) {
            static::addCurrentPage($objModel, $time);
            return true;
        }

        $arrSet = [
            'tstamp'    => (int)$time,
            'uuid'      => $objFile->uuid,
            'checksum'  => $checksum,
            'published' => 1,
            'start'     => '',
            'stop'      => '',
        ];

        $copyright = array_filter(deserialize($objFile->copyright, true));

        if ($objModel !== null) {
            // delete: remove credit if copyright is empty
            if (empty($copyright)) {
                // remove all pages for the credit before
                FileCreditPageModel::deleteByPid($objModel->id);

                $objModel->delete();

                return false;
            }

            // update: otherwise update existing filecredit
            $objModel->setRow($arrSet);
            $objModel->save();

            static::addCurrentPage($objModel, $time);

            return true;
        }

        // create: add new credit
        if (!empty($copyright)) {
            $objModel = new FileCreditModel();
            $objModel->setRow($arrSet);
            $objModel->save();

            static::addCurrentPage($objModel, $time);

            return true;
        }

        return false;
    }

    protected static function addCurrentPage(FileCreditModel $objCredit, int $time)
    {
        if (!$objCredit->id) {
            return false;
        }

        /** @var $objPage PageModel */
        global $objPage;

        $strRequestRaw = preg_replace('/\\?.*/', '', \Environment::get('request')); // strip get parameter from url
        $strRequestRaw = str_replace(['/app_dev.php', ':0/'], ['/', '/'], $strRequestRaw);
        $path          = rtrim($strRequestRaw, "/");
        $strAlias      = $objPage->getFrontendUrl();

        // only accept pages or auto_item pages
        if (!Validator::isRequestAlias($path, rtrim($strAlias, '/'))) {
            // cleanup pages that no longer exist
            $objModel = FileCreditPageModel::findByPidAndPageAndUrl($objCredit->id, $objPage->id, $strRequestRaw);

            if ($objModel !== null) {
                while ($objModel->next()) {
                    $objModel->delete();
                }
            }

            return false;
        }

        $strRequest = trim(\Environment::get('base') . $path);
        $objModel   = FileCreditPageModel::findByPidAndPageAndUrl($objCredit->id, $objPage->id, $strRequest);

        // do not index page again
        if ($objModel !== null) {
            return false;
        }

        $arrSet = [
            'pid'       => (int)$objCredit->id,
            'tstamp'    => (int)$time,
            'page'      => (int)$objPage->id,
            'url'       => $strRequest,
            'protected' => ($objPage->protected ? '1' : ''),
            'groups'    => $objPage->groups,
            'language'  => $objPage->language,
            'published' => 1,
            'start'     => '',
            'stop'      => '',
        ];

        // create: add new page for the credit, on duplicate key ignore
        Database::getInstance()->prepare('INSERT IGNORE INTO tl_filecredit_page %s')
            ->set($arrSet)
            ->execute();

        return true;
    }


    /**
     * @param \Contao\Model\Collection|FilesModel[]|FilesModel|null $objFile
     * @param int $time Index timestamp
     * @return bool
     */
    public static function indexFile($objFile = null, int $time)
    {
        if (null === $objFile) {
            return false;
        }

        if ($objFile instanceof \Model\Collection) {
            return static::indexFiles($objFile);
        }

        if (!$objFile instanceof \Model) {
            return false;
        }

        if (!static::doIndex()) {
            return false;
        }

        if (!static::addFile($objFile, $time)) {
            return false;
        }

        return true;
    }

    protected static function indexFiles(\Model\Collection $objFiles)
    {
        $blnCheck = true;

        while ($objFiles->next()) {
            $return   = static::indexFile($objFiles->current(), (int) Request::getGet(FileCredit::REQUEST_INDEX_PARAM));
            $blnCheck = !$blnCheck ?: $return;
        }

        return $blnCheck;
    }

}
