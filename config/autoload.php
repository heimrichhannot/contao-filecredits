<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
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
	'HeimrichHannot\FileCredit\FileCreditModel'       => 'system/modules/filecredits/models/FileCreditModel.php',
	'HeimrichHannot\FileCredit\FileCreditHybridModel' => 'system/modules/filecredits/models/FileCreditHybridModel.php',

	// Modules
	'HeimrichHannot\FileCredit\ModuleFileCredit'      => 'system/modules/filecredits/modules/ModuleFileCredit.php',

	// Classes
	'HeimrichHannot\FileCredit\FileCredit'            => 'system/modules/filecredits/classes/FileCredit.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_filecredit_empty' => 'system/modules/filecredits/templates/modules',
	'mod_filecredit'       => 'system/modules/filecredits/templates/modules',
	'filecredit_default'   => 'system/modules/filecredits/templates/credit',
));
