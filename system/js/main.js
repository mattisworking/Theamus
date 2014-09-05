Theamus = {
    Ajax: ajax,
    base_url: (function() {
        var url = document.getElementsByTagName("base")[0].href;
        if (url.slice(-1) === '/') return url.slice(0, -1);
        else return url;
    })(),
    Browser: (function() {
        var ua= navigator.userAgent, tem,
        M = ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*([\d\.]+)/i) || [];
        if (/trident/i.test(M[1])) {
            tem =  /\brv[ :]+(\d+(\.\d+)?)/g.exec(ua) || [];
            return 'IE '+(tem[1] || '');
        }
        M = M[2]? [M[1], M[2]]:[navigator.appName, navigator.appVersion, '-?'];
        if ((tem = ua.match(/version\/([\.\d]+)/i)) !== null) M[2]= tem[1];
        return M.join(' ');
    })(),
    Mobile: (function() {
        var check = false;
        (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);
        return check;
    })(),
    Tablet: (function() {
        return (/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(navigator.userAgent.toLowerCase()));
    })(),
    Notify: function(e, t) {
        var n = {
            success: "ion-checkmark-round",
            danger: "ion-close",
            warning: "ion-alert",
            info: "ion-information",
            spinner: "spinner spinner-fixed-size"
        };
        return "<div class='alert alert-" + e + "'><span class='glyphicon " + n[e] + "'></span>" + t + "</div>";
    }
};

function add_js_file(source) {
    // Define the source without the time variable
    var new_source = source.split("?")[0];

    // Loop through all of the scripts in the header
    for (var i = 0; i < $("script").length; i++) {
        // Ignore any undefined scripts
        if ($($("script")[i]).attr("src") === undefined) continue;

        // Define the script source for the loop and check it against the desired
        var script_source = $($("script")[i]).attr("src").split("?")[0];
        if (script_source === new_source) ($("script")[i]).remove();
    }

    // Add the new script source to the header
    $("head").append("<script type='text/javascript' src='"+source+"'></script>");
}

function check_js_file(source) {
    // Define the temp variable and source without the time variable
    var temp = new Array(),
        new_source = source.split("?")[0];

    // Loop through all of the scripts in the header
    for (var i = 0; i < $("script").length; i++) {
        // Define the script element and check to see if it has a valid source
        var script = $($("script")[i]);
        if (script.attr("src") === undefined) continue;

        // Define the script source, before the time variable and check to see if it exists already
        var script_src = script.attr("src").split("?")[0];
        temp.push(script_src === new_source ? true : false);
    }

    // Check the temp array for the scripts existance in the header
    return temp.indexOf(true) === -1 ? true : false;
}

function add_extras() {
    // Get all scripts to be added
    var scripts = $('[name="addscript"]');

    // Loop through all of the scripts
    for (var i = 0; i < scripts.length; i++) {
        add_js_file(scripts[i].value);
        $(scripts[i]).remove();
    }

    var styles = $("[name='addstyle']");

    for (var i = 0; i < styles.length; i++) {
        add_css(styles[i].value);
        $(styles[i]).remove();
    }
}

function add_css(source) {
    var base, styles, sources, head, check_source, remove;

    base = $("base")[0].href;
    styles = $("link");
    sources = new Array;
    head = $("head")[0];
    check_source = source.split("?")[0];

    for (var i = 0; i < styles.length; i++) {
        if (styles[i].href !== "") {
            sources.push(styles[i].href.split("?")[0]);
        }
    }

    if (sources.indexOf(base+source) === -1) {
        if (sources.indexOf(base+check_source) !== -1) {
            var remove_links = $("[href^='"+sources[sources.indexOf(base + check_source)].replace(base, "")+"']");
            for (var i = 0; i < remove_links.length; i++) {
                $(remove_links[i]).remove();
            }
        }

        $("head").append("<link rel='stylesheet' type='text/css' "+"href='"+source+"' />");
    }
}

function countdown(text, time, showTime) {
    // Define the countdown element
    var ele = document.getElementById("countdown");
    var elipses = document.getElementById("elipses");

    // Set the text/value of the element
    if (ele.innerHTML === "") {
        if (showTime === undefined || showTime === null) {
            ele.innerHTML = text + " " + time;
        } else {
            ele.innerHTML = text;
        }
    }

    // Define the new time
    var time = time - 1;

    // Count down the time
    timer = setInterval(function() {
        // Check for time left
        if (time > 0) {
            if (showTime === undefined || showTime === null) {
                // Update the countdown text
                ele.innerHTML = text + " " + time--;
            }
        } else {
            // Stop counting down and blinking
            clearInterval(countdownTimer);
            clearInterval(timer);
        }
    }, 1000);

    // Blink elipses
    countdownTimer = setInterval(function() {
        // Scrolling elipses
        if (elipses.innerHTML === "") {
            elipses.innerHTML = ".";
        } else if (elipses.innerHTML === ".") {
            elipses.innerHTML = "..";
        } else if (elipses.innerHTML === "..") {
            elipses.innerHTML = "...";
        } else if (elipses.innerHTML === "...") {
            elipses.innerHTML = "";
        }
    }, 200);
}

function scroll_top() {
    window.scrollTo(0,0);
}

function admin_scroll_top() {
    $("#admin-content").animate({
        scrollTop: 0
    }, "slow");
}

function reload(timer) {
    if (timer !== null && timer !== undefined) {
        setTimeout(function() {
            window.location.reload();
        }, timer);
    } else {
        window.location.reload();
    }

    return false;
}

function user_logout() {
    Theamus.Ajax.api({
        type:       'post',
        url:        Theamus.base_url+'/accounts/logout/',
        method:     ['Accounts', 'logout'],
        success:    go_to
    });

    return false;
}

function go_to(loc) {
    if (typeof loc !== "string") {
        loc = Theamus.base_url;
    }

    window.location = loc;
}

function go_back() {
    window.history.back();
}

function switch_notify(newClass, text) {
    var notify = document.getElementById("notify");
    notify.className = newClass;
    notify.innerHTML = text;
    notify.innerHTML += "<span id='countdown'></span>";
    notify.innerHTML += "<span id='elipses'></span>";
}

function insert_at_caret(ele, val) {
    //IE support
    if (document.selection) {
        var temp;
        ele.focus();
        sel = document.selection.createRange();
        temp = sel.text.length;
        sel.text = val;
        if (val.length == 0) {
            sel.moveStart('character', val.length);
            sel.moveEnd('character', val.length);
        } else {
            sel.moveStart('character', -val.length + temp);
        }
        sel.select();
    } else if (ele.selectionStart || ele.selectionStart == '0') {
        var startPos = ele.selectionStart;
        var endPos = ele.selectionEnd;
        ele.value = ele.value.substring(0, startPos) + val + ele.value.substring(endPos, ele.value.length);
        ele.selectionStart = startPos + val.length;
        ele.selectionEnd = startPos + val.length;
    } else {
        ele.value += val;
    }
}

function working() {
    var notify_class = $("#admin-content").hasClass("admin_content-wrapper-open") ? "admin-notifyinfo" : "site-notifyinfo";
    var div = $("<div class='"+notify_class+"'></div>");
    div.append("<img src='themes/default/img/loading.gif' height='16px' align='left' />");
    div.append("<span style='margin-left:20px'>Working...</span>");
    return div;
}

function notify(location, type, message) {
    return "<div class='"+location+"-notify"+type+"'>"+message+"</div>";
}

function alert_notify(type, message) {
    var glyph = {
        "success": "ion-checkmark-round",
        "danger": "ion-close",
        "warning": "ion-alert",
        "info": "ion-information",
        "spinner": "spinner spinner-fixed-size"
    };
    return "<div class='alert alert-"+type+"'><span class='glyphicon "+glyph[type]+"'></span>"+message+"</div>";
}