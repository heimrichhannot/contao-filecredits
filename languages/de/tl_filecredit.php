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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_filecredit']['tstamp'][0]    = 'Änderungsdatum';
$GLOBALS['TL_LANG']['tl_filecredit']['tstamp'][1]    = 'Datum und Uhrzeit der letzten Änderung';
$GLOBALS['TL_LANG']['tl_filecredit']['uuid'][0]      = 'Quelldatei';
$GLOBALS['TL_LANG']['tl_filecredit']['uuid'][1]      = 'Bitte wählen Sie eine Datei oder einen Ordner aus der Dateiübersicht.';
$GLOBALS['TL_LANG']['tl_filecredit']['published'][0] = 'Quellenangabe veröffentlichen';
$GLOBALS['TL_LANG']['tl_filecredit']['published'][1] = 'Die Quellenangabe auf der Webseite anzeigen.';
$GLOBALS['TL_LANG']['tl_filecredit']['start'][0]     = 'Anzeigen ab';
$GLOBALS['TL_LANG']['tl_filecredit']['start'][1]     = 'Die Quellenangabe nur bis zu diesem Tag auf der Webseite anzeigen.';
$GLOBALS['TL_LANG']['tl_filecredit']['stop'][0]      = 'Anzeigen bis';
$GLOBALS['TL_LANG']['tl_filecredit']['stop'][1]      = 'Die Quellenangabe erst ab diesem Tag auf der Webseite anzeigen.';
$GLOBALS['TL_LANG']['tl_filecredit']['author'][0]    = 'Autor';
$GLOBALS['TL_LANG']['tl_filecredit']['author'][1]    = 'Hier können Sie einen Autor der Bildquelle festlegen. Bildquellen mit Autor werden nicht gewartet.';


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_filecredit']['file_legend']    = 'Datei';
$GLOBALS['TL_LANG']['tl_filecredit']['publish_legend'] = 'Veröffentlichung';


/**
 * Header Fields
 */
$GLOBALS['TL_LANG']['tl_filecredit']['path'][0]      = 'Pfad zur Datei';
$GLOBALS['TL_LANG']['tl_filecredit']['copyright'][0] = 'Quellenangabe';


/**
 * Wizards
 */
$GLOBALS['TL_LANG']['tl_filecredit']['editCopyright'][0] = 'Quellenangabe bearbeiten';
$GLOBALS['TL_LANG']['tl_filecredit']['editCopyright'][1] = 'Quellenangabe %s bearbeiten';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_filecredit']['new']    = ['Neue Quellenangabe', 'Erstellt eine neue Quellenangabe.'];
$GLOBALS['TL_LANG']['tl_filecredit']['edit']   = ['Quellenangabe bearbeiten', 'Quellenangabe ID %s bearbeiten'];
$GLOBALS['TL_LANG']['tl_filecredit']['copy']   = ['Quellenangabe duplizieren', 'Quellenangabe ID %s duplizieren'];
$GLOBALS['TL_LANG']['tl_filecredit']['toggle'] = ['Quellenangabe veröffentlichen', 'Quellenangabe ID %s veröffentlichen/unveröffentlichen'];
$GLOBALS['TL_LANG']['tl_filecredit']['delete'] = ['Quellenangabe löschen', 'Quellenangabe ID %s löschen'];
$GLOBALS['TL_LANG']['tl_filecredit']['show']   = ['Quellenangabe anzeigen', 'Quellenangabe ID %s anzeigen'];
$GLOBALS['TL_LANG']['tl_filecredit']['sync']   = ['Syncronisieren', 'Bildnachweise syncronisieren'];

/**
 * Syncronize
 */
$GLOBALS['TL_LANG']['tl_filecredit']['syncHeadline']          = 'Bildnachweise syncronisieren';
$GLOBALS['TL_LANG']['tl_filecredit']['limitfilecreditpages']  = ['Seitenauswahl eingrenzen', 'Bildnachweise nur für ausgewählte Seiten neu aufbauen.'];
$GLOBALS['TL_LANG']['tl_filecredit']['checkAllLegend']        = 'Verfügbare Seiten';
$GLOBALS['TL_LANG']['tl_filecredit']['syncSubmit']            = 'Bildnachweise syncronisieren';
$GLOBALS['TL_LANG']['tl_filecredit']['noSearchable']          = 'Keine durchsuchbaren Seiten gefunden';
$GLOBALS['TL_LANG']['tl_filecredit']['indexNote']             = 'Bitte warten Sie, bis die Seite vollständig geladen ist, bevor Sie Ihre Arbeit fortsetzen!';
$GLOBALS['TL_LANG']['tl_filecredit']['indexLoading']          = 'Bitte warten Sie, während die Bildnachweise neu aufgebaut werden.';
$GLOBALS['TL_LANG']['tl_filecredit']['indexComplete']         = 'Die Bildnachweise wurden neu aufgebaut. Sie können nun fortfahren.';
$GLOBALS['TL_LANG']['tl_filecredit']['originInfo']            = 'Das Same-Origin-Policy (SOP) Sicherheitskonzept untersagt den meisten Server die Kommunikation mit anderen Systemen über Javascript. Bitte aktivieren Sie unter "System -> Einstellungen" die Option "Add X-Frame Header" und  "Add Access-Control-Allow-Origins Header" um die Kommunikation im Multidomainbetrieb zwischen verschiedenen Instanzen über Javascript zu erlauben.';
$GLOBALS['TL_LANG']['tl_filecredit']['requestInfo']['legend'] = 'Legende:';
$GLOBALS['TL_LANG']['tl_filecredit']['requestInfo']['green']  = 'Grün: Die Bildnachweise der Seite wurden erfolgreich indiziert.';
$GLOBALS['TL_LANG']['tl_filecredit']['requestInfo']['orange'] = 'Orange: Fehler beim Laden der Seite (Statuscode: 404,500…), die Bildnachweise werden anschließend deindexiert.';
$GLOBALS['TL_LANG']['tl_filecredit']['requestInfo']['red']    = 'Rot: Die Bildnachweise wurden erfolgreich deindexiert (Fehler beim Laden der Seite).';