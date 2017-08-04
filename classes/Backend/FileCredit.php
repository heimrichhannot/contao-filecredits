<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FileCredit\Backend;


use HeimrichHannot\FileCredit\Automator;

class FileCredit extends \Backend implements \executable
{

    /**
     * Return true if the module is active
     *
     * @return boolean
     */
    public function isActive()
    {
        return (\Config::get('enableSearch') && \Input::get('act') == 'index');
    }

    /**
     * Generate the module
     *
     * @return string
     */
    public function run()
    {
        $this->import('BackendUser', 'User');

        $this->registerEvents();

        $time = time();

        /** @var \BackendTemplate|object $objTemplate */
        $objTemplate                = new \BackendTemplate('be_filecredits_sync');
        $objTemplate->action        = ampersand(\Environment::get('request'));
        $objTemplate->syncHeadline  = $GLOBALS['TL_LANG']['tl_filecredit']['syncHeadline'];
        $objTemplate->isActive      = $this->isActive();
        $objTemplate->pageSelection = $this->generatePageSelection();

        if (!\Config::get('headerAddXFrame') || !\Config::get('headerAllowOrigins'))
        {
            $objTemplate->originInfo = $GLOBALS['TL_LANG']['tl_filecredit']['originInfo'];
        }

        // Add the error message
        if ($_SESSION['REBUILD_FILECREDIT_ERROR'] != '')
        {
            $objTemplate->indexMessage            = $_SESSION['REBUILD_FILECREDIT_ERROR'];
            $_SESSION['REBUILD_FILECREDIT_ERROR'] = '';
        }

        // Rebuild the index
        if (\Input::get('act') == 'index')
        {
            // Check the request token (see #4007)
            if (!isset($_GET['rt']) || !\RequestToken::validate(\Input::get('rt')))
            {
                $this->Session->set('INVALID_TOKEN_URL', \Environment::get('request'));
                $this->redirect('contao/confirm.php');
            }

            $arrPages = static::findFileCreditPages();

            // HOOK: take additional pages (news, events…)
            if (isset($GLOBALS['TL_HOOKS']['getSearchablePages']) && is_array($GLOBALS['TL_HOOKS']['getSearchablePages']))
            {
                foreach ($GLOBALS['TL_HOOKS']['getSearchablePages'] as $callback)
                {
                    $this->import($callback[0]);
                    $arrPages = $this->{$callback[0]}->{$callback[1]}($arrPages);
                }
            }

            $blnTruncateTable = true;

            if (\Input::get('limitfilecreditpages'))
            {
                $arrSelectedPages = \Input::get('filecreditpages');

                if (is_array($arrSelectedPages) && !empty($arrSelectedPages))
                {
                    $arrPages         = array_keys(array_intersect(array_flip($arrPages), $arrSelectedPages));
                    $blnTruncateTable = false;
                }
            }

            // Return if there are no pages
            if (empty($arrPages))
            {
                $_SESSION['REBUILD_FILECREDIT_ERROR'] = $GLOBALS['TL_LANG']['tl_filecredit']['noSearchable'];
                \Controller::redirect(\System::getReferer());
            }

            // Truncate the search tables
            if ($blnTruncateTable)
            {
                Automator::purgeFileCreditTables();
            }

            // Hide unpublished elements
            $this->setCookie('FE_PREVIEW', 0, ($time - 86400));

            // Calculate the hash
            $strHash = sha1(session_id() . (!\Config::get('disableIpCheck') ? \Environment::get('ip') : '') . 'FE_USER_AUTH');

            // Remove old sessions
            $this->Database->prepare("DELETE FROM tl_session WHERE tstamp<? OR hash=?")->execute(($time - \Config::get('sessionTimeout')), $strHash);

            // Log in the front end user
            if (is_numeric(\Input::get('user')) && \Input::get('user') > 0)
            {
                // Insert a new session
                $this->Database->prepare("INSERT INTO tl_session (pid, tstamp, name, sessionID, ip, hash) VALUES (?, ?, ?, ?, ?, ?)")->execute(
                        \Input::get('user'),
                        $time,
                        'FE_USER_AUTH',
                        session_id(),
                        \Environment::get('ip'),
                        $strHash
                    );

                // Set the cookie
                $this->setCookie('FE_USER_AUTH', $strHash, ($time + \Config::get('sessionTimeout')), null, null, false, true);
            } // Log out the front end user
            else
            {
                // Unset the cookies
                $this->setCookie('FE_USER_AUTH', $strHash, ($time - 86400), null, null, false, true);
                $this->setCookie('FE_AUTO_LOGIN', \Input::cookie('FE_AUTO_LOGIN'), ($time - 86400), null, null, false, true);
            }

            $strBuffer = '';
            $rand      = rand();

            // Display the pages
            for ($i = 0, $c = count($arrPages); $i < $c; $i++)
            {
                if (!\Validator::isUrl($arrPages[$i]))
                {
                    continue;
                }

                $strBuffer .= '<span class="page_url" data-url="' . $arrPages[$i] . '#' . $rand . $i . '">' . \StringUtil::substr($arrPages[$i], 100)
                              . '</span><br>';
                unset($arrPages[$i]); // see #5681
            }

            $objTemplate->content       = $strBuffer;
            $objTemplate->note          = $GLOBALS['TL_LANG']['tl_filecredit']['indexNote'];
            $objTemplate->loading       = $GLOBALS['TL_LANG']['tl_filecredit']['indexLoading'];
            $objTemplate->complete      = $GLOBALS['TL_LANG']['tl_filecredit']['indexComplete'];
            $objTemplate->indexContinue = $GLOBALS['TL_LANG']['MSC']['continue'];
            $objTemplate->theme         = \Backend::getTheme();
            $objTemplate->isRunning     = true;
        }

        // Default variables
        $objTemplate->indexSubmit = $GLOBALS['TL_LANG']['tl_filecredit']['syncSubmit'];
        $objTemplate->backHref    = \System::getReferer(true);
        $objTemplate->backTitle   = specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']);
        $objTemplate->backButton  = $GLOBALS['TL_LANG']['MSC']['backBT'];

        return $objTemplate->parse();
    }

    /**
     * Sync credits
     */
    public function sync()
    {
        return $this->run();
    }

    protected function generatePageSelection()
    {
        $objTemplate                            = new \BackendTemplate('be_filecredits_sync_pageselection');
        $objTemplate->limitFileCreditPagesLabel = $GLOBALS['TL_LANG']['tl_filecredit']['limitfilecreditpages'];

        return $objTemplate->parse();
    }

    protected function registerEvents()
    {
        if (\Environment::get('isAjaxRequest') && \Input::get('action') == 'toggleFileCreditPages')
        {
            if (\Input::get('state') == 1)
            {
                $arrPages = static::findFileCreditPages();

                // HOOK: take additional pages
                if (isset($GLOBALS['TL_HOOKS']['getSearchablePages']) && is_array($GLOBALS['TL_HOOKS']['getSearchablePages']))
                {
                    foreach ($GLOBALS['TL_HOOKS']['getSearchablePages'] as $callback)
                    {
                        $arrPages = \System::importStatic($callback[0])->{$callback[1]}($arrPages);
                    }
                }
            }

            $objTemplate                 = new \BackendTemplate('be_filecredits_sync_pageselection_tree');
            $objTemplate->pages          = is_array($arrPages) ? $arrPages : [];
            $objTemplate->checkAllLegend = $GLOBALS['TL_LANG']['tl_filecredit']['checkAllLegend'];
            die($objTemplate->parse());
        }
    }

    /**
     * Get all pages for filecredit index and return them as array
     *
     * @param integer $pid
     * @param string  $domain
     * @param boolean $blnIsSitemap
     * @param string  $strLanguage
     *
     * @return array
     */
    public static function findFileCreditPages($pid = 0, $domain = '', $blnIsSitemap = false, $strLanguage = '')
    {
        $time        = \Date::floorToMinute();
        $objDatabase = \Database::getInstance();

        // Get published pages
        $objPages = $objDatabase->prepare(
            "SELECT * FROM tl_page WHERE pid=? AND (start='' OR start<='$time') AND (stop='' OR stop>'" . ($time + 60)
            . "') AND published='1' ORDER BY sorting"
        )->execute($pid);

        if ($objPages->numRows < 1)
        {
            return [];
        }

        $arrPages = [];

        // Recursively walk through all subpages
        while ($objPages->next())
        {

            $domain = '';

            $objPage = \PageModel::findWithDetails($objPages->id);

            if ($objPage->domain != '')
            {
                $domain = ($objPage->rootUseSSL ? 'https://' : 'http://') . $objPage->domain . TL_PATH . '/';
            }
            else
            {
                $domain = \Environment::get('base');
            }

            // Set domain
            if ($objPage->type == 'root')
            {
                $strLanguage = $objPage->language;
            } // Add regular pages
            elseif ($objPage->type == 'regular')
            {
                // Not protected
                if ((!$objPage->protected || \Config::get('indexProtected')))
                {
                    // Published
                    if ($objPage->published && ($objPage->start == '' || $objPage->start <= $time)
                        && ($objPage->stop == ''
                            || $objPage->stop > ($time + 60))
                    )
                    {
                        $arrPages[] = $domain . str_replace($domain, '', static::generateFrontendUrl($objPage->row(), null, $strLanguage));

                        // Get articles with teaser
                        $objArticle = $objDatabase->prepare(
                            "SELECT * FROM tl_article WHERE pid=? AND (start='' OR start<='$time') AND (stop='' OR stop>'" . ($time + 60)
                            . "') AND published='1' AND showTeaser='1' ORDER BY sorting"
                        )->execute($objPage->id);

                        while ($objArticle->next())
                        {
                            $arrPages[] = $domain . str_replace(
                                    $domain,
                                    '',
                                    static::generateFrontendUrl(
                                        $objPage->row(),
                                        '/articles/' . (($objArticle->alias != ''
                                                         && !\Config::get(
                                                'disableAlias'
                                            )) ? $objArticle->alias : $objArticle->id),
                                        $strLanguage
                                    )
                                );
                        }
                    }
                }
            }

            // Get subpages
            if ((!$objPage->protected || \Config::get('indexProtected'))
                && ($arrSubpages = static::findFileCreditPages($objPage->id, $domain, $blnIsSitemap, $strLanguage)) != false
            )
            {
                $arrPages = array_merge($arrPages, $arrSubpages);
            }
        }

        return $arrPages;
    }

    public static function parseCopyright($varValue)
    {
        $varValue = deserialize($varValue);

        return is_array($varValue) ? implode(', ', $varValue) : $varValue;
    }
}