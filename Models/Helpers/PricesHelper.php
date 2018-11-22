<?php
/**
 * This file is part of SplashSync Project.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *  @author    Splash Sync <www.splashsync.com>
 *  @copyright 2015-2017 Splash Sync
 *  @license   GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007
 *
 **/

namespace   Splash\Models\Helpers;

use ArrayObject;

use Splash\Core\SplashCore      as Splash;

/**
 * @abstract    Helper for Prices Fields Management
 */
class PricesHelper
{
    /**
     * @abstract   Build a new price field array
     *
     * @param      double|null      $taxExcl        Price Without VAT (Or Null if Price Send with VAT)
     * @param      double      $vat            VAT percentile
     * @param      double      $taxIncl        Price With VAT
     * @param      string      $code           Price Currency Code
     * @param      string      $symbol         Price Currency Symbol
     * @param      string      $name           Price Currency Name
     *
     * @return     array|string
     */
    public static function encode($taxExcl, $vat, $taxIncl = null, $code = "", $symbol = "", $name = "")
    {
        //====================================================================//
        // Safety Checks
        if (!is_double($taxExcl) && !is_double($taxIncl)) {
            Splash::log()->err("ErrPriceInvalid", __FUNCTION__);
            return "Error Invalid Price";
        }
        if (is_double($taxExcl) && is_double($taxIncl)) {
            Splash::log()->err("ErrPriceBothValues", __FUNCTION__);
            return "Error Too Much Input Values";
        }
        if (!is_double($vat)) {
            Splash::log()->err("ErrPriceNoVATValue", __FUNCTION__);
            return "Error Invalid VAT";
        }
        if (empty($code)) {
            Splash::log()->err("ErrPriceNoCurrCode", __FUNCTION__);
            return "Error no Currency Code";
        }
        //====================================================================//
        // Build Price Array
        $price = array("vat" => $vat, "code" => $code,"symbol" => $symbol,"name" => $name);
        if (!is_null($taxExcl)) {
            $price["base"]  =    0;
            $price["ht"]    =    $taxExcl;
            $price["tax"]   =    $taxExcl * ($vat/100);
            $price["ttc"]   =    $taxExcl * (1 + $vat/100);
        } else {
            $price["base"]  =    1;
            $price["ht"]    =    $taxIncl / (1 + $vat/100);
            $price["tax"]   =    $taxIncl - $price["ht"];
            $price["ttc"]   =    $taxIncl;
        }
        return $price;
    }
    
    /**
     * @abstract   Compare Two Price Array
     *
     * @param      array       $price1          Price field Array
     * @param      array       $price2          Price field Array
     *
     * @return     bool
     */
    public static function compare($price1, $price2)
    {
        //====================================================================//
        // Check Both Prices are valid
        if (!self::isValid($price1) || !self::isValid($price2)) {
            Splash::log()->war(__FUNCTION__ . " : Given Prices are invalid");
            if (!self::isValid($price1)) {
                Splash::log()->www(__FUNCTION__ . " Price 1", $price1);
            }
            if (!self::isValid($price2)) {
                Splash::log()->www(__FUNCTION__ . " Price 2", $price2);
            }
            return false;
        }
        //====================================================================//
        // Compare Base Price
        if (((bool) $price1["base"]) != ((bool) $price2["base"])) {
            return false;
        }
        //====================================================================//
        // Compare Price
        return self::compareAmounts($price1, $price2);
    }
    
    /**
     * @abstract   Compare Two Price Array without Validation
     *
     * @param      array       $price1          Price field Array
     * @param      array       $price2          Price field Array
     *
     * @return     bool
     */
    public static function compareAmounts($price1, $price2)
    {
        //====================================================================//
        // Compare Price
        if ($price1["base"]) {
            if (abs($price1["ttc"] - $price2["ttc"]) > 1E-6) {
                return false;
            }
        } else {
            if (abs($price1["ht"] - $price2["ht"]) > 1E-6) {
                return false;
            }
        }
        //====================================================================//
        // Compare VAT
        if (abs($price1["vat"] - $price2["vat"]) > 1E-6) {
            return false;
        }
        //====================================================================//
        // Compare Currency If Set on Both Sides
        if (empty($price1["code"]) || empty($price2["code"])) {
            return true;
        }
        if ($price1["code"] !== $price2["code"]) {
            return false;
        }
        //====================================================================//
        // Prices Are Identical
        return true;
    }
    
    /**
     * @abstract   Verify Price field array
     *
     * @param      mixed       $price          Price field definition Array
     *
     * @return     bool
     */
    public static function isValid($price)
    {
        //====================================================================//
        // Check Contents Available
        if (!is_array($price) && !($price instanceof ArrayObject)) {
            return false;
        }
        /** @var array|ArrayObject $price */
        if (!isset($price["base"])) {
            return false;
        }
        if (!isset($price["ht"]) || !isset($price["ttc"]) || !isset($price["vat"])) {
            return false;
        }
        if (!self::isValidAmount($price)) {
            return false;
        }
        if (!self::isValidCurrency($price)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * @abstract   Verify Price Array Amount Infos are Available
     *
     * @param      array|ArrayObject       $price          Price field definition Array
     *
     * @return     bool
     */
    private static function isValidAmount($price)
    {
        //====================================================================//
        // Check Contents Type
        if (!empty($price["ht"]) && !is_numeric($price["ht"])) {
            return false;
        }
        if (!empty($price["ttc"]) && !is_numeric($price["ttc"])) {
            return false;
        }
        if (!empty($price["vat"]) && !is_numeric($price["vat"])) {
            return false;
        }

        return true;
    }
    
    /**
     * @abstract   Verify Price Array Currency Infos are Available
     *
     * @param      array|ArrayObject       $price          Price field definition Array
     *
     * @return     bool
     */
    private static function isValidCurrency($price)
    {
        //====================================================================//
        // Check Contents Available
        if (!isset($price["tax"])) {
            return false;
        }
        if (!isset($price["symbol"])) {
            return false;
        }
        if (!isset($price["code"])) {
            return false;
        }
        if (!isset($price["name"])) {
            return false;
        }
        return true;
    }
    
    /**
     * @abstract   Extract Data from Price Array
     *
     * @param      array       $price          Price field definition Array
     * @param      string      $key            Data Key
     *
     * @return     false|double
     */
    public static function extract($price, $key = "ht")
    {
        // Check Contents
        if (!isset($price[$key])) {
            return false;
        }
        if (!empty($price[$key]) && !is_numeric($price[$key])) {
            return false;
        }
        // Return Result
        return (double) $price[$key];
    }
    
    /**
     * @abstract   Extract Price without VAT from Price Array
     *
     * @param      array       $price          Price field definition Array
     *
     * @return     false|double
     */
    public static function taxExcluded($price)
    {
        return self::extract($price, 'ht');
    }

    /**
     * @abstract   Extract Price with VAT from Price Array
     *
     * @param      array       $price          Price field definition Array
     *
     * @return     false|double
     */
    public static function taxIncluded($price)
    {
        return self::extract($price, 'ttc');
    }
        
    /**
     * @abstract   Extract Price with VAT from Price Array
     *
     * @param      array       $price          Price field definition Array
     *
     * @return     false|double
     */
    public static function taxPercent($price)
    {
        return self::extract($price, 'vat');
    }
    
    /**
     * @abstract   Extract Price VAT Ratio from Price Array
     *
     * @param      array       $price          Price field definition Array
     *
     * @return     double
     */
    public static function taxRatio($price)
    {
        return (double) self::extract($price, 'vat') / 100;
    }
    
    
    /**
     * @abstract   Extract Price Tax Amount from Price Array
     *
     * @param      array       $price          Price field definition Array
     *
     * @return     false|double
     */
    public static function taxAmount($price)
    {
        return self::extract($price, 'tax');
    }
}
