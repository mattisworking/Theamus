Theamus.Style = {
    cards: {raw: [], good: [], object: {}}
    
    , locale: {
        possibleIDCharacters: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789",
        cards: {
            query: "section[type='card']"
        }
    }
    
    , loadCards: function() {
        this.findCards();
        this.giveCardsIDs();
        this.createCardObjects();
    }
    
    , loadCard: function(el) {
        if (!el) {
            console.error("Failed to add a new card because no element object was given.");
            return false;
        }
        
        this.cards.raw.push(el);
        this.giveCardsIDs();
        this.createCardObjects();
    }
    
    , toArray: function(nl) {
        var ret = [];
        for (var i = 0; i < nl.length; i++) {
            ret.push(nl[i]);
        }
        return ret;
    }
    
    , findCards: function() {
        var cards = document.querySelectorAll(this.locale.cards.query);
        if (cards.length < 1) return;
        this.cards.raw = this.toArray(cards);
    }
    
    , giveCardsIDs: function() {
        var i, card;
        
        for (i = 0; i < this.cards.raw.length; i++) {
            card = this.cards.raw[i];
            if (!card.id) card.id = this.makeID();
            this.cards.good.push(card);
        }
    }
    
    , makeID: function() {
        var i, text = "";
        for (i = 0; i < 10; i++) {
            text += this.locale.possibleIDCharacters.
                    charAt(Math.floor(Math.random() * this.locale.possibleIDCharacters.length));
        }
        return text;
    }
    
    , createCardObjects: function() {
        var i, card;
        for (i = 0; i < this.cards.good.length; i++) {
            card = this.cards.good[i];
            if (!this.cards.object[card.id]) {
                this.cards.object[card.id] = new Theamus.Style.Card(card);
            }
        }
    }

    , getAllCards: function() {
        return this.cards.object;
    }
    
    , getCard: function(cardID) {
        if (!cardID) console.error("Failed to get card because no card ID was given.");
        else if (!this.cards.object[cardID]) console.error("There's no card existing with that ID.");
        else return this.cards.object[cardID].card;
    }
};