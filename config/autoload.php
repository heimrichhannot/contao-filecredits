<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'HeimrichHannot',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Models
	'HeimrichHannot\FileCredit\FileCreditModel'     => 'system/modules/filecredits/models/FileCreditModel.php',
	'HeimrichHannot\FileCredit\FileCreditPageModel' => 'system/modules/filecredits/models/FileCreditPageModel.php',
	'HeimrichHannot\FileCredit\FilesModel'          => 'system/modules/filecredits/models/FilesModel.php',

	// Modules
	'HeimrichHannot\FileCredit\ModuleFileCredit'    => 'system/modules/filecredits/modules/ModuleFileCredit.php',

	// Classes
	'HeimrichHannot\FileCredit\Automator'           => 'system/modules/filecredits/classes/Automator.php',
	'HeimrichHannot\FileCredit\Hooks'               => 'system/modules/filecredits/classes/Hooks.php',
	'HeimrichHannot\FileCredit\Backend\FileCredit'  => 'system/modules/filecredits/classes/Backend/FileCredit.php',
	'HeimrichHannot\FileCredit\Validator'           => 'system/modules/filecredits/classes/Validator.php',
	'Contao\Feed'                                   => 'system/modules/filecredits/classes/Feed.php',
	'HeimrichHannot\FileCredit\FileCreditIndex'     => 'system/modules/filecredits/classes/FileCreditIndex.php',
	'HeimrichHannot\FileCredit\FileCredit'          => 'system/modules/filecredits/classes/FileCredit.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_filecredit_empty'                   => 'system/modules/filecredits/templates/modules',
	'mod_filecredit'                         => 'system/modules/filecredits/templates/modules',
	'filecredit_grouped'                     => 'system/modules/filecredits/templates/credit',
	'filecreditgroup_copyright'              => 'system/modules/filecredits/templates/credit',
	'filecredit_default'                     => 'system/modules/filecredits/templates/credit',
	'be_filecredits_sync'                    => 'system/modules/filecredits/templates/backend',
	'be_filecredits_sync_pageselection'      => 'system/modules/filecredits/templates/backend',
	'be_filecredits_sync_pageselection_tree' => 'system/modules/filecredits/templates/backend',
));
