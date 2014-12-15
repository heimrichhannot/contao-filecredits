<?php

namespace HeimrichHannot\FileCredit;

class FileCreditModel extends \FilesModel
{
	protected static $strTable = 'tl_files';
	
	public static function findMultiplePublishedBySelectedCredits($arrCredits)
	{
		$arrReturn = null;
		
		$objDatabase = \Database::getInstance();
		
		$t = static::$strTable;
		
		foreach($arrCredits as $arrCredit)
		{
			$objResult = $objDatabase->prepare
			(
				"
				SELECT NULL AS cid, NULL as ptable, NULL as parent, $t.* FROM $t WHERE id = ?
				"
			)->execute($arrCredit['file']);
			
			if ($objResult->numRows < 1) continue;
			
			$objResult->usage = $arrCredit['usage'];
			
			$arrReturn[] = (object) $objResult->row();
		}
		
		return empty($arrReturn) ? null : $arrReturn;
	}

	public static function findMultiplePublishedMultiSRCContentElements($arrIds, $arrExtensions, array $arrOptions=array())
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
				SELECT c.id AS cid, c.ptable as ptable, c.pid as parent, c.multiSRC FROM tl_content c WHERE c.multiSRC IS NOT NULL
			"
		)->execute();

		if ($objResult->numRows < 1)
		{
			return null;
		}
		
		$arrReturn = null;
		
		while($objResult->next())
		{
			$arrUuids = deserialize($objResult->multiSRC, true);
			
			$objFiles = \FilesModel::findMultipleByUuids($arrUuids);
			
			if($objFiles === null) continue;

			if(!$objFiles->copyright)
			{
				continue;
			}

			if($objFiles->type == 'folder')
			{
				$objSubfiles = \FilesModel::findByPid($objFiles->uuid);
				
				if ($objSubfiles === null)
				{
					continue;
				}
				
				while ($objSubfiles->next())
				{
					// Skip subfolders
					if ($objSubfiles->type == 'folder')
					{
						$objFolderFiles = \FilesModel::findMultipleFilesByFolder($objSubfiles->path);
						
						if($objFolderFiles === null)
						{
							continue;
						}
						
						while($objFolderFiles->next())
						{
							if (!in_array($objFolderFiles->extension, $arrExtensions))
							{
								continue;
							}
								
							if(!$objFolderFiles->copyright)
							{
								continue;
							}
							
							$arrReturn[] = (object) array_merge($objResult->row(), $objFolderFiles->row());
						}
					}
				
					if (!in_array($objSubfiles->extension, $arrExtensions))
					{
						continue;
					}


					if(!$objSubfiles->copyright)
					{
						continue;
					}
					
					$arrReturn[] = (object) array_merge($objResult->row(), $objSubfiles->row());
				}
			}
			else
			{
				$arrReturn[] = (object) array_merge($objResult->row(), $objFiles->row());
			}
			
		}

		return empty($arrReturn) ? null : $arrReturn;
	}
	
	public static function findMultiplePublishedSingleSRCContentElementsByExtensions($arrIds, $arrExtensions, array $arrOptions=array())
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


		// support additional tables
		if(is_array($GLOBALS['TL_FILECREDIT_MODELS']))
		{
			foreach($GLOBALS['TL_FILECREDIT_MODELS'] as $table => $autoitem)
			{
				$addTableSql .= "

					UNION ALL

					SELECT c.id AS cid, '$table' as ptable, c.id as parent, $t.*
					FROM $t
					LEFT JOIN $table c ON c.singleSRC = $t.uuid
					WHERE c.addImage = 1";
			}
		}

		\Controller::loadDataContainer('tl_content');

		$arrPalettes = $GLOBALS['TL_DCA']['tl_content']['palettes'];

		$arrFlatPalettes = array('image');
		$arrImagePalettes = array();

		foreach($arrPalettes as $key => $strPalette)
		{
			if(is_array($strPalette) || strstr($strPalette, 'addImage') === false) continue;

			$arrImagePalettes[] = $key;
		}

		$objResult = $objDatabase->prepare
		(
			"
				SELECT DISTINCT * FROM
				(
					-- singleSRC support
					SELECT c.id AS cid, c.ptable as ptable, c.pid as parent, $t.*
					FROM $t
					LEFT JOIN tl_content c ON c.singleSRC = $t.uuid
					WHERE (c.type IN('" . implode("','", $arrFlatPalettes) . "') OR (c.type IN('" . implode("','", $arrImagePalettes) . "') AND c.addImage = 1))

					UNION ALL

					-- text support
					SELECT c.id AS cid, c.ptable as ptable, c.pid as parent, $t.*
					FROM $t
					LEFT JOIN tl_content c ON c.text LIKE CONCAT('%',$t.path,'%
					UNION ALL

					-- news support
					SELECT c.id AS cid, 'tl_news' as ptable, c.id as parent, $t.*
					FROM $t
					LEFT JOIN tl_news c ON c.singleSRC = $t.uuid
					WHERE c.addImage = 1

					-- support addional tables
					$addTableSql

				) AS files
				WHERE files.extension IN('" . implode("','", $arrExtensions) . "')
				AND files.copyright != '' AND files.type = 'file'
				AND cid IS NOT NULL
			"
		)->execute();

		if ($objResult->numRows < 1)
		{
			return null;
		}

		$arrReturn = null;

		while($objResult->next())
		{
			$arrReturn[] = (object) $objResult->row();
		}

		return empty($arrReturn) ? null : $arrReturn;
	}
}