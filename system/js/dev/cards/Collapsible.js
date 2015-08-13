Theamus.Style.Card.Collapsible = function(collapsible) {
    this.collapsible = collapsible;
    this.createCollapsible();
};

Theamus.Style.Card.Collapsible.prototype = {
    constructor: Theamus.Style.Card.Collapsible
    
    , locale: {
        expandedClass: "collapsible-expanded",
        query: {
            header: "header[type='card_collapsible-header']",
            expansion: "section[type='card_collapsible-expansion']"
        },
        header: {
            selectableClass: "selectable",
            selectedClass: "selected"
        },
        chevron: {
            elementType: "div",
            class: "collapsible-chevron",
            innerHTML: "<span class='glyphicon ion-chevron-down'></span>",
            flippedClass: "collapsible-chevron-flipped",
            selectableClass: "selectable-collapsible-chevron"
        },
        selectable: {
            elementData: {
                id: "data-selectid",
                name: "data-selectname",
                value: "data-selectvalue"
            },
            input: {
                element: "input",
                type: "checkbox",
                class: "card_collapsible-selectable"
            }
        }
    }

    , defineCollapsibleVariables: function() {
        this.collapsible.expandedClass = this.locale.expandedClass;
        this.collapsible.chevronFlippedClass = this.locale.chevron.flippedClass;
        this.collapsible.expand = this.manualExpand;
        this.collapsible.collapse = this.manualCollapse;
        this.collapsible.functions = {selected: function(){return;}, unselected: function(){return;}};
        this.collapsible.setSelectableAttribute = this.setSelectableAttribute;
        this.collapsible.removeSelectableAttribute = this.removeSelectableAttribute;
    }

    , createCollapsible: function() {
        this.findHeader();
        this.checkSelectable();
        this.findExpansion();
        this.createChevron();
        this.defineCollapsibleVariables();
    }

    , findHeader: function() {
        this.collapsible.header = this.collapsible.querySelector(this.locale.query.header);
    }

    , findExpansion: function() {
        this.collapsible.expansion = this.collapsible.querySelector(this.locale.query.expansion);
    }

    , createChevron: function() {
        var chevron = document.createElement(this.locale.chevron.elementType);
        chevron.classList.add(this.locale.chevron.class);
        if (this.collapsible.isSelectable) {
            chevron.classList.add(this.locale.chevron.selectableClass);
        }
        chevron.innerHTML = this.locale.chevron.innerHTML;
        this.collapsible.chevron = chevron;
        this.setHeaderListeners();
        this.addChevron();
    }

    , addChevron: function() {
        this.collapsible.insertBefore(this.collapsible.chevron, this.collapsible.header);
        this.collapsible.chevron.expandedClass = this.locale.expandedClass;
    }

    , addHeaderInformation: function() {
        this.collapsible.header.expand = this.selfExpand;
        this.collapsible.header.collapse = this.selfCollapse;
        this.collapsible.header.expansion = this.collapsible.expansion;
        this.collapsible.header.expandedClass = this.locale.expandedClass;
        this.collapsible.header.chevronFlippedClass = this.locale.chevron.flippedClass;
        this.collapsible.header.selectedClass = this.locale.header.selectedClass;
    }

    , setHeaderListeners: function() {
        this.addHeaderInformation();
        this.collapsible.header.removeEventListener("click", function(){return;}, false);
        this.collapsible.header.addEventListener("click", this.headerClick, false);
        
        if (this.collapsible.isSelectable) {
            this.collapsible.chevron.removeEventListener("click", function(){return;}, false);
            this.collapsible.chevron.addEventListener("click", this.chevronClick, false);
        }
    }
    
    , chevronClick: function(e) {
        if (this.parentNode.classList.contains(this.expandedClass)) this.parentNode.collapse();
        else this.parentNode.expand();
    }

    , headerClick: function(e) {
        if (this.parentNode.isSelectable) {
            if (this.parentNode.checkbox.checked) {
                this.parentNode.checkbox.checked = false;
                this.classList.remove(this.selectedClass);
                this.parentNode.functions.unselected();
            } else {
                this.parentNode.checkbox.checked = true;
                this.classList.add(this.selectedClass);
                this.parentNode.functions.selected();
            }
        } else {
            e.preventDefault();
            if (this.expansion.classList.contains(this.expandedClass)) this.collapse();
            else this.expand();
        }
    }

    , selfExpand: function() {
        this.parentNode.classList.add(this.expandedClass);
        this.expansion.classList.add(this.expandedClass);
        this.parentNode.chevron.classList.add(this.chevronFlippedClass);
    }

    , selfCollapse: function() {
        this.parentNode.classList.remove(this.expandedClass);
        this.expansion.classList.remove(this.expandedClass);
        this.parentNode.chevron.classList.remove(this.chevronFlippedClass);
    }

    , manualExpand: function() {
        this.classList.add(this.expandedClass);
        this.expansion.classList.add(this.expandedClass);
        this.chevron.classList.add(this.chevronFlippedClass);
    }

    , manualCollapse: function() {
        this.classList.remove(this.expandedClass);
        this.expansion.classList.remove(this.expandedClass);
        this.chevron.classList.remove(this.chevronFlippedClass);
    }
    
    , checkSelectable: function() {
        if (!this.collapsible.getAttribute(this.locale.selectable.elementData.name)) return;
        
        var checkbox = document.createElement(this.locale.selectable.input.element);
        checkbox.classList.add(this.locale.selectable.input.class);
        checkbox.setAttribute("type", this.locale.selectable.input.type);
        checkbox.setAttribute("name", this.collapsible.getAttribute(this.locale.selectable.elementData.name));
        
        if (this.collapsible.getAttribute(this.locale.selectable.elementData.id)) {
            checkbox.setAttribute("id", this.collapsible.getAttribute(this.locale.selectable.elementData.id));
        }
        
        if (this.collapsible.getAttribute(this.locale.selectable.elementData.value)) {
            checkbox.setAttribute("value", this.collapsible.getAttribute(this.locale.selectable.elementData.value));
        } else {
            checkbox.setAttribute("value", "");
        }
        
        checkbox.header = this.collapsible.header;
        
        this.collapsible.header.appendChild(checkbox);
        this.collapsible.isSelectable = true;
        this.collapsible.checkbox = checkbox;
        
        this.collapsible.removeAttribute(this.locale.selectable.elementData.id);
        this.collapsible.removeAttribute(this.locale.selectable.elementData.name);
        this.collapsible.removeAttribute(this.locale.selectable.elementData.value);
        
        this.collapsible.header.classList.add(this.locale.header.selectableClass);
    }
    
    , setSelectableAttribute: function(name, value) {
        return this.checkbox.setAttribute(name, value);
    }
    
    , removeSelectableAttribute: function(name) {
        return this.checkbox.removeAttribute(name);
    }
};