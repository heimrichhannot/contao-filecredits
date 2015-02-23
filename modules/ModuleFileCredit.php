<?php

namespace HeimrichHannot\FileCredit;

class ModuleFileCredit extends FileCredit
{
	protected $strTemplate = 'mod_filecredit';

	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['filecredit'][0]) . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}
		// TEST
		return parent::generate();
	}

	protected function compile()
	{
		$objCredits = $this->getFileCredits();

		$this->Template->empty = $GLOBALS['TL_LANG']['MSC']['emptyCreditList'];

		$arrCredits = array();
		
		foreach($objCredits as $objCredit)
		{
			$strCredit = $this->parseCredit($objCredit);

			if(is_null($strCredit)) continue;

			$arrCredits[] = $strCredit;
		}

		$this->Template->credits = $arrCredits;
	}
}