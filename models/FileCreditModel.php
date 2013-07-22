<?php

namespace HeimrichHannot;

class FileCreditModel extends \FilesModel
{
	protected static $strTable = 'tl_files';

	public static function findMultiplePublishedContentElementsByExtensions($arrIds, $arrExtensions, array $arrOptions=array())
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

// 		$objResult = $objDatabase->prepare
// 		(
// 			"
// 				SELECT f.id AS fileId, c.id AS contentId, c.ptable as ptable FROM tl_content c
// 				LEFT JOIN f ON f.id = c.singleSRC
// 				WHERE f.extension IN('" . implode("','", $arrExtensions) . "')
// 				AND f.copyright != ''
// 				AND c.invisible = ''
// 			"
// 		)->execute();


		$objResult = $objDatabase->prepare
		(
			"
				SELECT DISTINCT * FROM
				(
					-- singleSRC support
					SELECT c.id AS cid, c.ptable as ptable, c.pid as parent, $t.*
					FROM $t
					LEFT JOIN tl_content c ON c.singleSRC = $t.id

					UNION ALL

					-- multiSRC support
					SELECT c.id AS cid, c.ptable as ptable, c.pid as parent, $t.*
					FROM $t
					LEFT JOIN tl_content c ON FIND_IN_SET($t.id, c.orderSRC)

					UNION ALL

					-- news support
					SELECT c.id AS cid, 'tl_news' as ptable, c.pid as parent, $t.*
					FROM $t
					LEFT JOIN tl_news c ON c.singleSRC = $t.id
					WHERE c.addImage = 1
				) AS files
				WHERE files.extension IN('" . implode("','", $arrExtensions) . "')
				AND files.copyright != ''
				AND cid IS NOT NULL
			"
		)->execute();

		if ($objResult->numRows < 1)
		{
			return null;
		}

		return $objResult;
	}
}