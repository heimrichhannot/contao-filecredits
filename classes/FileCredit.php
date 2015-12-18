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


class FileCredit extends \Controller
{
	const FILECREDIT_SORTBY_COPYRIGHT_ASC  = 'copyright_asc';
	const FILECREDIT_SORTBY_COPYRIGHT_DESC = 'copyright_desc';
	const FILECREDIT_SORTBY_PAGES_ASC      = 'pagecount_asc';
	const FILECREDIT_SORTBY_PAGES_DESC     = 'pagecount_desc';

	public static function parseCredit(FileCreditModel $objModel, array $arrPids = array(), $objModule)
	{
		global $objPage;

		$objTemplate = new \FrontendTemplate('filecredit_default');

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
		if (($objCreditPages = FileCreditPageModel::findPublishedByPids(array($objModel->id))) === null) {
			return null;
		}

		while ($objCreditPages->next()) {
			$arrCredit = $objCreditPages->row();

			// not a child of current root page
			if (!empty($arrPids) && !in_array($arrCredit['page'], $arrPids)) {
				continue;
			}

			if ($arrCredit['url'] == '' && ($objTarget = \PageModel::findByPk($arrCredit['page'])) !== null) {
				$arrCredit['url'] = \Controller::generateFrontendUrl($objTarget->row());
			}

			$arrPages[] = $arrCredit;
		}

		if ($arrPages === null) {
			return null;
		}
		
		$objTemplate->setData($objModel->row());
		$objTemplate->fileData   = $objFilesModel->row();
		$objTemplate->copyright  = $objFilesModel->copyright;
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

		$objTemplate->attribute =
			($strLightboxId ? ($objPage->outputFormat == 'html5' ? ' data-gallery="#gallery-' . $objModule->id . '" data-lightbox="' : ' rel="')
							  . $strLightboxId . '"' : '');

		return array('order' => static::getSortValue($objModule->creditsSortBy, $objTemplate), 'output' => $objTemplate->parse());
	}


	public static function parseCredits(\Model\Collection $objModels, array $arrPids = array(), $objModule)
	{
		$arrCredits = array();

		while ($objModels->next()) {

			if (($strReturn = static::parseCredit($objModels->current(), $arrPids, $objModule)) === null) {
				continue;
			}

			$arrCredits[] = $strReturn;
		}

		$arrCredits = static::sortCredits($arrCredits, $objModule->creditsSortBy);

		$arrCredits = array_map(
			function ($value) use (&$arrCredit) {
				return $value['output'];
			},
			$arrCredits
		);

		return $arrCredits;
	}

	public static function getSortOptions()
	{
		$ref = new \ReflectionClass(__CLASS__);

		$arrOptions = array();

		foreach ($ref->getConstants() as $key => $value) {
			if ($value == FILECREDIT_SORTBY_DEFAULT) {
				continue;
			}

			$arrOptions[] = $value;
		}

		return $arrOptions;
	}

	public static function getSortValue($sort, $objTemplate)
	{
		$ref = new \ReflectionClass(__CLASS__);

		switch ($sort) {
			case $ref->getConstant(FILECREDIT_SORTBY_COPYRIGHT_ASC):
			case $ref->getConstant(FILECREDIT_SORTBY_COPYRIGHT_DESC):
				return strtolower(ltrim(preg_replace('/[^A-Za-z0-9 ]/', '', $objTemplate->copyright)));
			case $ref->getConstant(FILECREDIT_SORTBY_PAGES_ASC):
			case $ref->getConstant(FILECREDIT_SORTBY_PAGES_DESC):
				return $objTemplate->pageCount;
			default:
				return ltrim(preg_replace('/[^A-Za-z0-9 ]/', '', $objTemplate->copyright));
		}
	}


	public static function sortCredits(array $arrCredits, $sort)
	{
		$ref = new \ReflectionClass(__CLASS__);

		$dir  = SORT_ASC;
		$type = SORT_REGULAR;

		switch ($sort) {
			case $ref->getConstant(FILECREDIT_SORTBY_COPYRIGHT_ASC):
			case $ref->getConstant(FILECREDIT_SORTBY_COPYRIGHT_DESC):
				$type = SORT_STRING OR SORT_FLAG_CASE;
				break;
			case $ref->getConstant(FILECREDIT_SORTBY_PAGES_ASC):
			case $ref->getConstant(FILECREDIT_SORTBY_PAGES_DESC):
				$type = SORT_NUMERIC;
				break;
		}

		switch ($sort) {
			case $ref->getConstant(FILECREDIT_SORTBY_PAGES_DESC):
			case $ref->getConstant(FILECREDIT_SORTBY_COPYRIGHT_DESC):
				$dir = SORT_DESC;
				break;
		}

		static::array_sort_by_column($arrCredits, 'order', $dir, $type);

		return $arrCredits;
	}


	private static function array_sort_by_column(&$arr, $col, $dir = SORT_ASC, $type = SORT_REGULAR)
	{
		$sort_col = array();
		foreach ($arr as $key => $row) {
			$sort_col[$key] = $row[$col];
		}
		
		array_multisort($sort_col, $dir, $arr, $type);
	}
}