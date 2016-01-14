Theamus.Style.Card.Input = function(input) {
    this.input = input;
    this.input.label = {element:{}, info:{}};
    
    
    this.defineInputVariables();
    if (this.checkInput() === false) return;
    if (!input.labels || input.labels.length === 0) {
        this.getInputAttachments();
        this.getLabelInformation(this.input.label);
        if (this.createLabel() === false) return;
        
    } else {
        input.labels[0].info = {};
        
        this.getInputAttachments(true); 
        
        this.input.label = input.labels[0];
        this.input.label.info.startLeft = this.getLabelStartLeft();
        
        this.defineLabelDefaults();
        this.getLabelInformation(input.labels[0]);
        this.addLabelInformation(input.labels[0]);
    }
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
        ignoreInputs: ["hidden"],
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
                "color",
                "select-one",
                "select-multiple"
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
        if (!this.input.label.info.startLeft) this.input.label.info.startLeft = 20;
        this.input.label.info.isClean = false;
    }
    
    , getLabelInformation: function(label) {
        label.info.text = this.input.getAttribute(this.locale.data.labelText);
        label.info.direction = this.input.getAttribute(this.locale.data.labelDirection);
        label.info.action = this.input.getAttribute(this.locale.data.labelAction);
        
        return this.cleanLabelInformation(label);
    }
    
    , cleanLabelInformation: function(label) {
        if (!label.info.text) label.info.text = this.locale.defaults.labelText;
        if (!label.info.direction) label.info.direction = this.locale.defaults.labelDirection;
        if (!label.info.action) label.info.action = this.locale.defaults.labelAction;
        
        return label;
    }

    , createLabel: function() {
        if (this.locale.label.text === "" || this.locale.ignoreInputs.indexOf(this.input.type) > -1) return false;
        if (this.input.classList.contains(this.locale.cleanClass)) {
            this.locale.label.isClean = true;
            this.loadPlaceholder();
        } else if (this.input.label.info.action === "none") {
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
        this.input.setAttribute("placeholder", this.locale.label.text);
    }

    , loadNormalLabel: function() {
        if (this.input.parentNode.querySelector(this.locale.label.element)) return;
        var label = document.createElement(this.locale.label.element);
        label.innerHTML = this.input.label.info.text;
        if (this.input.label.info.direction === "left") label.classList.add(this.locale.label.normal.leftClass);
        else label.classList.add(this.locale.label.normal.upClass);
        this.input.parentNode.insertBefore(label, this.input);
        
        label.info = this.input.label.info;
        this.addLabelInformation(label);
    }

    , loadTextLabel: function() {
        this.defineLabelDefaults();
        var label = document.createElement(this.locale.label.element);
        label.innerHTML = this.input.label.info.text;
        label.setAttribute("for", this.input.id);
        label.style.left = this.getLabelStartLeft() + "px";
        this.input.parentNode.insertBefore(label, this.input);
        
        label.info = this.input.label.info;
        this.addLabelInformation(label);
    }

    , loadInteractiveLabel: function() {
        var label = document.createElement(this.locale.label.element);
        label.classList.add(this.locale.label.normal.defaultClass);
        if (this.input.label.info.direction === "left") label.classList.add(this.locale.label.normal.leftClass);
        else label.classList.add(this.locale.label.normal.upClass);
        label.setAttribute("for", this.input.id);
        label.innerHTML = this.input.label.info.text;
        this.input.parentNode.insertBefore(label, this.input);
    }
    
    , mergeObjects: function(a, b) {
        var c = {}, attrname;
        for (attrname in a) { c[attrname] = a[attrname]; }
        for (attrname in b) { c[attrname] = b[attrname]; }
        return c;
    }

    , addLabelInformation: function(label) {
        var width = this.getBoldedLabelWidth(label);
        this.input.label.element = label;
        this.input.label.element.info = this.mergeObjects(this.locale.label, label.info);
        this.input.label.element.inputOffset = width + this.locale.label.padding;
        this.input.label.element.labelOffset = (width * -1) - (this.locale.label.padding - 10);
    }

    , getBoldedLabelWidth: function(label) {
        var boldWidth = label.offsetWidth, preStyle = label.getAttribute("style");
        label.style.fontWeight = "bold";
        boldWidth = label.offsetWidth;
        label.setAttribute("style", preStyle);
        return boldWidth;
    }

    , getInputAttachments: function(reinit) {
        if (!reinit) reinit = false;
        
        var i, child, input = this.input, pos = 0, before = true, attachments = {before: [], after: []},
            children = this.input.parentNode.children, start = (reinit) ? 1 : 0;
            
        while ((input = input.previousSibling) !== null) pos++;
            
        for (i = 0; i < children.length; i++) {
            child = children[i];
            if (i === pos || child.tagName.toLowerCase() === this.locale.label.element || !child.classList.contains("input-attachment")) continue;
            if (this.locale.textInputs.indexOf(child.type) > -1) before = false;
            if (this.locale.textInputs.indexOf(child.type) === -1) {
                if (i < pos || before) attachments.before.push(child);
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
        if (this.input.label.action === "none" || this.locale.interactiveInputs.indexOf(this.input.type) > -1) return;
        this.input.removeEventListener("focus", function(){return;}, false);
        this.input.removeEventListener("blur", function(){return;}, false);
        this.input.addEventListener("focus", this.eventFocusInput);
        this.input.addEventListener("blur", this.eventBlurInput);
    }

    , eventFocusInput: function() {
        if (this.label.element.info.isClean === true || this.label.element.info.action === "none") return;
        if (this.label.element.info.action === "disappear") this.label.element.style.display = "none";
        else if (this.label.element.info.direction === "left") {
            this.label.element.classList.add(this.label.element.info.slideLeftClass);
            this.label.element.style.left = this.label.element.labelOffset + "px";
            this.parentNode.setAttribute("style", "width: calc(100% - " + this.label.element.inputOffset + "px);");
            this.parentNode.style.left = this.label.element.inputOffset + "px";
        } else if (this.label.element.info.direction === "up") {
            this.label.element.classList.add(this.label.element.info.slideUpClass);
            this.parentNode.classList.add(this.label.element.info.slideUpClass);
        }
    }

    , eventBlurInput: function() {
        if (this.label.element.info.isClean === true || this.value !== "" || this.label.element.info.action === "none") return;
        if (this.label.element.info.action === "disappear") this.label.element.style.display = "block";
        else if (this.label.element.info.direction === "left") {
            this.label.element.classList.remove(this.label.element.info.slideLeftClass);
            this.label.element.style.left = this.label.element.info.startLeft + "px";
            this.parentNode.removeAttribute("style");
        } else if (this.label.element.info.direction === "up") {
            this.label.element.classList.remove(this.label.element.info.slideUpClass);
            this.parentNode.classList.remove(this.label.element.info.slideUpClass);
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