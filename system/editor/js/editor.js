var editor = new function() {
    this.executables = function() {
        this.exec = {
            bold: {"do": function() { editor.document_exec({"do":"bold"}); }},
            underline: {"do": function() { editor.document_exec({"do":"underline"});}},
            italicize: {"do": function() { editor.document_exec({"do":"italic"});}},
            unorderedlist: {"do": function() { editor.document_exec({"do":"insertunorderedlist"});}},
            orderedlist: {"do": function() { editor.document_exec({"do":"insertorderedlist"});}},
            indent: {"do": function() { editor.document_exec({"do":"indent"});}},
            outdent: {"do": function() { editor.document_exec({"do":"outdent"});}},
            alignleft: {"do": function() { editor.document_exec({"do":"justifyLeft"});}},
            aligncenter: {"do": function() { editor.document_exec({"do":"justifyCenter"});}},
            alignright: {"do": function() { editor.document_exec({"do":"justifyRight"});}},
            alignfull: {"do": function() { editor.document_exec({"do":"justifyFull"});}},
            fontsize: {"do": function() { editor.change_font_size();}},
            togglecode: {"do": function() { editor.toggle_code();}},
            addcodeblock: {"do": function() { editor.add_code_block();}},
            addlink: {"do": function() { editor.add_link_window();}},
            addimage: {"do": function() { editor.add_image_window();}},
            showmore: {"do": function() { editor.toggle_sink();}}
        };
    };

    this.set_options = function(argv) {
        if (!argv) argv = {};
        editor.el_id = "id" in argv ? argv.id : "editor";
        editor.el = document.getElementById(editor.el_id);
        editor.width = "width" in argv && argv.width !== "auto" ? argv.width : "500px";
        editor.code_id = editor.el_id+"-code";
        editor.code_el = document.getElementById(editor.code_id);
        editor.wrapper_id = "editor_wrapper";
        editor.wrapper = document.getElementById(editor.wrapper_id);
        editor.breadcrumb_id = "editor_breadcrumbs";
        editor.breadcrumb_el = document.getElementById(editor.breadcrumb_id);
        editor.window_wrapper_id = "editor_window-wrapper";
        editor.window_wrapper = document.getElementById(editor.window_wrapper_id);
        editor.link_info_wrapper_id = "editor_link-info-wrapper";
        editor.link_info_wrapper = document.getElementById(editor.link_info_wrapper_id);
        editor.sink_id = "editor_sink-wrapper";
        editor.sink = document.getElementById(editor.sink_id);
        editor.codemirror = false;
        if (typeof CodeMirror !== "undefined") {
            if (CodeMirror.xml_loaded === undefined) {
                var xml_script = "system/editor/js/codemirror/mode/xml/xml.js?x="+editor.load_time,
                    js_script = "system/editor/js/codemirror/mode/javascript/javascript.js?x="+editor.load_time,
                    css_script = "system/editor/js/codemirror/mode/css/css.js?x="+editor.load_time,
                    html_script = "system/editor/js/codemirror/mode/htmlmixed/htmlmixed.js?x="+editor.load_time;

                if (check_js_file(xml_script) === true) add_js_file(xml_script);
                if (check_js_file(js_script) === true) add_js_file(js_script);
                if (check_js_file(css_script) === true) add_js_file(css_script);
                if (check_js_file(html_script) === true) add_js_file(html_script);

                add_css("system/editor/css/codemirror.css?x="+editor.load_time);
            }
        }
    };

    this.initialize = function(argv) {
        // Define the load time, if it isn't defined already
        if (editor.load_time === undefined) editor.load_time = new Date().getTime();

        var test = editor.test_load();
        editor.set_options(argv);
        if (!test) {
            setTimeout(function() { editor.initialize(argv); }, 500);
        } else {
            if (navigator.appVersion.indexOf("MSIE 8.0") === -1) {
                editor.load_listeners();
                editor.set_listeners();
            } else editor.fallback();
        }
    };

    this.fallback = function() {
        var fb = document.createElement("textarea");
        fb.setAttribute("id", editor.el_id);
        fb.setAttribute("class", "editor_fancy-input");
        fb.style.width = editor.width;

        editor.wrapper.parentNode.replaceChild(fb, editor.wrapper);
    };

    this.test_load = function() {
        var ret = [],
            codemirror_script = "system/editor/js/codemirror/codemirror.js?x="+editor.load_time;
        if (check_js_file(codemirror_script) === false) {
            if (typeof CodeMirror === "undefined") ret.push(false);
            if (CodeMirror.xml_loaded === undefined) ret.push(false);
            else ret.push(true);
        } else {
            add_js_file(codemirror_script);
            ret.push(false);
        }
        if (editor.el_id !== "undefined") ret.push(true);
        else ret.push(false);

        if (ret.indexOf(false) === -1) return true;
        else return false;
    };

    this.parents = function(el, stop_el) {
        var parents = [], p;
        if (el && el.element) p = el.element;
        else p = el ? el.parentNode : el;
        while (p !== null && p !== undefined) {
            if (p === stop_el) break;
            parents.push(p);
            p = p.parentNode;
        }
        return parents;
    };

    this.dataset = function(el, arg, set) {
        var data = "data-"+arg;
        if (set) el.setAttribute(data, set);
        else {
            if (el.getAttribute(data) !== undefined) return el.getAttribute(data);
            else return "";
        }
    };

    this.toggle_disable = function(id) {
        var element = document.getElementById(id);
        if (element.getAttribute("disabled") === null) element.setAttribute("disabled", "disabled");
        else element.removeAttribute("disabled");
    };

    this.br2nl = function(string) {
        return string.replace(/<br>/g, "\r");
    };

    this.nl2br = function(string) {
        return string.replace(/(\r\n|\n\r|\r|\n)/g, "<br/>");
    };

    this.load_listeners = function() {
        this.listeners = [];
        this.executables();
        this.execute_listeners();
        this.load_code();
    };

    this.set_listeners = function() {
        this.toggle_editable();
        this.plain_paste();
        this.get_breadcrumbs();
        this.modify_return();
        this.modify_tab();
        this.add_tail();
        this.update_code();
        this.link_information();
        this.edit_img_listeners();
    };

    this.center_window = function(el) {
        var x = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) / 2,
            y = (window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight) / 2,
            elx = el.offsetWidth / 2,
            ely = el.offsetHeight / 2;

        el.style.position = "fixed";
        el.style.left = (x - elx) + "px";
        el.style.top = (y - ely) + "px";
    };

    this.editor_interact = function(argv) {
        if (!argv) argv = {};
        argv.element = "element" in argv ? argv.element : editor.el;
        argv.action = "action" in argv ? argv.action : "click";
        argv.on = "on" in argv ? argv.on : function(){};
        argv.off = "off" in argv ? argv.off : function(){};
        argv['do'] = "do" in argv ? argv['do'] : function() {};

        if (argv.action instanceof Array) {
            for (var i = 0; i < argv.action.length; i++) {
                this.add_event_listener({
                    element: argv.element,
                    action: argv.action[i],
                    on: argv.on,
                    off: argv.off,
                    "do": argv['do']
                });
            }
        } else this.add_event_listener(argv);
    };

    this.add_event_listener = function(argv) {
        if (argv.element !== undefined) {
            if (argv.element.addEventListener) {
                argv.element.addEventListener(argv.action, function(e) {
                    var parents = editor.parents(e.srcElement);
                    argv['do'](e);
                }, false);
            } else {
                if (argv.action === "click") argv.action = "onclick";
                if (argv.action === "keyup") argv.action = "onkeyup";
                argv.element.attachEvent(argv.action, function(e) {
                    argv['do'](e);
                });
            }
        }
    };

    this.insert_at_caret = function(node) {
        editor.el.focus();
        rangy.deserializeSelection(editor.range);
        var range = rangy.getSelection().getRangeAt(0);
        range.insertNode(node);
    };

    this.with_selection = function(argv) {
        if (!argv) argv = {};
        argv['do'] = "do" in argv ? argv['do'] : function(){};
        argv.fallback = "fallback" in argv ? argv.fallback : function(){};
        var sel, range;

        rsel = rangy.getSelection();
        sel = rsel.isCollapsed === true ? false : rsel;
        argv['do'](sel);
    };

    this.toggle_editable = function() {
        this.editor_interact({
            action: "click",
            "do": function(e) {
                    var target = e.target || e.srcElement;
                    if (target.tagName === "SELECT") return false;
                    if (target.tagName === "TEXTAREA") return false;
                    editor.el.setAttribute("contenteditable", true);
                    document.body.style.overflowX = "hidden";
                    editor.el.focus();
                }
        });
    };

    this.get_breadcrumbs = function() {
        this.editor_interact({
            action: ["keyup", "click"],
            "do": function(e) {
                var node = rangy.getSelection().focusNode,
                    parent = editor.parents(node, editor.wrapper),
                    ret_name = [],
                    ret_ele = [];
                for (var i = 0; i < parent.length; i++) {
                    if (parent[i].tagName === "FORM") break;
                    if (parent[i].tagName) {
                        ret_ele.push(parent[i]);
                        ret_name.push(parent[i].tagName.toLowerCase());
                    }
                }

                editor.breadcrumbs = ret_name;
                editor.show_breadcrumbs(ret_name);
                editor.set_current_formats(ret_name, ret_ele);
            }
        });
    };

    this.modify_tab = function() {
        this.editor_interact({
            action: "keydown",
            "do": function(e) {
                if (e.keyCode === 9) {
                    e.preventDefault();
                    if (window.getSelection) {
                        var sel = window.getSelection();
                        if (sel.getRangeAt && sel.rangeCount) var range = sel.getRangeAt(0);
                    }

                    var node = document.createTextNode("\u00A0\u00A0\u00A0\u00A0");
                    range.insertNode(node);
                    range.collapse(false);

                    var sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            }
        });
    };

    this.modify_return = function() {
        this.editor_interact({
            action: "keydown",
            "do": function(e) {
                var last_item = editor.breadcrumbs ? editor.breadcrumbs.reverse()[0] : "",
                    except = ["li", "ol", "ul"];
                if (e.which === 13 && except.indexOf(last_item) === -1 && document.activeElement !== editor.code_el) {
                    if (e.preventDefault) e.preventDefault();
                    else e.returnValue = false;
                    editor.with_selection({
                        "do": function(sel) {
                            var sel = rangy.getSelection();
                            var range = sel.getRangeAt(0),
                                br = document.createElement("br");
                            range.deleteContents();
                            range.insertNode(br);
                            range.setStartAfter(br);
                            range.setEndAfter(br);
                            range.collapse(false);
                            sel.removeAllRanges();
                            sel.addRange(range);
                        }
                    });
                    return false;
                }
            }
        });
    };

    this.execute_listeners = function() {
        var ops = document.querySelectorAll(".editor_executable");
        for (var i = 0; i < ops.length; i++) {
            if (!editor.dataset(ops[i], "click"))
                editor.add_event_listener({element: ops[i], action: "click", "do": function(e) { editor.do_execution(e, this); }});
            editor.add_event_listener({element: ops[i], action: "change", "do": function(e) { editor.do_execution(e, this); }});
        }
    };

    this.do_execution = function(e, el) {
        if (e.preventDefault) e.preventDefault();
        else e.returnValue = false;
        var el_exec = editor.dataset(el.element, "exec"),
            ex = el_exec in editor.exec ? editor.exec[el_exec] : {};
        ex['do'] = "do" in ex ? ex['do'] : function(){};
        editor.temp_el = el.element;
        ex['do']();
        delete editor.temp_el;
        editor.get_breadcrumbs();
    };

    this.show_breadcrumbs = function(argv) {
        var bc = [],
            a = argv.reverse();
        a[0] = "Editor";
        for (var i = 0; i < argv.length; i++) bc.push(a[i]);
        this.breadcrumb_el.innerHTML = bc.join(" / ");
        return bc;
    };

    this.document_exec = function(argv) {
        if (!argv) argv = {};
        argv['do'] = "do" in argv ? argv['do'] : "";
        argv.options = "options" in argv ? argv.options : "";
		document.execCommand(argv['do'], false, argv.options);
    };

    this.add_tail = function() {
        this.editor_interact({
            action: ["keyup", "click"],
            "do": function() {
                if (editor.el.innerHTML.slice(-1) != " ") {
                    editor.el.appendChild(document.createTextNode(" "));
                }
            }
        });
    };

    this.change_font_size = function() {
        this.with_selection({
            "do": function(sel) {
                if (sel.rangeCount) {
                    var container = document.createElement("div");
                    for (var i = 0, len = sel.rangeCount; i < len; ++i) {
                        container.appendChild(sel.getRangeAt(i).cloneContents());
                    }
                    var old_html = container.innerHTML.replace(/<(?!br\s*\/?)[^>]+>/g, "");
                }

                var parent = sel.getRangeAt(0).commonAncestorContainer;

                if (parent === $(".editor_format-options")[0]) {
                    return;
                }

                if (parent.nodeType !== 1 && parent.parentNode !== editor.el) {
                    parent.parentNode.parentNode.removeChild(parent.parentNode);
                }

                var range = sel.getRangeAt(0);
                range.deleteContents();
                if (editor.temp_el.value !== "normal") {
                    var new_fontsize = document.createElement("h"+editor.temp_el.value);
                    new_fontsize.setAttribute("class", "body-header");
                    new_fontsize.innerHTML = old_html;
                    if (old_html !== "") {
                        range.insertNode(new_fontsize);
                    }
                } else {
                    node = range.createContextualFragment(old_html);
                    range.insertNode(node);
                }
            }
        });
    };

    this.plain_paste = function() {
        editor.editor_interact({
            action: "paste",
            "do": function(e) {
                e.preventDefault();

                var sel = rangy.getSelection(),                                           // User's current selection of text
                    text = (e.originalEvent || e).clipboardData.getData('text/plain'),    // Text from the clip board
                    span = document.createElement("span"),                                // Text element
                    range = sel.getRangeAt(0);                                            // Selection range

                // Define the contents of the text element
                span.innerHTML = editor.nl2br(text.replace(/\ /g, "&nbsp;").replace(/(<([^>]+)>)/ig,""));

                // Replace the contents of the selection/where the caret is
                sel.getRangeAt(0).deleteContents();
                node = range.createContextualFragment(span.innerHTML);
                sel.getRangeAt(0).insertNode(node);
            }
        })
    };

    this.decode_html = function(s, way) {
        var html = ["&", "<", ">", "\"", " "],
            code = ["&amp;", "&lt;", "&gt;", "&quot;", "&nbsp;"],
            from = [],
            to = [];

        if (way === "decode") from = code, to = html;
        else return s;

        for (var i = 0; i < html.length; i++) {
            var r = new RegExp(from[i], "g");
            s = s.replace(r, to[i]);
        }
        return s;
    };

    this.update_code = function() {
        this.editor_interact({
            action: ["keyup", "click"],
            "do": function() {
                if (editor.codemirror !== false) {
                    editor.codemirror.setValue(editor.decode_html(editor.br2nl(editor.el.innerHTML), "decode"));
                }
            }
        });

        this.editor_interact({
            element: editor.codemirror_el,
            action: ["keyup", "click"],
            "do": function() {
                editor.el.innerHTML = editor.decode_html(editor.codemirror.getValue(), "encode");
            }
        });
    };

    this.load_code = function() {
        if (editor.el) editor.code_el.value = editor.br2nl(editor.el.innerHTML);
    };

    this.toggle_code = function() {
        if (editor.codemirror === false) {
            editor.codemirror = CodeMirror.fromTextArea(editor.code_el, {
                mode: "htmlmixed",
                lineNumbers: true,
                indentUnit: 4,
                extraKeys: {
                    Tab: function(cm) {
                        var spaces = Array(cm.getOption("indentUnit") + 1).join("\u00A0");
                        cm.replaceSelection(spaces);
                        cm.setCursor(cm.getCursor());
                    }
                },
                enableCodeFormatting: true
            });
            editor.codemirror_el = editor.codemirror.getWrapperElement();
            if (editor.codemirror_el)
                editor.codemirror_el.classList.add("editor_code-input");
        }

        if (editor.codemirror_el.classList.contains("editor_code-open")) {
            editor.add_current(document.querySelectorAll(".editor_togglecode")[0], true);
            editor.codemirror_el.classList.remove("editor_code-open");
            editor.codemirror_el.style.display = "none";
            editor.el.classList.remove("editor_fancy-closed");
            editor.el.innerHTML = editor.decode_html(editor.codemirror.getValue().replace(/\    /g, "").replace(/\  /g, "&nbsp;").replace(/<br>\n/g, "<br>"), "encode");
        } else {
            editor.add_current(document.querySelectorAll(".editor_togglecode")[0]);
            editor.codemirror_el.classList.add("editor_code-open");
            editor.codemirror_el.style.display = "";
            editor.el.classList.add("editor_fancy-closed");
            editor.codemirror.setValue(editor.el.innerHTML.replace(/&nbsp;/g, " ").replace(/<br\/>/g, "<br/>\n"));
            var totalLines = editor.codemirror.lineCount();
            var totalChars = editor.codemirror.getTextArea().value.length;

            editor.codemirror.autoFormatRange({line:0, ch:0}, {line:totalLines, ch:totalChars});

            editor.codemirror.setCursor(0, 0);
        }
    };

    this.add_code_block = function() {
        this.with_selection({
            "do": function(sel) {
                var code = document.createElement("code"),
                    range = rangy.getSelection().getRangeAt(0);
                code.classList.add("prettyprint");
                code.innerHTML = "<br>";
                range.deleteContents();
                range.insertNode(code);
                code.focus();
            }
        });
    };

    this.open_window = function(argv) {
        if (!argv) argv = {};
        argv.id = "id" in argv ? argv.id : "";
        argv.header = "header" in argv ? argv.header : "";
        argv.path = "path" in argv ? argv.path : "";
        argv.after = "after" in argv ? argv.after : function(){};

        if (!document.getElementById(argv.id)) this.create_window(argv);
    };

    this.create_window = function(argv) {
        if (!argv) argv = {};
        argv.id = "id" in argv ? argv.id : "some-window";
        argv.header = "header" in argv ? argv.header : "Some Header";
        argv.path = "path" in argv ? argv.path : "";
        argv.before = "before" in argv ? argv.before : function(){};
        argv.after = "after" in argv ? argv.after : function(){};
        argv.buttons = "buttons" in argv ? argv.buttons : [];

        if (!document.getElementById(argv.id) && argv.id !== "") {
            el = document.createElement("div");
            el.setAttribute("id", argv.id);
            el.setAttribute("class", "editor_window window-open");
            el.style.visibility = "hidden";

            var header_el = document.createElement("div");
            header_el.setAttribute("class", "editor_window-header");
            header_el.innerHTML = argv.header;

            var content_el = document.createElement("div");
            content_el.setAttribute("id", "editor_wc-"+argv.id);
            content_el.setAttribute("class", "editor_window-content");

            var buttons_el = document.createElement("div");
            buttons_el.setAttribute("class", "editor_window-submit");
            buttons_el.innerHTML = argv.buttons.join("");

            argv.before();
            el.appendChild(header_el);
            el.appendChild(content_el);
            el.appendChild(buttons_el);
            this.window_wrapper.appendChild(el);

            Theamus.Ajax.run({
                url:    argv.path,
                result: "editor_wc-"+argv.id,
                type:   "system",
                after: function() {
                    editor.set_window_height(el, content_el);
                    editor.center_window(el);
                    argv.after();
                    document.body.style.overflow = "hidden";
                    el.style.visibility = "";
                }
            });
        }
    };

    this.close_window = function(el) {
        var parents = this.parents(el, this.wrapper);
        for (var i = 0; i < parents.length; i++) {
            if (parents[i].classList.contains("editor_window")) var open = parents[i];
        }
        document.body.style.overflow = "";
        if (open) this.window_wrapper.removeChild(open);
    };

    this.set_window_height = function(el, content_el, last) {
        if (last === undefined || last === null) last = 0;
        if (el) {
            var new_height = $(content_el).height();
            if (new_height !== last) {
                el.style.height = (120 + new_height)+"px";
                content_el.style.top = "55px";

                $(".editor_window-submit").css("top", (55 + new_height));
                setTimeout(function() {
                    editor.set_window_height(el, content_el, new_height);
                }, 200);
            }
        } else {
            setTimeout(function() { editor.set_window_height(); }, 200);
        }
    };

    this.add_link_window = function(argv) {
        if (document.querySelectorAll(".editor_link")[0].getAttribute("disabled")) return;
        rangy.init();
        editor.with_selection({
            "do": function(sel) {
                var string = !sel ? "" : sel.toString(),
                    params = ["ltext="+string];
                editor.create_window({
                    id:     "new-link",
                    header: "Add a Link",
                    path:   "editor/windows/link/?"+params.join("&"),
                    before: editor.before_window,
                    after:  editor.after_link_window,
                    buttons: [
                        "<input type='button' id='create_link' value='OK' />",
                        "<input type='button' name='cancel' id='close_window' value='Close' />"
                    ]
                });
            }
        });
    };

    this.create_link_element = function(el) {
        if (editor.allow_new_link === false) {
            editor.close_window(el);
            return false;
        }

        var url = document.getElementById('weburl').value;
        if (editor.serialized !== false) rangy.deserializeSelection(editor.selection);
        editor.with_selection({
            "do": function(sel) {
                rangy.init();
                var text = document.getElementById("link-text").value,
                    node = document.createTextNode(text);

                if (!sel) {
                    editor.insert_at_caret(node);
                } else {
                    var  range = sel.getRangeAt(0);
                    sel.getRangeAt(0).deleteContents();
                    sel.getRangeAt(0).insertNode(node);
                }

                var r2 = rangy.createRange();
                r2.selectNodeContents(node);
                var s2 = rangy.getSelection();
                s2.setSingleRange(r2);
            }
        });

        editor.document_exec({"do": "unlink"});
        editor.document_exec({"do": "CreateLink", options: url});
        editor.close_window(el);
        editor.create_link_information();
    };

    this.link_information = function() {
        this.editor_interact({
            action: ["mouseover", "click"],
            "do": this.create_link_information
        });
    };

    this.create_link_information = function() {
        var a = editor.el.getElementsByTagName("a");
        for (var i = 0; i < a.length; i++) {
            var link = a[i],
                id = link.getAttribute("id"),
                new_id = Math.floor((Math.random() * 99999));
            if (!id) {
                link.setAttribute("id", new_id);
                var new_info = document.createElement("div");
                new_info.setAttribute("class", "editor_link-info");
                new_info.setAttribute("data-id", new_id);
                new_info.innerHTML = "<a target='_blank' href='"+link.href+"'>"+link.href+"</a>";
                new_info.innerHTML += "<a href='#' name='edit_link' data-id='"+new_id+"'>Edit</a>";
                new_info.innerHTML += "<a href='#' name='remove_link' data-id='"+new_id+"'>Remove</a>";
                editor.link_info_wrapper.appendChild(new_info);
                editor.on_info = true;

                editor.add_event_listener({
                    element: link,
                    action: "mouseover",
                    "do": function(e) {
                        var info = document.querySelectorAll(".editor_link-info");
                        for (var o = 0; o < info.length; o++) {
                            if (editor.dataset(info[o], "id") === this.element.getAttribute("id")) {
                                var current_link = this.element;
                                info[o].style.position = "absolute";
                                info[o].style.top = (editor.el.offsetTop + current_link.offsetTop + 20)+"px";
                                info[o].style.left = (editor.el.offsetLeft + current_link.offsetLeft - 25)+"px";
                                info[o].classList.add("editor_link-info-open");
                                editor.add_remove_listener();
                                editor.add_edit_listener();
                            }
                        }
                    }
                });

                editor.add_event_listener({
                    element: link,
                    action: "mouseout",
                    "do": function() {
                        var info = document.querySelectorAll(".editor_link-info");
                        for (var o = 0; o < info.length; o++) {
                            if (editor.dataset(info[o], "id") === this.element.getAttribute("id")) {
                                info[o].classList.remove("editor_link-info-open");
                            }
                        }
                    }
                });
            }
        }
    };

    this.add_remove_listener = function() {
        var e_link = document.getElementsByName("remove_link");
        for (var i = 0; i < e_link.length; i++) {
            if (editor.listeners.indexOf(e_link[i]) === -1) {
                editor.add_event_listener({
                    element: e_link[i],
                    action: "click",
                    "do": function(e) {
                        var el = this.element;
                        editor.listeners.push(el);
                        if (e.preventDefault) e.preventDefault();
                        else e.returnValue = false;
                        var link = document.getElementById(editor.dataset(el, "id"));
                        var range = document.createRange();
                        range.selectNodeContents(link);
                        var sel = rangy.getSelection();
                        sel.setSingleRange(range);

                        editor.document_exec({"do": "unlink"});
                        editor.link_info_wrapper.removeChild(el.parentNode);
                    }
                });
            }
        }
    };

    this.add_edit_listener = function() {
        var e_link = document.getElementsByName("edit_link");
        for (var i = 0; i < e_link.length; i++) {
            if (editor.listeners.indexOf(e_link[i]) === -1) {
                editor.add_event_listener({
                    element: e_link[i],
                    action: "click",
                    "do": function(e) {
                        var el = this.element;
                        editor.listeners.push(el);
                        if (e.preventDefault) e.preventDefault();
                        else e.returnValue = false;
                        var range = rangy.createRange();
                        range.selectNodeContents(document.getElementById(editor.dataset(el, "id")));
                        var sel = rangy.getSelection();
                        sel.setSingleRange(range);

                        editor.with_selection({
                            "do": function(sel) {
                                var p = sel.getRangeAt(0).commonAncestorContainer,
                                    e = p.href ? p : p.parentNode,
                                    params = ["ltext="+e.innerHTML,"lurl="+e.href];
                                editor.create_window({
                                    id:     "edit-link",
                                    header: "Edit Link",
                                    path:   "editor/windows/link?"+params.join("&"),
                                    before: editor.before_window,
                                    after:  editor.after_link_window,
                                    buttons: [
                                        "<input type='button' id='create_link' value='OK' />",
                                        "<input type='button' name='cancel' id='close_window' value='Close' />"
                                    ]
                                });
                            }
                        });
                    }
                });
            }
        }
    };

    this.before_window = function() {
        editor.with_selection({
            "do": function(sel) {
                if (sel !== false) {
                    var range = sel.getRangeAt(0);
                    editor.selection = rangy.serializeSelection(rangy.getSelection(), true);
                    editor.serialized = true;
                } else {
                    var range = rangy.getSelection().getRangeAt(0);
                    editor.range = rangy.serializeSelection(rangy.getSelection(), true);
                    editor.serialized = false;
                    editor.allow_new_link = true;
                }

                var parentElement = range.commonAncestorContainer;
                if (editor.parents(parentElement).indexOf(editor.el) === -1
                        && parentElement !== editor.el) editor.allow_new_link = false;
                else editor.allow_new_link = true;
            }
        });
    };

    this.after_link_window = function() {
        var type_el = document.getElementsByName("link_type");
        for (var i = 0; i < type_el.length; i++) {
            var t = type_el[i];
            editor.add_event_listener({
                element: t,
                action: "click",
                "do": function(e) {
                    if (e.preventDefault) e.preventDefault();
                    else e.returnValue = false;
                    document.getElementById("website-"+current).style.display = "none";
                    document.getElementById("website-"+editor.dataset(this, "type")).style.display = "";
                }
            });
        }

        editor.add_event_listener({
            element: document.getElementById("create_link"),
            action: "click",
            "do" :function(e) {
                if (e.preventDefault) e.preventDefault();
                else e.returnValue = false;
                editor.create_link_element(this.element);
            }
        });

        editor.add_event_listener({
            element: document.getElementById("close_window"),
            action: "click",
            "do" :function(e) {
                editor.close_window(this.element);
            }
        });
    };

    this.add_img_tabs = function() {
        var image_tabs = "<div class='image-window-tabs'><a href='#' name='image_type' data-for='url'>Add Image from URL</a><a href='#' name='image_type' data-for='lib'>Add Image from Library</a></div>";
        $("#editor_wc-add-image").prepend(image_tabs);

        for (var i = 0; i < $("[name='image_type']").length; i++) {
            editor.add_event_listener({
                element: $("[name='image_type']")[i],
                action: "click",
                "do": function(e) {
                    e.preventDefault();
                    var data = editor.dataset(this.element, "for"),
                        url = "";
                    if (data === "url") url = "editor/windows/add-image-link/";
                    if (data === "lib") url = "editor/windows/add-image/";

                    $("#editor_wc-add-image").html(working());
                    Theamus.Ajax.run({
                        url:    url,
                        result: "editor_wc-add-image",
                        type:   "system",
                        after:  function() {
                            editor.add_img_tabs();
                            editor.set_window_height($(".editor_window")[0], $("#editor_wc-add-image")[0]);
                            editor.center_window($(".editor_window")[0]);
                            editor.add_img_listeners();
                        }
                    });
                }
            });
        }
    };

    this.add_image_window = function() {
        editor.create_window({
            id:     "add-image",
            header: "Add an Image",
            path:   "editor/windows/add-image",
            before: editor.before_window,
            after: function() {
                editor.center_after_imgload();
                editor.add_img_listeners();
                editor.add_img_tabs();

                editor.add_event_listener({
                    element: document.getElementById("close_window"),
                    action: "click",
                    "do" :function(e) {
                        editor.close_window(this.element);
                    }
                });
            },
            buttons: [
                "<input type='button' name='cancel' id='close_window' value='Close' />"
            ]
        });
    };

    this.center_after_imgload = function() {
        var img_window = document.getElementById("add-image");
        if (editor.check_imgload) editor.center_window(img_window);
        else setTimeout(editor.center_after_imgload, 200);
    };

    this.check_imgload = function() {
        var img_window = document.getElementById("add-image");
        if (!img_window) return true;
        var imgs = img_window.getElementsByTagName("img"),
            ret = [];
        for (var i = 0; i < imgs.length; i++) ret.push(imgs[i].complete);
        if (ret.indexOf(false) === -1) return true;
        else setTimeout(editor.check_imgload, 200);
    };

    this.images_next_page = function(page) {
        Theamus.Ajax.run({
            url: "editor/windows/add-image&page=" + page,
            result: "editor_window-content-add-image",
            type: "system",
            after: editor.add_img_listeners
        });
        return false;
    };

    this.add_img_listeners = function() {
        var a = document.getElementsByName("add-image");
        for (var i = 0; i < a.length; i++) {
            editor.add_event_listener({
                element: a[i],
                action: "click",
                "do" :function(e) {
                    if (e.preventDefault) e.preventDefault();
                    else e.returnValue = false;

                    if (editor.allow_new_link === false) editor.close_window(this);
                    editor.create_img(this);
                    editor.close_window(this.element);
                }
            });
        }
    };

    this.create_img = function(link) {
        var width = document.getElementById("width"),
            height = document.getElementById("height"),
            align = document.getElementById("alignment"),
            path = document.getElementById("image_path"),
            type = document.getElementById("image_type"),
            fail = false,
            src_attribute = "src";
        if (editor.serialized === true) rangy.deserializeSelection(editor.selection);
        editor.with_selection({
            "do": function(sel) {
                if (link !== undefined && link.element !== undefined) {
                    if (editor.dataset(link.element, "type") !== null) {
                        var media_type = editor.dataset(link.element, "type"),
                            node = document.createElement(media_type === "image" ? "img" : media_type),
                            src_attribute = media_type === "object" ? "data" : "src";
                    } else {
                        fail = true;
                    }

                    if (editor.dataset(link.element, "path") !== null) {
                        node.setAttribute(src_attribute, editor.dataset(link.element, "path"));
                    } else fail = true;
                } else {
                    node = document.createElement(type.value);
                    src_attribute = type.value !== "IMG" ? "data" : "src";
                    if (path !== null) node.setAttribute(src_attribute, path.value);
                    else fail = true;
                }
                if (fail === false) {
                    if (width && height && align) {
                        node.style.height = height.value+"px";
                        node.style.width = width.value+"px";
                        node.setAttribute("align", align.value);
                    } else {
                        node.style.height = "auto";
                        node.style.maxWidth = "200px";
                    }

                    if (!sel) editor.insert_at_caret(node);
                    else {
                        var range = sel.getRangeAt(0);
                        sel.getRangeAt(0).deleteContents();
                        sel.getRangeAt(0).insertNode(node);
                    }

                    var r2 = rangy.createRange();
                    r2.selectNodeContents(node);
                    var s2 = rangy.getSelection();
                    s2.setSingleRange(r2);
                }
            }
        });
    };

    this.edit_img_listeners = function() {
        editor.editor_interact({
            action: ["mouseover", "click"],
            "do": function() {
                var imgs = editor.el.getElementsByTagName("img"),
                    objects = editor.el.getElementsByTagName("object"),
                    media_nodes = imgs.length === 0 ? objects : imgs;

                for (var i = 0; i < media_nodes.length; i++) {
                    var node = media_nodes[i];
                    if (editor.listeners.indexOf(node) === -1) {
                        editor.listeners.push(node);
                        node.addEventListener("click", function() {
                            var src = this.getAttribute("src").split("/"),
                                path = src.indexOf("http:") === -1 && src.indexOf("https:") === -1 ? src[src.length - 3]+"/"+src[src.length - 2]+"/"+src[src.length - 1] : this.getAttribute("src"),
                                params = [
                                    "path="+path.replace(/:/g, "{t:colon:}").replace(/\//g, "{t:bslash:}").replace(/\./g, "{t:period:}"),
                                    "width="+this.offsetWidth,
                                    "height="+this.offsetHeight,
                                    "align="+this.getAttribute("align"),
                                    "type="+this.tagName
                                ];

                            var range = rangy.createRange();
                            range.selectNode(this);
                            var sel = rangy.getSelection();
                            sel.setSingleRange(range);

                            editor.image_size_ratio = Math.round((parseInt(this.offsetHeight) / parseInt(this.offsetWidth)) * 100) / 100;

                            editor.create_window({
                                id:     "add-image",
                                header: "Edit Image",
                                path:   "editor/windows/edit-image&"+params.join("&"),
                                before: editor.before_window,
                                after: function() {
                                    editor.center_after_imgload();

                                    editor.add_event_listener({
                                        element: document.getElementsByName("update-image")[0],
                                        action: "click",
                                        "do": function() {
                                            editor.create_img();
                                            editor.close_window(this);
                                        }
                                    });

                                    document.getElementById("height").addEventListener("keyup", function() {
                                        editor.constrain_image_demensions();
                                    });

                                    document.getElementById("constrain").addEventListener("change", function() {
                                        editor.toggle_disable("width");
                                    });

                                    document.getElementById("close_window").addEventListener("click", function() {
                                        editor.close_window(this);
                                    });
                                },
                                buttons: [
                                    "<input type='button' name='update-image' value='Update Image' data-path='"+this.getAttribute("src")+"' />",
                                    "<input type='button' name='cancel' id='close_window' value='Close' />"
                                ]
                            });
                        });
                    }
                }
            }
        });
    };

    this.constrain_image_demensions = function() {
        if (document.getElementById("constrain").checked) {
            var height = document.getElementById("height").value,
                width = Math.round((height * 100) / (this.image_size_ratio * 100));
            document.getElementById("width").value = width;
        }
    };

    this.add_current = function(el, remove) {
        if (!remove) remove = false;
        if (remove === false && !el.classList.contains("editor_current")) el.classList.add("editor_current");
        else if (remove === true) el.classList.remove("editor_current");
    };

    this.toggle_sink = function() {
        var sink_link = document.querySelectorAll(".editor_sink")[0];

        if (editor.sink.classList.contains("editor_sink-open")) {
            editor.sink.classList.remove("editor_sink-open");
            editor.add_current(sink_link, true);
        } else {
            editor.sink.classList.add("editor_sink-open");
            editor.add_current(sink_link);
        }
    };

    this.set_current_formats = function(argv, arge) {
        var execs = document.querySelectorAll(".editor_executable"),
            legend = {
                bold: "b",
                underline: "u",
                italicize: "i",
                orderedlist: "ol",
                unorderedlist: "ul",
                addlink: "a",
                addcodeblock: "code"
            };

        for (var i = 0; i < execs.length; i++) {
            var exec = execs[i],
                select_exec = document.getElementById("editor_font-size"),
                align = "";

            if (exec === document.querySelectorAll(".editor_sink")[0]) continue;

            if (argv.indexOf(legend[editor.dataset(execs[i], "exec")]) !== -1) editor.add_current(exec);
            else editor.add_current(execs[i], true);

            if (argv.indexOf("a") > -1 && exec.tagName !== "SELECT") exec.setAttribute("disabled", true);
            else exec.removeAttribute("disabled", true);
        }

        select_exec.value = "normal";
        for (var e = 0; e < arge.length; e++) {
            if (arge[e].tagName.toLowerCase() === "span") {
                if (arge[e].style.fontSize) {
                    select_exec.value = parseInt(arge[e].style.fontSize);
                }
            }
            if (arge[e].tagName.toLowerCase() === "div") {
                if (arge[e].style.textAlign) align = arge[e].style.textAlign;
            }
        }
        var just_link = document.querySelectorAll(".editor_justify-"+align);
        if (just_link.length > 0) editor.add_current(just_link[0]);
    };
};
