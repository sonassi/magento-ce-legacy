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
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
var Checkout = Class.create();
Checkout.prototype = {
    initialize: function(accordion, progressUrl, reviewUrl, saveMethodUrl, failureUrl){
        this.accordion = accordion;
        this.progressUrl = progressUrl;
        this.reviewUrl = reviewUrl;
        this.saveMethodUrl = saveMethodUrl;
		this.failureUrl = failureUrl;
        this.billingForm = false;
        this.shippingForm= false;
        this.syncBillingShipping = false;
        this.method = '';
        this.payment = '';
        this.loadWaiting = false;

        this.steps = ['login', 'billing', 'shipping', 'shipping_method', 'payment', 'review'];

        //this.onSetMethod = this.nextStep.bindAsEventListener(this);

        this.accordion.disallowAccessToNextSections = true;
    },

	ajaxFailure: function(){
		location.href = this.failureUrl;
	},

    reloadProgressBlock: function(){
        var updater = new Ajax.Updater($$('.col-right')[0], this.progressUrl, {method: 'get', onFailure: this.ajaxFailure.bind(this)});
    },

    reloadReviewBlock: function(){
        var updater = new Ajax.Updater('checkout-review-load', this.reviewUrl, {method: 'get', onFailure: this.ajaxFailure.bind(this)});
    },

    setLoadWaiting: function(step) {
        if (step) {
            if (this.loadWaiting) {
                this.setLoadWaiting(false);
            }
            $(step+'-buttons-container').setStyle({opacity:.5});
            Element.show(step+'-please-wait');
        } else {
            if (this.loadWaiting) {
                $(this.loadWaiting+'-buttons-container').setStyle({opacity:1});
                Element.hide(this.loadWaiting+'-please-wait');
            }
        }
        this.loadWaiting = step;
    },

    gotoSection: function(section)
    {
    	section = $('opc-'+section);
    	section.addClassName('allow');
    	this.accordion.openSection(section);
    },

    setMethod: function(){
        if ($('login:guest') && $('login:guest').checked) {
            this.method = 'guest';
            var request = new Ajax.Request(
                this.saveMethodUrl,
                {method: 'post', onFailure: this.ajaxFailure.bind(this), parameters: {method:'guest'}}
            );
            Element.hide('register-customer-password');
            this.gotoSection('billing');
        }
        else if($('login:register') && $('login:register').checked) {
            this.method = 'register';
            var request = new Ajax.Request(
                this.saveMethodUrl,
                {method: 'post', onFailure: this.ajaxFailure.bind(this), parameters: {method:'register'}}
            );
            Element.show('register-customer-password');
            this.gotoSection('billing');
        }
        else{
            alert(Translator.translate('Please choose to register or to checkout as a guest'));
            return false;
        }
    },

    setBilling: function() {
    	if (($('billing:pickup_or_use_for_shipping_yes')) && ($('billing:pickup_or_use_for_shipping_yes').checked)) {
    		shipping.syncWithBilling();
    		$('opc-shipping').addClassName('allow');
    		this.gotoSection('shipping_method');
    	} else if (($('billing:pickup_or_use_for_shipping_no')) && ($('billing:pickup_or_use_for_shipping_no').checked)) {
    		$('shipping:same_as_billing').checked = false;
    		this.gotoSection('shipping');
    	} else {
    		$('shipping:same_as_billing').checked = true;
    		this.gotoSection('shipping');
    	}

    	// this refreshes the checkout progress column
    	this.reloadProgressBlock();

//        if ($('billing:use_for_shipping') && $('billing:use_for_shipping').checked){
//            shipping.syncWithBilling();
//            //this.setShipping();
//            //shipping.save();
//	        $('opc-shipping').addClassName('allow');
//	        this.gotoSection('shipping_method');
//        } else {
//            $('shipping:same_as_billing').checked = false;
//	        this.gotoSection('shipping');
//        }
//        this.reloadProgressBlock();
//        //this.accordion.openNextSection(true);
    },

    setShipping: function() {
        this.reloadProgressBlock();
        //this.nextStep();
        this.gotoSection('shipping_method');
        //this.accordion.openNextSection(true);
    },

    setShippingMethod: function() {
        this.reloadProgressBlock();
        //this.nextStep();
        this.gotoSection('payment');
        //this.accordion.openNextSection(true);
    },

    setPayment: function() {
        this.reloadProgressBlock();
        //this.nextStep();
        this.gotoSection('review');
        //this.accordion.openNextSection(true);
    },

    setReview: function() {
        this.reloadProgressBlock();
        //this.nextStep();
        //this.accordion.openNextSection(true);
    },

    back: function(){
        if (this.loadWaiting) return;
        this.accordion.openPrevSection(true);
    }
}

// billing
var Billing = Class.create();
Billing.prototype = {
    initialize: function(form, addressUrl, saveUrl){
        this.form = form;
        this.addressUrl = addressUrl;
        this.saveUrl = saveUrl;
        this.onAddressLoad = this.fillForm.bindAsEventListener(this);
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },

    setAddress: function(addressId){
        if (addressId) {
            request = new Ajax.Request(
                this.addressUrl+addressId,
                {method:'get', onSuccess: this.onAddressLoad, onFailure: checkout.ajaxFailure.bind(checkout)}
            );
        }
        else {
            this.fillForm(false);
        }
    },

    newAddress: function(isNew){
        if (isNew) {
            this.resetSelectedAddress();
            Element.show('billing-new-address-form');
        } else {
            Element.hide('billing-new-address-form');
        }
    },

    resetSelectedAddress: function(){
        var selectElement = $('billing-address-select')
        if (selectElement) {
            selectElement.value='';
        }
    },

    fillForm: function(transport){
        var elementValues = {};
        if (transport && transport.responseText){
            try{
                elementValues = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                elementValues = {};
            }
        }
        else{
            this.resetSelectedAddress();
        }
        arrElements = Form.getElements(this.form);
        for (var elemIndex in arrElements) {
            if (arrElements[elemIndex].id) {
                var fieldName = arrElements[elemIndex].id.replace(/^billing:/, '');
                arrElements[elemIndex].value = elementValues[fieldName] ? elementValues[fieldName] : '';
                if (fieldName == 'country_id' && billingForm){
                    billingForm.elementChildLoad(arrElements[elemIndex]);
                }
            }
        }
    },

    setUseForShipping: function(flag) {
        $('shipping:same_as_billing').checked = flag;
    },

    /*
    	Possible flags

    	1 - Use the billing address for shipping
    	0 - Use different address
    	2 - Store Pickup (no shipping or billing info needed)
     */
    setPickupOrUseForShipping: function(flag) {
    	switch(flag) {
    		case 1:
    			$('shipping:same_as_billing').checked = true;
    			break;
    		case 0:
    		case 2:
    			$('shipping:same_as_billing').checked = false;
	    		break;
    	}
    },

    save: function(){
        if (checkout.loadWaiting!=false) return;

        var validator = new Validation(this.form);
        if (validator.validate()) {
            checkout.setLoadWaiting('billing');
            if (checkout.method=='register' && $('billing:customer_password').value != $('billing:confirm_password').value) {
                alert(Translator.translate('Error: Passwords do not match'));
                return;
            }

//            if ($('billing:use_for_shipping') && $('billing:use_for_shipping').checked) {
//                $('billing:use_for_shipping').value=1;
//            }

            var request = new Ajax.Request(
                this.saveUrl,
                {
                    method: 'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
					onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form)
                }
            );
        }
    },

    resetLoadWaiting: function(transport){
        checkout.setLoadWaiting(false);
    },

    /**
    	This method recieves the AJAX response on success.
    	There are 3 options: error, redirect or html with shipping options.
    */
    nextStep: function(transport){
        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        if (response.error){
            alert(response.message);
            return false;
        }
        if (response.redirect) {
            location.href = response.redirect;
            return;
        }
        if (response.shipping_methods_html) {
        	$('checkout-shipping-method-load').innerHTML = response.shipping_methods_html;
        }
        // DELETE
        //alert('error: ' + response.error + ' / redirect: ' + response.redirect + ' / shipping_methods_html: ' + response.shipping_methods_html);
        // This moves the accordion panels of one page checkout and updates the checkout progress
        checkout.setBilling();
    }
}

// shipping
var Shipping = Class.create();
Shipping.prototype = {
    initialize: function(form, addressUrl, saveUrl, methodsUrl){
        this.form = form;
        this.addressUrl = addressUrl;
        this.saveUrl = saveUrl;
        this.methodsUrl = methodsUrl;
        this.onAddressLoad = this.fillForm.bindAsEventListener(this);
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },

    setAddress: function(addressId){
        if (addressId) {
            request = new Ajax.Request(
                this.addressUrl+addressId,
                {method:'get', onSuccess: this.onAddressLoad, onFailure: checkout.ajaxFailure.bind(checkout)}
            );
        }
        else {
            this.fillForm(false);
        }
    },

    newAddress: function(isNew){
        if (isNew) {
            this.resetSelectedAddress();
            Element.show('shipping-new-address-form');
        } else {
            Element.hide('shipping-new-address-form');
        }
        shipping.setSameAsBilling(false);
    },

    resetSelectedAddress: function(){
        var selectElement = $('shipping-address-select')
        if (selectElement) {
            selectElement.value='';
        }
    },

    fillForm: function(transport){
        var elementValues = {};
        if (transport && transport.responseText){
            try{
                elementValues = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                elementValues = {};
            }
        }
        else{
            this.resetSelectedAddress();
        }
        arrElements = Form.getElements(this.form);
        for (var elemIndex in arrElements) {
            if (arrElements[elemIndex].id) {
                var fieldName = arrElements[elemIndex].id.replace(/^shipping:/, '');
                arrElements[elemIndex].value = elementValues[fieldName] ? elementValues[fieldName] : '';
                if (fieldName == 'country_id' && shippingForm){
                    shippingForm.elementChildLoad(arrElements[elemIndex]);
                }
            }
        }
    },

    setSameAsBilling: function(flag) {
        $('shipping:same_as_billing').checked = flag;
        $('billing:pickup_or_use_for_shipping_yes').checked = flag;
        if (flag) {
            this.syncWithBilling();
        }
    },

    syncWithBilling: function () {
        $('billing-address-select') && this.newAddress(!$('billing-address-select').value);
        $('shipping:same_as_billing').checked = true;
        if (!$('billing-address-select') || !$('billing-address-select').value) {
            arrElements = Form.getElements(this.form);
            for (var elemIndex in arrElements) {
                if (arrElements[elemIndex].id) {
                    var sourceField = $(arrElements[elemIndex].id.replace(/^shipping:/, 'billing:'));
                    if (sourceField){
                        arrElements[elemIndex].value = sourceField.value;
                    }
                }
            }
            //$('shipping:country_id').value = $('billing:country_id').value;
            shippingRegionUpdater.update();
            $('shipping:region_id').value = $('billing:region_id').value;
            $('shipping:region').value = $('billing:region').value;
            //shippingForm.elementChildLoad($('shipping:country_id'), this.setRegionValue.bind(this));
        } else {
            $('shipping-address-select').value = $('billing-address-select').value;
        }
    },

    setRegionValue: function(){
        $('shipping:region').value = $('billing:region').value;
    },

    save: function(){
    	if (checkout.loadWaiting!=false) return;
        var validator = new Validation(this.form);
        if (validator.validate()) {
            checkout.setLoadWaiting('shipping');
            var request = new Ajax.Request(
                this.saveUrl,
                {
                    method:'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
					onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form)
                }
            );
        }
    },

    resetLoadWaiting: function(transport){
        checkout.setLoadWaiting(false);
    },

    nextStep: function(transport){
    	if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        if (response.redirect) {
            location.href = response.redirect;
            return;
        }
        if (response.shipping_methods_html) {
        	$('checkout-shipping-method-load').innerHTML = response.shipping_methods_html;
        }
        /*
        var updater = new Ajax.Updater(
            'checkout-shipping-method-load',
            this.methodsUrl,
            {method:'get', onSuccess: checkout.setShipping.bind(checkout)}
        );
        */
        checkout.setShipping();
    }
}

// shipping method
var ShippingMethod = Class.create();
ShippingMethod.prototype = {
    initialize: function(form, saveUrl){
        this.form = form;
        this.saveUrl = saveUrl;
        this.validator = new Validation(this.form);
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },

    validate: function() {
    	var methods = document.getElementsByName('shipping_method');
    	if (methods.length==0) {
    		alert(Translator.translate('Your order can not be completed at this time as there is no shipping methods available for it. Please make neccessary changes in your shipping address.'));
    		return false;
    	}

    	if(!this.validator.validate()) {
    	    return false;
    	}

    	for (var i=0; i<methods.length; i++) {
    		if (methods[i].checked) {
    			return true;
    		}
    	}
    	alert(Translator.translate('Please specify shipping method.'));
    	return false;
    },

    save: function(){

    	if (checkout.loadWaiting!=false) return;
        if (this.validate()) {
            checkout.setLoadWaiting('shipping-method');
            var request = new Ajax.Request(
                this.saveUrl,
                {
                    method:'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
					onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form)
                }
            );
        }
    },

    resetLoadWaiting: function(transport){
        checkout.setLoadWaiting(false);
    },

    nextStep: function(){
        checkout.setShippingMethod();
    }
}


// payment
var Payment = Class.create();
Payment.prototype = {
    initialize: function(form, saveUrl){
        this.form = form;
        this.saveUrl = saveUrl;
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
        var elements = Form.getElements(form);
        var method = null;
        for (var i=0; i<elements.length; i++) {
            if (elements[i].name=='payment[method]') {
                if (elements[i].checked) {
                    method = elements[i].value;
                }
            } else {
                elements[i].disabled = true;
            }
        }
        if (method) this.switchMethod(method);
    },

    switchMethod: function(method){
        if (this.currentMethod && $('payment_form_'+this.currentMethod)) {
            var form = $('payment_form_'+this.currentMethod);
            form.style.display = 'none';
            var elements = form.getElementsBySelector('input', 'select', 'textarea');
            for (var i=0; i<elements.length; i++) elements[i].disabled = true;
        }
        if ($('payment_form_'+method)){
            var form = $('payment_form_'+method);
            form.style.display = '';
            var elements = form.getElementsBySelector('input', 'select', 'textarea');
            for (var i=0; i<elements.length; i++) elements[i].disabled = false;
            this.currentMethod = method;
        }
    },

    validate: function() {
    	var methods = document.getElementsByName('payment[method]');
    	if (methods.length==0) {
    		alert(Translator.translate('Your order can not be completed at this time as there is no payment methods available for it.'));
    		return false;
    	}
    	for (var i=0; i<methods.length; i++) {
    		if (methods[i].checked) {
    			return true;
    		}
    	}
    	alert(Translator.translate('Please specify payment method.'));
    	return false;
    },

    save: function(){
    	if (checkout.loadWaiting!=false) return;
        var validator = new Validation(this.form);
        if (this.validate() && validator.validate()) {
            checkout.setLoadWaiting('payment');
            var request = new Ajax.Request(
                this.saveUrl,
                {
                    method:'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
					onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form)
                }
            );
        }
    },

    resetLoadWaiting: function(){
        checkout.setLoadWaiting(false);
    },

    nextStep: function(transport){
    	if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        /*
        * if there is an error in payment, need to show error message
        */
        if (response.error) {
            alert(response.error);
            return;
        }
        if (response.redirect) {
            location.href = response.redirect;
            return;
        }
        if (response.review_html) {
        	$('checkout-review-load').innerHTML = response.review_html;
        }
        checkout.setPayment();
    }
}

var Review = Class.create();
Review.prototype = {
    initialize: function(saveUrl, successUrl){
        this.saveUrl = saveUrl;
        this.successUrl = successUrl;
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },

    save: function(){
    	if (checkout.loadWaiting!=false) return;
        checkout.setLoadWaiting('review');
        var request = new Ajax.Request(
            this.saveUrl,
            {
                method:'post',
                parameters:{save:true},
                onComplete: this.onComplete,
                onSuccess: this.onSave,
				onFailure: checkout.ajaxFailure.bind(checkout)
            }
        );
    },

    resetLoadWaiting: function(transport){
        checkout.setLoadWaiting(false);
    },

    nextStep: function(transport){
        if (transport && transport.responseText) {
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
            if (response.redirect) {
                location.href = response.redirect;
                return;
            }
            if (response.success) {
                window.location=this.successUrl;
            }
            else{
                var msg = response.error_messages;
                if (typeof(msg)=='object') {
                    msg = msg.join("\n");
                }
                alert(msg);
            }
        }
    }
}