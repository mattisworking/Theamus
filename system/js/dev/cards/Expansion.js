Theamus.Style.Card.Expansion = function(card) {
    this.card = card;
    this.hasExpansion = true;
    if (this.getExpansion() === false) {
        this.hasExpansion = false;
        return;
    }
    if (this.getHeader() === false) return;
    this.createChevron();
};

Theamus.Style.Card.Expansion.prototype = {
    constructor: Theamus.Style.Card.Expansion
    
    , locale: {
        expandedClass: "card_expanded",
        query: {
            expansion: "section[type='card_expansion']",
            header: "header[type='card_header']",
        },
        chevron: {
            element: "div",
            innerHTML: "<span class='glyphicon ion-chevron-down'></span>",
            class: "card_expander",
            flipClass: "chevron-flip"
        }
    }
    
    , getExpansion: function() {
        var expansion = this.card.querySelector(this.locale.query.expansion);
        if (!expansion) return false;
        this.expansion = expansion;
    }
    
    , getHeader: function() {
        var header = this.card.querySelector(this.locale.query.header);
        if (!header) {
            this.card.classList.add("no-header-expansion");
            this.header = false;
        }
        this.header = header;
    }
    
    , createChevron: function() {
        var chevron = document.createElement(this.locale.chevron.element);
        chevron.innerHTML = this.locale.chevron.innerHTML;
        chevron.classList.add(this.locale.chevron.class);
        chevron.removeEventListener("click", function(){return;}, false);
        
        if (this.header) this.header.appendChild(chevron);
        else this.card.appendChild(chevron);
        this.chevron = chevron;
        
        this.addChevronListeners();
        this.addChevronInformation();
    }
    
    , addChevronInformation: function() {
        this.chevron.expand = this.chevronExpand;
        this.chevron.collapse = this.chevronCollapse;
        this.chevron.expansion = this.expansion;
        this.chevron.expandedClass = this.locale.expandedClass;
        this.chevron.flipClass = this.locale.chevron.flipClass;
    }
    
    , addChevronListeners: function() {
        this.chevron.addEventListener("click", this.chevronClick);
        this.expand = this.expandCard;
        this.collapse = this.collapseCard;
    }
    
    , chevronClick: function() {
        if (this.expansion.classList.contains(this.expandedClass)) {
            this.collapse();
        } else {
            this.expand();
        }
    }
    
    , chevronExpand: function() {
        this.expansion.classList.add(this.expandedClass);
        this.classList.add(this.flipClass);
    }
    
    , chevronCollapse: function() {
        this.expansion.classList.remove(this.expandedClass);
        this.classList.remove(this.flipClass);
    }
    
    , expandCard: function() {
        this.expansion.classList.add(this.locale.expandedClass);
        this.chevron.classList.add(this.locale.chevron.flipClass);
    }
    
    , collapseCard: function() {
        this.expansion.classList.remove(this.locale.expandedClass);
        this.chevron.classList.remove(this.locale.chevron.flipClass);
    }
};