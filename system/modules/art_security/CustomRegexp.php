<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

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

class CustomRegexp {
    
    public function addCustomRegexp($strRegexp, $varValue, Widget $objWidget)
    {
        if ($strRegexp == 'passwordComplexity')
        {
            
            $nonAlphaNum = '!#$%&()*+,-.:;>=<?@[]_{}';
            $countCategories = 0;
            
            if (preg_match('/[a-z]+/', $varValue)) $countCategories++;
            if (preg_match('/[A-Z]+/', $varValue)) $countCategories++;
            if (preg_match('/[0-9]+/', $varValue)) $countCategories++;
            if (preg_match('/['.  preg_quote($nonAlphaNum).']+/', $varValue)) $countCategories++;
            
            if ($countCategories < 3)
            {
                $objWidget->addErrorMessage("test");
//                $objWidget->addError($GLOBALS['TL_LANG']['tl_user']['regexp']['passwordComplexity']['minThreeOfFourCategories']);
//                var_dump($objWidget);
                echo "du error";
            }
            
            return true;
        }

        return false;
    }
    
}