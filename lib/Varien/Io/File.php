<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Varien
 * @package    Varien_Io
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Filesystem client
 *
 * @category   Varien
 * @package    Varien_Io
 */
class Varien_Io_File extends Varien_Io_Abstract
{
    /**
     * Save initial working directory
     *
     * @var string
     */
    protected $_iwd;

    /**
     * Use virtual current working directory for application integrity
     *
     * @var string
     */
    protected $_cwd;

    /**
     * Used to grep ls() output
     *
     * @const
     */
    const GREP_FILES = 'files_only';

    /**
     * Used to grep ls() output
     *
     * @const
     */
    const GREP_DIRS = 'dirs_only';

    /**
     * Open a connection
     *
     * Possible arguments:
     * - path     default current path
     *
     * @param array $args
     * @return boolean
     */
    public function open(array $args=array())
    {
        $this->_iwd = getcwd();
        $this->cd(!empty($args['path']) ? $args['path'] : $this->_iwd);
        return true;
    }

    /**
     * Close a connection
     *
     * @return boolean
     */
    public function close()
    {
        return true;
    }

    /**
     * Create a directory
     *
     * @param string $dir
     * @param int $mode
     * @param boolean $recursive
     * @return boolean
     */
    public function mkdir($dir, $mode=0777, $recursive=true)
    {
        if ($this->_cwd) {
            chdir($this->_cwd);
        }
        $result = @mkdir($dir, $mode, $recursive);
        if ($this->_iwd) {
            chdir($this->_iwd);
        }
        return $result;
    }

    /**
     * Delete a directory
     *
     * @param string $dir
     * @return boolean
     */
    public function rmdir($dir, $recursive=false)
    {
        @chdir($this->_cwd);
        $result = @rmdir($dir);
        @chdir($this->_iwd);
        return $result;
    }

    /**
     * Get current working directory
     *
     * @return string
     */
    public function pwd()
    {
        return $this->_cwd;
    }

    /**
     * Change current working directory
     *
     * @param string $dir
     * @return boolean
     */
    public function cd($dir)
    {
        if( is_dir($dir) ) {
            @chdir($this->_iwd);
            $this->_cwd = realpath($dir);
            return true;
        } else {
            throw new Exception('Unable to list current working directory.');
            return false;
        }
    }

    /**
     * Read a file to result, file or stream
     *
     * If $dest is null the output will be returned.
     * Otherwise it will be saved to the file or stream and operation result is returned.
     *
     * @param string $filename
     * @param string|resource $dest
     * @return boolean|string
     */
    public function read($filename, $dest=null)
    {
        chdir($this->_cwd);
        $result = @file_get_contents($filename);
        chdir($this->_iwd);

        if (is_string($dest) || is_resource($dest)) {
            return @file_put_contents($dest, $result);
        } elseif (is_null($dest)) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Write a file from string, file or stream
     *
     * @param string $filename
     * @param string|resource $src
     * @return int|boolean
     */
    public function write($filename, $src, $mode=null)
    {
        if (is_string($src) && is_readable($src)) {
            $src = realpath($src);
            $srcIsFile = true;
        } elseif (is_string($src) || is_resource($src)) {
            $srcIsFile = false;
        } else {
            return false;
        }
        chdir($this->_cwd);

        if (file_exists($filename)) {
            if (!is_writeable($filename)) {
                return false;
            }
        } else {
            if (!is_writable(dirname($filename))) {
                return false;
            }
        }
        if ($srcIsFile) {
            $result = @copy($src, $filename);
        } else {
            $result = @file_put_contents($filename, $src);
        }
        if (!is_null($mode)) {
            @chmod($filename, $mode);
        }
        chdir($this->_iwd);
        return $result;
    }

    /**
     * Delete a file
     *
     * @param string $filename
     * @return boolean
     */
    public function rm($filename)
    {
        @chdir($this->_cwd);
        $result = @unlink($filename);
        @chdir($this->_iwd);
        return $result;
    }

    /**
     * Rename or move a directory or a file
     *
     * @param string $src
     * @param string $dest
     * @return boolean
     */
    public function mv($src, $dest)
    {
        chdir($this->_cwd);
        $result = @rename($src, $dest);
        chdir($this->_iwd);
        return $result;
    }

    /**
     * Copy a file
     *
     * @param string $src
     * @param string $dest
     * @return boolean
     */
    public function cp($src, $dest)
    {
        chdir($this->_cwd);
        $result = @copy($src, $dest);
        chdir($this->_iwd);
        return $result;
    }

    /**
     * Change mode of a directory or a file
     *
     * @param string $filename
     * @param int $mode
     * @return boolean
     */
    public function chmod($filename, $mode)
    {
        if ($this->_cwd) {
            chdir($this->_cwd);
        }
        $result = @chmod($filename, $mode);
        if ($this->_iwd) {
            chdir($this->_iwd);
        }
        return $result;
    }

    /**
     * Get list of cwd subdirectories and files
     *
     * Suggestions (from moshe):
     * - Use filemtime instead of filectime for performance
     * - Change $grep to $flags and use binary flags
     *   - LS_DIRS  = 1
     *   - LS_FILES = 2
     *   - LS_ALL   = 3
     *
     * @param Varien_Io_File const
     * @access public
     * @return array
     */
    public function ls($grep=null)
    {
        $ignoredDirectories = Array('.', '..');

        if( is_dir($this->_cwd) ) {
            $dir = $this->_cwd;
        } elseif( is_dir($this->_iwd) ) {
            $dir = $this->_iwd;
        } else {
            throw new Exception('Unable to list current working directory.');
        }

        $list = Array();

        if ($dh = opendir($dir)) {
            while (($entry = readdir($dh)) !== false) {
                $list_item = Array();

                $fullpath = $dir . DIRECTORY_SEPARATOR . $entry;

                if( ($grep == self::GREP_DIRS) && (!is_dir($fullpath)) ) {
                    continue;
                } elseif( ($grep == self::GREP_FILES) && (!is_file($fullpath)) ) {
                    continue;
                } elseif( in_array($entry, $ignoredDirectories) ) {
                    continue;
                }

                $list_item['text'] = $entry;
                $list_item['mod_date'] = date ('Y-m-d H:i:s', filectime($fullpath));
                $list_item['permissions'] = $this->_parsePermissions(fileperms($fullpath));
                $list_item['owner'] = $this->_getFileOwner($fullpath);

                if( is_file($fullpath) ) {
                    $pathinfo = pathinfo($fullpath);
                    $list_item['size'] = filesize($fullpath);
                    $list_item['leaf'] = true;
                    if( isset($pathinfo['extension']) && in_array(strtolower($pathinfo['extension']), Array('jpg', 'jpeg', 'gif', 'bmp', 'png')) && $list_item['size'] > 0 ) {
                        $list_item['is_image'] = true;
                        $list_item['filetype'] = $pathinfo['extension'];
                    } elseif( $list_item['size'] == 0 ) {
                        $list_item['is_image'] = false;
                        $list_item['filetype'] = 'unknown';
                    } elseif( isset($pathinfo['extension']) ) {
                        $list_item['is_image'] = false;
                        $list_item['filetype'] = $pathinfo['extension'];
                    } else {
                        $list_item['is_image'] = false;
                        $list_item['filetype'] = 'unknown';
                    }
                } else {
                    $list_item['leaf'] = false;
                    $list_item['id'] = $fullpath;
                }

                $list[] = $list_item;
            }
            closedir($dh);
        } else {
            throw new Exception('Unable to list current working directory. Access forbidden.');
        }

        return $list;
    }

    /**
     * Convert integer permissions format into human readable
     *
     * @param integer $mode
     * @access protected
     * @return string
     */
    protected function _parsePermissions($mode)
    {
        if( $mode & 0x1000 )
            $type='p'; /* FIFO pipe */
        else if( $mode & 0x2000 )
            $type='c'; /* Character special */
        else if( $mode & 0x4000 )
            $type='d'; /* Directory */
        else if( $mode & 0x6000 )
            $type='b'; /* Block special */
        else if( $mode & 0x8000 )
            $type='-'; /* Regular */
        else if( $mode & 0xA000 )
            $type='l'; /* Symbolic Link */
        else if( $mode & 0xC000 )
            $type='s'; /* Socket */
        else
            $type='u'; /* UNKNOWN */

        /* Determine permissions */
        $owner['read'] = ($mode & 00400) ? 'r' : '-';
        $owner['write'] = ($mode & 00200) ? 'w' : '-';
        $owner['execute'] = ($mode & 00100) ? 'x' : '-';
        $group['read'] = ($mode & 00040) ? 'r' : '-';
        $group['write'] = ($mode & 00020) ? 'w' : '-';
        $group['execute'] = ($mode & 00010) ? 'x' : '-';
        $world['read'] = ($mode & 00004) ? 'r' : '-';
        $world['write'] = ($mode & 00002) ? 'w' : '-';
        $world['execute'] = ($mode & 00001) ? 'x' : '-';

        /* Adjust for SUID, SGID and sticky bit */
        if( $mode & 0x800 )
            $owner["execute"] = ($owner['execute']=='x') ? 's' : 'S';
        if( $mode & 0x400 )
            $group["execute"] = ($group['execute']=='x') ? 's' : 'S';
        if( $mode & 0x200 )
            $world["execute"] = ($world['execute']=='x') ? 't' : 'T';

        $s=sprintf('%1s', $type);
        $s.=sprintf('%1s%1s%1s', $owner['read'], $owner['write'], $owner['execute']);
        $s.=sprintf('%1s%1s%1s', $group['read'], $group['write'], $group['execute']);
        $s.=sprintf('%1s%1s%1s', $world['read'], $world['write'], $world['execute']);
        return trim($s);
    }

    /**
     * Get file owner
     *
     * @param string $filename
     * @access protected
     * @return string
     */
    protected function _getFileOwner($filename)
    {
        if( !function_exists('posix_getpwuid') ) {
            return 'n/a';
        }

        $owner = posix_getpwuid(fileowner($filename));

        $groupid   = posix_getegid();
        $groupinfo = posix_getgrgid($groupid);
        return $owner['name'] . ' / ' . $groupinfo['name'];
    }

    public function dirsep()
    {
        return DIRECTORY_SEPARATOR;
    }
}
