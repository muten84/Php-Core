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

/**
 * @abstract    Low Level Files Management Class
 * @author      B. Paquier <contact@splashsync.com>
 */

namespace   Splash\Components;

use Splash\Core\SplashCore      as Splash;
use ArrayObject;

//====================================================================//
//   INCLUDES 
//====================================================================//

//====================================================================//
//  CLASS DEFINITION
//====================================================================//

class FileManager 
{

    /**
     *      @abstract   Read a file from Splash Server
     * 
     *      @param      string      $file       File Identifier (Given by Splash Server)
     *      @param      string      $md5        Local FileName 
     * 
     *      @return     array       $file       False if not found, else file contents array
     */    
    function GetFile($file,$md5)
    {
        //====================================================================//
        // Stack Trace
        Splash::Log()->Trace(__CLASS__,__FUNCTION__);  
        
        //====================================================================//
        // PHPUNIT Exception => Look First in Local FileSystem  
        //====================================================================//
        if ( SPLASH_DEBUG ) {
            $FilePath   = dirname(__DIR__) . "/Resources/files/"  . $file;
            if ( is_file( $FilePath ) && ( md5_file( $FilePath ) == $md5 ) ) {
                return $this->ReadFile(dirname($FilePath), basename($FilePath));
            }
            $ImgPath    = dirname(__DIR__) . "/Resources/img/"  . $file;
            if ( is_file( $ImgPath ) && ( md5_file( $ImgPath ) == $md5 ) ) {
                return $this->ReadFile(dirname($ImgPath), basename($ImgPath));
            }
        }
        
        //====================================================================//
        // Initiate Tasks parameters array 
        $params             = new ArrayObject(array(),  ArrayObject::ARRAY_AS_PROPS);
        $params->file       = $file;     
        $params->md5        = $md5;       
        //====================================================================//
        // Add Task to Ws Task List
        Splash::Ws()->AddTask(SPL_F_GETFILE, $params, Splash::Trans("MsgSchRemoteReadFile",$file) );
        //====================================================================//
        // Execute Task
        $Response   =   Splash::Ws()->Call(SPL_S_FILE);
        //====================================================================//
        // Analyze NuSOAP results
        if ( !isset ($Response->result) || ($Response->result != True) ) {
            return False;
        }     

        //====================================================================//
        // Get Next Task Result 
        if ( count($Response->tasks) ) {
            //====================================================================//
            // Shift Task Array 
            if ( is_a($Response->tasks, "ArrayObject") ) {
                $Task = array_shift ( $Response->tasks->getArrayCopy() );
            } else {
                $Task = array_shift ( $Response->tasks );
            }
            //====================================================================//
            // Return Task Data 
            return   isset($Task->data)?$Task->data:False;
        }
        return False;     
    }    
    

    
    /**
     *  @abstract   Check whether if a file exists or not
     *  @param      string      $dir        Local File Directory 
     *  @param      string      $file       Local FileName 
     *  @return     array       $infos      0 if not found, else file informations array
     *  
     *              File Information Array Structure            
     *              $infos["owner"]     =>  File Owner Name (Human Readable);
     *              $infos["readable"]  =>  File is Readable;
     *              $infos["writable"]  =>  File is writable;
     *              $infos["mtime"]     =>  Last Modification TimeStamp;
     *              $infos["modified"]  =>  Last Modification Date (Human Readable);
     *              $infos["md5"]       =>  File MD5 Checksum
     *              $infos["size"]      =>  File Size in bytes
     * 
     *  @remark     For all function used remotly, all parameters have default predefined values
     *              in order to avoid remote execution errors. 
     * 
     */
    public function isFile($dir=NULL,$file=NULL)
    {
        //====================================================================//
        // Safety Checks 
        if (empty($dir)) {
            return Splash::Log()->Err("ErrFileDirMissing",__FUNCTION__);
        }
        if (empty($file)) {
            return Splash::Log()->Err("ErrFileFileMissing",__FUNCTION__);
        }
        //====================================================================//
        // Assemble full Filename
        $fullpath = $dir.$file;
        //====================================================================//
        // Check if folder exists
        if (is_dir($dir)) {
            //====================================================================//
            // Check if file exists 
            if (is_file($fullpath)) {
                Splash::Log()->Deb("OsWs Local - Get File Infos : File ".$file." exists.");
                //====================================================================//
                // Read file Informations 
                $infos = array();
                $owner = posix_getpwuid ( fileowner($fullpath) );
                $infos["owner"]     = $owner["name"];
                $infos["readable"]  = is_readable($fullpath);
                $infos["writable"]  = is_writable($fullpath);
                $infos["mtime"]     = filemtime($fullpath);
                $infos["modified"]  = date ("F d Y H:i:s.", $infos["mtime"]);
                $infos["md5"]       = md5_file ($fullpath);
                $infos["size"]      = filesize($fullpath);
                return $infos;
            } else {
                Splash::Log()->War("ErrFileNoExists",__FUNCTION__,$fullpath);
            }
        } else {
            Splash::Log()->War("ErrFileDirNoExists",__FUNCTION__,$dir);
        }
        return OSWS_KO;
    }

    /**
     *  @abstract   Read a file from local filesystem
     *  @param      string      $dir        Local File Directory 
     *  @param      string      $file       Local FileName 
     *  @return     array       $file       0 if not found, else file contents array
     *  
     *              File Information Array Structure            
     *              $infos["filename"]  =>  File Name
     *              $infos["raw"]       =>  Raw File Contents
     *              $infos["md5"]       =>  File MD5 Checksum
     *              $infos["size"]      =>  File Size in bytes
     * 
     *  @remark     For all function used remotly, all parameters have default predefined values
     *              in order to avoid remote execution errors. 
     * 
     */
    public function ReadFile($dir=NULL,$file=NULL)
    {
        //====================================================================//
        // Safety Checks 
        if (empty($dir)) {
            return Splash::Log()->Err("ErrFileDirMissing",__FUNCTION__);
        }
        if (empty($file)) {
            return Splash::Log()->Err("ErrFileFileMissing",__FUNCTION__);
        }
        //====================================================================//
        // Assemble full Filename
        $fullpath = $dir . "/" . $file;
        //====================================================================//
        // Check if folder exists
        if (!is_dir($dir)) {
            return Splash::Log()->War("ErrFileDirNoExists",$dir);
        }
        //====================================================================//
        // Check if file exists 
        if ( !is_file($fullpath) || !is_readable($fullpath) ) {
            return  Splash::Log()->War("ErrFileReadable",$fullpath);
        }
        Splash::Log()->Deb("MsgFileExists",$file);
        //====================================================================//
        // Open File 
        $filehandle = fopen($fullpath, "rb");
        if ($filehandle == FALSE) {
            return  Splash::Log()->Err("ErrFileRead",$fullpath);
        }
        //====================================================================//
        // Fill file Informations 
        $infos = array();
        $infos["filename"]      = $file;
        $infos["raw"]           = base64_encode(fread($filehandle, filesize($fullpath)));
        fclose($filehandle);
        $infos["md5"]           = md5_file ($fullpath);
        $infos["size"]          = filesize($fullpath);
        Splash::Log()->Deb("MsgFileRead",$file);
        return $infos;
    }

    /**
     *  @abstract   Read a file contents from local filesystem & encode it to base64 format
     * 
     *  @param      string      $FileName       Full path local FileName 
     *  
     *  @return     string  Base64 encoded raw file
     * 
     */
    public function ReadFileContents($FileName  =   NULL)
    {
        //====================================================================//
        // Safety Checks 
        if (empty($FileName)) {
            return Splash::Log()->Err("ErrFileFileMissing");
        }

        //====================================================================//
        // Check if file exists 
        if (!is_file($FileName)) {
            return Splash::Log()->Err("ErrFileNoExists",$FileName);
        }
        
        //====================================================================//
        // Check if file is readable
        if (!is_readable($FileName)) {
            return Splash::Log()->Err("ErrFileReadable",$FileName);
        }
        
        Splash::Log()->Deb("MsgFileRead",$FileName);
        
        //====================================================================//
        // Read File Contents 
        return base64_encode(file_get_contents($FileName));
    }
    
    /**
     *  @abstract   Write a file on local filesystem
     *  @param      string      $dir        Local File Directory 
     *  @param      string      $file       Local FileName 
     *  @param      string      $md5        File MD5 Checksum
     *  @param      string      $raw        Raw File Contents (base64 Encoded)
     *  @return     int         $result     0 if OK, 1 if OK
     * 
     *  @remark     For all function used remotly, all parameters have default predefined values
     *              in order to avoid remote execution errors. 
     * 
     */
    public function WriteFile($dir=NULL,$file=NULL,$md5=NULL,$raw=NULL)
    {
        //====================================================================//
        // Safety Checks 
        if (empty($dir)) {
            return Splash::Log()->Err("ErrFileDirMissing",__FUNCTION__);
        }
        if (empty($file)) {
            return Splash::Log()->Err("ErrFileFileMissing",__FUNCTION__);
        }
        if (empty($md5)) {
            return Splash::Log()->Err("ErrFileMd5Missing",__FUNCTION__);
        }
        if (empty($raw)) {
            return Splash::Log()->Err("ErrFileRawMissing",__FUNCTION__);
        }
        //====================================================================//
        // Assemble full Filename
        $fullpath = $dir.$file;
        //====================================================================//
        // Check if folder exists or create it
        if (!is_dir($dir)) {    mkdir($dir,0777,TRUE);    }
        //====================================================================//
        // Check if folder exists
        if (is_dir($dir)) {
            //====================================================================//
            // Check if file exists 
            if (is_file($fullpath) ) {
                Splash::Log()->Deb("MsgFileExists",__FUNCTION__,$file);
                //====================================================================//
                // Check if file is writable
                if (!is_writable($file)) {
                    return Splash::Log()->Err("ErrFileWriteable",$file);
                }
            }
            //====================================================================//
            // Open File 
            $filehandle = fopen($fullpath, 'wb');
            if ($filehandle != FALSE) {
                //====================================================================//
                // Write file
                fwrite($filehandle, base64_decode($raw));
                fclose($filehandle);
                //====================================================================//
                // Verify file checksum
                if ( $md5 === md5_file ($fullpath) ) {
                    return Splash::Log()->Msg("MsgFileWrite",$file);
                } else {
                    return Splash::Log()->Err("ErrFileWrite",$file);
                }
            } else {
                Splash::Log()->War("ErrFileOpen",__FUNCTION__,$fullpath);
            }
        } else {
            Splash::Log()->War("ErrFileDirNoExists",__FUNCTION__,$dir);
        }
        return OSWS_KO;
    }
    
    /**
     *  @abstract   Delete a file exists or not
     *  @param      string      $dir        Local File Directory 
     *  @param      string      $file       Local FileName 
     *  @return     int         $result     0 if OK, 1 if OK
     * 
     *  @remark     For all function used remotly, all parameters have default predefined values
     *              in order to avoid remote execution errors. 
     * 
     */
    public function DeleteFile($dir=NULL,$file=NULL)
    {
        //====================================================================//
        // Safety Checks 
        if (empty($dir)) {
            return Splash::Log()->Err("ErrFileDirMissing",__FUNCTION__);
        }
        if (empty($file)) {
            return Splash::Log()->Err("ErrFileFileMissing",__FUNCTION__);
        }
        //====================================================================//
        // Assemble full Filename
        $fullpath = $dir.$file;
        //====================================================================//
        // Check if folder exists
        if (is_dir($dir)) {
            //====================================================================//
            // Check if file exists 
            if (is_file($fullpath)) {
                Splash::Log()->Deb("MsgFileExists",__FUNCTION__,$file);
                //====================================================================//
                // Delete File 
                if ( unlink ( $fullpath ) ) {
                    return Splash::Log()->Msg("MsgFileDeleted",$file);
                } else {
                    return Splash::Log()->Err("ErrFileDeleted",$file);
                }
            } else {
                Splash::Log()->War("ErrFileNoExists",__FUNCTION__,$file);
            }
        } else {
            Splash::Log()->War("ErrFileDirNoExists",__FUNCTION__,$dir);
        }
        return OSWS_KO;
    }
    
    
    
    
    
    
    
}
?>