var Sandbox = function() {
    this.addDataListener();
    
    return this;
};

Sandbox.prototype.addDataListener = function() {
    var self = this;
    document.getElementById("addData").addEventListener("click", function(e) {
        e.preventDefault();
        self.addDataItem();
    });
    
    return this;
};

Sandbox.prototype.addDataItem = function() {
    var wrapper = document.createElement("div");
    wrapper.classList.add("data-wrapper");
    
    var keyColumn = document.createElement("section");
    keyColumn.setAttribute("type", "card_column");
    keyColumn.classList.add("third");
    wrapper.appendChild(keyColumn);
    
    var keyWrapper = document.createElement("div");
    keyWrapper.classList.add("card_input-wrapper");
    keyColumn.appendChild(keyWrapper);
    
    var key = document.createElement("input");
    key.setAttribute("type", "text");
    key.setAttribute("name", "key");
    key.setAttribute("placeholder", "key");
    keyWrapper.appendChild(key);
    
    var valueColumn = document.createElement("section");
    valueColumn.setAttribute("type", "card_column");
    valueColumn.style.width = "60%";
    wrapper.appendChild(valueColumn);
    
    var valueWrapper = document.createElement("div");
    valueWrapper.classList.add("card_input-wrapper");
    valueColumn.appendChild(valueWrapper);
    
    var value = document.createElement("input");
    value.setAttribute("type", "text");
    value.setAttribute("name", "value");
    value.setAttribute("placeholder", "value");
    valueWrapper.appendChild(value);
    
    var deleteButton = document.createElement("a");
    deleteButton.setAttribute("href", "#");
    deleteButton.innerHTML = "<span class='glypicon ion-close'></span>";
    wrapper.appendChild(deleteButton);
    this.deleteDataListener(deleteButton);
    
    document.getElementById("dataInformation").appendChild(wrapper);
    
    document.getElementById("dataDefault").style.display = "none";
    
    return this;
};

Sandbox.prototype.deleteDataListener = function(element) {
    var self = this;
    element.addEventListener("click", function(e) {
        e.preventDefault();
        self.deleteDataItem(element.parentNode);
    });
    
    return this;
};

Sandbox.prototype.deleteDataItem = function(element) {
    element.parentNode.removeChild(element);
    if (document.querySelectorAll(".data-wrapper").length === 0) {
        document.getElementById("dataDefault").style.display = "block";
    }
    
    return this;
};

Sandbox.prototype.loadFeatureFunctions = function() {
    var functionElement = document.getElementById("function");
    
    // remove all options
    while (functionElement.firstChild) {
        functionElement.removeChild(functionElement.firstChild);
    }
    
    Theamus.Ajax.api({
        type: "get",
        url: Theamus.base_url + "/sandbox/",
        method: ["Sandbox", "get_feature_functions"],
        data: { custom: {
            feature: document.getElementById("feature").value
        }},
        success: function(r) {
            var methods = r.response.data;
            if (methods.length === 0) {
                var option = document.createElement("option");
                option.innerHTML = "There are no methods that can be run for this feature.";
            } else {
                for (var i = 0; i < methods.length; i++) {
                    var option = document.createElement("option");
                    option.setAttribute("value", methods[i][0] + ":" + methods[i][1]);
                    option.innerHTML = methods[i][0] + "->" + methods[i][1];
                    functionElement.appendChild(option);
                }
            }
        }
    });
    
    return this;
};

Sandbox.prototype.listenFeatureFunctions = function() {
    var self = this;
    document.getElementById("feature").addEventListener("change", function() {
        self.loadFeatureFunctions();
    });
    
    return this;
};

Sandbox.prototype.listenSend = function() {
    var self = this;
    document.getElementById("send").addEventListener("click", function(e) {
        e.preventDefault();
        self.send();
    });
};

Sandbox.prototype.send = function() {
    Theamus.Style.getCard("sendinfo").addNotification("spinner", "Running API request...");
    
    var self = this,
        infoElement = document.getElementById("info");
        dataElement = document.getElementById("data"),
        errorElement = document.getElementById("errors");
        
    infoElement.innerHTML = "";
    dataElement.innerHTML = "";
    errorElement.innerHTML = "";
        
    infoElement.classList.remove("prettyprinted");
    dataElement.classList.remove("prettyprinted");
    errorElement.classList.remove("prettyprinted");
    
    Theamus.Ajax.api({
        type: this.getSendType(),
        url: this.getURL(),
        method: [this.getMethodClass(), this.getMethod()],
        data: {
            custom: this.getData()
        },
        success: function(r) {
            Theamus.Style.getCard("sendinfo").removeNotification();
            
            console.log(r);
            
            infoElement.innerHTML = JSON.stringify(self.getInfo(), null, 2);
            dataElement.innerHTML = JSON.stringify(r.response.data, null, 2);
            errorElement.innerHTML = JSON.stringify(r.error, null, 2);
            prettyPrint();
        }
    })
};

Sandbox.prototype.getSendType = function() {
    return document.getElementById("requestType").value;
};

Sandbox.prototype.getURL = function() {
    return Theamus.base_url + "/" + document.getElementById("feature").value + "/";
};

Sandbox.prototype.getMethod = function() {
    return document.getElementById("function").value.split(":")[1];
};

Sandbox.prototype.getMethodClass = function() {
    return document.getElementById("function").value.split(":")[0];
};

Sandbox.prototype.getData = function() {
    var keys = document.querySelectorAll("[name='key']"),
        values = document.querySelectorAll("[name='value']"),
        data = {};
        
    for (var i = 0; i < keys.length; i++) {
        if (keys[i].value === "") continue;
        data[keys[i].value] = values[i].value;
    }
    
    return data;
};

Sandbox.prototype.getInfo = function() {
    return {
        requestType: this.getSendType(),
        url: this.getURL(),
        method: this.getMethodClass() + "->" + this.getMethod() + "()",
        data: this.getData()
    };
};