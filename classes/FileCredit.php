<?php

namespace HeimrichHannot;

abstract class FileCredit extends \Module
{
	protected function parseCredit($objCredit)
	{
		global $objPage;

		$objTemplate = new \FrontendTemplate('filecredit_default');

		$objJumpTo = \PageModel::findByPk($objCredit->pageId);
		$objFile = \FilesModel::findByPk($objCredit->fileId);

		$objTemplate->setData($objFile->row());
		$objTemplate->link = $this->generateFrontendUrl($objJumpTo->row());
		$objTemplate->linkText = $GLOBALS['TL_LANG']['MSC']['creditLinkText'];
		$objTemplate->pageTitle = $objJumpTo->pageTitle ? $objJumpTo->pageTitle : $objJumpTo->title;

		// colorbox support
		if ($objPage->outputFormat == 'xhtml')
		{
			$strLightboxId = 'lightbox';
		}
		else
		{
			$strLightboxId = 'lightbox[' . substr(md5($objTemplate->getName() . '_' . $objFile->id), 0, 6) . ']';
		}

		$objTemplate->attribute = ($strLightboxId ? ($objPage->outputFormat == 'html5' ? ' data-lightbox="' : ' rel="') . $strLightboxId .'"' : '');

		return $objTemplate->parse();
	}

	protected function getFileCredits()
	{
		$arrAllowedTypes = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['validImageTypes']));

		$arrIds = $this->getChildRecords(array($this->defineRoot), 'tl_page');

		$objCredits = \FileCreditModel::findMultiplePublishedContentElementsByExtensionsAndRoot($arrIds, $arrAllowedTypes);

		if($objCredits === null) return null;

		return $objCredits;
	}

}