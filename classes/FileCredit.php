<?php

namespace HeimrichHannot\FileCredit;

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

		$objTemplate->attribute = ($strLightboxId ? ($objPage->outputFormat == 'html5' ? ' data-gallery="#gallery-' . $this->id . '" data-lightbox="' : ' rel="') . $strLightboxId .'"' : '');
		
		return $objTemplate->parse();
	}

	protected function generateCreditUrl($objCredit)
	{
		$strCacheKey = 'id-' . $objCredit->page->id . '-' . $objCredit->result->ptable . '-' . $objCredit->parent->id;

		$autoitem = null;
		
		switch($objCredit->result->ptable)
		{
			case 'tl_news':
				$autoitem = 'items';
				break;
			case 'tl_calendar_events':
				$autoitem = 'events';
				break;
		}

		// Load the URL from cache
		if (isset(self::$arrUrlCache[$strCacheKey]))
		{
			return self::$arrUrlCache[$strCacheKey];
		}

		if(is_null($autoitem))
		{
			self::$arrUrlCache[$strCacheKey] = $this->generateFrontendUrl($objCredit->page->row());
		}
		else
		{
			self::$arrUrlCache[$strCacheKey] = ampersand($this->generateFrontendUrl($objCredit->page->row(), (($GLOBALS['TL_CONFIG']['useAutoItem'] && !$GLOBALS['TL_CONFIG']['disableAlias']) ?  '/' : '/' . $autoitem . '/') . ((!$GLOBALS['TL_CONFIG']['disableAlias'] && $objCredit->parent->alias != '') ? $objCredit->parent->alias : $objCredit->parent->id)));
		}

		return self::$arrUrlCache[$strCacheKey];
	}

	protected function getFileCredits()
	{
		$arrAllowedTypes = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['validImageTypes']));

		$arrIds = $this->getChildRecords(array($this->defineRoot), 'tl_page');

		$objSingleSRCCredits = FileCreditModel::findMultiplePublishedSingleSRCContentElementsByExtensions($arrIds, $arrAllowedTypes);
		
		$objMultiSRCCredits = FileCreditModel::findMultiplePublishedMultiSRCContentElements($arrIds, $arrAllowedTypes);
		
		if($objSingleSRCCredits === null || $objMultiSRCCredits === null) return null;

		$arrAll = array_merge($objSingleSRCCredits, $objMultiSRCCredits);
		
		uasort($arrAll, 'HeimrichHannot\FileCredit\FileCredit::sortByParent');
		
		return $arrAll;
	}
	
	
	public static function sortByParent($a, $b)
	{
		if ($a->parent == $b->parent)
		{
			return 0;
		}
		return ($b->parent > $a->parent) ? 1 : -1;
	}
}