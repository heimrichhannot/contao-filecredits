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

	public static function parseCredit(FileCreditModel $objModel, $objModule)
	{
		global $objPage;

		$objTemplate = new \FrontendTemplate('filecredit_default');

		// skip if no files model exists
		if(($objFilesModel = $objModel->getRelated('uuid')) === null)
		{
			return null;
		}

		// cleanup: remove credits where copyright was deleted
		if($objFilesModel->copyright == '')
		{
			FileCreditPageModel::deleteByPid($objModel->id);
			$objModel->delete();
			return null;
		}

		// skip if credit occurs on no page
		if(($objCreditPages = FileCreditPageModel::findPublishedByPids(array($objModel->id))) === null)
		{
			return null;
		}

		$arrPages = array();

		while($objCreditPages->next())
		{
			$arrCredit = $objCreditPages->row();

			if ($arrCredit['url'] == '' && ($objTarget = \PageModel::findByPk($arrCredit['page'])) !== null)
			{
				$arrCredit['url'] = \Controller::generateFrontendUrl($objTarget->row());
			}

			$arrPages[] = $arrCredit;
		}

		if($arrPages === null)
		{
			return null;
		}
		
		$objTemplate->setData($objModel->row());
		$objTemplate->fileData = $objFilesModel->row();
		$objTemplate->copyright = $objFilesModel->copyright;
		$objTemplate->link = $GLOBALS['TL_LANG']['MSC']['creditLinkText'];
		$objTemplate->pagesLabel = $GLOBALS['TL_LANG']['MSC']['creditPagesLabel'];
		$objTemplate->path = $objFilesModel->path;

		$objTemplate->pages = $arrPages;

		// colorbox support
		if ($objPage->outputFormat == 'xhtml')
		{
			$strLightboxId = 'lightbox';
		}
		else
		{
			$strLightboxId = 'lightbox[' . substr(md5($objTemplate->getName() . '_' . $objFilesModel->id), 0, 6) . ']';
		}

		$objTemplate->attribute = ($strLightboxId ? ($objPage->outputFormat == 'html5' ? ' data-gallery="#gallery-' . $objModule->id . '" data-lightbox="' : ' rel="') . $strLightboxId .'"' : '');

		return $objTemplate->parse();
	}


	public static function parseCredits(\Model\Collection $objModels, $objModule)
	{
		$arrCredits = array();

		while ($objModels->next()) {

			if (($strReturn = static::parseCredit($objModels->current(), $objModule)) === null)
			{
				continue;
			}

			$arrCredits[] = $strReturn;
		}

		return $arrCredits;
	}
}