<?php

$dca = &$GLOBALS['TL_DCA']['tl_settings'];

/**
 * Palettes
 */
$dca['palettes']['default'] .= ';{file_credit_legend},deactivateFileCreditsCron;';

/**
 * Fields
 */
$dca['fields']['deactivateFileCreditsCron'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['deactivateFileCreditsCron'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "char(1) NOT NULL default ''"
];