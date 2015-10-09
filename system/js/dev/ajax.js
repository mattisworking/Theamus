var ajax = new function() {
    this.fail = false;
    this.has_file = false;
    this.hideable = false;
    this.allow_file_upload = true;

    this.run = function(info) {
        this.allow_file_upload = true;
        var form_data, url;

        form_data = this.get_form_data(info);
        form_data = this.get_extra_fields(info, form_data);
        form_data = this.add_ajax_type(info, form_data);
        form_data = this.add_file_object(info, form_data);
        form_data = this.get_ajax_hash(form_data);
        form_data = this.get_location(form_data);

        url = this.sanitize_url(info);

        if (this.fail === false) {
            $.ajax({
                type: 'POST',
                url: url,
                data: form_data,
                processData: this.processData,
                contentType: this.contentType,
                xhr: function() {
                    var xhr = new XMLHttpRequest();


                    if (window.FormData !== undefined) {
                        xhr.upload.addEventListener("progress", function(event) {
                            ajax.show_upload(event, info);
                        }, false);
                    }

                    return xhr;
                },
                success: function (return_data) {
                    ajax.hide_upload(info);
                    ajax.show_results(info, return_data);
                    ajax.run_after(info);
                    ajax.run_return_functions();
                    add_extras();
                },
                error: function (xhr, status, error) {
                    ajax.run_on_fail(info, xhr.status, error);
                }
            });
        } else {
            console.log("AJAX Setup Error: " + this.fail);
        }

        return false;
    };

    this.get_form_data = function(info) {
        var form, form_elements, element_values, form_data;

        if ("form" in info) {
            form = $('#' + info.form);
            if (form.length > 0) {
                form_elements = this.get_form_elements(info.form);
                element_values = this.get_element_values(form_elements);
                form_data = this.make_form_data(element_values);
            } else {
                form_data = this.form_data();
            }
        } else {
            form_data = this.form_data();
        }

        return form_data;
    };

    this.form_data = function() {
        if(window.FormData === undefined) {
            this.processData = true;
            this.contentType = 'application/x-www-form-urlencoded';
            return {};
        } else {
            var formdata = new FormData();
            this.processData = false;
            this.contentType = false;
            return formdata;
        }
    };

    this.form_data_append = function(parent, key, value) {
        if(window.FormData === undefined) {
            parent[key] = value;
        } else {
            parent.append(key, value);
        }
        return parent;
    };

    this.get_form_elements = function(form_id) {
        var elements = typeof form_id === "object" ? $(form_id).find(":input") : $("#" + form_id + " :input");
        return elements;
    };

    this.reset_form_elements = function(form_id, exceptions) {
        var elements = this.get_form_elements(form_id);
        exceptions = exceptions !== undefined ? exceptions : new Array();

        for (var i = 0; i < elements.length; i++) {
            if (exceptions.indexOf(elements[i].name) === -1) {
                if (elements[i].type !== "button" && elements[i].type !== "submit") {
                    elements[i].value = "";
                }
            }
        }
    };

    this.get_id_elements = function(element_ids) {
        var elements = new Array();
        for (var i = 0; i < element_ids.length; i++) {
            elements.push($("#" + element_ids[i])[0]);
        }

        return elements;
    };

    this.get_element_values = function(elements, values_only) {
        var elements_values, element, value;
        elements_values = new Array();

        if (values_only === undefined || values_only === null) values_only = false;

        for (var i = 0; i < elements.length; i++) {
            element = elements[i];
            if (element === undefined || element === null) {
                this.fail = "Failed to gather an element.";
                break;
            }

            if (element.type === "checkbox") {
                value = element.checked === true ? "true" : "false";

            } else if (element.type === "file" && window.FormData !== undefined) {
                if (element.files.length > 0 && this.allow_file_upload === true) {
                    if (element.files.length >= 1) {
                        value = element.files[0];
                        this.has_file = true;
                    } else {
                        value = "false";
                        this.has_file = false;
                    }
                } else {
                    value = "false";
                    this.has_file = false;
                }

            } else if (element.type === "radio") {
                var radio = $("[name='" + element.name + "']");
                for ( var r = 0; r < radio.length; r++) {
                    if (radio[r].checked) {
                        value = radio[r].value;
                        break;
                    }
                }

            } else if (element.tagName.toLowerCase() === "select") {
                if (element.getAttribute("multiple") === "multiple") {
                    var options, option_values;
                    options = element.options;
                    option_values = new Array;

                    for (var o = 0; o < options.length; o++) {
                        if (options[o].selected === true) {
                            option_values.push(options[o].value);
                        }
                    }
                    value = option_values.join(",");
                } else {
                    value = element.value;
                }

            } else if (element.tagName.toLowerCase() === "div") {
                value = element.innerHTML;
            } else {
                value = element.value;
            }

            if (values_only === false) {

                if (element.name === undefined) {
                    elements_values[element.id] = value;
                } else {
                    elements_values[element.name] = value;
                }
            } else {
                elements_values.push(value);
            }
        }

        return elements_values;
    };

    this.make_form_data = function(elements, form_data) {
        if (form_data === undefined || form_data === null) {
            form_data = this.form_data();
        }
        var value;

        for (key in elements) {
            if (typeof elements[key] === 'object') {
                value = elements[key];
            } else {
                value = encodeURIComponent(elements[key]);
            }
            form_data = this.form_data_append(form_data, key, value);
        }

        return form_data;
    };

    this.get_extra_fields = function(info, form_data) {
        var fields, elements, values;

        if ("extra_fields" in info) {
            fields = info.extra_fields;

            if ($.isArray(fields) === false) {
                fields = new Array();
                fields.push(info.extra_fields);
            }

            elements = this.get_id_elements(fields);
            values = this.get_element_values(elements);
            form_data = this.make_form_data(values, form_data);

            return form_data;
        } else {
            return form_data;
        }
    };

    this.format_bytes = function(bytes) {
        var i = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
    };

    this.get_ajax_hash = function(form_data) {
        var api = false;
        if (form_data === undefined) api = true;

        var hash_data = document.getElementById("ajax-hash-data");

        if (hash_data === undefined) {
            if (api === true) this.api_fail = "Unable to make AJAX request.";

            else this.fail = "Unable to make AJAX request.";
        } else {
            if (api === true) return hash_data.value;
            else {
                form_data = this.form_data_append(form_data, "ajax-hash-data", hash_data.value);
                return form_data;
            }
        }
    };

    this.get_location = function(form_data) {
        if ($('.admin').length === 0 || !$('.admin').hasClass("admin-panel-open")) {
            form_data = this.form_data_append(form_data, "location", "site");
        } else {
            form_data = this.form_data_append(form_data, "location", "admin");
        }
        return form_data;
    };

    this.sanitize_url = function(info) {
        var trailing, url;

        if ("url" in info) {
            trailing = info.url.slice(-1) !== "/" ? "" : "";
            url = info.url + trailing;

            return url;
        } else {
            this.fail = "There is no URL to go to.";
        }
    };

    this.add_ajax_type = function(info, form_data) {
        var type = "type" in info ? info.type : "script";
        form_data = this.form_data_append(form_data, "ajax", type);
        return form_data;
    };

    this.get_result_area = function(info) {
        if ("result" in info) {
            if (typeof(info.result) === "string") {
                if ($("#" + info.result).length > 0) {
                    return $("#" + info.result);
                } else {
                    this.fail = "The AJAX result div provided doesn't exist.";
                }
            } else {
                return $(info.result);
            }
        } else {
            this.fail = "There is nowhere to put the AJAX results.";
        }
    };

    this.show_upload = function(event, info) {
        var percent_completed, show_number;
        var percent_growbar = "upload-progress";
        var percent_number = "upload-percentage";
        var stop = 5;
        var show = true;

        if ("upload" in info) {
            percent_growbar = "growbar" in info.upload ? info.upload.growbar : percent_growbar;
            percent_number = "percentage" in info.upload ? info.upload.percentage : percent_number;
            stop = "stop" in info.upload ? info.upload.stop : stop;
            show = "show" in info.upload ? info.upload.show : show;
        }

        if ($("#" + percent_growbar).length < 1) show = false;
        if ($("#" + percent_number).length < 1) show_number = false;

        if (event.lengthComputable) {
            if ($("#" + percent_growbar).length > 0) {
                if ($("#" + percent_growbar)[0].style.display !== "block") {
                    $("#" + percent_growbar).show();
                }
            }

            var upload_data = ajax.get_upload_data(event);
            percent_completed = upload_data.completed;
            if (show === true && this.has_file !== false) {
                $("#" + percent_growbar).show();
                $("#" + percent_growbar)[0].style.width = percent_completed * stop + "px";
                if (show_number !== false) $("#" + percent_number).html(percent_completed + "%");
                this.hideable = true;
                this.stop = stop;
            }

            return percent_completed;
        }
    };

    this.get_upload_data = function(event) {
        var percent_completed = 0;

        if (event.lengthComputable) {
            percent_completed = Math.floor((event.loaded / event.total) * 100);
        }

        var ret = {
            percent_completed:      percent_completed,
            percentage:             percent_completed+"%",
            loaded:                 event.loaded,
            loaded_formatted:       this.format_bytes(event.loaded),
            total_bytes:            event.total,
            total_bytes_formatted:  this.format_bytes(event.total),
            time_micro:             event.timeStamp,
            time_formatted:         new Date(event.timeStamp)
        }

        return ret;
    };

    this.hide_upload = function(info) {
        if (this.hideable === true) {
            if ("upload" in info) {
                var hide = "hide" in info.upload ? info.upload.hide : true;
                var hide_time = "hide_time" in info.upload ? info.upload.hide_time : 3;
                hide_time = hide_time * 1000;
                var percent_growbar = "growbar" in info.upload ? info.upload.growbar : "upload-progress";
                var percent_number = "percentage" in info.upload ? info.upload.percentage : "upload-percentage";

                $("#" + percent_growbar)[0].style.width = (100 * this.stop) + "px";
                $("#" + percent_number).html("100%");

                if (hide === true) {
                    setTimeout(function() {
                        $("#" + percent_growbar).hide();
                    }, hide_time);
                }
            }
        }
    };

    this.run_after = function(info) {
        var do_function, arguments;

        if ("after" in info) {
            if (typeof info.after === "function") info.after();
            else if ("do_function" in info.after) {
                do_function = info.after.do_function;
                if (typeof do_function === 'string') {
                    arguments = "arguments" in info.after ? info.after.arguments : "";
                    window[do_function](arguments);
                } else {
                    for (var i=0; i< do_function.length; i++) {
                        arguments = "arguments" in info.after ? info.after.arguments[i] : "";
                        window[do_function[i]](arguments);
                    }
                }
            }
        }
    };
    
    this.run_on_fail = function(info, status, error) {
        if ("fail" in info) {
            if (typeof info.fail === "function") info.fail(status, error);
            else return false;
        }
    }

    this.run_return_functions = function() {
        var do_function, arguments, info;
        var after_elements = $("[name='run_after']");

        if (after_elements.length > 0) {
            for (var i = 0; i < after_elements.length; i++) {
                do_function = after_elements[i].getAttribute("function");
                arguments = after_elements[i].getAttribute("arguments");
                if (arguments !== "") arguments = $.parseJSON(arguments);

                window[do_function](arguments);
                after_elements[i].remove();
            }
        }
    };

    this.show_results = function(info, data) {
        var result = this.get_result_area(info);
        if (result !== undefined) {
            result.show();
            result.html(data);

            if ("hide_result" in info) {
                setTimeout(function() {
                    result.hide();
                }, (info.hide_result * 1000));
            }
        } else {
            console.log(this.fail);
        }
    };

    this.add_file_object = function(info, form_data) {
        if ("file_object" in info) {
            form_data = this.form_data_append(form_data, "file", info.file_object);
            this.has_file = true;
        }

        return form_data;
	};


    // Theamus AJAX API --------------------------------------------------------
    this.api = function(args) {
        if (!this.iterating) this.iterating = false;
        
        api_return = false;

        this.allow_file_upload = true;
        this.api_fail = false; // By default, we are good to go

        // Check the arguments and for a failure
        var api_vars = this.check_api_args(args);
        api_vars.url = this.sanitize_url(api_vars);

        // Set up the type data (GET or POST)
        api_vars.data = this.make_api_data(api_vars);
        api_vars.form_data = api_vars.type === "post" ? this.make_form_data(api_vars.data) : api_vars.data;

        // Add file objects
        if (args.upload !== undefined && args.upload.files !== undefined) {
            api_vars.form_data = this.form_data_append(api_vars.form_data, "upload_file", args.upload.files[0]);
            this.has_file = true;
        }

        // Define the processData and contentType for GET requests
        if (api_vars.type === "get") {
            this.processData = true;
            this.contentType = "application/x-www-form-urlencoded; charset=UTF-8";
        }

        // Define the api_fail json response
        this.api_fail = this.api_fail_response();

        // Check for any errors
        if (this.api_fail !== false) {
            // Show error in the console if the given success variable isn't valid
            if (typeof api_vars.success !== "function") {
                console.log("Theamus API error: "+this.api_fail.error.message);
            } else {
                // Run the success function, with the failure data
                api_vars.success(this.api_fail);
            }
        }

        // Run the AJAX to call the API
        if (this.api_fail === false) {
            $.ajax({
                async: this.iterating ? false : true,
                type: api_vars.type,
                url: api_vars.url,
                data: api_vars.form_data,
                processData: this.processData,
                contentType: this.contentType,
                xhr: function() {
                    var xhr = new XMLHttpRequest();

                    // Listen to the upload progress, if applicable
                    if (window.FormData !== undefined) {
                        xhr.upload.addEventListener("progress", function(event) {
                            // Return data to the "upload.during()" function, if applicable
                            if ("upload" in args && "during" in args.upload) {
                                // The upload.during variable must be a function to proceed
                                if (typeof args.upload.during === "function") {
                                    args.upload.during(ajax.get_upload_data(event));
                                }
                            }
                        }, false);
                    }

                    return xhr;
                },
                success: function (data, text, xhr) {
                    // Try to decode the returned data, or do nothing
                    try {
                        var data = JSON.parse(data);

                        data.response.headers = xhr.getAllResponseHeaders();
                        data.response.text = text;
                        data.response.status = xhr.status;
                    } catch (e) {
                        var data = {
                            error: {
                                status: 1,
                                message: 'Failed to decode API return data.'
                            },
                            response: {
                                headers: xhr.getAllResponseHeaders,
                                text: text,
                                status: xhr.status,
                                data: data
                            }
                        };
                    }

                    // Run the defined success function with the data
                    api_return = api_vars.success(data);
                }
            });
        }

        return api_return;
    };


    this.check_api_args = function(args) {
        // Define the defaults
        var ret = {ajax: "api", "hash": this.get_ajax_hash()};

        // Check for args
        if (typeof args !== "object") {
            // Fail, define default arguments and return
            this.api_fail = "API arguments are not valid.";
            args = {url: "", method: ""};
            return args;
        }

        // Check the type
        if ("type" in args && typeof args.type === "string") {
            // Check the type for 'post' or 'get', as they are the only types allowed
            if (args.type !== "post" && args.type !== "get") {
                this.api_fail = "API request type must be 'post' or 'get'.";
            } else {
                ret.type = args.type.toLowerCase();
            }

            // If the type is get, we can't allow file uploads
            if (args.type === "get") {
                this.allow_file_upload = false;
            }
        } else {
            this.api_fail = "Invalid API request type.";
        }

        // Check the URL
        if ("url" in args && typeof args.url === "string") {
            ret.url = args.url;
        } else {
            this.api_fail = "Invalid API url.";
        }

        // Check the method
        if ("method" in args) {
            ret.method_class = "";
            if (typeof args.method === "string") {
                ret.method = args.method;
            } else if (typeof args.method === "object") {
                // Define the method class
                if (args.method.length >= 1) {
                    ret.method_class = args.method[0];
                } else {
                    this.api_fail = "Undefined API method.";
                }

                // Define the method
                if (args.method.length >= 2) {
                    ret.method = args.method[1];
                } else {
                    this.api_fail = "Undefined API method after finding class.";
                }
            } else {
                this.api_fail = "Invalid API method defined.";
            }
        } else if (ret.type !== "get") {
            this.api_fail = "API method not defined.";
        }

        // Check the data
        if ("data" in args) {
            // Define the form information
            if ("form" in args.data) {
                // The form must be defined already, as an object containing the element
                if (typeof args.data.form === "object") {
                    ret.data_form = args.data.form;
                } else {
                    this.api_fail = "Invalid API form selector.";
                }
            }

            // Define the custom information
            if ("custom" in args.data) {
                // The custom information must be a JSON object
                if (typeof args.data.custom === "object") {
                    ret.data_custom = args.data.custom;
                } else {
                    this.api_fail = "Invalid API custom data type.";
                }
            }

            // Define any elements
            if ("elements" in args.data) {
                // The elements must be an array of objects that define the elements holding values
                if (typeof args.data.elements === "object") {
                    ret.data_elements = args.data.elements;
                } else {
                    this.api_fail = "Invalid API elements data type.";
                }
            }
        }

        // Check the success function
        if ("success" in args) {
            if (typeof args.success === "function") {
                ret.success = args.success;
            } else {
                this.api_fail = "API success must be a function.";
            }
        } else {
            this.api_fail = "Undefined 'success' function to run.";
        }

        // Return the argument data
        return ret;
    };


    this.api_fail_response = function() {
        // Check for a failure during the API setup
        if (this.api_fail !== false) {
            // Define and the return data
            return {
                error: {status: 1, message: this.api_fail},
                response: {status: 0, data: "", text: ""}
            };
        }
        return false;
    };


    this.make_api_data = function(args) {
        // Define the defaults
        var data = {
            method_class: args.method_class,
            method: args.method,
            ajax: args.ajax,
            "ajax-hash-data": args.hash,
            "api-from": "javascript"
        };

        // Define the form elements/objects
        var form_elements = this.get_form_elements(args.data_form),
            form_values = this.get_element_values(form_elements);
        for (var key in form_values) data[key] = form_values[key];

        // Define the custom data
        for (var key in args.data_custom) {
            if (data[key] !== undefined) {
                this.api_fail = "Multiple data key detected. Conflicted key = '"+key+"'.";
                break;
            } else {
                data[key] = args.data_custom[key];
            }
        }

        // Define the elements
        if (args.data_elements !== undefined) {
            for (var i = 0; i < args.data_elements.length; i++) {
                if (args.data_elements[i].length > 1) {
                    this.api_fail = "Multiple custom elements detected where there should be one.";
                    break;
                } else {
                    var element = $(args.data_elements[i]),
                        key = "";

                    // Define the key based on the ID
                    if (element.attr("id") !== undefined) {
                        key = element.attr("id");
                    }

                    // Define the key based on the element name, if the key isn't alredy defined
                    if (key === "" && element.attr("name") !== undefined) {
                        key = element.attr("name");
                    }

                    // If there is no key after the two tries above, fail
                    if (key === "") {
                        this.api_fail = "Element has no identifiable name or id.";
                        break;

                    // If the key already exists in the data array, fail
                    } else if (data[key] !== undefined) {
                        this.api_fail = "Multiple data key detected.  Conflicted key = '"+key+"'.";

                    // Get the value of the key and define it in the data array
                    } else {
                        data[key] = this.get_element_values(args.data_elements[i], true)[0];
                    }
                }
            }
        }

        return data; // Return the data
	};

    this.iterate_calls = function(functions, pause, count) {
        this.iterating = true;
        
        // Define variables
        var original_pause = pause,
            pause = pause !== undefined ? parseFloat(pause)*1000 : 0,
            count = count !== undefined ? count : 0,
            func = function(){};

        // Check and error out for undefined functions
        if (functions === undefined) {
            console.log("Iterate calls error: Functions not defined.");
            return;
        }

        // Make sure the function exists before attempting to run
        if (functions[count] !== undefined) {
            // Set a timeout (optional variable)
            setTimeout(function() {
                // Define the function from a string/function array value
                func = typeof(functions[count]) === "function" ? functions[count] : window[functions[count]];

                // Run the function and run on to the next function if this one worked
                if (func() !== false) {
                    ajax.iterate_calls(functions, original_pause, (count + 1));
                }
            }, pause);
        }

        return; // Return to end
    };
};