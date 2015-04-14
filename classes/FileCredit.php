<?php

namespace HeimrichHannot\FileCredit;

abstract class FileCredit extends \Module
{
	/**
	 * URL cache array
	 * @var array
	 */
	private static $arrUrlCache = array();

	protected $arrPids = array();

	protected function parseCredit($objItem)
	{
		global $objPage;

		$objCredit = new FileCreditHybridModel();

		$objCredit = $objCredit->findRelatedByCredit($objItem, $this->arrPids);

		if(is_null($objCredit)) return null;

		$objTemplate = new \FrontendTemplate('filecredit_default');

		$objTemplate->setData($objCredit->file->row());
		// TODO
		$objTemplate->link = $this->generateCreditUrl($objCredit);
		$objTemplate->linkText = $GLOBALS['TL_LANG']['MSC']['creditLinkText'];

		// TODO
		if($objCredit->page === null && $objCredit->result->usage)
		{
			$objTemplate->pageTitle = $objCredit->result->usage;
			
		}
		else
		{
			$objTemplate->pageTitle = $objCredit->page->pageTitle ? $objCredit->page->pageTitle : $objCredit->page->title;
		}

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
		if($objCredit->page === null) return null;
		
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
			default: 
				if(isset($GLOBALS['TL_FILECREDIT_MODELS'][$objCredit->result->ptable]))
				{
					$autoitem = $GLOBALS['TL_FILECREDIT_MODELS'][$objCredit->result->ptable];
				}
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

		$objSingleSRCCredits = FileCreditModel::findMultiplePublishedSingleSRCContentElementsByExtensions($this->arrPids, $arrAllowedTypes);
		$objMultiSRCCredits = FileCreditModel::findMultiplePublishedMultiSRCContentElements($this->arrPids, $arrAllowedTypes);
		$objMultiSelectedCredits = FileCreditModel::findMultiplePublishedBySelectedCredits(deserialize(($this->selectedCredits)));
		
		if($objSingleSRCCredits === null)
		{
			$objSingleSRCCredits = array();
		}
		
		if($objMultiSRCCredits === null)
		{
			$objMultiSRCCredits = array();
		}
		
		if($objMultiSelectedCredits === null) 
		{
			$objMultiSelectedCredits = array();
		}
		
		$arrAll = array_merge($objSingleSRCCredits, $objMultiSRCCredits, $objMultiSelectedCredits);
		
		if($arrAll === null) return null;
		
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