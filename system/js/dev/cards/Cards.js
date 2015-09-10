Theamus.Style.Card = function(card) {
    this.card = card;
    this.defineHeader();
    this.findInputs();
    this.findCollapsibles();
    this.handleContentCards();
    this.giveIDs();
    this.createObjects();
    this.defineCardFunctions();
};

Theamus.Style.Card.prototype = {
    constructor: Theamus.Style.Card
    
    , locale: {
        input: {
            query: ["input", "textarea", "select"]
        },
        headerQuery: "header[type='card_header']",
        collapsibleQuery: "section[type='card_collapsible']",
        contentCardClass: "card_text-expansion",
        progressQuery: ".card_progress-wrapper",
        notification: {
            element: "div",
            class: "alert",
            classPrefix: "alert-",
            icon: {
                class: "glyphicon",
                element: "span",
                type: {
                    success: "ion-checkmark-round",
                    danger: "ion-close",
                    warning: "ion-alert",
                    info: "ion-information",
                    spinner: ["spinner", "spinner-fixed-size"]
                }
            }
        }
    }
    
    , defineHeader: function() {
        var header = this.card.querySelector(this.locale.headerQuery);
        if (header === null) this.header = null;
        else this.card.header = header;
    }

    , defineCardFunctions: function() {
        this.card.registerCollapsible = this.registerCollapsible;
        this.card.deleteCollapsible = this.deleteCollapsible;
        this.card.getAllCollapsibles = this.getAllCollapsibles;
        this.card.getCollapsible = this.getCollapsible;
        this.card.isExpandable = this.isExpandable;
        this.card.hasContentExpansion = this.hasContentExpansion;
        this.card.expandContent = this.expandContent;
        this.card.addProgress = this.addProgress;
        this.card.getProgress = this.getProgress;
        this.card.progressQuery = this.locale.progressQuery;
        this.card.getAllInputs = this.getAllInputs;
        this.card.getInput = this.getInput;
        this.card.addNotification = this.addNotification;
        this.card.removeNotification = this.removeNotification;
        this.card.notification = this.locale.notification;
        this.card.notification.makeIcon = this.makeNotificationIcon;
        this.card.notification.create = this.createNotificationElement;
    }

    , giveIDs: function() {
        this.giveInputsIDs();
        this.giveCollapsibleIDs();
    }

    , createObjects: function() {
        this.createInputObjects();
        this.createExpansionObject();
        this.createCollapsibleObjects();
    }
    
    , findInputs: function() {
        this.card.inputs = {raw: [], object: {}};
        this.card.inputs.raw = this.card.querySelectorAll(this.locale.input.query.join(", "));
    }

    , findCollapsibles: function() {
        this.card.collapsibles = {raw: [], object: {}};
        this.card.collapsibles.raw = this.card.querySelectorAll(this.locale.collapsibleQuery);
    }
    
    , giveInputsIDs: function() {
        var i, input;
        for (i = 0; i < this.card.inputs.raw.length; i++) {
            input = this.card.inputs.raw[i];
            if (!input.id) input.id = Theamus.Style.makeID();
        }
    }
    
    , giveCollapsibleIDs: function() {
        var i, collapsible;
        for (i = 0; i < this.card.collapsibles.raw.length; i++) {
            collapsible = this.card.collapsibles.raw[i];
            if (!collapsible.id) collapsible.id = Theamus.Style.makeID();
        }
    }
    
    , createInputObjects: function() {
        var i, input;
        for (i = 0; i < this.card.inputs.raw.length; i++) {
            input = this.card.inputs.raw[i];
            this.card.inputs.object[input.id] = new Theamus.Style.Card.Input(input);
        }
    }

    , getAllInputs: function() {
        return this.inputs.object;
    }

    , getInput: function(inputID) {
        if (!inputID) return false;
        return this.inputs.object[inputID].input;
    }
    
    , createExpansionObject: function() {
        this.card.expansion = new Theamus.Style.Card.Expansion(this.card);
    }

    , createCollapsibleObjects: function() {
        var i, collapsible;
        for (i = 0; i < this.card.collapsibles.raw.length; i++) {
            collapsible = this.card.collapsibles.raw[i];
            this.card.collapsibles.object[collapsible.id] = new Theamus.Style.Card
                                                                    .Collapsible(collapsible);
        }
    }

    , getAllCollapsibles: function() {
        return this.collapsibles.object;
    }

    , getCollapsible: function(collapsibleID) {
        return this.collapsibles.object[collapsibleID].collapsible;
    }

    , isExpandable: function() {
        return this.expansion.hasExpansion;
    }

    , handleContentCards: function() {
        if (this.card.classList.contains(this.locale.contentCardClass)) {
            this.card.content = new Theamus.Style.Card.Content(this.card);
        }
    }

    , hasContentExpansion: function() {
        if (!this.content) return false;
        else return this.content.hasExpansion;
    }

    , addProgress: function() {
        this.progress = new Theamus.Style.Card.Progress(this);
    }

    , addNotification: function(type, message) {
        if (!type || type === "" || !message || message === "") return;
        if (!this.notification.object) {
            this.notification.type = type;
            this.notification.message = message;
            this.notification.create();
            
            this.insertBefore(this.notification.object, this.header.nextSibling);
        } else {
            this.removeNotification();
            this.addNotification(type, message);
        }
    }
    
    , createNotificationElement: function() {
        var notify = document.createElement(this.element);
        notify.classList.add(this.class);
        notify.classList.add(this.classPrefix + this.type);
        this.object = notify;
        this.makeIcon();
        notify.appendChild(document.createTextNode(this.message));
    }
    
    , makeNotificationIcon: function() {
        var icon = document.createElement(this.icon.element);
        icon.classList.add(this.icon.class);
        if (typeof this.icon.type[this.type] === "string") {
            icon.classList.add(this.icon.type[this.type]);
        } else {
            for (var i = 0; i < this.icon.type[this.type].length; i++) {
                icon.classList.add(this.icon.type[this.type][i]);
            }
        }
        this.object.appendChild(icon);
    }

    , removeNotification: function() {
        if (!this.notification.object) return;
        this.removeChild(this.notification.object);
        this.notification.object = null;
    }
    
    , registerCollapsible: function(collapsible) {
        if (!collapsible) {
            console.error("Failed to register collapsible object because it was undefined.");
        }
        
        this.appendChild(collapsible);
        if (!collapsible.id) collapsible.id = Theamus.Style.makeID();
        this.collapsibles.object[collapsible.id] = new Theamus.Style.Card.Collapsible(collapsible);
        return this.collapsibles.object[collapsible.id].collapsible;
    }
    
    , deleteCollapsible: function(collapsible) {
        if (!collapsible) {
            console.error("Failed to delete collapsible object because it was undefined.");
        }
        
        collapsible.parentNode.removeChild(collapsible);
        delete this.collapsibles.object[collapsible.id];
    }
};