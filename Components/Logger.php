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
 * @abstract    Requests Log & Debug Management Class
 * @author      SplashSync <contact@splashsync.com>
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Logger
{
    /**
     *      @abstract   Store Show Debug Messages
     *      @var        Bool
     */
    private $debug;
    
    /**
     *      @abstract   Store Show Debug Messages
     *      @var        String
     */
    private $prefix;
    
    /**
     *      @abstract   Success Messages
     *      @var        Array
     */
    public $msg = array();
    
    /**
     *      @abstract   Warning Messages
     *      @var        Array
     */
    public $war = array();
    
    /**
     *      @abstract   Error Messages
     *      @var        Array
     */
    public $err = array();
    
    /**
     *      @abstract   Debug Messages
     *      @var        Array
     */
    public $deb = array();
    
    /**
     *      @abstract      Class Constructor
     *
     *      @param          bool     $debug      Allow Debug
     *
     *      @return         boot
     */
    public function __construct($debug = SPLASH_DEBUG)
    {
        //====================================================================//
        //  Store Debug Parameter
        $this->debug        =   $debug;
        //====================================================================//
        //  Define Standard Messages Prefix if Not Overiden
        $this->prefix = "Splash Client";
        return true;
    }
    
    //====================================================================//
    //  MESSAGES & LOG MANAGEMENT
    //====================================================================//

    /**
      *      @abstract   Clean WebServer Class Logs Messages
      *      @return     none
      */
    public function cleanLog()
    {
        if (isset($this->err)) {
            $this->err = array();
        }
        if (isset($this->war)) {
            $this->war = array();
        }
        if (isset($this->msg)) {
            $this->msg = array();
        }
        if (isset($this->deb)) {
            $this->deb = array();
        }
        $this->deb("Log Messages Buffer Cleaned");
        return   true;
    }

    /**
      *      @abstract      Log WebServer Error Messages
      *
      *      @param      string      $text       Input String / Key to translate
      *      @param      string      $param1     chaine de param1
      *      @param      string      $param2     chaine de param2
      *      @param      string      $param3     chaine de param3
      *      @param      string      $param4     chaine de param4
      *      @param      string      $param5     chaine de param5
      *
      *      @return     False
      */
    public function err($text, $param1 = '', $param2 = '', $param3 = '', $param4 = '', $param5 = '')
    {
        //====================================================================//
        // Initialise buffer if unset
        if (!isset($this->err)) {
            $this->err = array();
        }
        //====================================================================//
        // Add text message to buffer
        $Text = Splash::trans($text, $param1, $param2, $param3, $param4, $param5);
        $this->err[] = $Text;
        //====================================================================//
        // Add Message To Log File
        self::addLogToFile($Text, "ERROR");
        return   false;
    }
   
    /**
      *      @abstract      Log WebServer Warning Messages
      *
      *      @param      string      $text       Input String / Key to translate
      *      @param      string      $param1     chaine de param1
      *      @param      string      $param2     chaine de param2
      *      @param      string      $param3     chaine de param3
      *      @param      string      $param4     chaine de param4
      *      @param      string      $param5     chaine de param5
      *
      *      @return     True
     */
    public function war($text, $param1 = '', $param2 = '', $param3 = '', $param4 = '', $param5 = '')
    {
        //====================================================================//
        // Initialise buffer if unset
        if (!isset($this->war)) {
            $this->war = array();
        }
        //====================================================================//
        // Add text message to buffer
        $Text = Splash::trans($text, $param1, $param2, $param3, $param4, $param5);
        $this->war[] = $Text;
        //====================================================================//
        // Add Message To Log File
        self::addLogToFile($Text, "WARNING");
        return   true;
    }

    /**
      *      @abstract      Log WebServer Commons Messages
      *
      *      @param      string      $text       Input String / Key to translate
      *      @param      string      $param1     chaine de param1
      *      @param      string      $param2     chaine de param2
      *      @param      string      $param3     chaine de param3
      *      @param      string      $param4     chaine de param4
      *      @param      string      $param5     chaine de param5
      *
      *      @return     True
      */
    public function msg($text, $param1 = '', $param2 = '', $param3 = '', $param4 = '', $param5 = '')
    {
        //====================================================================//
        // Initialise buffer if unset
        if (!isset($this->msg)) {
            $this->msg = array();
        }
        //====================================================================//
        // Add text message to buffer
        $Text = Splash::trans($text, $param1, $param2, $param3, $param4, $param5);
        $this->msg[] = $Text;
        //====================================================================//
        // Add Message To Log File
        self::addLogToFile($Text, "MESSAGE");
        return   true;
    }

    /**
      *      @abstract      Log WebServer Debug Messages
      *
      *      @param      string      $text       Input String / Key to translate
      *      @param      string      $param1     chaine de param1
      *      @param      string      $param2     chaine de param2
      *      @param      string      $param3     chaine de param3
      *      @param      string      $param4     chaine de param4
      *      @param      string      $param5     chaine de param5
      *
      *      @return     True
      */
    public function deb($text, $param1 = '', $param2 = '', $param3 = '', $param4 = '', $param5 = '')
    {
        if (!isset($this->debug) || !$this->debug) {
            return   true;
        }
        //====================================================================//
        // Initialise buffer if unset
        if (!isset($this->deb)) {
            $this->deb = array();
        }
        //====================================================================//
        // Add text message to buffer
        $Text = Splash::trans($text, $param1, $param2, $param3, $param4, $param5);
        $this->deb[] = $Text;
        //====================================================================//
        // Add Message To Log File
        self::addLogToFile($Text, "DEBUG");
        return   true;
    }
   

    /**
     *      @abstract    Return All WebServer current Log WebServer in Html format
     *      @return      string      All existing log messages in an human readable Html format
     */
    public function getHtml($msgarray, $title = "", $Color = "#000000")
    {
        $html  = '<font color="' . $Color . '">';
        
        if (count($msgarray) > 0) {
            //====================================================================//
            // Prepare Title as Bold
            if ($title) {
                $html .= '<u><b>' . $title . '</b></u></br> ';
            }
            //====================================================================//
            // Add Messages
            foreach ($msgarray as $txt) {
                $html .= $txt . "</br>";
            }
        }
        
        return $html . "</font>";
    }
   
    /**
     *      @abstract    Return All WebServer current Log WebServer in Html format
     *      @param       bool            True if messages needs to be cleaned after reading.
     *      @return      string      All existing log messages in an human readable Html format
     */
    public function getHtmlLog($clean = false)
    {
        $html  = null;
        //====================================================================//
        // Read All Messages as Html
        $html .= $this->getHtml($this->err, "Errors", "#FF3300");
        $html .= $this->getHtml($this->war, "Warning", "#FF9933");
        $html .= $this->getHtml($this->msg, "Messages", "#006600");
        $html .= $this->getHtml($this->deb, "Debug", "#003399");
        //====================================================================//
        // Clear Log Buffer If Requiered
        if ($clean) {
            $this->cleanLog();
        }
        return $html;
    }
   
    /**
     *      @abstract    Return WebServer Log Item in Html Checklist format
     *      @return      string      Log message in an human readable Html format
     */
    private function getHtmlListItem($Message, $Type = null)
    {
        switch ($Type) {
            case "Error":
                $Color = "#FF3300";
                $Text  = "&nbsp;KO&nbsp;";
                break;
            case "Warning":
                $Color = "#FF9933";
                $Text  = "&nbsp;WAR&nbsp;";
                break;
            default:
                $Color = "#006600";
                $Text  = "&nbsp;OK&nbsp;";
                break;
        }
        
        return '[<font color="' . $Color . '">' . $Text . '</font>]&nbsp;&nbsp;&nbsp;' . $Message . PHP_EOL . "</br>";
    }
    
    /**
     *      @abstract    Return All WebServer current Log WebServer in Html Checklist format
     *      @return      string      All existing log messages in an human readable Html format
     */
    private function getHtmlList($Msgarray, $Type)
    {
        $html  = null;
        
        if (count($Msgarray) > 0) {
            //====================================================================//
            // Add Messages
            foreach ($Msgarray as $Message) {
                $html .= $this->getHtmlListItem($Message, $Type);
            }
        }
        
        return $html;
    }
   
    /**
     *      @abstract    Return All WebServer current Log WebServer in Html Checklist format
     *      @param       bool            True if messages needs to be cleaned after reading.
     *      @return      string      All existing log messages in an human readable Html format
     */
    public function getHtmlLogList($clean = false)
    {
        $html  = null;
        //====================================================================//
        // Read All Messages as Html
        $html .= $this->getHtmlList($this->err, "Error");
        $html .= $this->getHtmlList($this->war, "Warning");
        $html .= $this->getHtmlList($this->msg, "Message");
        $html .= $this->getHtmlList($this->deb, "Debug");
        //====================================================================//
        // Clear Log Buffer If Requiered
        if ($clean) {
            $this->cleanLog();
        }
        return $html;
    }
   
    /**
     *      @abstract    Return All WebServer current Log WebServer Console Colored format
     *      @return      string      All existing log messages in an human readable Html format
     */
    private function getConsole($msgarray, $title = "", $Color = "")
    {
        $Out  = "";
        
        if (count($msgarray) > 0) {
            //====================================================================//
            // Add Messages
            foreach ($msgarray as $txt) {
                $Out .= PHP_EOL . $Color . $title . html_entity_decode($txt);
            }
        }
        
        return $Out;
    }
   
    /**
     *      @abstract    Return All WebServer current Log WebServer in Console Colored format
     *      @param       bool            True if messages needs to be cleaned after reading.
     *      @return      string      All existing log messages in an human readable Html format
     */
    public function getConsoleLog($clean = false)
    {
        $Out  = null;
        //====================================================================//
        // Read All Messages as Html
        $Out .= $this->getConsole($this->err, " - Error    => ", "\e[31m");
        $Out .= $this->getConsole($this->war, " - Warning  => ", "\e[33m");
        $Out .= $this->getConsole($this->msg, " - Messages => ", "\e[32m");
        $Out .= $this->getConsole($this->deb, " - Debug    => ", "\e[97m");
        $Out .= "\e[0m";
        
        //====================================================================//
        // Clear Log Buffer If Requiered
        if ($clean) {
            $this->cleanLog();
        }
        return $Out;
    }
   
    /**
     *      @abstract    Return All WebServer current Log WebServer in an arrayobject variable
     *
     *      @param       bool                True if messages needs to be cleaned after reading.
     *
     *      @return      ArrayObject         All existing log messages in an arrayobject structure
     */
    public function getRawLog($clean = false)
    {
        $raw = new ArrayObject();
        if ($this->err) {
            $raw->err = $this->err;
        }
        if ($this->war) {
            $raw->war = $this->war;
        }
        if ($this->msg) {
            $raw->msg = $this->msg;
        }
        if ($this->deb) {
            $raw->deb = $this->deb;
        }
        if ($clean) {
            $this->cleanLog();
        }
        return $raw;
    }
   
    /**
     *      @abstract    Merge All Log messages from a second class with current class
     *
     *      @param       logs                 a second logging class structure.
     *
     *      @return      True
     */
    public function merge($logs)
    {
        if (!empty($logs->msg)) {
            $this->mergeCore("msg", $logs->msg);
            $this->addLogBlockToFile($logs->msg, "MESSAGE");
        }
        
        if (!empty($logs->err)) {
            $this->mergeCore("err", $logs->err);
            $this->addLogBlockToFile($logs->err, "ERROR");
        }
        
        if (!empty($logs->war)) {
            $this->mergeCore("war", $logs->war);
            $this->addLogBlockToFile($logs->war, "WARNING");
        }
        
        if (!empty($logs->deb)) {
            $this->mergeCore("deb", $logs->deb);
            $this->addLogBlockToFile($logs->deb, "DEBUG");
        }
        return true;
    }
   
    /**
     *      @abstract    Merge Messages from a second class with current class
     *
     *      @param       logs                 a second logging class structure.
     *
     *      @return      True
     */
    private function mergeCore($what, $In)
    {
        if (!empty($In)) {
            if (is_a($In, "ArrayObject")) {
                $In = $In->getArrayCopy();
            }
            
            if (!isset($this->$what)) {
                $this->$what = $In;
            } else {
                $this->$what = array_merge($this->$what, $In);
            }
        }
        return true;
    }

    /**
     *      @abstract    Set Debug Flag & Clean buffers if needed
     *
     *      @param       bool    $debug       Use debug??
     *
     *      @return      True
     */
    public function setDebug($debug)
    {
        //====================================================================//
        // Change Parameter State
        $this->debug = $debug;
        //====================================================================//
        // Delete Existing Debug Messages
        if (($debug == 0) && isset($this->debug)) {
            unset($this->deb);
        }
        return true;
    }
   
    /**
     *      @abstract    Set Prefix String
     *
     *      @param       string      $prefix     Prefix for all Splash Messages
     *
     *      @return      True
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        
        return true;
    }
    
    //====================================================================//
    //  VARIOUS TOOLS
    //====================================================================//
   
    /**
     *
     *  @abstract    Read & Returns var_dump() standard php function html result
     *
     *  @param      var       $var        Any Object to dump
     *
     *  @return     string                HTML display string of this object
     *
     */
    private function getVarDump($var)
    {
        //====================================================================//
        // Safety Check
        if (empty($var)) {
            return "Empty Object";
        }

        //====================================================================//
        // Var Dump reading
        ob_start();                     // Turn on output buffering
        var_dump($var);                 // Dumps information about a variable
        $Html = ob_get_contents();         // Read the contents of the output buffer
        ob_end_clean();                 // Clean (erase) the output buffer and turn off output buffering

        //====================================================================//
        // Return Contents
        return "<PRE>" . $Html . "</PRE>";
    }

    /**
     *  @abstract    Read & Returns var_dump() of a variable in a debug message
     *
     *  @param      string    $txt        Any text to display before dump
     *  @param      var       $var        Any Object to dump
     *
     *  @return     string                HTML display string of this object
     */
    public function ddd($txt, $var)
    {
        $this->deb($txt . $this->getVarDump($var));
        return true;
    }

    /**
     *  @abstract    Read & Returns var_dump() of a variable in a warning message
     *
     *  @param      string    $txt        Any text to display before dump
     *  @param      var       $var        Any Object to dump
     *  @return     string                HTML display string of this object
     */
    public function www($txt, $var)
    {
        $this->war($txt . "<PRE>" . print_r($var, 1) . "</PRE>");
        return true;
    }
    
    /**
     *  @abstract    Log a debug message trace stack
     *
     *  @param      string    $Class      shall be __CLASS__
     *  @param      string    $Fucntion   shall be __FUNCTION__
     */
    public function trace($Class, $Fucntion)
    {
        //====================================================================//
        //  Load Translation File
        Splash::translator()->load("main");

        
        $this->deb("DebTraceMsg", $Class, $Fucntion);
    }

    /**
     *  @abstract    Read & Store Outputs Buffer Contents in a warning message
     */
    public function flushOuputBuffer()
    {
        
        //====================================================================//
        // Read the contents of the output buffer
        $Contents = ob_get_contents();
        
        //====================================================================//
        // Clean (erase) the output buffer and turn off output buffering
        ob_end_clean();

        if ($Contents) {
            $this->war("UnexOutputs", $Contents);
            $this->war("UnexOutputsMsg");
        }
    }

    
    //====================================================================//
    //  LOG FILE MANAGEMENT
    //====================================================================//
   
    /**
     *  @abstract    Add a message to Log File
     *
     *  @param      string    $txt        Message text to log
     *  @param      string    $type       Message Type
     *  @return     True
     */
    private static function addLogToFile($txt, $type = "Unknown")
    {
        //====================================================================//
        // Safety Check
        if (Splash::configuration()->Logging == 0) {
            return true;
        }
        if (strlen(SPLASH_DIR) == 0) {
            return true;
        }
        //====================================================================//
        // Open Log File
        $logfile = SPLASH_DIR . "/splash.log";
        $filefd = @fopen($logfile, 'a+');
        //====================================================================//
        // Write Log File
        if ($filefd) {
            $message = date("Y-m-d H:i:s")." ".sprintf("%-15s", $type) . $txt;
            fwrite($filefd, $message."\n");
            fclose($filefd);
            @chmod($logfile, 0604);
        }
        return true;
    }
    
    /**
     *  @abstract    Add a message to Log File
     *
     *  @param      array     $array      Array of Message text to log
     *  @param      string    $type       Message Type
     *  @return     True
     */
    private static function addLogBlockToFile($array, $type = "Unknown")
    {
        //====================================================================//
        // Safety Check
        if (Splash::configuration()->Logging == false) {
            return true;
        }
        
        //====================================================================//
        // Run a Messages List
        if ((count($array) > 0) && (!empty($array))) {
            foreach ($array as $message) {
                //====================================================================//
                // Add Message To Log File
                self::addLogToFile(utf8_decode(html_entity_decode($message)), $type);
            }
        }
        return true;
    }
}
