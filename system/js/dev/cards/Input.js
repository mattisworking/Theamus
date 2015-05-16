Theamus.Style.Card.Input = function(input) {
    this.input = input;
    this.input.label = {};
    this.defineInputVariables();
    if (this.checkInput() === false) return;
    this.getInputAttachments();
    this.getLabelInformation();
    if (this.createLabel() === false) return;
    this.addInputEvents();
};

Theamus.Style.Card.Input.prototype = {
    constructor: Theamus.Style.Card.Input
    
    , locale: {
        feedback: {
            elementType: "span",
            class: "glyphicon",
            types: ["warn", "success", "info", "danger"],
            warnClass: "ion-alert",
            successClass: "ion-checkmark",
            infoClass: "ion-information",
            dangerClass: "ion-close",
            parentClass: "feedback",
            prefix: "feedback-",
            query: ".glyphicon",
            offsetDefault: 20,
            offsetPadding: 25
        },
        textInputs: [
                "text",
                "password",
                "search",
                "email",
                "url",
                "tel",
                "number",
                "textarea"
            ],
        interactiveInputs: [
                "checkbox",
                "radio",
                "range",
                "date",
                "color"
            ],
        cleanClass: "input-clean",
        defaultInputType: "input",
        data: {
            labelText: "data-label",
            labelDirection: "data-labeldirection",
            labelAction: "data-labelaction"
        },
        defaults: {
            labelText: "",
            labelDirection: "left",
            labelAction: "slide"
        },
        label: {
            element: "label",
            slideLeftClass: "out-left",
            slideUpClass: "out-up",
            padding: 15,
            inlineClassPrefix: "inline-",
            normal: {
                defaultClass: "label-normal",
                leftClass: "input_label-left",
                upClass: "input_label-up"
            }
        },
        attachmentClass: "input-attachment",
        attachmentOffsetDefault: 21
    }

    /**
     * Ignore any inputs that are in attachments
     **/
    , checkInput: function() {
        if (this.input.parentNode.classList.contains(this.locale.attachmentClass)) return false;
    }

    , defineInputVariables: function() {
        this.input.addFeedback = this.addFeedback;
        this.input.locale = this.locale;
        this.input.createFeedbackIcon = this.createFeedbackIcon;
        this.input.removeFeedback = this.removeFeedback;
    }

    , defineLabelDefaults: function() {
        this.input.label.startLeft = 20;
        this.input.label.isClean = false;
    }
    
    , getLabelInformation: function() {
        this.input.label.text = this.input.getAttribute(this.locale.data.labelText);
        this.input.label.direction = this.input.getAttribute(this.locale.data.labelDirection);
        this.input.label.action = this.input.getAttribute(this.locale.data.labelAction);
        
        this.cleanLabelInformation();
    }
    
    , cleanLabelInformation: function() {
        if (!this.input.label.text) this.input.label.text = this.locale.defaults.labelText;
        if (!this.input.label.direction) this.input.label.direction = this.locale.defaults.labelDirection;
        if (!this.input.label.action) this.input.label.action = this.locale.defaults.labelAction;
        
        this.input.removeAttribute(this.locale.data.labelText);
        this.input.removeAttribute(this.locale.data.labelDirection);
        this.input.removeAttribute(this.locale.data.labelAction);
    }

    , createLabel: function() {
        if (this.input.label.text === "") return false;
        if (this.input.classList.contains(this.locale.cleanClass)) {
            this.input.label.isClean = true;
            this.loadPlaceholder();
        } else if (this.input.label.action === "none") {
            this.loadNormalLabel();
        } else if (this.locale.textInputs.indexOf(this.input.type) > -1) {
            this.loadTextLabel();
        } else if (this.locale.interactiveInputs.indexOf(this.input.type) > -1) {
            this.loadInteractiveLabel();
        } else {
            console.error("Failed to create a label because the input type " + 
            "couldn't be identified for the input with an ID of " 
            + this.input.tagName.toLowerCase() + "#" + this.input.id);
        }
    }

    , loadPlaceholder: function() {
        this.input.setAttribute("placeholder", this.input.label.text);
    }

    , loadNormalLabel: function() {
        var label = document.createElement(this.locale.label.element);
        label.innerHTML = this.input.label.text;
        if (this.input.label.direction === "left") label.classList.add(this.locale.label.normal.leftClass);
        else label.classList.add(this.locale.label.normal.upClass);
        this.input.parentNode.insertBefore(label, this.input);
    }

    , loadTextLabel: function() {
        this.defineLabelDefaults();
        var label = document.createElement(this.locale.label.element);
        label.innerHTML = this.input.label.text;
        label.setAttribute("for", this.input.id);
        label.style.left = this.getLabelStartLeft() + "px";
        this.input.parentNode.insertBefore(label, this.input);
        this.addLabelInformation(label);
    }

    , loadInteractiveLabel: function() {
        var label = document.createElement(this.locale.label.element);
        label.classList.add(this.locale.label.inlineClassPrefix + this.input.label.direction);
        label.classList.add(this.locale.label.normal.defaultClass);
        label.innerHTML = this.input.label.text;
        this.input.parentNode.insertBefore(label, this.input);
    }

    , addLabelInformation: function(label) {
        var width = this.getBoldedLabelWidth(label);
        this.input.label.element = label;
        this.input.label.info = this.locale.label;
        this.input.label.inputOffset = width + this.locale.label.padding;
        this.input.label.labelOffset = (width * -1) - (this.locale.label.padding - 10);
    }

    , getBoldedLabelWidth: function(label) {
        var boldWidth = label.offsetWidth, preStyle = label.getAttribute("style");
        label.style.fontWeight = "bold";
        boldWidth = label.offsetWidth;
        label.setAttribute("style", preStyle);
        return boldWidth;
    }

    , getInputAttachments: function() {
        var i, child, before = true, attachments = {before: [], after: []},
            children = this.input.parentNode.children;
        for (i = 0; i < children.length; i++) {
            child = children[i];
            if (this.locale.textInputs.indexOf(child.type) > -1) before = false;
            if (this.locale.textInputs.indexOf(child.type) === -1) {
                if (before === true) attachments.before.push(child);
                else attachments.after.push(child);
            }
        }
        this.input.attachments = attachments;
    }

    , getLabelStartLeft: function() {
        if (this.input.attachments.before.length > 0) {
            this.input.label.startLeft = this.input.attachments.before[0].offsetWidth + 
                this.locale.attachmentOffsetDefault;
        }
        return this.input.label.startLeft;
    }

    , addInputEvents: function() {
        if (this.input.label.action === "none") return;
        this.input.removeEventListener("focus", function(){return;}, false);
        this.input.removeEventListener("blur", function(){return;}, false);
        this.input.addEventListener("focus", this.eventFocusInput);
        this.input.addEventListener("blur", this.eventBlurInput);
    }

    , eventFocusInput: function() {
        if (this.label.isClean === true) return;
        if (this.label.action === "disappear") this.label.element.style.display = "none";
        else if (this.label.direction === "left") {
            this.label.element.classList.add(this.label.info.slideLeftClass);
            this.label.element.style.left = this.label.labelOffset + "px";
            this.parentNode.setAttribute("style", "width: calc(100% - " + this.label.inputOffset + "px);");
            this.parentNode.style.left = this.label.inputOffset + "px";
        } else if (this.label.direction === "up") {
            this.label.element.classList.add(this.label.info.slideUpClass);
            this.parentNode.classList.add(this.label.info.slideUpClass);
        }
    }

    , eventBlurInput: function() {
        if (this.label.isClean === true) return;
        if (this.label.action === "disappear") this.label.element.style.display = "block";
        else if (this.label.direction === "left") {
            this.label.element.classList.remove(this.label.info.slideLeftClass);
            this.label.element.style.left = this.label.startLeft + "px";
            this.parentNode.removeAttribute("style");
        } else if (this.label.direction === "up") {
            this.label.element.classList.remove(this.label.info.slideUpClass);
            this.parentNode.classList.remove(this.label.info.slideUpClass);
        }
    }

    , addFeedback: function(type, message) {
        this.feedbackType = !type ? "" : type;
        this.feedbackMessage = !message ? "" : message;

        if (this.locale.feedback.types.indexOf(type) === -1) return;

        this.removeFeedback();
        this.parentNode.classList.add(this.locale.feedback.parentClass);
        this.parentNode.classList.add(this.locale.feedback.prefix + type);
        this.createFeedbackIcon();
    }

    , removeFeedback: function() {
        var i, type, existing;
        this.parentNode.classList.remove(this.locale.feedback.parentClass);
        for (i = 0; i < this.locale.feedback.types.length; i++) {
            type = this.locale.feedback.types[i];
            this.parentNode.classList.remove(this.locale.feedback.prefix + type);
        }
        existing = this.parentNode.querySelector(this.locale.feedback.query);
        if (existing !== null) this.parentNode.removeChild(existing);
    }

    , createFeedbackIcon: function() {
        var icon = document.createElement(this.locale.feedback.elementType);
        icon.classList.add(this.locale.feedback.class);
        if (this.attachments.after.length > 0) {
            icon.style.right = (this.attachments.after[0].offsetWidth + this.locale.feedback.offsetPadding)
                                    + "px";
        } else {
            icon.style.right = this.locale.feedback.offsetDefault + "px";
        }
        
        if (this.feedbackMessage !== "") icon.innerHTML = "<span>" + this.feedbackMessage + "</span>";
        
        if (this.feedbackType === "warn") icon.classList.add(this.locale.feedback.warnClass);
        if (this.feedbackType === "success") icon.classList.add(this.locale.feedback.successClass);
        if (this.feedbackType === "info") icon.classList.add(this.locale.feedback.infoClass);
        if (this.feedbackType === "danger") icon.classList.add(this.locale.feedback.dangerClass);
        this.parentNode.appendChild(icon);
    }
};