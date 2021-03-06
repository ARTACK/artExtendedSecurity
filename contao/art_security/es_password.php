<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  ARTACK WebLab GmbH 2012
 * @author     Patrick Landolt <http://www.artack.ch>
 * @package    art_security
 * @license    LGPL
 * @filesource
 */


/**
 * Initialize the system
 */
define('TL_MODE', 'BE');
require_once('../../system/initialize.php');


/**
 * Class Index
 *
 * Provides a form to change the back end password.
 * @copyright  ARTACK WebLab GmbH 2012
 * @author     Patrick Landolt <http://www.artack.ch>
 * @package    art_security
 */
class Index extends Backend
{

    /**
     * Initialize the controller
     * 
     * 1. Import the user
     * 2. Call the parent constructor
     * 3. Authenticate the user
     * 4. Load the language files
     * DO NOT CHANGE THIS ORDER!
     */
    public function __construct()
    {
        $this->import('BackendUser', 'User');
        parent::__construct();

        $this->User->authenticate();

        $this->loadLanguageFile('default');
        $this->loadLanguageFile('modules');
    }


    /**
     * Run the controller and parse the password template
     */
    public function run()
    {
        $this->Template = new BackendTemplate('be_password_es');
        $GLOBALS['TL_LANG'] = array_merge($GLOBALS['TL_LANG'], Loader::loadTranslations($GLOBALS['TL_LANGUAGE']));

        if ($this->Input->post('FORM_SUBMIT') == 'tl_password')
        {
            $pw = $this->Input->post('password');
            $cnf = $this->Input->post('confirm');

            // Do not allow special characters
            if (preg_match('/[#\(\)\/<=>]/', html_entity_decode($this->Input->post('password'))))
            {
                    $this->addErrorMessage($GLOBALS['TL_LANG']['ERR']['extnd']);
            }
            // Passwords do not match
            elseif ($pw != $cnf)
            {
                    $this->addErrorMessage($GLOBALS['TL_LANG']['ERR']['passwordMatch']);
            }
            // Password too short
            elseif (utf8_strlen($pw) < Loader::loadMinPasswordLength())
            {
                    $this->addErrorMessage(sprintf($GLOBALS['TL_LANG']['ERR']['passwordLength'], Loader::loadMinPasswordLength()));
            }
            // Password and username are the same
            elseif ($pw == $this->User->username)
            {
                    $this->addErrorMessage($GLOBALS['TL_LANG']['ERR']['passwordName']);
            }
            // Save the data
            else
            {
                // add own checks
                $gotOwnError = false;
                
                // check for password complexity
                if ($GLOBALS['TL_CONFIG']['extended_security_higher_password_complexity'])
                {
                    $vRet = Validator::validatePasswordComplexity($pw);
                    if (!$vRet)
                    {
                        $this->addErrorMessage($GLOBALS['TL_LANG']['tl_user']['validator']['higherPasswordComplexity']);
                        $gotOwnError = true;
                    }
                }

                // check for parts of username in password
                if ($GLOBALS['TL_CONFIG']['extended_security_password_not_contain_user'])
                {
                    $vRet = Validator::validatePartOfUsernameInPassword($this->User->username, $pw);
                    if (!$vRet)
                    {
                        $this->addErrorMessage($GLOBALS['TL_LANG']['tl_user']['validator']['usernamePartOfPassword']);
                        $gotOwnError = true;
                    }
                }
                
                if (!$gotOwnError)
                {

                    list(, $strSalt) = explode(':', $this->User->password);
                    $strPassword = sha1($strSalt . $pw);

                    // Make sure the password has been changed
                    if ($strPassword . ':' . $strSalt == $this->User->password)
                    {
                        $this->addErrorMessage($GLOBALS['TL_LANG']['MSC']['pw_change']);
                    }
                    else
                    {
                        $strSalt = substr(md5(uniqid(mt_rand(), true)), 0, 23);
                        $strPassword = sha1($strSalt . $pw);

                        $this->Database->prepare("UPDATE tl_user SET password=?, pwChange='', pwChangeTstamp = ? WHERE id=?")
                                                   ->execute($strPassword . ':' . $strSalt, time(), $this->User->id);

                        $this->addConfirmationMessage($GLOBALS['TL_LANG']['MSC']['pw_changed']);
                        $this->redirect('contao/main.php');
                    }
                    
                }
            }

            $this->reload();
        }

        $this->Template->theme = $this->getTheme();
        $this->Template->messages = $this->getMessages();
        $this->Template->base = $this->Environment->base;
        $this->Template->language = $GLOBALS['TL_LANGUAGE'];
        $this->Template->title = $GLOBALS['TL_CONFIG']['websiteTitle'];
        $this->Template->charset = $GLOBALS['TL_CONFIG']['characterSet'];
        $this->Template->action = ampersand($this->Environment->request);
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['pw_change'];
        $this->Template->submitButton = specialchars($GLOBALS['TL_LANG']['MSC']['continue']);
        $this->Template->password = $GLOBALS['TL_LANG']['MSC']['password'][0];
        $this->Template->confirm = $GLOBALS['TL_LANG']['MSC']['confirm'][0];
        $this->Template->disableCron = $GLOBALS['TL_CONFIG']['disableCron'];

        $this->Template->output();
    }
}


/**
 * Instantiate the controller
 */
$objIndex = new Index();
$objIndex->run();