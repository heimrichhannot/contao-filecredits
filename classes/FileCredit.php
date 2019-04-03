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

use Contao\Controller;
use Contao\Database;
use Contao\Date;
use Contao\Environment;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\Model\Collection;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;

class FileCredit extends Controller
{
    const REQUEST_INDEX_PARAM = 'filecredit_index';

    const FILECREDIT_SORTBY_COPYRIGHT_ASC  = 'copyright_asc';
    const FILECREDIT_SORTBY_COPYRIGHT_DESC = 'copyright_desc';
    const FILECREDIT_SORTBY_PAGES_ASC      = 'pagecount_asc';
    const FILECREDIT_SORTBY_PAGES_DESC     = 'pagecount_desc';

    const FILECREDIT_GROUPBY_COPYRIGHT = 'copyright';


    /**
     * Get file credits for a file
     *
     * @param mixed $file Valid path or uuid
     *
     * @return bool|array The credits as array, or false if no credits set or file not found or invalid.
     */
    public static function getFileCredit($file)
    {
        $model = null;

        if (Validator::isUuid($file)) {
            $model = FilesModel::findByUuid($file);
        } elseif ($file) {
            $model = FilesModel::findByPath($file);
        }

        if ($model === null) {
            return false;
        }

        $credits = array_filter(deserialize($model->copyright, true));

        if (empty($credits)) {
            return false;
        }

        return $credits;
    }

    public static function parseCredit(FileCreditModel $objModel, array $arrPids = [], $objModule)
    {
        global $objPage;

        $objTemplate = new FrontendTemplate(!$objModule->creditsGroupBy ? 'filecredit_default' : 'filecredit_grouped');

        // skip if no files model exists
        if (($objFilesModel = $objModel->getRelated('uuid')) === null) {
            return null;
        }

        // cleanup: remove credits where copyright was deleted
        if ($objFilesModel->copyright == '') {
            FileCreditPageModel::deleteByPid($objModel->id);
            $objModel->delete();

            return null;
        }

        // skip if credit occurs on no page
        if (($objCreditPages = FileCreditPageModel::findPublishedByPids([$objModel->id])) === null) {
            return null;
        }

        while ($objCreditPages->next()) {
            $arrCredit = $objCreditPages->row();

            // not a child of current root page
            if (!empty($arrPids) && !in_array($arrCredit['page'], $arrPids)) {
                continue;
            }

            if ($arrCredit['url'] == '' && ($objTarget = PageModel::findByPk($arrCredit['page'])) !== null) {
                $arrCredit['url'] = Controller::generateFrontendUrl($objTarget->row());
            }

            $arrPages[] = $arrCredit;
        }

        if ($arrPages === null) {
            return null;
        }

        $objTemplate->setData($objModel->row());
        $objTemplate->fileData = $objFilesModel->row();
        static::addCopyrightToTemplate($objTemplate, $objFilesModel, $objModule);
        $objTemplate->link       = $GLOBALS['TL_LANG']['MSC']['creditLinkText'];
        $objTemplate->pagesLabel = $GLOBALS['TL_LANG']['MSC']['creditPagesLabel'];
        $objTemplate->path       = $objFilesModel->path;

        $objTemplate->pages     = $arrPages;
        $objTemplate->pageCount = count($arrPages);

        // colorbox support
        if ($objPage->outputFormat == 'xhtml') {
            $strLightboxId = 'lightbox';
        } else {
            $strLightboxId = 'lightbox[' . substr(md5($objTemplate->getName() . '_' . $objFilesModel->id), 0, 6) . ']';
        }

        $objTemplate->attribute = ($strLightboxId ? ($objPage->outputFormat == 'html5' ? ' data-gallery="#gallery-' . $objModule->id . '" data-lightbox="' : ' rel="') . $strLightboxId . '"' : '');

        $objTemplate->addImage = false;

        // Add an image
        if (!is_file(TL_ROOT . '/' . $objModel->path)) {
            $arrData = ['singleSRC' => $objFilesModel->path, 'doNotIndex' => true];

            $size = deserialize($objModule->imgSize);

            if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                $arrData['size'] = $objModule->imgSize;
            }

            Controller::addImageToTemplate($objTemplate, $arrData);
        }

        return [
            'pages'  => $arrPages,
            'order'  => static::getSortValue($objModule->creditsSortBy, $objTemplate),
            'group'  => static::getGroupValue($objModule->creditsGroupBy, $objTemplate),
            'output' => $objTemplate->parse(),
        ];
    }


    public static function parseCredits(Collection $objModels, array $arrPids = [], $objModule)
    {
        $arrCredits = [];

        while ($objModels->next()) {

            if (($strReturn = static::parseCredit($objModels->current(), $arrPids, $objModule)) === null) {
                continue;
            }

            $arrCredits[] = $strReturn;
        }

        $arrCredits = static::sortCredits($arrCredits, $objModule->creditsSortBy);

        $arrCredits = static::groupCredits($arrCredits, $objModule->creditsGroupBy);

        $arrCredits = array_map(function ($value) use (&$arrCredit) {
            return $value['output'];
        }, $arrCredits);

        return $arrCredits;
    }

    public static function getGroupOptions()
    {
        $ref = new \ReflectionClass(__CLASS__);

        $arrOptions = [];

        foreach ($ref->getConstants() as $key => $value) {
            if (!\HeimrichHannot\Haste\Util\StringUtil::startsWith($key, 'FILECREDIT_GROUPBY')) {
                continue;
            }

            $arrOptions[] = $value;
        }

        return $arrOptions;
    }

    public static function getSortOptions()
    {
        $ref = new \ReflectionClass(__CLASS__);

        $arrOptions = [];

        foreach ($ref->getConstants() as $key => $value) {

            if (!\HeimrichHannot\Haste\Util\StringUtil::startsWith($key, 'FILECREDIT_SORTBY')) {
                continue;
            }

            $arrOptions[] = $value;
        }

        return $arrOptions;
    }

    public static function getSortValue($sort, $objTemplate)
    {
        switch ($sort) {
            case static::FILECREDIT_SORTBY_COPYRIGHT_ASC:
            case static::FILECREDIT_SORTBY_COPYRIGHT_DESC:
                return strtolower(ltrim(preg_replace('/[^A-Za-z0-9 ]/', '', $objTemplate->copyright)));
            case static::FILECREDIT_SORTBY_PAGES_ASC:
            case static::FILECREDIT_SORTBY_PAGES_DESC:
                return $objTemplate->pageCount;
            default:
                return ltrim(preg_replace('/[^A-Za-z0-9 ]/', '', $objTemplate->copyright));
        }
    }

    public static function getGroupValue($mode, $objTemplate)
    {
        switch ($mode) {
            case static::FILECREDIT_GROUPBY_COPYRIGHT:
                return $objTemplate->copyright;
            default:
                return null;
        }
    }

    public static function sortCredits(array $arrCredits, $sort)
    {
        $dir  = SORT_ASC;
        $type = SORT_REGULAR;

        switch ($sort) {
            case static::FILECREDIT_SORTBY_COPYRIGHT_ASC:
            case static::FILECREDIT_SORTBY_COPYRIGHT_DESC:
                $type = SORT_STRING OR SORT_FLAG_CASE;
                break;
            case static::FILECREDIT_SORTBY_PAGES_ASC:
            case static::FILECREDIT_SORTBY_PAGES_DESC:
                $type = SORT_NUMERIC;
                break;
        }

        switch ($sort) {
            case static::FILECREDIT_SORTBY_PAGES_DESC:
            case static::FILECREDIT_SORTBY_COPYRIGHT_DESC:
                $dir = SORT_DESC;
                break;
        }

        static::array_sort_by_column($arrCredits, 'order', $dir, $type);

        return $arrCredits;
    }


    public static function groupCredits(array $arrCredits, $mode)
    {
        if (!$mode) {
            return $arrCredits;
        }

        $arrGroups = [];
        $arrReturn = [];

        switch ($mode) {
            case static::FILECREDIT_GROUPBY_COPYRIGHT:


                $objTemplate = new FrontendTemplate('filecreditgroup_' . $mode);

                foreach ($arrCredits as $arrCredit) {
                    $arrGroups[$arrCredit['group']][] = $arrCredit['output'];
                }

                foreach ($arrGroups as $title => $items) {
                    $objTemplate->cssID    = 'filecredit_' . substr(md5($title), 0, 8);
                    $objTemplate->title    = $title;
                    $objTemplate->items    = $items;
                    $arrReturn[]['output'] = $objTemplate->parse();
                }

                break;
        }


        return $arrReturn;
    }

    private static function array_sort_by_column(&$arr, $col, $dir = SORT_ASC, $type = SORT_REGULAR)
    {
        $sort_col = [];
        foreach ($arr as $key => $row) {
            $sort_col[$key] = $row[$col];
        }

        array_multisort($sort_col, $dir, $arr, $type);
    }

    protected static function addCopyrightToTemplate(&$objTemplate, $objFilesModel, $objModule)
    {
        $arrCopyright = deserialize($objFilesModel->copyright, true);
        $arrList      = [];

        foreach ($arrCopyright as $strCopyright) {
            $strCopyright = StringUtil::decodeEntities(StringUtil::restoreBasicEntities($strCopyright));

            if ($objModule->creditsPrefix != '') {
                $strPrefix = StringUtil::decodeEntities(StringUtil::restoreBasicEntities($objModule->creditsPrefix));

                if (!($strPrefix === "" || strrpos($strCopyright, $strPrefix, -strlen($strCopyright)) !== false)) {
                    $strCopyright = $strPrefix . trim(ltrim($strCopyright, $strPrefix));
                }
            }

            $arrList[] = $strCopyright;
        }

        $objTemplate->copyright = implode(', ', $arrList);
    }

    public static function indexStop()
    {
        $GLOBALS['FILECREDIT_INDEX_STOP'] = true;
    }

    public static function indexContinue()
    {
        unset($GLOBALS['FILECREDIT_INDEX_STOP']);
    }

    public static function isIndexSuspended()
    {
        return $GLOBALS['FILECREDIT_INDEX_STOP'];
    }

    public static function addCopyrightFieldToDca($strTable, $strField, $strFileField, $blnUseDefaultLabel = true)
    {
        Controller::loadDataContainer('tl_files');
        System::loadLanguageFile('tl_files');

        Controller::loadDataContainer($strTable);
        System::loadLanguageFile($strTable);

        $arrDca = &$GLOBALS['TL_DCA'][$strTable];

        $arrDca['fields'][$strField] = $GLOBALS['TL_DCA']['tl_files']['fields']['copyright'];

        if (!$blnUseDefaultLabel) {
            $arrDca['fields'][$strField]['label'] = &$GLOBALS['TL_LANG'][$strTable][$strField];
        }

        $arrDca['fields'][$strField]['eval']['fileField'] = $strFileField;

        $arrDca['fields'][$strField]['load_callback'] = [['HeimrichHannot\FileCredit\FileCredit', 'loadCopyright']];
        $arrDca['fields'][$strField]['save_callback'] = [['HeimrichHannot\FileCredit\FileCredit', 'saveCopyright']];
    }

    public static function loadCopyright($varValue, \DataContainer $objDc)
    {
        if (($objNews = NewsModel::findByPk($objDc->id)) === null) {
            return null;
        }

        Controller::loadDataContainer($objDc->table);
        System::loadLanguageFile($objDc->table);

        $arrDca        = $GLOBALS['TL_DCA'][$objDc->table];
        $strImageField = $arrDca['fields'][$objDc->field]['eval']['fileField'];

        if ($strImageField && $objNews->{$strImageField} && ($objFile = FilesModel::findByUuid($objNews->{$strImageField})) !== null) {
            return $objFile->copyright;
        }
    }

    public static function saveCopyright($varValue, \DataContainer $objDc)
    {
        if (($objNews = NewsModel::findByPk($objDc->id)) === null) {
            return null;
        }

        Controller::loadDataContainer($objDc->table);
        System::loadLanguageFile($objDc->table);

        $arrDca        = $GLOBALS['TL_DCA'][$objDc->table];
        $strImageField = $arrDca['fields'][$objDc->field]['eval']['fileField'];

        if ($strImageField && $objNews->{$strImageField} && ($objFile = FilesModel::findByUuid($objNews->{$strImageField})) !== null) {
            $objFile->copyright = $varValue;
            $objFile->save();
        }

        return $varValue;
    }

    /**
     * Get all pages for filecredit index and return them as array
     *
     * @param integer $pid
     * @param string  $domain
     * @param boolean $blnIsSitemap
     * @param string  $strLanguage
     *
     * @return array
     */
    protected static function findFileCreditPages($pid = 0, $domain = '', $blnIsSitemap = false, $strLanguage = '')
    {
        $time        = Date::floorToMinute();
        $objDatabase = Database::getInstance();

        // Get published pages
        $objPages = $objDatabase->prepare("SELECT * FROM tl_page WHERE pid=? AND (start='' OR start<='$time') AND (stop='' OR stop>'" . ($time + 60) . "') AND published='1' ORDER BY sorting")->execute($pid);

        if ($objPages->numRows < 1) {
            return [];
        }

        $arrPages = [];

        // Recursively walk through all subpages
        while ($objPages->next()) {
            $objPage = PageModel::findWithDetails($objPages->id);

            if ($objPage->noSearch) {
                continue;
            }

            if ($objPage->domain != '') {
                $domain = ($objPage->rootUseSSL ? 'https://' : 'http://') . $objPage->domain . '/';
            } else {
                $rootPage   = PageModel::findByPk($objPage->rootId);
                $rootDomain = $rootPage->domain ?: Environment::get('host');

                if (!preg_match('/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,6}$/', $rootDomain)) {
                    System::log(sprintf('Filecredit Indexer: You must declare a domain on the root page of page with id %s in order to index file credits.', $objPage->id), __METHOD__, TL_ERROR);
                    continue;
                }

                $domain = ($objPage->rootUseSSL ? 'https://' : 'http://') . $rootDomain . '/';
            }

            // Set domain
            if ($objPage->type == 'root') {
                $strLanguage = $objPage->language;
            } // Add regular pages
            elseif ($objPage->type == 'regular') {
                // Not protected
                if ((!$objPage->protected)) {
                    // Published
                    if ($objPage->published && ($objPage->start == '' || $objPage->start <= $time) && ($objPage->stop == '' || $objPage->stop > ($time + 60))) {
                        $arrPages[] = $domain . Controller::generateFrontendUrl($objPage->row(), null, $strLanguage);
                    }
                }
            }

            // Get subpages
            if ((!$objPage->protected) && ($arrSubpages = static::findFileCreditPages($objPage->id, $domain, $blnIsSitemap, $strLanguage)) != false) {
                $arrPages = array_merge($arrPages, $arrSubpages);
            }
        }

        return $arrPages;
    }

    /**
     * Find all credit pages, including all news, events and more
     *
     * @return array
     */
    public static function findAllFileCreditPages()
    {
        $arrPages = static::findFileCreditPages();

        // HOOK: take additional pages (news, events…)
        if (isset($GLOBALS['TL_HOOKS']['getSearchablePages']) && is_array($GLOBALS['TL_HOOKS']['getSearchablePages'])) {
            foreach ($GLOBALS['TL_HOOKS']['getSearchablePages'] as $callback) {
                if (($objCallback = Controller::importStatic($callback[0])) !== null) {
                    $arrPages = $objCallback->{$callback[1]}($arrPages);
                }
            }
        }

        $time = time();

        $arrPages = array_map(function ($url) use ($time) {
            $url = str_replace(['/app_dev.php', ':0/'], ['', '/'], $url);

            // filter out non absolute urls
            if (false === strpos($url, 'http')) {
                return null;
            }

            if (false === strpos($url, static::REQUEST_INDEX_PARAM)) {
                return $url . '?' . static::REQUEST_INDEX_PARAM . '=' . $time;
            }

            return $url;
        }, $arrPages);

        $arrPages = array_filter(array_unique($arrPages));

        return $arrPages;
    }
}