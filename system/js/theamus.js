/*
 * classList.js: Cross-browser full element.classList implementation.
 * 2012-11-15
 *
 * By Eli Grey, http://eligrey.com
 * Public Domain.
 * NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.
 */
if(typeof document!=="undefined"&&!("classList"in document.documentElement)){(function(e){"use strict";if(!("HTMLElement"in e)&&!("Element"in e))return;var t="classList",n="prototype",r=(e.HTMLElement||e.Element)[n],i=Object,s=String[n].trim||function(){return this.replace(/^\s+|\s+$/g,"")},o=Array[n].indexOf||function(e){var t=0,n=this.length;for(;t<n;t++){if(t in this&&this[t]===e){return t}}return-1},u=function(e,t){this.name=e;this.code=DOMException[e];this.message=t},a=function(e,t){if(t===""){throw new u("SYNTAX_ERR","An invalid or illegal string was specified")}if(/\s/.test(t)){throw new u("INVALID_CHARACTER_ERR","String contains an invalid character")}return o.call(e,t)},f=function(e){var t=s.call(e.className),n=t?t.split(/\s+/):[],r=0,i=n.length;for(;r<i;r++){this.push(n[r])}this._updateClassName=function(){e.className=this.toString()}},l=f[n]=[],c=function(){return new f(this)};u[n]=Error[n];l.item=function(e){return this[e]||null};l.contains=function(e){e+="";return a(this,e)!==-1};l.add=function(){var e=arguments,t=0,n=e.length,r,i=false;do{r=e[t]+"";if(a(this,r)===-1){this.push(r);i=true}}while(++t<n);if(i){this._updateClassName()}};l.remove=function(){var e=arguments,t=0,n=e.length,r,i=false;do{r=e[t]+"";var s=a(this,r);if(s!==-1){this.splice(s,1);i=true}}while(++t<n);if(i){this._updateClassName()}};l.toggle=function(e,t){e+="";var n=this.contains(e),r=n?t!==true&&"remove":t!==false&&"add";if(r){this[r](e)}return!n};l.toString=function(){return this.join(" ")};if(i.defineProperty){var h={get:c,enumerable:true,configurable:true};try{i.defineProperty(r,t,h)}catch(p){if(p.number===-2146823252){h.enumerable=false;i.defineProperty(r,t,h)}}}else if(i[n].__defineGetter__){r.__defineGetter__(t,c)}})(self)}

// Theamus Theme
document.addEventListener('DOMContentLoaded', function(){$("#nav-response-btn").click(function(e){e.preventDefault();$($(this).data("open")).toggle()})})

