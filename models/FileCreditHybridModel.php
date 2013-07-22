<?php

namespace HeimrichHannot;

class FileCreditHybridModel
{

	public static function findRelatedByCredit($objCredit)
	{
		$objThis = new FileCreditHybridModel();

		$objThis->result = $objCredit;

		$objFile = \FilesModel::findByPk($objCredit->id);
		if($objFile === null) return null;
		$objThis->file = $objFile;

		switch($objCredit->ptable)
		{
			case 'tl_article':

				$objArticle = \ArticleModel::findPublishedById($objCredit->parent);
				if($objArticle === null) return null;
				$objThis->parent = $objArticle;

				$objJumpTo = \PageModel::findPublishedById($objArticle->pid);
				if($objJumpTo == null) return null;
				$objThis->page = $objJumpTo;


			break;
			case 'tl_news':

				$objNews = \NewsModel::findPublishedByPid($objCredit->parent);
				if($objNews === null) return null;

				$objThis->parent = $objNews;

				$objNewsArchive = \NewsArchiveModel::findByPk($objNews->pid);

				$objJumpTo = \PageModel::findPublishedById($objNewsArchive->jumpTo);
				if($objJumpTo == null) return null;
				$objThis->page = $objJumpTo;

			break;
		}

		return $objThis;
	}
}