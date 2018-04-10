<?php
/*
 * This file is part of SplashSync Project.
 *
 * Copyright (C) Splash Sync <www.splashsync.com>
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace   Splash\Components;

use Splash\Core\SplashCore      as Splash;
use ArrayObject;

/**
 * @abstract    This Class is a Generator for Objects Fields Definition
 * @author      B. Paquier <contact@splashsync.com>
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class FieldsFactory
{
    /**
     * Default Field Definition Resolver Array
     *
     * @var array
     */
    private static $FIELDS_DEFAULTS = array(
        //==============================================================================
        //      GENERAL FIELD PROPS
        "required"  =>  false,                  //  Field is Required to Create a New Object (Bool)
        "type"      =>  null,                   //  Field Fomat Type Name
        "id"        =>  null,                   //  Field Object Unique Identifier
        "name"      =>  null,                   //  Field Humanized Name (String)
        "desc"      =>  null,                   //  Field Description (String)
        "group"     =>  null,                   //  Field Section/Group (String)
        //==============================================================================
        //      ACCES PROPS
        "read"      =>  true,                   //  Field is Readable (Bool)
        "write"     =>  true,                   //  Field is Writable (Bool)
        "inlist"    =>  false,                  //  Field is Available in Object List Response (Bool)
        //==============================================================================
        //      SCHEMA.ORG IDENTIFICATION
        "itemprop"  =>  null,                   //  Field Unique Schema.Org "Like" Property Name
        "itemtype"  =>  null,                   //  Field Unique Schema.Org Object Url
        "tag"       =>  null,                   //  Field Unique Linker Tags (Self-Generated)
        //==============================================================================
        //      DATA SPECIFIC FORMATS PROPS
        "choices"   =>  array(),                //  Possible Values used in Editor & Debugger Only  (Array)
        //==============================================================================
        //      DATA LOGGING PROPS
        "log"       =>  false,                  //  Field is To Log (Bool)
        //==============================================================================
        //      DEBUGGER PROPS
        "asso"      =>  array(),                //  Associated Fields. Fields to Generate with this field.
        "options"   =>  array(),                //  Fields Constraints to Generate Fake Data during Tests
        "notest"    =>  false,                  //  Do No Perform Tests for this Field
    );
    
    //====================================================================//
    // Data Storage

    /**
     *      @abstract   Empty Template Object Field Storage
     *      @var        Array
     */
    private $empty;

    
    /**
     *      @abstract   New Object Field Storage
     *      @var        ArrayObject
     */
    private $new;
    
    /**
     *      @abstract   Object Fields List Storage
     *      @var        Array
     */
    private $fields;
    
    /**
     *      @abstract     Initialise Class
     *      @return         int           <0 if KO, >0 if OK
     */
    public function __construct()
    {
        
        //====================================================================//
        // Initialize Data Storage
        $this->new            = null;
        $this->fields         = array();
        
        //====================================================================//
        // Initialize Template Field
        $this->empty          = self::$FIELDS_DEFAULTS;
        
        return true;
    }

    //====================================================================//
    //  FIELDS :: DATA TYPES DEFINITION
    //====================================================================//

    /**
     *  @abstract   Create a new Field Definition with default parameters
     *
     *  @param      string      $type       Standard Data Type (Refer osws.inc.php)
     *  @param      string      $id         Local Data Identifier (Shall be unik on local machine)
     *  @param      string      $name       Data Name (Will Be Translated by OsWs if Possible)
     *  @return     int                     <0 if KO, >0 if OK
     */
    public function create($type, $id = null, $name = null)
    {
        //====================================================================//
        // Commit Last Created if not already done
        if (!empty($this->new)) {
            $this->commit();
        }
        
        //====================================================================//
        // Unset Current
        unset($this->new);
        
        //====================================================================//
        // Create new empty field
        $this->new          =   new ArrayObject($this->empty, ArrayObject::ARRAY_AS_PROPS);
        //====================================================================//
        // Set Field Type
        $this->new->type    =   $type;
        //====================================================================//
        // Set Field Identifier
        if (!is_null($id)) {
            $this->identifier($id);
        }
        //====================================================================//
        // Set Field Name
        if (!is_null($name)) {
            $this->name($name);
        }
        
        return $this;
    }
    
    /**
     *  @abstract   Set Current New Field Identifier
     *
     *  @param      string      $id         Local Data Identifier (Shall be unik on local machine)
     *
     *  @return     FieldsFactory
     */
    public function identifier($id)
    {
        //====================================================================//
        // Safety Checks ==> Verify a new Field Exists
        if (empty($this->new)) {
            Splash::log()->err("ErrFieldsNoNew");
        } else {
            //====================================================================//
            // Update New Field structure
            $this->new->id    = $id;
        }
        
        return $this;
    }
    
    /**
     *  @abstract   Update Current New Field set as it inside a list
     *
     *  @param      string      $ListName         Name of List
     *
     *  @return     FieldsFactory
     */
    public function inList($ListName)
    {
        //====================================================================//
        // Safety Checks ==> Verify List Name Not Empty
        if (empty($ListName)) {
            return $this;
        }
        
        //====================================================================//
        // Safety Checks ==> Verify a new Field Exists
        if (empty($this->new)) {
            Splash::log()->err("ErrFieldsNoNew");
        } else {
            //====================================================================//
            // Update New Field Identifier
            $this->new->id      =   $this->new->id . LISTSPLIT . $ListName;
            //====================================================================//
            // Update New Field Type
            $this->new->type    =   $this->new->type . LISTSPLIT . SPL_T_LIST;
        }
        
        return $this;
    }
    
    /**
     *  @abstract   Set Current New Field Name (Translated)
     *
     *  @param      string      $name       Data Name (Will Be Translated if Possible)
     *
     *  @return     FieldsFactory
     */
    public function name($name)
    {
        //====================================================================//
        // Safety Checks ==> Verify a new Field Exists
        if (empty($this->new)) {
            Splash::log()->err("ErrFieldsNoNew");
        } else {
            //====================================================================//
            // Update New Field structure
            $this->new->name    = $name;
            if (empty($this->new->desc)) {
                $this->description($name);
            }
        }
        
        return $this;
    }
    
    /**
     *  @abstract   Update Current New Field with descriptions (Translated)
     *
     *  @param      string      $desc       Data Description (Will Be Translated if Possible)
     *
     *  @return     FieldsFactory
     */
    public function description($desc)
    {
        //====================================================================//
        // Safety Checks ==> Verify a new Field Exists
        if (empty($this->new)) {
            Splash::log()->err("ErrFieldsNoNew");
        } else {
            //====================================================================//
            // Update New Field structure
            $this->new->desc    = Splash::trans(trim($desc));
        }
        
        return $this;
    }
    
    /**
     *  @abstract   Update Current New Field with Field Group Name (Translated)
     *
     *  @param      string      $group       Data Group (Will Be Translated if Possible)
     *
     *  @return     FieldsFactory
     */
    public function group($group)
    {
        //====================================================================//
        // Safety Checks ==> Verify a new Field Exists
        if (empty($this->new)) {
            Splash::log()->err("ErrFieldsNoNew");
        } else {
            //====================================================================//
            // Update New Field structure
            $this->new->group    = Splash::trans(trim($group));
        }
        
        return $this;
    }
    
    /**
     *  @abstract   Update Current New Field set as Read Only Field
     *
     *  @return     FieldsFactory
     */
    public function isReadOnly()
    {
        //====================================================================//
        // Safety Checks ==> Verify a new Field Exists
        if (empty($this->new)) {
            Splash::log()->err("ErrFieldsNoNew");
        } else {
            //====================================================================//
            // Update New Field structure
            $this->new->read    = true;
            $this->new->write   = false;
        }
        
        return $this;
    }
    
    /**
     *  @abstract   Update Current New Field set as Write Only Field
     *
     *  @return     FieldsFactory
     */
    public function isWriteOnly()
    {
        //====================================================================//
        // Safety Checks ==> Verify a new Field Exists
        if (empty($this->new)) {
            Splash::log()->err("ErrFieldsNoNew");
        } else {
            //====================================================================//
            // Update New Field structure
            $this->new->read    = false;
            $this->new->write   = true;
        }
        
        return $this;
    }
    
    /**
     *  @abstract   Update Current New Field set as required for creation
     *
     *  @return     FieldsFactory
     */
    public function isRequired()
    {
        //====================================================================//
        // Safety Checks ==> Verify a new Field Exists
        if (empty($this->new)) {
            Splash::log()->err("ErrFieldsNoNew");
        } else {
            //====================================================================//
            // Update New Field structure
            $this->new->required  = true;
        }
        
        return $this;
    }
    
    /**
     *  @abstract   Update Current New Field set list of associated fields
     *
     *  @param      string                  Objects Fields Identifiers
     *
     *  @return     FieldsFactory
     */
    public function association()
    {
        //====================================================================//
        // Safety Checks ==> Verify a new Field Exists
        if (empty($this->new)) {
            Splash::log()->err("ErrFieldsNoNew");
        } else {
            //====================================================================//
            // Field Clear Fields Associations
            if (!empty($this->new->asso)) {
                unset($this->new->asso);
            }
            
            //====================================================================//
            // Set New Field Associations
            if (!empty(func_get_args())) {
                $this->new->asso  = func_get_args();
            }
        }
        
        return $this;
    }
    
    /**
     *  @abstract   Update Current New Field set as available in objects list
     *
     *  @return     FieldsFactory
     */
    public function isListed()
    {
        //====================================================================//
        // Safety Checks ==> Verify a new Field Exists
        if (empty($this->new)) {
            Splash::log()->err("ErrFieldsNoNew");
        } else {
            //====================================================================//
            // Update New Field structure
            $this->new->inlist  = true;
        }
        
        return $this;
    }
    
    /**
     *  @abstract   Update Current New Field set as recommended for logging
     *
     *  @return     FieldsFactory
     */
    public function isLogged()
    {
        //====================================================================//
        // Safety Checks ==> Verify a new Field Exists
        if (empty($this->new)) {
            Splash::log()->err("ErrFieldsNoNew");
        } else {
            //====================================================================//
            // Update New Field structure
            $this->new->log  = true;
        }
        
        return $this;
    }
    
    /**
     *  @abstract   Update Current New Field set its meta informations for autolinking
     *
     *  @param      string      $ItemType   Field Microdata Type Url
     *  @param      string      $ItemProp   Field Microdata Property Name
     *
     *  @return     FieldsFactory
     */
    public function microData($ItemType, $ItemProp)
    {
        //====================================================================//
        // Safety Checks ==> Verify a new Field Exists
        if (empty($this->new)) {
            Splash::log()->err("ErrFieldsNoNew");
        } else {
            //====================================================================//
            // Update New Field structure
            $this->new->itemtype            = $ItemType;
            $this->new->itemprop            = $ItemProp;
            $this->setTag($ItemProp . IDSPLIT . $ItemType);
        }
        
        return $this;
    }
    
    /**
     *  @abstract   Update Current New Field set its unik tag for autolinking
     *
     *  @param      string      $Tag       Field Unik Tag
     *
     *  @return     FieldsFactory
     */
    protected function setTag($Tag)
    {
        //====================================================================//
        // Safety Checks ==> Verify a new Field Exists
        if (empty($this->new)) {
            Splash::log()->err("ErrFieldsNoNew");
        } else {
            //====================================================================//
            // Update New Field structure
            $this->new->tag     = md5($Tag);
        }
        
        return $this;
    }
        
    /**
     *  @abstract   Update Current New Field set as not possible to test
     *
     *  @return     FieldsFactory
     */
    public function isNotTested()
    {
        //====================================================================//
        // Safety Checks ==> Verify a new Field Exists
        if (empty($this->new)) {
            Splash::log()->err("ErrFieldsNoNew");
        } else {
            //====================================================================//
            // Update New Field structure
            $this->new->notest  = true;
        }
        
        return $this;
    }
    
    /**
     *  @abstract   Add Possible Choice to Current New Field Name (Translated)
     *
     *  @param      array      $Choices      Possible Choice Array (Value => Decsription)
     *
     *  @return     FieldsFactory
     */
    public function addChoices($Choices)
    {
        foreach ($Choices as $Value => $Description) {
            $this->addChoice($Value, $Description);
        }
        return $this;
    }

    /**
     *  @abstract   Add Possible Choice to Current New Field Name (Translated)
     *
     *  @param      string      $Value          Possible Choice Value
     *  @param      string      $Description    Choice Description for Display (Will Be Translated if Possible)
     *
     *  @return     FieldsFactory
     */
    public function addChoice($Value, $Description)
    {
        //====================================================================//
        // Safety Checks ==> Verify a new Field Exists
        if (empty($this->new)) {
            Splash::log()->err("ErrFieldsNoNew");
        } else {
            //====================================================================//
            // Update New Field structure
            $this->new->choices[]   = array(
            "key"   =>  $Value,
            "value" =>  Splash::trans(trim($Description))
                );
        }
        
        return $this;
    }
    
    /**
     *  @abstract   Add New Options Array for Current Field
     *
     *  @param      array      $Options     Array of Options (Type => Value)
     *
     *  @return     FieldsFactory
     */
    public function addOptions($Options)
    {
        foreach ($Options as $Type => $Value) {
            $this->AddConstrain($Type, $Value);
        }
        return $this;
    }

    /**
     *  @abstract   Add New Option for Current Field
     *
     *  @param      string      $Type           Constrain Type
     *  @param      string      $Value          Constrain Value
     *
     *  @return     FieldsFactory
     */
    public function addOption($Type, $Value = true)
    {
        //====================================================================//
        // Safety Checks ==> Verify a new Field Exists
        if (empty($this->new)) {
            Splash::log()->err("ErrFieldsNoNew");
        } elseif (empty($Type)) {
            Splash::log()->err("Field Option Type Cannot be Empty");
        } else {
            //====================================================================//
            // Update New Field structure
            $this->new->options[$Type]   = $Value;
        }
        return $this;
    }
    
    /**
     *  @abstract   Verify Current New Field data
     *
     *  @return     bool
     */
    private function verify()
    {
        //====================================================================//
        // If new Field is Empty
        if (!isset($this->new) || empty($this->new)) {
            return false;
        }
        return $this->validate($this->new);
    }
    
    private function validate($Field)
    {
        //====================================================================//
        // Verify - Field Type is Not Empty
        if (empty($Field->type) || !is_string($Field->type)) {
            return Splash::log()->err("ErrFieldsNoType");
        }

        //====================================================================//
        // Verify - Field Id is Not Empty
        if (empty($Field->id) || !is_string($Field->id)) {
            return Splash::log()->err("ErrFieldsNoId");
        }
        
        //====================================================================//
        // Verify - Field Name is Not Empty
        if (empty($Field->name) || !is_string($Field->name)) {
            return Splash::log()->err("ErrFieldsNoName");
        }
        
        //====================================================================//
        // Verify - Field Desc is Not Empty
        if (empty($Field->desc) || !is_string($Field->desc)) {
            return Splash::log()->err("ErrFieldsNoDesc");
        }

        return true;
    }

    /**
     *  @abstract   Save Current New Field in list & Clean current new field
     *
     *  @return     bool
     */
    private function commit()
    {
        //====================================================================//
        // Safety Checks
        if (empty($this->new)) {
            return true;
        }
            
        //====================================================================//
        // Create Field List
        if (empty($this->fields)) {
            $this->fields   = array();
        }

        //====================================================================//
        // Validate New Field
        if (!$this->verify()) {
            unset($this->new);
            return false;
        }
        
        //====================================================================//
        // Insert Field List
        $this->fields[] = $this->new;
        unset($this->new);
        
        return true;
    }
    
    /**
     *  @abstract   Save Current New Field in list & Clean current new field
     *
     *  @return     int                     <0 if KO, >0 if OK
     */
    public function publish()
    {
        //====================================================================//
        // Commit Last Created if not already done
        if (!empty($this->new)) {
            $this->commit();
        }
        
        //====================================================================//
        // Safety Checks
        if (empty($this->fields)) {
            return Splash::log()->err("ErrFieldsNoList");
        } //====================================================================//
        // Return fields List
        else {
            $buffer = $this->fields;
            unset($this->fields);
            return $buffer;
        }
        
        return false;
    }
    
    /**
     *  @abstract   Seach for a Field by unik tag
     *
     *  @param      array       $List      Array Of Field definition
     *  @param      string      $Tag       Field Unik Tag
     *  @return     mixed                  FALSE if KO, Field Definition array if OK
     */
    public function seachtByTag($List, $Tag)
    {
        //====================================================================//
        // Safety Checks
        if (!count($List)) {
            return false;
        }
        if (empty($Tag)) {
            return false;
        }
        //====================================================================//
        // Walk Through List and select by Tag
        foreach ($List as $field) {
            if ($field["tag"] == $Tag) {
                return $field;
            }
        }
        return false;
    }
    /**
     *  @abstract   Seach for a Field by id
     *
     *  @param      array       $List      Array Of Field definition
     *  @param      string      $Id        Field Identifier
     *  @return     mixed                  FALSE if KO, Field Definition array if OK
     */
    public function seachtById($List, $Id)
    {
        //====================================================================//
        // Safety Checks
        if (!count($List)) {
            return false;
        }
        if (empty($Id)) {
            return false;
        }
        //====================================================================//
        // Walk Through List and select by Tag
        foreach ($List as $field) {
            if ($field["id"] == $Id) {
                return $field;
            }
        }
        return false;
    }
}
