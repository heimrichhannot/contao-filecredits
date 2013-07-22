<?php

namespace HeimrichHannot;

abstract class FileCredit extends \Module
{
	/**
	 * URL cache array
	 * @var array
	 */
	private static $arrUrlCache = array();

	protected function parseCredit($objCredit)
	{
		global $objPage;

		$objCredit = FileCreditHybridModel::findRelatedByCredit($objCredit);

		if(is_null($objCredit)) return null;

		$objTemplate = new \FrontendTemplate('filecredit_default');

		$objTemplate->setData($objCredit->file->row());
		// TODO
		$objTemplate->link = $this->generateCreditUrl($objCredit);
		$objTemplate->linkText = $GLOBALS['TL_LANG']['MSC']['creditLinkText'];

		// TODO
		$objTemplate->pageTitle = $objCredit->page->pageTitle ? $objCredit->page->pageTitle : $objCredit->page->title;

		// colorbox support
		if ($objPage->outputFormat == 'xhtml')
		{
			$strLightboxId = 'lightbox';
		}
		else
		{
			$strLightboxId = 'lightbox[' . substr(md5($objTemplate->getName() . '_' . $objCredit->file->id), 0, 6) . ']';
		}

		$objTemplate->attribute = ($strLightboxId ? ($objPage->outputFormat == 'html5' ? ' data-lightbox="' : ' rel="') . $strLightboxId .'"' : '');

		return $objTemplate->parse();
	}

	protected function generateCreditUrl($objCredit)
	{
		$strCacheKey = 'id-' . $objCredit->page->id . '-' . $objCredit->result->ptable . '-' . $objCredit->parent->id;

		// Load the URL from cache
		if (isset(self::$arrUrlCache[$strCacheKey]))
		{
			return self::$arrUrlCache[$strCacheKey];
		}

		self::$arrUrlCache[$strCacheKey] = $this->generateFrontendUrl($objCredit->page->row());

		return self::$arrUrlCache[$strCacheKey];
	}

	protected function getFileCredits()
	{
		$arrAllowedTypes = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['validImageTypes']));

		$arrIds = $this->getChildRecords(array($this->defineRoot), 'tl_page');

		$objCredits = \FileCreditModel::findMultiplePublishedContentElementsByExtensions($arrIds, $arrAllowedTypes);

		if($objCredits === null) return null;

		return $objCredits;
	}
}