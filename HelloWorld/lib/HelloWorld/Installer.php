<?php
/**
 * Copyright Craig Heydenburg 2010 - HelloWorld
 *
 * HelloWorld
 * Demonstration of Zikula Module
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 */

class HelloWorld_Installer extends Zikula_Installer
{
    /**
     * Initializes a new install
     *
     * This function will initialize a new installation.
     * It is accessed via the Zikula Admin interface and should
     * not be called directly.
     *
     * @return  boolean    true/false
     * @access  public
     */
    public function install()
    {
        // create tables
        if (!DBUtil::createTable('helloworld')) {
            return LogUtil::registerError($this->__('Error! Could not create the table.'));
        }

        return true;
    }
    
    /**
     * Upgrades an old install
     *
     * This function is used to upgrade an old version
     * of the module.  It is accessed via the Zikula
     * Admin interface and should not be called directly.
     *
     * @return  boolean    true/false
     * @param   string    $oldversion Version we're upgrading
     * @access  public
     */
    public function upgrade($oldversion)
    {
        if (!SecurityUtil::checkPermission('HelloWorld::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    
        switch ($oldversion) {
            case '1.0.0':
                //future development
        }
    
        // if we get this far - clear the cache
        $this->view->clear_cache();
    
        return true;
    }
    
    /**
     * Deletes an install
     *
     * This function removes the module from your
     * Zikula install and should be accessed via
     * the Zikula Admin interface
     *
     * @return  boolean    true/false
     * @access  public
     */
    public function uninstall()
    {
        $result = DBUtil::dropTable('helloworld');
        $result = $result && $this->delVars();

        return $result;
    }
} // end class def