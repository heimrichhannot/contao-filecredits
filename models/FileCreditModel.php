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

		$objResult = $objDatabase->prepare
		(
			"
				SELECT DISTINCT * FROM
				(
					-- singleSRC support
					SELECT c.id AS cid, c.ptable as ptable, c.pid as parent, $t.*
					FROM $t
					LEFT JOIN tl_content c ON c.singleSRC = $t.id
					WHERE (c.type = 'image' OR c.type='text' AND c.addImage = 1)

					UNION ALL

					-- text support
					SELECT c.id AS cid, c.ptable as ptable, c.pid as parent, $t.*
					FROM $t
					LEFT JOIN tl_content c ON c.text LIKE CONCAT('%',$t.path,'%')

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