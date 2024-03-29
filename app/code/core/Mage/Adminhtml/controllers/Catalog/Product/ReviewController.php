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
 * Reviews admin controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Catalog_Product_ReviewController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('catalog/review');

        $this->_addContent($this->getLayout()->createBlock('adminhtml/review_main'));

        $this->renderLayout();
    }

    public function pendingAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('catalog/review');

        Mage::register('usePendingFilter', true);
        $this->_addContent($this->getLayout()->createBlock('adminhtml/review_main'));

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('catalog/review');

        $this->_addContent($this->getLayout()->createBlock('adminhtml/review_edit'));

        $this->renderLayout();
    }

    public function newAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('catalog/review');

        $this->getLayout()->getBlock('root')->setCanLoadExtJs(true);

        $this->_addContent($this->getLayout()->createBlock('adminhtml/review_add'));

        $this->renderLayout();
    }

    public function saveAction()
    {
        $reviewId = $this->getRequest()->getParam('id', false);
        if ($data = $this->getRequest()->getPost()) {
            $review = Mage::getModel('review/review')->load($reviewId)->addData($data);
            try {
                $review->setId($reviewId)
                    ->save();

                $arrRatingId = $this->getRequest()->getParam('ratings', array());
                $votes =  Mage::getModel('rating/rating_option_vote')
                    ->getResourceCollection()
                    ->setReviewFilter($reviewId)
                    ->addOptionInfo()
                    ->load()
                    ->addRatingOptions();
                foreach ($arrRatingId as $ratingId=>$optionId) {
                    if($vote = $votes->getItemByColumnValue('rating_id', $ratingId)) {
                        Mage::getModel('rating/rating')
                            ->setVoteId($vote->getId())
                            ->setReviewId($review->getId())
                            ->updateOptionVote($optionId);
                    } else {
                        Mage::getModel('rating/rating')
                            ->setRatingId($ratingId)
                            ->setReviewId($review->getId())
                            ->addOptionVote($optionId, $review->getEntityPkValue());
                    }
                }

                $review->aggregate();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('Review was saved successfully'));
                if( $this->getRequest()->getParam('ret') == 'pending' ) {
                    $this->getResponse()->setRedirect(Mage::getUrl('*/*/pending'));
                } else {
                    $this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
                }
                return;
            } catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirectReferer();
    }

    public function deleteAction()
    {
        $reviewId = $this->getRequest()->getParam('id', false);

        try {
            Mage::getModel('review/review')->setId($reviewId)
                ->aggregate()
                ->delete();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('Review successfully deleted'));
            if( $this->getRequest()->getParam('ret') == 'pending' ) {
                $this->getResponse()->setRedirect(Mage::getUrl('*/*/pending'));
            } else {
                $this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
            }
            return;
        } catch (Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirectReferer();
    }

    public function productGridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/review_product_grid')->toHtml());
    }

    public function reviewGridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/review_grid')->toHtml());
    }

    public function jsonProductInfoAction()
    {
        $response = new Varien_Object();
        $id = $this->getRequest()->getParam('id');
        if( intval($id) > 0 ) {
            $product = Mage::getModel('catalog/product')
                ->load($id);

            $response->setId($id);
            $response->addData($product->getData());
            $response->setError(0);
        } else {
            $response->setError(1);
            $response->setMessage(Mage::helper('catalog')->__('Unable to get product id.'));
        }
        $this->getResponse()->setBody($response->toJSON());
    }

    public function postAction()
    {
        $productId = $this->getRequest()->getParam('product_id', false);
        if ($data = $this->getRequest()->getPost()) {
            if(isset($data['select_stores'])) {
                $data['stores'] = $data['select_stores'];
            }

            $review = Mage::getModel('review/review')->setData($data);

            $product = Mage::getModel('catalog/product')
                ->load($productId);

            try {
                $review->setEntityId(1) // product
                    ->setEntityPkValue($productId)
                    ->setStoreId($product->getStoreId())
                    ->setStatusId($data['status_id'])
                    ->save();

                $arrRatingId = $this->getRequest()->getParam('ratings', array());
                foreach ($arrRatingId as $ratingId=>$optionId) {
                	Mage::getModel('rating/rating')
                	   ->setRatingId($ratingId)
                	   ->setReviewId($review->getId())
                	   ->addOptionVote($optionId, $productId);
                }

                $review->aggregate();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('Review was successfully saved'));
                if( $this->getRequest()->getParam('ret') == 'pending' ) {
                    $this->getResponse()->setRedirect(Mage::getUrl('*/*/pending'));
                } else {
                    $this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
                }

                return;
            } catch (Exception $e){
                die($e->getMessage());
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
        return;
    }

    public function ratingItemsAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/review_rating_detailed')->setIndependentMode()->toHtml());
    }

    protected function _isAllowed()
    {
    	switch ($this->getRequest()->getActionName()) {
            case 'pending':
                return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/reviews/pending');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/reviews/all');
                break;
    	}
    }
}
