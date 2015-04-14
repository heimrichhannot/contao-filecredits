<?php

namespace HeimrichHannot\FileCredit;

class FileCreditHybridModel extends \Controller
{
	public $result;
	public $file;
	public $parent;
	public $page;

	public function __construct()
	{
		parent::__construct();
	}
	
	public function findRelatedByCredit($objCredit, $arrPids)
	{
		$this->result = $objCredit;

		$objFile = \FilesModel::findByPk($objCredit->id);
		if($objFile === null) return null;
		$this->file = $objFile;
		
		switch($objCredit->ptable)
		{
			case 'tl_article':

				$objArticle = \ArticleModel::findPublishedById($objCredit->parent);
				if($objArticle === null) return null;
				$this->parent = $objArticle;

				$objJumpTo = $objArticle->getRelated('pid');
				if($objJumpTo == null) return null;

				if(!in_array($objJumpTo->id, $arrPids)) return null;
				
				$this->page = $objJumpTo;

			break;
			case 'tl_news':
				$objNews = \NewsModel::findByPk($objCredit->parent);

				if($objNews === null) return null;

				$this->parent = $objNews->current();

				$objNewsArchive = \NewsArchiveModel::findByPk($objNews->pid);

				$objJumpTo = \PageModel::findPublishedById($objNewsArchive->jumpTo);
				
				if($objJumpTo == null) return null;

				if(!in_array($objJumpTo->id, $arrPids)) return null;

				$this->page = $objJumpTo;


				break;
			default:
				$this->parent = null;
				$this->page = null;
				
				// TODO refactor
				if(isset($GLOBALS['TL_FILECREDIT_MODELS'][$objCredit->ptable]))
				{
					$strClass = $GLOBALS['TL_MODELS'][$objCredit->ptable];
					
					if (!$this->classFileExists($strClass)) return null;
					
					$this->loadDataContainer($objCredit->ptable);
					
					$archiveTable = $GLOBALS['TL_DCA'][$objCredit->ptable]['config']['ptable'];
					
					if(!$archiveTable || !isset($GLOBALS['TL_MODELS'][$archiveTable])) return null;

					$strArchiveClass = $GLOBALS['TL_MODELS'][$archiveTable];
					
					if (!$this->classFileExists($strArchiveClass)) return null;
					
					$objItem = $strClass::findByPk($objCredit->parent);
					
					if($objItem === null) return null;
					
					$this->parent = $objItem->current();
					
					$objItemArchive = $strArchiveClass::findByPk($objItem->pid);
					
					
					$objJumpTo = \PageModel::findPublishedById($objItemArchive->jumpTo);
					
					if($objJumpTo == null) return null;

					if(!in_array($objJumpTo->id, $arrPids)) return null;
					
					$this->page = $objJumpTo;
				}
				
		}

		return $this;
	}
}
