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
$GLOBALS['TL_DCA']['tl_filecredit'] = [

    // Config
    'config'      => [
        'dataContainer'   => 'Table',
        'ctable'          => ['tl_filecredit_page'],
        'sql'             => [
            'keys' => [
                'id'   => 'primary',
                'uuid' => 'unique',
            ],
        ],
        'onload_callback' => [
            ['tl_filecredit', 'checkPermission'],
        ],
    ],
    // List
    'list'        => [
        'sorting'           => [
            'mode'            => 1,
            'panelLayout'     => 'filter;search,limit',
            'fields'          => ['tstamp'],
        ],
        'label'             => [
            'fields'         => ['uuid'],
            'format'         => '%s',
            'label_callback' => ['tl_filecredit', 'listCredits']
        ],
        'global_operations' => [
            'sync' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_filecredit']['sync'],
                'href'            => 'key=sync',
                'class'           => 'header_sync',
                'button_callback' => ['tl_filecredit', 'syncCredits'],
            ],
            'all'  => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"',
            ],
        ],
        'operations'        => [
            'edit'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_filecredit']['edit'],
                'href'  => 'table=tl_filecredit_page',
                'icon'  => 'edit.gif',
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_filecredit']['editheader'],
                'href'  => 'act=edit',
                'icon'  => 'header.gif',
            ],
            'copy'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_filecredit']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete'     => [
                'label'      => &$GLOBALS['TL_LANG']['tl_filecredit']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                    . '\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle'     => [
                'label'           => &$GLOBALS['TL_LANG']['tl_filecredit']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['tl_filecredit', 'toggleIcon'],
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_slick_config']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],
    // Palettes
    'palettes'    => [
        '__selector__' => ['published'],
        'default'      => '{file_legend},uuid,copyright,author;{publish_legend},published',
    ],
    // Subpalettes
    'subpalettes' => [
        'published' => 'start,stop',
    ],
    // Fields
    'fields'      => [
        'id'        => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp'    => [
            'label' => &$GLOBALS['TL_LANG']['tl_filecredit']['tstamp'],
            'flag'  => 5,
            'sql'   => "int(10) unsigned NOT NULL default '0'",
        ],
        'author'    => [
            'label'      => &$GLOBALS['TL_LANG']['tl_filecredit']['author'],
            'default'    => BackendUser::getInstance()->id ?: 0,
            'exclude'    => true,
            'search'     => true,
            'filter'     => true,
            'sorting'    => true,
            'inputType'  => 'select',
            'foreignKey' => 'tl_user.name',
            'eval'       => ['doNotCopy' => true, 'chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'hasOne', 'load' => 'lazy']
        ],
        'uuid'      => [
            'label'      => &$GLOBALS['TL_LANG']['tl_filecredit']['uuid'],
            'inputType'  => 'fileTree',
            'sql'        => "binary(16) NULL",
            'foreignKey' => 'tl_files.path',
            'relation'   => ['type' => 'hasMany', 'load' => 'lazy', 'field' => 'uuid', 'submitOnChange' => true],
            'eval'       => ['filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'doNotCopy' => true],
        ],
        'copyright' => [
            'label'            => $GLOBALS['TL_LANG']['tl_filecredit']['copyright'],
            'inputType'        => 'tagsinput',
            'options_callback' => ['tl_filecredit', 'getCreditOptions'],
            'eval'             => ['maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'long clr', 'freeInput' => true, 'multiple' => true, 'doNotSaveEmpty' => true],
            'reference'        => &$GLOBALS['TL_LANG']['tl_files'],
            'load_callback'    => [
                ['tl_filecredit', 'getCopyright'],
            ],
            'save_callback'    => [
                ['tl_filecredit', 'setCopyRight'],
            ],
        ],
        'checksum'  => [
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'published' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filecredit']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'doNotCopy' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'start'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filecredit']['start'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'stop'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_filecredit']['stop'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
    ],
];


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
     * Check permissions to edit the table
     */
    public function checkPermission()
    {
        if ($this->User->isAdmin) {
            return;
        }

        // Check the additional operation permissions
        switch (Input::get('key')) {
            case 'sync':
                if (!$this->User->hasAccess('sync', 'filecredits')) {
                    $this->log('Not enough permissions to sync filecredits', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;
        }
    }


    /**
     * Return the "toggle visibility" button
     *
     * @param array $row
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
     * @param integer $intId
     * @param boolean $blnVisible
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
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, ($dc ?: $this));
                } elseif (is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, ($dc ?: $this));
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_filecredit SET tstamp=" . time() . ", published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")->execute(
            $intId
        );

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

        if (in_array($objModel->extension, trimsplit(',', \Config::get('validImageTypes')))) {
            $args[0] = \Image::getHtml(\Image::get($objModel->path, 64, 64, 'crop'));
        } else {
            $objFile = new \File($objModel->path, true);

            if ($objFile->icon) {
                $args[0] = \Image::getHtml(TL_ASSETS_URL . 'assets/contao/images/' . $objFile->icon);
            } else {
                $args[0] = '';
            }
        }

        $args[1] = \HeimrichHannot\FileCredit\Backend\FileCredit::parseCopyright($objModel->copyright);

        return $args;
    }


    public function setCopyRight($varValue, DataContainer $dc)
    {
        if (!$dc->activeRecord) {
            return '';
        }

        $objModel = \FilesModel::findByUuid($dc->activeRecord->uuid);

        if ($objModel === null) {
            return '';
        }

        $objVersions = new Versions('tl_files', $objModel->id);
        $objVersions->initialize();

        $objModel->copyright = $varValue;
        $objModel->save();

        $objVersions->create();

        return '';
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

    public function getCreditOptions($dc)
    {
        $arrOptions = [];

        $objFileCredits = \HeimrichHannot\FileCredit\FilesModel::findWithCopyright();

        if ($objFileCredits === null) {
            return $arrOptions;
        }

        while ($objFileCredits->next()) {
            $arrOptions = array_merge($arrOptions, deserialize($objFileCredits->copyright, true));
        }

        return $arrOptions;
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

    /**
     * Return the sync credit button
     *
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $class
     * @param string $attributes
     *
     * @return string
     */
    public function syncCredits($href, $label, $title, $class, $attributes)
    {
        return $this->User->hasAccess('sync', 'filecredits') ? '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '" class="'
            . $class . '"' . $attributes . '>' . $label . '</a> ' : '';
    }
}

