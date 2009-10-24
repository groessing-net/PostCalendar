<?php
/**
 * @package     PostCalendar
 * @author      $Author$
 * @link        $HeadURL$
 * @version     $Id$
 * @copyright   Copyright (c) 2002, The PostCalendar Team
 * @copyright   Copyright (c) 2009, Craig Heydenburg, Sound Web Development
 * @license     http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

/**
 * Initializes a new install of PostCalendar
 *
 * This function will initialize a new installation of PostCalendar.
 * It is accessed via the Zikula Admin interface and should
 * not be called directly.
 *
 * @return  boolean    true/false
 * @access  public
 * @author  Roger Raymond <iansym@yahoo.com>
 */
function postcalendar_init()
{
    $dom = ZLanguage::getModuleDomain('PostCalendar');
    // create tables
    if (!DBUtil::createTable('postcalendar_events') || !DBUtil::createTable('postcalendar_categories')) {
        return LogUtil::registerError(__('Error! Could not create the table.', $dom));
    }

    // insert default category
    $defaultcat = array('catname' => __('Default', $dom), 'catdesc' => __('Default category', $dom));
    if (!DBUtil::insertObject($defaultcat, 'postcalendar_categories', 'catid')) {
        return LogUtil::registerError(__('Error! Could not create the new default category.', $dom));
    }

    // PostCalendar Default Settings
    $defaultsettings = postcalendar_init_getdefaults();
    $result = pnModSetVars('PostCalendar', $defaultsettings);
    if (!$result) {
        return LogUtil::registerError(__('Error! Could not set the default settings for PostCalendar.', $dom));
    }

    postcalendar_init_reset_scribite();

    return true;
}

/**
 * Upgrades an old install of PostCalendar
 *
 * This function is used to upgrade an old version
 * of PostCalendar.  It is accessed via the Zikula
 * Admin interface and should not be called directly.
 *
 * @return boolean    true/false
 * @param  string    $oldversion Version we're upgrading
 * @access  public
 * @author  Roger Raymond <iansym@yahoo.com>
 * @copyright    The PostCalendar Team 2002
 */
function postcalendar_upgrade($oldversion)
{
    $dom = ZLanguage::getModuleDomain('PostCalendar');
    // We only support upgrade from version 4 and up. Notify users if they have a version below that one.
    if (version_compare($oldversion, '4', '<'))
    {
        $modversion = array('version' => 'unknown');
        // Receive the current version information, where $modversion will be overwritten
        require dirname(__FILE__) . '/pnversion.php';

        // Inform user about error, and how he can upgrade to $modversion['version']
        return LogUtil::registerError(__f('Notice: This version does not support upgrades from PostCalendar 3.x and earlier. Please download and install version 4.0.3  (available from <a href=\"http://code.zikula.org/soundwebdevelopment/downloads\">code.zikula.org/soundwebdevelopment</a>). After upgrading, you can install PostCalendar %version% and perform this upgrade.', $modversion, $dom));
    }

    // change the database. DBUtil + ADODB detect the changes on their own
    // and perform all necessary steps without help from the module author
    if (!DBUtil::changeTable('postcalendar_events') || !DBUtil::changeTable('postcalendar_categories')) {
        return LogUtil::registerError(__('Error! Could not upgrade the tables.', $dom));
    }

    switch ($oldversion) {

        case '4.0.0':
        case '4.0.1':
        case '4.0.2':
        case '4.0.3': // Also support upgrades from PostCalendar 4.03a (http://www.krapohl.info)
            // v4b TS start
            pnModSetVar('PostCalendar', 'pcRepeating', '0');
            pnModSetVar('PostCalendar', 'pcMeeting', '0');
            pnModSetVar('PostCalendar', 'pcAddressbook', '1');
            // v4b TS end
        case '5.0.0':
            pnModSetVar('PostCalendar', 'pcTemplate', 'default');
        case '5.0.1':
            pnModDelVar('PostCalendar', 'pcTemplate');
        case '5.1.0':
            pnModSetVar('PostCalendar', 'pcNotifyAdmin2Admin', '0');
        case '5.5.0':
            postcalendar_init_reset_scribite();
        case '5.5.1':
        case '5.5.2':
        case '5.5.3':
            pnModSetVar('PostCalendar', 'pcAllowCatFilter', '1');
            pnModDelVar('PostCalendar', 'pcDayHighlightColor');
            pnModDelVar('PostCalendar', 'pcAllowSiteWide');
            pnModDelVar('PostCalendar', 'pcAddressbook');
            pnModDelVar('PostCalendar', 'pcMeeting');
            pnModDelVar('PostCalendar', 'pcUseInternationalDates');
        case '5.8.1':
            // no changes
        //case '5.8.1':
            // modify table to remove cols: pc_comments, pc_counter, pc_recurrfreq, pc_meeting_id, pc_topic?
            // remove postcalendar_categories table entirely?
    }

    // if we get this far - clear the cache
    pnModAPIFunc('PostCalendar', 'admin', 'clearCache');

    return true;
}

/**
 * PostCalendar Default Module Settings
 *
 * @return array An associated array with key=>value pairs of the default module settings
 */
function postcalendar_init_getdefaults()
{
    // PostCalendar Default Settings
    $defaults = array(
    'pcTime24Hours'           => _TIMEFORMAT == 24 ? '1' : '0',
    'pcEventsOpenInNewWindow' => '0',
    'pcFirstDayOfWeek'        => '0', /* Sunday */
    'pcUsePopups'             => '0',
    'pcAllowDirectSubmit'     => '0',
    'pcListHowManyEvents'     => '15',
    'pcDisplayTopics'         => '0',
    'pcEventDateFormat'       => '%B %d, %Y', /* American: e.g. July 4, 2010 */
    'pcRepeating'             => '1', /* display repeating options */
    'pcAllowUserCalendar'     => '0',
    'pcTimeIncrement'         => '15',
    'pcDefaultView'           => 'month',
    'pcUseCache'              => '0',
    'pcCacheLifetime'         => '3600',
    'pcNotifyAdmin'           => '1',
    'pcNotifyEmail'           => pnConfigGetVar('adminmail'),
    'pcNotifyAdmin2Admin'     => '0',
    'pcAllowCatFilter'        => '1',
    );

    return $defaults;
}

/**
 * Reset scribite config for PostCalendar module.
 *
 * Since we updated the functionname for creating / editing a new event from func=submit to func=new,
 * scribite doesn't load any editor. If we force it to our new function.
 */
function postcalendar_init_reset_scribite()
{
    $dom = ZLanguage::getModuleDomain('PostCalendar');
    // update the scribite
    if (pnModAvailable('scribite') && pnModAPILoad('scribite', 'user') && pnModAPILoad('scribite', 'admin')) {
        $modconfig = pnModAPIFunc('scribite', 'user', 'getModuleConfig', array('modulename' => 'PostCalendar'));
        $mid = false;

        if (count($modconfig)) {
            $modconfig['modfuncs'] = 'new,edit,submit';
            $modconfig['modareas'] = 'description';
            $mid = pnModAPIFunc('scribite', 'admin', 'editmodule', $modconfig);
        } else {
            // create new module in db
            $modconfig = array('modulename' => 'PostCalendar',
                               'modfuncs'   => 'new,edit,submit',
                               'modareas'   => 'description',
                               'modeditor'  => '-');
            $mid = pnModAPIFunc('scribite', 'admin', 'addmodule', $modconfig);
        }

        // Error tracking
        if ($mid === false) {
            //pnModLangLoad('scribite', 'user');
            LogUtil::registerStatus (__('Error! Could not update the configuration.', $dom));
        }
    }
}

/**
 * Deletes an install of PostCalendar
 *
 * This function removes PostCalendar from you
 * Zikula install and should be accessed via
 * the Zikula Admin interface
 *
 * @return  boolean    true/false
 * @access  public
 * @author  Roger Raymond <iansym@yahoo.com>
 * @copyright    The PostCalendar Team 2002
 */
function postcalendar_delete()
{
    $result = DBUtil::dropTable('postcalendar_events');
    $result = $result && DBUtil::dropTable('postcalendar_categories');
    $result = $result && pnModDelVar('PostCalendar');

    return $result;
}
