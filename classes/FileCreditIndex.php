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
				foreach (array_keys($_GET) as $key)
				{
					if (in_array($key, $GLOBALS['TL_NOINDEX_KEYS']) || strncmp($key, 'page_', 5) === 0)
					{
						return false;
					}
				}

				return true;
			}
		}

		return false;
	}

	public static function addFile($objFile)
	{
		$checksum = md5(preg_replace('/ +/', ' ', strip_tags($objFile->copyright)));

		$objModel = FileCreditModel::findByUuid($objFile->uuid);

		// do not index again if copyright did not change, but add the current page
		if ($objModel !== null && $checksum == $objModel->checksum)
		{
			static::addCurrentPage($objModel);
			return false;
		}

		$arrSet = array
		(
			'tstamp'    => time(),
			'uuid'      => $objFile->uuid,
			'checksum'  => $checksum,
			'published' => 1,
			'start'     => '',
			'stop'      => '',
		);

		if ($objModel !== null)
		{
			// delete: remove credit if copyright is empty
			if ($objFile->copyright == '')
			{
				// remove all pages for the credit before
				FileCreditPageModel::deleteByPid($objModel->id);

				$objModel->delete();

				return false;
			}

			// update: otherwise update existing filecredit
			$objModel->setRow($arrSet);
			$objModel->save();

			static::addCurrentPage($objModel);

			return true;
		}

		// create: add new credit
		if ($objFile->copyright != '')
		{
			$objModel = new FileCreditModel();
			$objModel->setRow($arrSet);
			$objModel->save();

			static::addCurrentPage($objModel);

			return true;
		}

		return false;
	}

	protected static function addCurrentPage(FileCreditModel $objCredit)
	{
		global $objPage;

		$strUrl = preg_replace('/\\?.*/', '', \Environment::get('request')); // strip get parameter from url

		// set url from objPage
		if($strUrl == '')
		{
			$strUrl = \Controller::generateFrontendUrl($objPage->row());

			if($strUrl == '') return false;
		}

		$objModel = FileCreditPageModel::findByPidAndPageAndUrl($objCredit->id, $objPage->id, $strUrl);

		// do not index page again
		if ($objModel !== null)
		{
			return false;
		}

		$arrSet = array
		(
			'pid'       => $objCredit->id,
			'tstamp'    => time(),
			'page'      => $objPage->id,
			'url'       => $strUrl,
			'protected' => ($objPage->protected ? '1' : ''),
			'groups'    => $objPage->groups,
			'language'  => $objPage->language,
			'published' => 1,
			'start'     => '',
			'stop'      => '',
		);


		// create: add new page for the credit
		$objModel = new FileCreditPageModel();
		$objModel->setRow($arrSet);
		$objModel->save();

		return true;
	}

	public static function indexFile(\FilesModel $objFile)
	{
		if (!static::doIndex()) {
			return false;
		}

		if (!static::addFile($objFile)) {
			return false;
		}


		return true;
	}
	
}