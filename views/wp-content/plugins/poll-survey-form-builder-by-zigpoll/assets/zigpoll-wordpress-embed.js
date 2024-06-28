;(function () {

function isMe(scriptElem){
  var src = scriptElem.getAttribute('src');
  if (src) {
    return src.indexOf('zigpoll-wordpress-embed') !== -1;
  }
}

var me = null;
var scripts = document.getElementsByTagName("script")
for (var i = 0; i < scripts.length; ++i) {
  if (isMe(scripts[i])) {
    me = scripts[i];
  }
}

function getParameterByName(name, url) {
  if (!url) url = window.location.href;
  name = name.replace(/[\[\]]/g, '\\$&');
  var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
      results = regex.exec(url);
  if (!results) return null;
  if (!results[2]) return '';
  return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

var src = me.getAttribute('src');
var accountId = getParameterByName('accountId', src);

if (window.Zigpoll) {

  console.warn('[Zigpoll] There are multiple Zigpoll embeds on this page. Please check your code.');

} else if (!accountId) {

  console.warn('[Zigpoll] No Account ID has been provided. Please log into your wordpress admin dashboard, click the Zigpoll menu Icon, and enter your Account ID.');

} else {

  /* Zigpoll script with account id */
  window.Zigpoll = {
    accountId: accountId
  };

  var script = document.createElement("script");
  script.type = "text/javascript";
  script.charset = "utf-8";
  script.src = '//cdn.zigpoll.com/static/js/main.js';

  document.head.appendChild(script);

}

}());