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
        chevron: {
            elementType: "div",
            class: "collapsible-chevron",
            innerHTML: "<span class='glyphicon ion-chevron-down'></span>",
            flippedClass: "collapsible-chevron-flipped"
        }
    }

    , defineCollapsibleVariables: function() {
        this.collapsible.expandedClass = this.locale.expandedClass;
        this.collapsible.chevronFlippedClass = this.locale.chevron.flippedClass;
        this.collapsible.expand = this.manualExpand;
        this.collapsible.collapse = this.manualCollapse;
    }

    , createCollapsible: function() {
        this.findHeader();
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
        chevron.innerHTML = this.locale.chevron.innerHTML;
        this.collapsible.chevron = chevron;
        this.setHeaderListeners();
        this.addChevron();
    }

    , addChevron: function() {
        this.collapsible.insertBefore(this.collapsible.chevron, this.collapsible.header);
    }

    , addHeaderInformation: function() {
        this.collapsible.header.expand = this.selfExpand;
        this.collapsible.header.collapse = this.selfCollapse;
        this.collapsible.header.expansion = this.collapsible.expansion;
        this.collapsible.header.expandedClass = this.locale.expandedClass;
        this.collapsible.header.chevronFlippedClass = this.locale.chevron.flippedClass;
    }

    , setHeaderListeners: function() {
        this.addHeaderInformation();
        this.collapsible.header.removeEventListener("click", function(){return;}, false);
        this.collapsible.header.addEventListener("click", this.headerClick, false);
    }

    , headerClick: function(e) {
        e.preventDefault();
        if (this.expansion.classList.contains(this.expandedClass)) this.collapse();
        else this.expand();
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
};