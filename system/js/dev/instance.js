"use strict"

Theamus.Instance = function() {
    this.DefineDefaultVariables();
    return;
};

Theamus.Instance.prototype = {
    constructor: Theamus.Instance
    
    , stop: function() {
        this.continue = false;
        return false;
    }
    
    , DefineDefaultVariables: function() {
        this.Feature    = "";
        this.Class      = "";
        this.Function   = "";
        this.Data       = {};
        
        this.Method = "post";
        this.URL    = "";
        this.Async  = true;
        
        this.continue   = true;
        
        this.ID = 0;
        
        return;
    }
    
    , CreateCompleteEvent: function(data) {
        if (typeof this.complete === "function") {
            this.complete(data);
        }
    }
    
    , CreateErrorEvent: function(data) {
        if (typeof this.error === "function") {
            this.error(data);
        }
    }
    
    , CreateSuccessEvent: function(data) {
        if (typeof this.success === "function") {
            this.success(data);
        }
    }
    
    , CheckVariable: function(variable) {
        if (typeof variable === "undefined") return this.stop();
        if (typeof variable !== "string") return this.stop();
        if (typeof variable === "") return this.stop();
        
        return true;
    }
    
    , GatherSendData: function() {
        var hash_data = document.getElementById("ajax-hash-data");
        
        if (hash_data === undefined || typeof hash_data !== "object") {
            console.error("Failed to find the AJAX hash information.");
            this.stop();
        }
        
        this.Data['ajax-hash-data']         = hash_data.value;
        this.Data['ajax']                   = "instance";
        this.Data['instance_feature']       = this.Feature;
        this.Data['instance_class']         = this.Class;
        this.Data['instance_function']      = this.Function;
        this.Data['instance_id']            = this.ID;
        this.Data['instance_delete']        = 0;
        this.Data['instance_delete_all']    = 0;
        this.Data['instance_reference']     = 0;
        this.Data['instance_reference_all'] = 0;
    }
    
    , CreateFormData: function() {
        if (this.Method === "post") {
            var form_data = new FormData();
            for (var key in this.Data) form_data.append(key, this.Data[key]);
            return form_data;
        } else if (this.Method === "get") {
            var url_data = [];
            for (var key in this.Data) url_data.push(encodeURIComponent(key) + "=" + encodeURIComponent(this.Data[key]));
            return url_data;
        }
    }
    
    , Send: function(custom_data) {
        if (!this.CheckVariable(this.URL)) {
            console.error("No URL is defined to make a call.");
            return this.stop();
        }
        
        if (!this.CheckVariable(this.Method)) {
            console.error("No call method is defined.");
            return this.stop();
        }
        
        if (typeof custom_data === undefined || typeof custom_data !== "boolean") {
            custom_data = false;
        }
        
        if (this.Async !== true || this.Async !== false) this.Async = true;
        
        if (!custom_data) this.GatherSendData();
        if (!this.continue) return;
        
        var xhr = new XMLHttpRequest(),
            inst = this;
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var data = {};

                    data.response = {headers: xhr.getAllResponseHeaders(), status: xhr.status};
                    data.success = {data: JSON.parse(xhr.responseText)};
                    
                    if ("error" in data.success.data && data.success.data.error.status === 1) {
                        data.error = data.success.data.error;
                    } else {                   
                        data.error = {status: 0, message: "", code: 0};
                    }
                } catch (e) {
                    var data = {},
                        error_message = "Data response error.",
                        warning = xhr.responseText.indexOf("<br />\n<b>Warning</b>:"),
                        notice = xhr.responseText.indexOf("<br />\n<b>Notice</b>:");
                    
                    if (warning > -1 || notice > -1) {
                        error_message = xhr.responseText.replace(/(<([^>]+)>)/ig, "").replace(/\n/ig, "").replace(/<br>/ig, "");
                        var first_curly = error_message.indexOf("{");
                        error_message = error_message.substr(0, first_curly);
                    }
                    
                    data.error = {status: 1, message: error_message, code: 1};
                    data.response = {headers: xhr.getAllResponseHeaders, status: xhr.status};
                    data.success = {data: xhr.responseText};
                }
                
                inst.CreateCompleteEvent(data);
                if (data.error.status === 1) inst.CreateErrorEvent(data.error);
                else inst.CreateSuccessEvent(data.success);
            }
        };
        
        if (this.Method === "get") this.URL = this.URL + "?" + this.CreateFormData().join("&");
        
        xhr.open(this.Method, this.URL, this.Async);
        if (this.Method === "post") xhr.send(this.CreateFormData());
        else xhr.send();
    }
    
    , Delete: function(id) {
        this.GatherSendData();
        this.Data['instance_delete'] = 1;
        this.Send(true);
    }
    
    , DeleteAll: function() {
        this.GatherSendData();
        this.Data['instance_delete_all'] = 1;
        this.Send(true);
    }
    
    , Reference: function(id) {
        this.GatherSendData();
        this.Data['instance_reference'] = 1;
        this.Send(true);
    }
    
    , ReferenceAll: function(id) {
        this.GatherSendData();
        this.Data['instance_reference_all'] = 1;
        this.Send(true);
    }
};