Theamus.Style.Card.Progress = function(card) {
    this.card = card;
    this.progress = {};
    if (this.getProgressElement() === false) return;
    this.createPercentage();
};

Theamus.Style.Card.Progress.prototype = {
    constructor: Theamus.Style.Card.Progress
    
    , locale: {
        percentage: {
            elementType: "div",
            class: "card_progress-percentage",
            query: ".card_progress-percentage"
        },
        progress: {
            query: ".card_progress-wrapper",
            finished: "card_progress-finished"
        }
    }

    , defineProgressVariables: function() {
        this.progress.updatePercentage = this.updatePercentage();
    }

    , getProgressElement: function() {
        var progress = this.card.querySelector(this.locale.progress.query);
        if (progress === null) {
            console.error("No progress element could be found to update.");
            return false;
        } else {
            this.progress.element = progress;
            return true;
        }
    }

    , getPercentage: function() {
        if (!this.findPercentage()) {
            this.createPercentage();
        }
    }

    , findPercentage: function() {
        var percentage = this.card.querySelector(this.locale.percentage.query);
        if (percentage === null) return false;
        this.progress.percentage = percentage;
        return percentage;
    }

    , createPercentage: function() {
        var percentage = this.progress.element.querySelector("."+this.locale.percentage.class);
        if (!percentage) { 
            percentage = document.createElement(this.locale.percentage.elementType);
            percentage.classList.add(this.locale.percentage.class);
            this.progress.element.appendChild(percentage);
        }
        this.progress.percentage = percentage;
        this.updatePercentage(0);
    }

    , updatePercentage: function(number) {
        if (!number) number = 0;
        else number = parseInt(number);
        this.progress.percentage.number = number;

        if (number < 100) {
            this.changeProgress();
        } else {
            this.finishProgress();
        }
    }

    , changeProgress: function() {
        this.progress.element.style.display = "block";
        this.progress.element.style.width = this.progress.percentage.number + "%";
        this.progress.percentage.innerHTML = this.progress.percentage.number + "%";
    }

    , finishProgress: function() {
        this.progress.element.style.width = "100%";
        this.progress.percentage.innerHTML = "100%";

        if (!this.finishText) return;
        else {
            this.progress.element.innerHTML = this.finishText;
            this.progress.element.classList.add(this.locale.progress.finished);
        }
    }
};