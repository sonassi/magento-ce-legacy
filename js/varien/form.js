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
VarienForm = Class.create();
VarienForm.prototype = {
    initialize: function(formId, firstFieldFocus){
        this.cache      = $A();
        this.currLoader = false;
        this.currDataIndex = false;
        this.form       = $(formId);
        this.validator  = new Validation(this.form);
        this.elementFocus   = this.elementOnFocus.bindAsEventListener(this);
        this.elementBlur    = this.elementOnBlur.bindAsEventListener(this);
        this.childLoader    = this.onChangeChildLoad.bindAsEventListener(this);
        this.highlightClass = 'highlight';
        this.extraChildParams = '';
        this.firstFieldFocus= firstFieldFocus || false;
        this.bindElements();
        if(this.firstFieldFocus){
            try{
                Form.Element.focus(Form.findFirstElement(this.form))
            }
            catch(e){}
        }
    },

    bindElements:function (){
        var elements = Form.getElements(this.form);
        for (var row in elements) {
            if (elements[row].id) {
                Event.observe(elements[row],'focus',this.elementFocus);
                Event.observe(elements[row],'blur',this.elementBlur);
            }
        }
    },

    elementOnFocus: function(event){
        var element = Event.findElement(event, 'fieldset');
        if(element.className){
            Element.addClassName(element, this.highlightClass);
        }
    },

    elementOnBlur: function(event){
        var element = Event.findElement(event, 'fieldset');
        if(element.className){
            Element.removeClassName(element, this.highlightClass);
        }
    },

    setElementsRelation: function(parent, child, dataUrl, first){
        if (parent=$(parent)) {
            // TODO: array of relation and caching
            if (!this.cache[parent.id]){
                this.cache[parent.id] = $A();
                this.cache[parent.id]['child']     = child;
                this.cache[parent.id]['dataUrl']   = dataUrl;
                this.cache[parent.id]['data']      = $A();
                this.cache[parent.id]['first']      = first || false;
            }
            Event.observe(parent,'change',this.childLoader);
        }
    },

    onChangeChildLoad: function(event){
        element = Event.element(event);
        this.elementChildLoad(element);
    },

    elementChildLoad: function(element, callback){
        this.callback = callback || false;
        if (element.value) {
            this.currLoader = element.id;
            this.currDataIndex = element.value;
            if (this.cache[element.id]['data'][element.value]) {
                this.setDataToChild(this.cache[element.id]['data'][element.value]);
            }
            else{
                new Ajax.Request(this.cache[this.currLoader]['dataUrl'],{
                        method: 'post',
                        parameters: {"parent":element.value},
                        onComplete: this.reloadChildren.bind(this)
                });
            }
        }
    },

    reloadChildren: function(transport){
        var data = eval('(' + transport.responseText + ')');
        this.cache[this.currLoader]['data'][this.currDataIndex] = data;
        this.setDataToChild(data);
    },

    setDataToChild: function(data){
        if (data.length) {
            var child = $(this.cache[this.currLoader]['child']);
            if (child){
                var html = '<select name="'+child.name+'" id="'+child.id+'" class="'+child.className+'" title="'+child.title+'" '+this.extraChildParams+'>';
                if(this.cache[this.currLoader]['first']){
                    html+= '<option value="">'+this.cache[this.currLoader]['first']+'</option>';
                }
                for (var i in data){
                    if(data[i].value) {
                        html+= '<option value="'+data[i].value+'"';
                        if(child.value && (child.value == data[i].value || child.value == data[i].label)){
                            html+= ' selected';
                        }
                        html+='>'+data[i].label+'</option>';
                    }
                }
                html+= '</select>';
                new Insertion.Before(child,html);
                Element.remove(child);
            }
        }
        else{
            var child = $(this.cache[this.currLoader]['child']);
            if (child){
                var html = '<input type="text" name="'+child.name+'" id="'+child.id+'" class="'+child.className+'" title="'+child.title+'" '+this.extraChildParams+'>';
                new Insertion.Before(child,html);
                Element.remove(child);
            }
        }

        this.bindElements();
        if (this.callback) {
            this.callback();
        }
    }
}

RegionUpdater = Class.create();
RegionUpdater.prototype = {
	initialize: function (countryEl, regionTextEl, regionSelectEl, regions)
	{
		this.countryEl = $(countryEl);
		this.regionTextEl = $(regionTextEl);
		this.regionSelectEl = $(regionSelectEl);
		this.regions = regions;

		this.update();

		Event.observe(this.countryEl, 'change', this.update.bind(this));
	},

	update: function()
	{
    	if (this.regions[this.countryEl.value]) {
    		var i, option, region;
    		var def = this.regionTextEl.value.toLowerCase();
    		if (!def) {
    		    def = this.regionSelectEl.getAttribute('defaultValue');
    		}

    		this.regionTextEl.value = '';

			this.regionSelectEl.options.length = 1;
    		for (regionId in this.regions[this.countryEl.value]) {
    			region = this.regions[this.countryEl.value][regionId];

    			option = document.createElement('OPTION');
    			option.value = regionId;
    			option.text = region.name;

    			if (this.regionSelectEl.options.add) {
    				this.regionSelectEl.options.add(option);
    			} else {
    				this.regionSelectEl.appendChild(option);
    			}

    			if (regionId==def || region.name.toLowerCase()==def || region.code.toLowerCase()==def) {
    				this.regionSelectEl.value = regionId;
    			}
    		}

    		this.regionTextEl.style.display = 'none';
    		this.regionSelectEl.style.display = '';
    		this.setMarkDisplay(this.regionSelectEl, true);
    	} else {
    		this.regionTextEl.style.display = '';
    		this.regionSelectEl.style.display = 'none';
    		this.setMarkDisplay(this.regionSelectEl, false);
    	}
	},

	setMarkDisplay: function(elem, display){
	    if(elem.parentNode){
	        var marks = Element.getElementsByClassName(elem.parentNode, 'required');
	        if(marks[0]){
	            display ? marks[0].show() : marks[0].hide();
	        }
	    }
	}
}