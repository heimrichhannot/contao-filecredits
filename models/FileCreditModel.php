<?php

namespace HeimrichHannot;

class FileCreditModel extends \FilesModel
{

	protected static $strTable = 'tl_files';

	public static function findMultiplePublishedContentElementsByExtensionsAndRoot($arrIds, $arrExtensions, array $arrOptions=array())
	{
		if (!is_array($arrIds) || empty($arrIds) || !is_array($arrExtensions) || empty($arrExtensions))
		{
			return null;
		}

		foreach ($arrExtensions as $k=>$v)
		{
			if (!preg_match('/^[a-z0-9]{2,5}$/i', $v))
			{
				unset($arrExtensions[$k]);
			}
		}


		$t = static::$strTable;

		$objDatabase = \Database::getInstance();

		$objResult = $objDatabase->prepare
		(
			"
				SELECT f.id AS fileId, a.id AS articleId, p.id AS pageId, c.id AS contentId FROM tl_content c
				LEFT JOIN $t f on f.id = c.singleSRC
				LEFT JOIN tl_article a ON a.id = c.pid
				LEFT JOIN tl_page p ON p.id = a.pid
				WHERE f.extension IN('" . implode("','", $arrExtensions) . "')
				AND p.id IN('" . implode("','", $arrIds) . "')
				AND f.copyright != ''
				AND a.published = 1
				AND p.published = 1
				AND c.invisible = ''
			"
		)->execute();

		if ($objResult->numRows < 1)
		{
			return null;
		}

		return $objResult;
	}
}