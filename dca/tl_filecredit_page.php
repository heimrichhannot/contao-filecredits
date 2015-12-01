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

/**
 * Table tl_filecredit_page
 */
$GLOBALS['TL_DCA']['tl_filecredit_page'] = array
(

	// Config
	'config'      => array
	(
		'dataContainer' => 'Table',
		'ptable'        => 'tl_filecredit',
		'sql'           => array
		(
			'keys' => array
			(
				'id'   => 'primary',
				'pid'  => 'index',
				'page' => 'index',
				'url'  => 'index',
			),
		),
	),
	// List
	'list'        => array(
		'sorting'           => array
		(
			'mode'                  => 4,
			'fields'                => array('page ASC'),
			'headerFields'          => array('uuid', 'tstamp'),
			'panelLayout'           => 'filter;sort,search,limit',
			'child_record_callback' => array('tl_filecredit_page', 'listCreditPages'),
			'child_record_class'    => 'no_padding',
			'header_callback'       => array('tl_filecredit_page', 'parseHeader'),
		),
		'label'             => array
		(
			'fields' => array('url'),
			'format' => '%s',
		),
		'global_operations' => array(
			'all' => array(
				'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'       => 'act=select',
				'class'      => 'header_edit_all',
				'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"',
			),
		),
		'operations'        => array(
			'edit'   => array(
				'label' => &$GLOBALS['TL_LANG']['tl_filecredit_page']['edit'],
				'href'  => 'act=edit',
				'icon'  => 'edit.gif',
			),
			'copy'   => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_filecredit_page']['copy'],
				'href'  => 'act=copy',
				'icon'  => 'copy.gif',
			),
			'delete' => array
			(
				'label'      => &$GLOBALS['TL_LANG']['tl_filecredit_page']['delete'],
				'href'       => 'act=delete',
				'icon'       => 'delete.gif',
				'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
								. '\'))return false;Backend.getScrollOffset()"',
			),
			'toggle' => array
			(
				'label'           => &$GLOBALS['TL_LANG']['tl_filecredit_page']['toggle'],
				'icon'            => 'visible.gif',
				'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback' => array('tl_filecredit_page', 'toggleIcon'),
			),
			'show'   => array(
				'label' => &$GLOBALS['TL_LANG']['tl_slick_config']['show'],
				'href'  => 'act=show',
				'icon'  => 'show.gif',
			),
		),
	),
	// Palettes
	'palettes'    => array
	(
		'__selector__' => array('published'),
		'default'      => '{page_legend},page,url;{publish_legend},published',
	),
	// Subpalettes
	'subpalettes' => array
	(
		'published' => 'start,stop',
	),
	// Fields
	'fields'      => array
	(
		'id'        => array
		(
			'sql' => "int(10) unsigned NOT NULL auto_increment",
		),
		'pid'       => array
		(
			'sql' => "int(10) unsigned NOT NULL default '0'",
		),
		'tstamp'    => array
		(
			'sql' => "int(10) unsigned NOT NULL default '0'",
		),
		'page'      => array
		(
			'label'      => &$GLOBALS['TL_LANG']['tl_filecredit_page']['page'],
			'inputType'  => 'pageTree',
			'sql'        => "int(10) unsigned NOT NULL default '0'",
			'foreignKey' => 'tl_page.title',
			'relation'   => array('type' => 'hasMany', 'load' => 'lazy'),
			'eval'       => array('mandatory' => true),
		),
		'url'       => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_filecredit_page']['url'],
			'inputType' => 'text',
			'sql'       => "varchar(255) NOT NULL default ''",
		),
		'protected' => array
		(
			'sql' => "char(1) NOT NULL default ''",
		),
		'groups'    => array
		(
			'sql' => "blob NULL",
		),
		'language'  => array
		(
			'sql' => "varchar(5) NOT NULL default ''",
		),
		'published' => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_filecredit_page']['published'],
			'exclude'   => true,
			'filter'    => true,
			'flag'      => 1,
			'inputType' => 'checkbox',
			'eval'      => array('submitOnChange' => true, 'doNotCopy' => true),
			'sql'       => "char(1) NOT NULL default ''",
		),
		'start'     => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_filecredit_page']['start'],
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => array('rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'),
			'sql'       => "varchar(10) NOT NULL default ''",
		),
		'stop'      => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_filecredit_page']['stop'],
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => array('rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'),
			'sql'       => "varchar(10) NOT NULL default ''",
		),
	),
);


class tl_filecredit_page extends \Backend
{
	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}


	/**
	 * Return the "toggle visibility" button
	 *
	 * @param array  $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 *
	 * @return string
	 */
	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		if (strlen(Input::get('tid'))) {
			$this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
			$this->redirect($this->getReferer());
		}

		// Check permissions AFTER checking the tid, so hacking attempts are logged
		if (!$this->User->hasAccess('tl_filecredit_page::published', 'alexf')) {
			return '';
		}

		$href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

		if (!$row['published']) {
			$icon = 'invisible.gif';
		}

		return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label)
			   . '</a> ';
	}


	/**
	 * Disable/enable a user group
	 *
	 * @param integer       $intId
	 * @param boolean       $blnVisible
	 * @param DataContainer $dc
	 */
	public function toggleVisibility($intId, $blnVisible, DataContainer $dc = null)
	{
		// Check permissions to edit
		Input::setGet('id', $intId);
		Input::setGet('act', 'toggle');

		// Check permissions to publish
		if (!$this->User->hasAccess('tl_filecredit_page::published', 'alexf')) {
			$this->log('Not enough permissions to publish/unpublish filecredit item ID "' . $intId . '"', __METHOD__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_filecredit_page']['fields']['published']['save_callback'])) {
			foreach ($GLOBALS['TL_DCA']['tl_filecredit_page']['fields']['published']['save_callback'] as $callback) {
				if (is_array($callback)) {
					$this->import($callback[0]);
					$blnVisible = $this->$callback[0]->$callback[1]($blnVisible, ($dc ?: $this));
				} elseif (is_callable($callback)) {
					$blnVisible = $callback($blnVisible, ($dc ?: $this));
				}
			}
		}

		// Update the database
		$this->Database->prepare("UPDATE tl_filecredit_page SET tstamp=" . time() . ", published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
			->execute($intId);

	}

	public function listCreditPages($arrRow)
	{
		$url = $arrRow['url'];
		
		if ($url == '' && ($objTarget = \PageModel::findByPk($arrRow['page'])) !== null) {
			$url = \Controller::generateFrontendUrl($objTarget->row());
		}

		return '<div class="tl_content_left">' . $url . ' <span style="color:#b3b3b3;padding-left:3px">[' . Date::parse(
			Config::get('datimFormat'),
			$arrRow['date']
		) . ']</span></div>';
	}


	public function parseHeader($arrRow, DataContainer $dc)
	{
		$objCredit = HeimrichHannot\FileCredit\FileCreditModel::findByPk($dc->id);

		if ($objCredit == null) {
			return $arrRow;
		}

		$objModel = \FilesModel::findByUuid($objCredit->uuid);
		
		if ($objModel == null) {
			return $arrRow;
		}
		
		if ($objModel !== null) {
			if (in_array($objModel->extension, trimsplit(',', \Config::get('validImageTypes')))) {
				$preview = \Image::getHtml(\Image::get($objModel->path, 64, 64, 'crop'));
			} else {
				$preview[0] = $objModel->name;
			}

			$arrData[$GLOBALS['TL_LANG']['tl_filecredit']['uuid'][0]] = $preview;

			$arrData[$GLOBALS['TL_LANG']['tl_filecredit']['path'][0]]      = $objModel->path;
			$arrData[$GLOBALS['TL_LANG']['tl_filecredit']['copyright'][0]] = $objModel->copyright;
		}

		return $arrData;
	}
}