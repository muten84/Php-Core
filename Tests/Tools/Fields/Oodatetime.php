<?php

namespace Splash\Tests\Tools\Fields;

/**
 * @abstract    DateTime Field : Date & Time as Text (Format Y-m-d G:i:s)
 *
 * @example     2016-12-25 12:25:30
 */
class Oodatetime extends Oovarchar
{
    //==============================================================================
    //      Structural Data
    //==============================================================================

    const FORMAT        =   'DateTime';
    
    //==============================================================================
    //      DATA VALIDATION
    //==============================================================================

    /**
     * Verify given Raw Data is Valid
     *
     * @param   string $data
     *
     * @return true|string
     */
    public static function validate($data)
    {
        //==============================================================================
        //      Verify Data is not Empty
        if (empty($data)) {
            return true;
        }

        //==============================================================================
        //      Verify Data is a DateTime Type
        if (\DateTime::createFromFormat(SPL_T_DATETIMECAST, $data) !== false) {
            return true;
        }

        return "Field Data is not a DateTime with right Format (" . SPL_T_DATETIMECAST . ").";
    }
    
    //==============================================================================
    //      FAKE DATA GENERATOR
    //==============================================================================

    /**
     * Generate Fake Raw Field Data for Debugger Simulations
     *
     * @param      array   $settings   User Defined Faker Settings
     *
     * @return string
     */
    public static function fake($settings)
    {
        //==============================================================================
        //      Generate a random DateTime
        $date = new \DateTime("now");
        $date->modify('-' . mt_rand(1, 10) . ' months');
        $date->modify('-' . mt_rand(1, 60) . ' minutes');
        $date->modify('-' . mt_rand(1, 60) . ' seconds');
        //==============================================================================
        //      Return DateTime is Right Format
        return $date->format(SPL_T_DATETIMECAST);
    }
}
