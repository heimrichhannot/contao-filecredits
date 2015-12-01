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
 * Table tl_filecredit
 */
$GLOBALS['TL_DCA']['tl_filecredit'] = array
(

	// Config
	'config'      => array
	(
		'dataContainer' => 'Table',
		'ctable'        => array('tl_filecredit_page'),
		'sql'           => array
		(
			'keys' => array
			(
				'id'   => 'primary',
				'uuid' => 'unique',
			),
		),
	),
	// List
	'list'        => array(
		'sorting'           => array
		(
			'mode'        => 1,
			'flag'        => 1,
			'panelLayout' => 'filter;search,limit',
			'fields'      => array('uuid'),
		),
		'label'             => array
		(
			'fields'         => array('uuid'),
			'format'         => '%s',
			'label_callback' => array('tl_filecredit', 'listCredits'),
			'group_callback' => array('tl_filecredit', 'groupCredits'),
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
			'edit'       => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_filecredit']['edit'],
				'href'  => 'table=tl_filecredit_page',
				'icon'  => 'edit.gif',
			),
			'editheader' => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_filecredit']['editheader'],
				'href'  => 'act=edit',
				'icon'  => 'header.gif',
			),
			'copy'       => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_filecredit']['copy'],
				'href'  => 'act=copy',
				'icon'  => 'copy.gif',
			),
			'delete'     => array
			(
				'label'      => &$GLOBALS['TL_LANG']['tl_filecredit']['delete'],
				'href'       => 'act=delete',
				'icon'       => 'delete.gif',
				'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
								. '\'))return false;Backend.getScrollOffset()"',
			),
			'toggle'     => array
			(
				'label'           => &$GLOBALS['TL_LANG']['tl_filecredit']['toggle'],
				'icon'            => 'visible.gif',
				'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback' => array('tl_filecredit', 'toggleIcon'),
			),
			'show'       => array(
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
		'default'      => '{file_legend},uuid,copyright;{publish_legend},published',
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
		'tstamp'    => array
		(
			'label' => &$GLOBALS['TL_LANG']['tl_filecredit']['tstamp'],
			'sql'   => "int(10) unsigned NOT NULL default '0'",
		),
		'uuid'      => array
		(
			'label'      => &$GLOBALS['TL_LANG']['tl_filecredit']['uuid'],
			'inputType'  => 'fileTree',
			'sql'        => "binary(16) NULL",
			'foreignKey' => 'tl_files.path',
			'relation'   => array('type' => 'hasMany', 'load' => 'lazy', 'field' => 'uuid', 'submitOnChange' => true),
			'eval'       => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'doNotCopy' => true),
		),
		'copyright' => array
		(
			'label'         => $GLOBALS['TL_LANG']['tl_filecredit']['copyright'],
			'inputType'     => 'text',
			'load_callback' => array
			(
				array('tl_filecredit', 'getCopyright'),
			),
			'wizard'        => array
			(
				array('tl_filecredit', 'editCopyright'),
			),
			'eval'          => array('readonly' => true),
		),
		'checksum'  => array
		(
			'sql' => "varchar(32) NOT NULL default ''",
		),
		'published' => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_filecredit']['published'],
			'exclude'   => true,
			'filter'    => true,
			'flag'      => 1,
			'inputType' => 'checkbox',
			'eval'      => array('submitOnChange' => true, 'doNotCopy' => true),
			'sql'       => "char(1) NOT NULL default ''",
		),
		'start'     => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_filecredit']['start'],
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => array('rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'),
			'sql'       => "varchar(10) NOT NULL default ''",
		),
		'stop'      => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_filecredit']['stop'],
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => array('rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'),
			'sql'       => "varchar(10) NOT NULL default ''",
		),
	),
);


class tl_filecredit extends \Backend
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
		if (!$this->User->hasAccess('tl_filecredit::published', 'alexf')) {
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
		if (!$this->User->hasAccess('tl_filecredit::published', 'alexf')) {
			$this->log('Not enough permissions to publish/unpublish filecredit item ID "' . $intId . '"', __METHOD__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_filecredit']['fields']['published']['save_callback'])) {
			foreach ($GLOBALS['TL_DCA']['tl_filecredit']['fields']['published']['save_callback'] as $callback) {
				if (is_array($callback)) {
					$this->import($callback[0]);
					$blnVisible = $this->$callback[0]->$callback[1]($blnVisible, ($dc ?: $this));
				} elseif (is_callable($callback)) {
					$blnVisible = $callback($blnVisible, ($dc ?: $this));
				}
			}
		}

		// Update the database
		$this->Database->prepare("UPDATE tl_filecredit SET tstamp=" . time() . ", published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
			->execute($intId);

	}

	public function groupCredits($group, $mode, $field, $arrRow, DataContainer $dc)
	{
		$objModel = \FilesModel::findByUuid($arrRow['uuid']);

		if ($objModel === null) {
			return $group;
		}

		return $objModel->path;
	}

	
	public function listCredits($arrRow, $strLabel, DataContainer $dc, $args)
	{
		$objModel = \FilesModel::findByUuid($arrRow['uuid']);

		if ($objModel === null) {
			return $strLabel;
		}
		
		if (in_array($objModel->extension, trimsplit(',', \Config::get('validImageTypes'))))
		{
			$args[0] = \Image::getHtml(\Image::get($objModel->path, 64, 64, 'crop'));
		} else
		{
			$objFile = new \File($objModel->path, true);

			if($objFile->icon)
			{
				$args[0] =  \Image::getHtml(TL_ASSETS_URL . 'assets/contao/images/' . $objFile->icon);
			}
			else
			{
				$args[0] = '';
			}
		}

		$args[1] = $objModel->copyright;

		return $args;
	}


	public function getCopyright($varValue, DataContainer $dc)
	{
		if (!$dc->activeRecord) {
			return '';
		}

		$objModel = \FilesModel::findByUuid($dc->activeRecord->uuid);

		if ($objModel === null) {
			return '';
		}

		return $objModel->copyright;
	}


	/**
	 * Return the edit copyright wizard
	 *
	 * @param DataContainer $dc
	 *
	 * @return string
	 */
	public function editCopyright(DataContainer $dc)
	{
		if (!$dc->activeRecord) {
			return '';
		}
		
		$objModel = \FilesModel::findByUuid($dc->activeRecord->uuid);

		if ($objModel === null) {
			return '';
		}

		return (!$objModel->path)
			? ''
			: ' <a href="contao/main.php?do=files&amp;amp;act=edit&amp;id=' . $objModel->path . '&amp;popup=1&amp;nb=1&amp;rt=' . REQUEST_TOKEN
			  . '" title="' . sprintf(
				  specialchars($GLOBALS['TL_LANG']['tl_filecredit']['editCopyright'][1]),
				  $dc->value
			  ) . '" style="padding-left:3px" onclick="Backend.openModalIframe({\'width\':768,\'title\':\'' . specialchars(
				  str_replace("'", "\\'", sprintf($GLOBALS['TL_LANG']['tl_filecredit']['editCopyright'][1], $dc->value))
			  ) . '\',\'url\':this.href});return false">' . Image::getHtml(
				'alias.gif',
				$GLOBALS['TL_LANG']['tl_filecredit']['editCopyright'][1][0],
				'style="vertical-align:top"'
			) . '</a>';
	}
}

