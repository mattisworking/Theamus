Theamus.Style.Card.Content = function(card) {
    this.hasExpansion = true;
    this.card = card;
    this.content = {};
    if (this.getContentWrapper() === false) {
        this.hasExpansion = false;
        return;
    }
    this.createExpansion();
    this.defineContentVariables();
};

Theamus.Style.Card.Content.prototype = {
    constructor: Theamus.Style.Card.Content
    
    , locale: {
        query: {
            expanded: ".card_text-expansion-expanded",
            contentWrapper: ".card_text-expansion-wrapper"
        },
        expandedClass: "card_text-expansion-expanded",
        expandedPadding: 110,
        maxExpansionHeight: 300,
        chevron: {
            elementType: "div",
            class: "card_text-expansion-hider",
            innerHTML: "<span class='glyphicon ion-chevron-down'></span>"
        }
    }

    , defineContentVariables: function() {
        this.expand = this.manualExpand;
        this.collapse =this.manualCollapse;
    }

    , getContentWrapper: function() {
        var contentWrapper = this.card.querySelector(this.locale.query.contentWrapper);
        if (contentWrapper === null) return false;
        else {
            this.content.wrapper = contentWrapper;
            return true;
        }
    }

    , createExpansion: function() {
        this.createChevron();
    }

    , createChevron: function() {
        var chevron = document.createElement(this.locale.chevron.elementType);
        chevron.classList.add(this.locale.chevron.class);
        chevron.innerHTML = this.locale.chevron.innerHTML;
        this.content.chevron = chevron;
        this.addChevron();
    }

    , addChevron: function() {
        this.content.wrapper.appendChild(this.content.chevron);
        this.setChevronListeners();
        this.setChevronVariables();
    }

    , setChevronVariables: function() {
        this.content.chevron.parent = this.card;
        this.content.chevron.expandedClass = this.locale.expandedClass;
        this.content.chevron.expandedPadding = this.locale.expandedPadding;
        this.content.chevron.maxExpansionHeight = this.locale.maxExpansionHeight;
        this.content.chevron.collapse = this.selfCollapse;
        this.content.chevron.expand = this.selfExpand;
    }

    , setChevronListeners: function() {
        this.content.chevron.removeEventListener("click", function(){return;}, false);
        this.content.chevron.addEventListener("click", this.chevronClick, false);
    }

    , chevronClick: function(e) {
        e.preventDefault();
        if (this.parent.classList.contains(this.expandedClass)) this.collapse();
        else this.expand();
    }

    , selfExpand: function() {
        this.parent.style.maxHeight = (this.parentNode.offsetHeight + this.expandedPadding) + "px";
        this.parent.classList.add(this.expandedClass);
    }

    , selfCollapse: function() {
        this.parent.style.maxHeight = this.maxExpansionHeight + "px";
        this.parent.classList.remove(this.expandedClass);
    }

    , manualExpand: function() {
        this.content.wrapper.parentNode.style.maxHeight = (this.content.wrapper.offsetHeight 
                                                            + this.locale.expandedPadding) + "px";
        this.content.wrapper.parentNode.classList.add(this.locale.expandedClass);
    }

    , manualCollapse: function() {
        this.content.wrapper.parentNode.style.maxHeight = this.locale.maxExpansionHeight + "px";
        this.content.wrapper.parentNode.classList.remove(this.locale.expandedClass);
    }
}