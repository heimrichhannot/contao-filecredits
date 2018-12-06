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

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use HeimrichHannot\Request\Request;

class Hooks extends \Controller
{
    /**
     * Modify frontend page hook
     * @param $strBuffer
     * @param $strTemplate
     * @return mixed
     */
    public function modifyFrontendPageHook($strBuffer, $strTemplate)
    {
        if (TL_MODE == 'BE' || !Request::hasGet(FileCredit::REQUEST_INDEX_PARAM)) {
            return $strBuffer;
        }

        $paths = [];

        // find relative `files` images that were not be resized
        if (preg_match_all('@"\/?(?<paths>files\/[^"]+\.(png|jpe?g|svg?z|bmp|gif|tiff))"@i', $strBuffer, $images)) {
            $paths = array_merge($paths, $images['paths']);
        }

        if (preg_match_all('@"background-image:\s*url\((?<paths>[^)]+\.(png|jpe?g|svg?z|bmp|gif|tiff))\)"@i', $strBuffer, $backgrounds)) {
            $paths = array_merge($paths, $backgrounds['paths']);
        }

        FileCreditIndex::indexFile(\Contao\FilesModel::findMultipleByPaths($paths), (int) Request::getGet(FileCredit::REQUEST_INDEX_PARAM));

        return $strBuffer;
    }

    /**
     * Modify page layout hook
     * @param PageModel $objPage
     * @param LayoutModel $objLayout
     * @param PageRegular $page
     */
    public function getPageLayoutHook(PageModel $objPage, LayoutModel $objLayout, PageRegular $page)
    {
        if (TL_MODE == 'BE' || !Request::hasGet(FileCredit::REQUEST_INDEX_PARAM)) {
            return;
        }

        // purge all file credits before running executeResizeHook
        Automator::purgeFileCreditTables([$objPage->id], (int) Request::getGet(FileCredit::REQUEST_INDEX_PARAM));
    }

    /**
     * Execute image resize hook
     * @param $objImage
     * @return bool
     */
    public function executeResizeHook($objImage)
    {
        if (TL_MODE == 'BE' || !Request::hasGet(FileCredit::REQUEST_INDEX_PARAM)) {
            return false;
        } // do not return a string to not interrupt Image::executeResize

        FileCreditIndex::indexFile(\Contao\FilesModel::findByPath($objImage->getOriginalPath()), (int) Request::getGet(FileCredit::REQUEST_INDEX_PARAM));

        return false; // do not return a string to not interrupt Image::executeResize
    }

    /**
     * Add additional tags
     *
     * @param $strTag
     * @param $blnCache
     * @param $strCache
     * @param $flags
     * @param $tags
     * @param $arrCache
     * @param $index
     * @param $count
     *
     * @return mixed Return false, if the tag was not replaced, otherwise return the value of the replaced tag
     */
    public function replaceInsertTagsHook($strTag, $blnCache, $strCache, $flags, $tags, $arrCache, $index, $count)
    {
        $elements = explode('::', $strTag);

        switch (strtolower($elements[0])) {
            case 'copyright':
                if (!$elements[1]) {
                    return '';
                }

                $delimiter = $elements[2] ?: ',';

                $credits = FileCredit::getFileCredit($elements[1]);

                if (!$credits || empty($credits)) {
                    return '';
                }

                return implode($delimiter, $credits);
        }

        return false;
    }

}