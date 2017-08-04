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

class ModuleFileCredit extends \Module
{
	protected $strTemplate = 'mod_filecredit';

	public function generate()
	{
		if (TL_MODE == 'BE') {
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['filecredit'][0]) . ' ###';
			$objTemplate->title    = $this->headline;
			$objTemplate->id       = $this->id;
			$objTemplate->link     = $this->name;
			$objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}


		return parent::generate();
	}

	protected function compile()
	{
		FileCredit::indexStop();

		$arrPids = [];

		if ($this->defineRoot && $this->rootPage > 0) {
			if (($objRoot = $this->objModel->getRelated('rootPage')) !== null) {
				$arrPids = \Database::getInstance()->getChildRecords([$objRoot->id], 'tl_page');
			}
		}

		$objCredits = FileCreditModel::findByPublished();

		if ($objCredits === null) {
			$this->Template->empty = $GLOBALS['TL_LANG']['MSC']['emptyCreditList'];

			return;
		}

		$this->Template->cssClass = 'sortby_' . $this->creditsSortBy;
		$this->Template->cssClass .= $this->creditsGroupBy ? (' groupby_' . $this->creditsGroupBy) : '';
		$this->Template->group = $this->creditsGroupBy;
		$this->Template->credits = FileCredit::parseCredits($objCredits, $arrPids, $this);

		FileCredit::indexContinue();
	}
}