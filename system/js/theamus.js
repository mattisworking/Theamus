/*
 * classList.js: Cross-browser full element.classList implementation.
 * 2012-11-15
 *
 * By Eli Grey, http://eligrey.com
 * Public Domain.
 * NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.
 */
if(typeof document!=="undefined"&&!("classList"in document.documentElement)){(function(e){"use strict";if(!("HTMLElement"in e)&&!("Element"in e))return;var t="classList",n="prototype",r=(e.HTMLElement||e.Element)[n],i=Object,s=String[n].trim||function(){return this.replace(/^\s+|\s+$/g,"")},o=Array[n].indexOf||function(e){var t=0,n=this.length;for(;t<n;t++){if(t in this&&this[t]===e){return t}}return-1},u=function(e,t){this.name=e;this.code=DOMException[e];this.message=t},a=function(e,t){if(t===""){throw new u("SYNTAX_ERR","An invalid or illegal string was specified")}if(/\s/.test(t)){throw new u("INVALID_CHARACTER_ERR","String contains an invalid character")}return o.call(e,t)},f=function(e){var t=s.call(e.className),n=t?t.split(/\s+/):[],r=0,i=n.length;for(;r<i;r++){this.push(n[r])}this._updateClassName=function(){e.className=this.toString()}},l=f[n]=[],c=function(){return new f(this)};u[n]=Error[n];l.item=function(e){return this[e]||null};l.contains=function(e){e+="";return a(this,e)!==-1};l.add=function(){var e=arguments,t=0,n=e.length,r,i=false;do{r=e[t]+"";if(a(this,r)===-1){this.push(r);i=true}}while(++t<n);if(i){this._updateClassName()}};l.remove=function(){var e=arguments,t=0,n=e.length,r,i=false;do{r=e[t]+"";var s=a(this,r);if(s!==-1){this.splice(s,1);i=true}}while(++t<n);if(i){this._updateClassName()}};l.toggle=function(e,t){e+="";var n=this.contains(e),r=n?t!==true&&"remove":t!==false&&"add";if(r){this[r](e)}return!n};l.toString=function(){return this.join(" ")};if(i.defineProperty){var h={get:c,enumerable:true,configurable:true};try{i.defineProperty(r,t,h)}catch(p){if(p.number===-2146823252){h.enumerable=false;i.defineProperty(r,t,h)}}}else if(i[n].__defineGetter__){r.__defineGetter__(t,c)}})(self)}

// Theamus AJAX
var ajax=new function(){this.fail=false;this.has_file=false;this.hideable=false;this.allow_file_upload=true;this.run=function(e){this.allow_file_upload=true;var t,n;t=this.get_form_data(e);t=this.get_extra_fields(e,t);t=this.add_ajax_type(e,t);t=this.add_file_object(e,t);t=this.get_ajax_hash(t);t=this.get_location(t);n=this.sanitize_url(e);if(this.fail===false){$.ajax({type:"POST",url:n,data:t,processData:this.processData,contentType:this.contentType,xhr:function(){var t=new XMLHttpRequest;if(window.FormData!==undefined){t.upload.addEventListener("progress",function(t){ajax.show_upload(t,e)},false)}return t},success:function(t){ajax.hide_upload(e);ajax.show_results(e,t);ajax.run_after(e);ajax.run_return_functions();add_extras()},error:function(e,t,n){console.log("AJAX Error: "+n)}})}else{console.log("AJAX Setup Error: "+this.fail)}return false};this.get_form_data=function(e){var t,n,r,i;if("form"in e){t=$("#"+e.form);if(t.length>0){n=this.get_form_elements(e.form);r=this.get_element_values(n);i=this.make_form_data(r)}else{i=this.form_data()}}else{i=this.form_data()}return i};this.form_data=function(){if(window.FormData===undefined){this.processData=true;this.contentType="application/x-www-form-urlencoded";return{}}else{var e=new FormData;this.processData=false;this.contentType=false;return e}};this.form_data_append=function(e,t,n){if(window.FormData===undefined){e[t]=n}else{e.append(t,n)}return e};this.get_form_elements=function(e){var t=typeof e==="object"?$(e).find(":input"):$("#"+e+" :input");return t};this.reset_form_elements=function(e,t){var n=this.get_form_elements(e);t=t!==undefined?t:new Array;for(var r=0;r<n.length;r++){if(t.indexOf(n[r].name)===-1){if(n[r].type!=="button"&&n[r].type!=="submit"){n[r].value=""}}}};this.get_id_elements=function(e){var t=new Array;for(var n=0;n<e.length;n++){t.push($("#"+e[n])[0])}return t};this.get_element_values=function(e,t){var n,r,i;n=new Array;if(t===undefined||t===null)t=false;for(var s=0;s<e.length;s++){r=e[s];if(r===undefined||r===null){this.fail="Failed to gather an element.";break}if(r.type==="checkbox"){i=r.checked===true?"true":"false"}else if(r.type==="file"&&window.FormData!==undefined){if(r.files.length>0&&this.allow_file_upload===true){if(r.files.length>=1){i=r.files[0];this.has_file=true}else{i="false";this.has_file=false}}else{i="false";this.has_file=false}}else if(r.type==="radio"){var o=$("[name='"+r.name+"']");for(var u=0;u<o.length;u++){if(o[u].checked){i=o[u].value;break}}}else if(r.tagName.toLowerCase()==="select"){if(r.getAttribute("multiple")==="multiple"){var a,f;a=r.options;f=new Array;for(var l=0;l<a.length;l++){if(a[l].selected===true){f.push(a[l].value)}}i=f.join(",")}else{i=r.value}}else if(r.tagName.toLowerCase()==="div"){i=r.innerHTML}else{i=r.value}if(t===false){if(r.name===undefined){n[r.id]=i}else{n[r.name]=i}}else{n.push(i)}}return n};this.make_form_data=function(e,t){if(t===undefined||t===null){t=this.form_data()}var n;for(key in e){if(typeof e[key]==="object"){n=e[key]}else{n=encodeURIComponent(e[key])}t=this.form_data_append(t,key,n)}return t};this.get_extra_fields=function(e,t){var n,r,i;if("extra_fields"in e){n=e.extra_fields;if($.isArray(n)===false){n=new Array;n.push(e.extra_fields)}r=this.get_id_elements(n);i=this.get_element_values(r);t=this.make_form_data(i,t);return t}else{return t}};this.format_bytes=function(e){var t=Math.floor(Math.log(e)/Math.log(1024));return(e/Math.pow(1024,t)).toFixed(2)*1+" "+["B","kB","MB","GB","TB"][t]};this.get_ajax_hash=function(e){var t=false;if(e===undefined)t=true;var n=document.getElementById("ajax-hash-data");if(n===undefined){if(t===true)this.api_fail="Unable to make AJAX request.";else this.fail="Unable to make AJAX request."}else{if(t===true)return n.value;else{e=this.form_data_append(e,"ajax-hash-data",n.value);return e}}};this.get_location=function(e){if($(".admin").length===0||!$(".admin").hasClass("admin-panel-open")){e=this.form_data_append(e,"location","site")}else{e=this.form_data_append(e,"location","admin")}return e};this.sanitize_url=function(e){var t,n;if("url"in e){t=e.url.slice(-1)!=="/"?"":"";n=e.url+t;return n}else{this.fail="There is no URL to go to."}};this.add_ajax_type=function(e,t){var n="type"in e?e.type:"script";t=this.form_data_append(t,"ajax",n);return t};this.get_result_area=function(e){if("result"in e){if($("#"+e.result).length>0){return $("#"+e.result)}else{this.fail="The AJAX result div provided doesn't exist."}}else{this.fail="There is nowhere to put the AJAX results."}};this.show_upload=function(e,t){var n,r;var i="upload-progress";var s="upload-percentage";var o=5;var u=true;if("upload"in t){i="growbar"in t.upload?t.upload.growbar:i;s="percentage"in t.upload?t.upload.percentage:s;o="stop"in t.upload?t.upload.stop:o;u="show"in t.upload?t.upload.show:u}if($("#"+i).length<1)u=false;if($("#"+s).length<1)r=false;if(e.lengthComputable){if($("#"+i).length>0){if($("#"+i)[0].style.display!=="block"){$("#"+i).show()}}var a=ajax.get_upload_data(e);n=a.completed;if(u===true&&this.has_file!==false){$("#"+i).show();$("#"+i)[0].style.width=n*o+"px";if(r!==false)$("#"+s).html(n+"%");this.hideable=true;this.stop=o}return n}};this.get_upload_data=function(e){var t=0;if(e.lengthComputable){t=Math.floor(e.loaded/e.total*100)}var n={percent_completed:t,percentage:t+"%",loaded:e.loaded,loaded_formatted:this.format_bytes(e.loaded),total_bytes:e.totalSize,total_bytes_formatted:this.format_bytes(e.totalSize),time_micro:e.timeStamp,time_formatted:new Date(e.timeStamp)};return n};this.hide_upload=function(e){if(this.hideable===true){if("upload"in e){var t="hide"in e.upload?e.upload.hide:true;var n="hide_time"in e.upload?e.upload.hide_time:3;n=n*1e3;var r="growbar"in e.upload?e.upload.growbar:"upload-progress";var i="percentage"in e.upload?e.upload.percentage:"upload-percentage";$("#"+r)[0].style.width=100*this.stop+"px";$("#"+i).html("100%");if(t===true){setTimeout(function(){$("#"+r).hide()},n)}}}};this.run_after=function(e){var t,n;if("after"in e){if(typeof e.after==="function")e.after();else if("do_function"in e.after){t=e.after.do_function;if(typeof t==="string"){n="arguments"in e.after?e.after.arguments:"";window[t](n)}else{for(var r=0;r<t.length;r++){n="arguments"in e.after?e.after.arguments[r]:"";window[t[r]](n)}}}}};this.run_return_functions=function(){var e,t,n;var r=$("[name='run_after']");if(r.length>0){for(var i=0;i<r.length;i++){e=r[i].getAttribute("function");t=r[i].getAttribute("arguments");if(t!=="")t=$.parseJSON(t);window[e](t);r[i].remove()}}};this.show_results=function(e,t){var n=this.get_result_area(e);if(n!==undefined){n.show();n.html(t);if("hide_result"in e){setTimeout(function(){n.hide()},e.hide_result*1e3)}}else{console.log(this.fail)}};this.add_file_object=function(e,t){if("file_object"in e){t=this.form_data_append(t,"file",e.file_object);this.has_file=true}return t};this.api=function(e){api_return=false;this.allow_file_upload=true;this.api_fail=false;var t=this.check_api_args(e);t.url=this.sanitize_url(t);t.data=this.make_api_data(t);t.form_data=t.type==="post"?this.make_form_data(t.data):t.data;if(e.upload!==undefined&&e.upload.files!==undefined){t.form_data=this.form_data_append(t.form_data,"upload_file",e.upload.files[0]);this.has_file=true}if(t.type==="get"){this.processData=true;this.contentType="application/x-www-form-urlencoded; charset=UTF-8"}this.api_fail=this.api_fail_response();if(this.api_fail!==false){if(typeof t.success!=="function"){console.log("Theamus API error: "+this.api_fail.error.message)}else{t.success(this.api_fail)}}if(this.api_fail===false){$.ajax({async:this.has_file?true:false,type:t.type,url:t.url,data:t.form_data,processData:this.processData,contentType:this.contentType,xhr:function(){var t=new XMLHttpRequest;if(window.FormData!==undefined){t.upload.addEventListener("progress",function(t){if("upload"in e&&"during"in e.upload){if(typeof e.upload.during==="function"){e.upload.during(ajax.get_upload_data(t))}}},false)}return t},success:function(e,n,r){try{var e=JSON.parse(e);e.response.headers=r.getAllResponseHeaders();e.response.text=n;e.response.status=r.status}catch(i){var e={error:{status:1,message:"Failed to decode API return data."},response:{headers:r.getAllResponseHeaders,text:n,status:r.status,data:e}}}api_return=t.success(e)}})}return api_return};this.check_api_args=function(e){var t={ajax:"api",hash:this.get_ajax_hash()};if(typeof e!=="object"){this.api_fail="API arguments are not valid.";e={url:"",method:""};return e}if("type"in e&&typeof e.type==="string"){if(e.type!=="post"&&e.type!=="get"){this.api_fail="API request type must be 'post' or 'get'."}else{t.type=e.type.toLowerCase()}if(e.type==="get"){this.allow_file_upload=false}}else{this.api_fail="Invalid API request type."}if("url"in e&&typeof e.url==="string"){t.url=e.url}else{this.api_fail="Invalid API url."}if("method"in e){t.method_class="";if(typeof e.method==="string"){t.method=e.method}else if(typeof e.method==="object"){if(e.method.length>=1){t.method_class=e.method[0]}else{this.api_fail="Undefined API method."}if(e.method.length>=2){t.method=e.method[1]}else{this.api_fail="Undefined API method after finding class."}}else{this.api_fail="Invalid API method defined."}}else{this.api_fail="API method not defined."}if("data"in e){if("form"in e.data){if(typeof e.data.form==="object"){t.data_form=e.data.form}else{this.api_fail="Invalid API form selector."}}if("custom"in e.data){if(typeof e.data.custom==="object"){t.data_custom=e.data.custom}else{this.api_fail="Invalid API custom data type."}}if("elements"in e.data){if(typeof e.data.elements==="object"){t.data_elements=e.data.elements}else{this.api_fail="Invalid API elements data type."}}}if("success"in e){if(typeof e.success==="function"){t.success=e.success}else{this.api_fail="API success must be a function."}}else{this.api_fail="Undefined 'success' function to run."}return t};this.api_fail_response=function(){if(this.api_fail!==false){return{error:{status:1,message:this.api_fail},response:{status:0,data:"",text:""}}}return false};this.make_api_data=function(e){var t={method_class:e.method_class,method:e.method,ajax:e.ajax,"ajax-hash-data":e.hash,"api-from":"javascript"};var n=this.get_form_elements(e.data_form),r=this.get_element_values(n);for(var i in r)t[i]=r[i];for(var i in e.data_custom){if(t[i]!==undefined){this.api_fail="Multiple data key detected. Conflicted key = '"+i+"'.";break}else{t[i]=e.data_custom[i]}}if(e.data_elements!==undefined){for(var s=0;s<e.data_elements.length;s++){if(e.data_elements[s].length>1){this.api_fail="Multiple custom elements detected where there should be one.";break}else{var o=$(e.data_elements[s]),i="";if(o.attr("id")!==undefined){i=o.attr("id")}if(i===""&&o.attr("name")!==undefined){i=o.attr("name")}if(i===""){this.api_fail="Element has no identifiable name or id.";break}else if(t[i]!==undefined){this.api_fail="Multiple data key detected.  Conflicted key = '"+i+"'."}else{t[i]=this.get_element_values(e.data_elements[s],true)[0]}}}}return t};this.iterate_calls=function(e,t,n){var r=t,t=t!==undefined?parseInt(t)*1e3:0,n=n!==undefined?n:0,i=function(){};if(e===undefined){console.log("Iterate calls error: Functions not defined.");return}if(e[n]!==undefined){setTimeout(function(){i=typeof e[n]==="function"?e[n]:window[e[n]];if(i()!==false){ajax.iterate_calls(e,r,n+1)}},t)}return}}

// Theamus Main JS
function add_js_file(e){var t=e.split("?")[0];for(var n=0;n<$("script").length;n++){if($($("script")[n]).attr("src")===undefined)continue;var r=$($("script")[n]).attr("src").split("?")[0];if(r===t)$("script")[n].remove()}$("head").append("<script type='text/javascript' src='"+e+"'></script>")}function check_js_file(e){var t=new Array,n=e.split("?")[0];for(var r=0;r<$("script").length;r++){var i=$($("script")[r]);if(i.attr("src")===undefined)continue;var s=i.attr("src").split("?")[0];t.push(s===n?true:false)}return t.indexOf(true)===-1?true:false}function add_extras(){var e=$('[name="addscript"]');for(var t=0;t<e.length;t++){add_js_file(e[t].value);$(e[t]).remove()}var n=$("[name='addstyle']");for(var t=0;t<n.length;t++){add_css(n[t].value);$(n[t]).remove()}}function add_css(e){var t,n,r,i,s,o;t=$("base")[0].href;n=$("link");r=new Array;i=$("head")[0];s=e.split("?")[0];for(var u=0;u<n.length;u++){if(n[u].href!==""){r.push(n[u].href.split("?")[0])}}if(r.indexOf(t+e)===-1){if(r.indexOf(t+s)!==-1){var a=$("[href^='"+r[r.indexOf(t+s)].replace(t,"")+"']");for(var u=0;u<a.length;u++){$(a[u]).remove()}}$("head").append("<link rel='stylesheet' type='text/css' "+"href='"+e+"' />")}}function countdown(e,t,n){var r=document.getElementById("countdown");var i=document.getElementById("elipses");if(r.innerHTML===""){if(n===undefined||n===null){r.innerHTML=e+" "+t}else{r.innerHTML=e}}var t=t-1;timer=setInterval(function(){if(t>0){if(n===undefined||n===null){r.innerHTML=e+" "+t--}}else{clearInterval(countdownTimer);clearInterval(timer)}},1e3);countdownTimer=setInterval(function(){if(i.innerHTML===""){i.innerHTML="."}else if(i.innerHTML==="."){i.innerHTML=".."}else if(i.innerHTML===".."){i.innerHTML="..."}else if(i.innerHTML==="..."){i.innerHTML=""}},200)}function scroll_top(){window.scrollTo(0,0)}function admin_scroll_top(){$("#admin-content").animate({scrollTop:0},"slow")}function reload(e){if(e!==null&&e!==undefined){setTimeout(function(){window.location.reload()},e)}else{window.location.reload()}return false}function user_logout(){Theamus.Ajax.api({type:"post",url:Theamus.base_url+"/accounts/logout/",method:["Accounts","logout"],success:go_to});return false}function go_to(e){if(typeof e!=="string"){e=Theamus.base_url}window.location=e}function go_back(){window.history.back()}function switch_notify(e,t){var n=document.getElementById("notify");n.className=e;n.innerHTML=t;n.innerHTML+="<span id='countdown'></span>";n.innerHTML+="<span id='elipses'></span>"}function insert_at_caret(e,t){if(document.selection){var n;e.focus();sel=document.selection.createRange();n=sel.text.length;sel.text=t;if(t.length==0){sel.moveStart("character",t.length);sel.moveEnd("character",t.length)}else{sel.moveStart("character",-t.length+n)}sel.select()}else if(e.selectionStart||e.selectionStart=="0"){var r=e.selectionStart;var i=e.selectionEnd;e.value=e.value.substring(0,r)+t+e.value.substring(i,e.value.length);e.selectionStart=r+t.length;e.selectionEnd=r+t.length}else{e.value+=t}}function working(){var e=$("#admin-content").hasClass("admin_content-wrapper-open")?"admin-notifyinfo":"site-notifyinfo";var t=$("<div class='"+e+"'></div>");t.append("<img src='themes/default/img/loading.gif' height='16px' align='left' />");t.append("<span style='margin-left:20px'>Working...</span>");return t}function notify(e,t,n){return"<div class='"+e+"-notify"+t+"'>"+n+"</div>"}function alert_notify(e,t){var n={success:"ion-checkmark-round",danger:"ion-close",warning:"ion-alert",info:"ion-information",spinner:"spinner spinner-fixed-size"};return"<div class='alert alert-"+e+"'><span class='glyphicon "+n[e]+"'></span>"+t+"</div>"}Theamus={Ajax:ajax,base_url:function(){var e=document.getElementsByTagName("base")[0].href;if(e.slice(-1)==="/")return e.slice(0,-1);else return e}(),Browser:function(){var e=navigator.userAgent,t,n=e.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*([\d\.]+)/i)||[];if(/trident/i.test(n[1])){t=/\brv[ :]+(\d+(\.\d+)?)/g.exec(e)||[];return"IE "+(t[1]||"")}n=n[2]?[n[1],n[2]]:[navigator.appName,navigator.appVersion,"-?"];if((t=e.match(/version\/([\.\d]+)/i))!==null)n[2]=t[1];return n.join(" ")}(),Mobile:function(){var e=false;(function(t){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(t)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(t.substr(0,4)))e=true})(navigator.userAgent||navigator.vendor||window.opera);return e}(),Tablet:function(){return/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(navigator.userAgent.toLowerCase())}(),Notify:function(e,t){var n={success:"ion-checkmark-round",danger:"ion-close",warning:"ion-alert",info:"ion-information",spinner:"spinner spinner-fixed-size"};return"<div class='alert alert-"+e+"'><span class='glyphicon "+n[e]+"'></span>"+t+"</div>"}}

// Theamus Theme
document.addEventListener('DOMContentLoaded', function(){$("#nav-response-btn").click(function(e){e.preventDefault();$($(this).data("open")).toggle()})})

