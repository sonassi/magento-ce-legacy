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
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Convert profile edit tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_System_Convert_Profile_Edit_Tab_History extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('history_grid');
        $this->setDefaultSort('performed_at', 'desc');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('core/convert_history_collection')
            ->addFieldToFilter('profile_id', Mage::registry('current_convert_profile')->getId());
        $collection->getSelect()
            ->join(array(
                'u'=>Mage::getSingleton('core/resource')->getTableName('admin/user')),
                'u.user_id=main_table.user_id',
                array('user_name'=>"concat(u.firstname, ' ', u.lastname)"));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('action_code', array(
            'header' => Mage::helper('adminhtml')->__('Action Code'),
            'index' => 'action_code',
        ));

        $this->addColumn('performed_at', array(
            'header' => Mage::helper('adminhtml')->__('Performed At'),
            'type' => 'datetime',
            'align' => 'center',
            'index' => 'performed_at',
            'width' => '150px',
        ));

        $this->addColumn('user_name', array(
            'header' => Mage::helper('adminhtml')->__('User Name'),
            'index' => 'user_name',
            'width' => '200px',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return Mage::getUrl('*/*/history', array('_current' => true));
    }
}
