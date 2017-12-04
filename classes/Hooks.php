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

class Hooks extends \Controller
{
    public function executeResizeHook($objImage)
    {
        if (TL_MODE == 'BE') {
            return false;
        } // do not return a string to not interrupt Image::executeResize

        FileCreditIndex::indexFile($objImage);

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