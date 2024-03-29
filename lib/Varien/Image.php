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
 * @package    Varien_Image
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Image handler library
 *
 * @category   Varien
 * @package    Varien_Image
 */
class Varien_Image
{
    protected $_adapter;

    protected $_fileName;
    
    /**
     * Constructor
     * 
     * @param Varien_Image_Adapter $adapter. Default value is GD2
     * @param string $fileName 
     * @return void
     */
    function __construct($adapter=Varien_Image_Adapter::ADAPTER_GD2, $fileName=null)
    {
        $this->_getAdapter($adapter);
        $this->_fileName = $fileName;
        if( isset($fileName) ) {
            $this->open();
        }
    }

    /**
     * Opens an image and creates image handle
     * 
     * @access public
     * @return void
     */
    public function open()
    {
        $this->_getAdapter()->checkDependencies();

        if( !file_exists($this->_fileName) ) {
            throw new Exception("File '{$this->_fileName}' does not exists.");
        }

        $this->_getAdapter()->open($this->_fileName);
    }

    /**
     * Display handled image in your browser
     * 
     * @access public
     * @return void
     */
    public function display()
    {
        $this->_getAdapter()->display();
    }

    /**
     * Save handled image into file
     * 
     * @param string $destination. Default value is NULL
     * @param string $newFileName. Default value is NULL
     * @access public
     * @return void
     */
    public function save($destination=null, $newFileName=null)
    {
        $this->_getAdapter()->save($destination, $newFileName);
    }
    
    /**
     * Rotate an image.
     * 
     * @param int $angle 
     * @access public
     * @return void
     */
    public function rotate($angle)
    {
        $this->_getAdapter()->rotate($angle);
    }
    
    /**
     * Crop an image.
     * 
     * @param int $top. Default value is 0
     * @param int $left. Default value is 0
     * @param int $right. Default value is 0 
     * @param int $bottom. Default value is 0 
     * @access public
     * @return void
     */
    public function crop($top=0, $left=0, $right=0, $bottom=0)
    {
        $this->_getAdapter()->crop($left, $top, $right, $bottom);
    }
    
    /**
     * Resize an image
     * 
     * @param int $width. Default value is NULL
     * @param int $height. Default value is NULL
     * @access public
     * @return void
     */
    public function resize($width=null, $height=null)
    {
        $this->_getAdapter()->resize($width, $height);
    }

    /**
     * Adds watermark to our image.
     * 
     * @param string $watermarkImage. Absolute path to watermark image.
     * @param int $positionX. Watermark X position.
     * @param int $positionY. Watermark Y position. 
     * @param int $watermarkImageOpacity. Watermark image opacity. 
     * @param bool $repeat. Enable or disable watermark brick.
     * @access public
     * @return void
     */
    public function watermark($watermarkImage, $positionX=0, $positionY=0, $watermarkImageOpacity=30, $repeat=false)
    {
        if( !file_exists($watermarkImage) ) {
            throw new Exception("Required file '{$watermarkImage}' does not exists.");
        }
        $this->_getAdapter()->watermark($watermarkImage, $positionX, $positionY, $watermarkImageOpacity, $repeat);
    }
    
    /**
     * Get mime type of handled image
     * 
     * @access public
     * @return string
     */
    public function getMimeType()
    {
        return $this->_getAdapter()->getMimeType();
    }

    /**
     * process 
     * 
     * @access public
     * @return void
     */
    public function process()
    {
        
    }

    /**
     * instruction 
     * 
     * @access public
     * @return void
     */
    public function instruction()
    {
        
    }

    /**
     * Set image background color 
     * 
     * @param int $color 
     * @access public
     * @return void
     */
    public function setImageBackgroundColor($color)
    {
        $this->_getAdapter()->imageBackgroundColor = intval($color);
    }

    protected function _getAdapter($adapter=null)
    {
        if( !isset($this->_adapter) ) {
            $this->_adapter = Varien_Image_Adapter::factory( $adapter );
        }
        return $this->_adapter;
    }

}
