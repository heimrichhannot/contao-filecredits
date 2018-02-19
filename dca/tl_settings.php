<?php

$dca = &$GLOBALS['TL_DCA']['tl_settings'];

/*
 * Palettes
 */
$palette                   = '{filecredits_legend},fileCreditsDisablePoorMansCron;';
$dca['palettes']['default'] = str_replace('{chmod_legend', $palette . ';{chmod_legend', $dc['palettes']['default']);

/**
 * Fields
 */
$fields = [
    'fileCreditsDisablePoorMansCron' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['fileCreditsDisablePoorMansCron'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50']
    ]
];

$dca['fields'] = array_merge($fields, $dca['fields']);