/**
Proto is basic class that should be extended
it provides: 
- acync JSON with callback (JSONP)
- unique namespace for the instance
- caching
- other tools

Example usage:
var options = {
	'flickr':'("http://api.flickr.com/services/feeds/photos_public.gne",
	'callbackName':'jsoncallback'
};
var proto = new Proto(options);
proto.get({
    tags: "cat",
    tagmode: "any",
    format: "json"
  },
  'flickr',
  function(data){
  		console.log(data); 
	}
);

Proto is extended like this:

var MyClass = Proto.extend({
	// optionaly override constructor ('init' method)
	'init':function(opts){
		// parent constructor must be called
		this._super(opts);
	},
	'myNewMethod':function(){
	},
	'myOverrideMethod':function(){
		// optionally parent method can be called with this._super(params);
	}
});


*/

/* Simple JavaScript Inheritance
 * By John Resig http://ejohn.org/
 * MIT Licensed.
 */
// Inspired by base2 and Prototype
(function(){
  var initializing = false, fnTest = /xyz/.test(function(){xyz;}) ? /\b_super\b/ : /.*/;

  // The base Class implementation (does nothing)
  this.Class = function(){};
 
  // Create a new Class that inherits from this class
  Class.extend = function(prop) {
    var _super = this.prototype;
   
    // Instantiate a base class (but only create the instance,
    // don't run the init constructor)
    initializing = true;
    var prototype = new this();
    initializing = false;
   
    // Copy the properties over onto the new prototype
    for (var name in prop) {
      // Check if we're overwriting an existing function
      prototype[name] = typeof prop[name] == "function" &&
        typeof _super[name] == "function" && fnTest.test(prop[name]) ?
        (function(name, fn){
          return function() {
            var tmp = this._super;
           
            // Add a new ._super() method that is the same method
            // but on the super-class
            this._super = _super[name];
           
            // The method only need to be bound temporarily, so we
            // remove it when we're done executing
            var ret = fn.apply(this, arguments);       
            this._super = tmp;
           
            return ret;
          };
        })(name, prop[name]) :
        prop[name];
    }
   
    // The dummy class constructor
    function Class() {
      // All construction is actually done in the init method
      if ( !initializing && this.init )
        this.init.apply(this, arguments);
    }
   
    // Populate our constructed prototype object
    Class.prototype = prototype;
   
    // Enforce the constructor to be what we expect
    Class.prototype.constructor = Class;

    // And make this class extendable
    Class.extend = arguments.callee;
    return Class;
  };
})();


// The Proto Script
var _Proto = {};
_Proto.init = function(opt){
	this.opt = opt;
	this.globalVar = 'papps';
	this.id = 'a'+Math.floor(Math.random()*100001);
	if ((typeof window[this.globalVar]) != 'object'){
		window[this.globalVar] = {};
	}
	window[this.globalVar][this.id] = this;
	this.params = {};
	for(var i in opt){
		if(typeof opt[i] == 'string' && opt[i].match(/https{0,1}\:\/\/[\w\.\-\:]+/)){
			this.params[i] = this.uriToObject(opt[i]);
		}
	}
	this.func = {};
	if(!window.protoCache){window.protoCache = {};}
	this.opt.callbackName = opt.callbackName?opt.callbackName:'callback';
	// Constructor Method of the class that inherits this one must start with 'Init'
	for(var i in this){
		if(i.substr(0, 4) == 'Init'){
			this[i].call(this, arguments);
		}
	}
	this.checkForDummyPage(); // optionaly use dummy page for window.transport
};
_Proto.uriToObject = function(uri, separator){
	if(!separator){separator=',';}
	uri = uri.toString();
	var matched = uri.match(/(https{0,1}\:\/\/[\w\.\-\:]+)([\w\/\.\-]+)\?(.+)/);
	var params = {};
	if (matched && matched[1] && matched[2] && matched[3]){
		params.base = matched[1];
		params.method = matched[2];
		var splits = matched[3].split('&');
		if (splits){
			params.query = {};
			for (var i=0;i<splits.length;i++){
				var split = splits[i].split('=');
				if (split[0] && split[1]){
					if (split[1].indexOf(separator) != -1){
						var ats = split[1].split(separator);
						params.query[split[0]] = [];
						for (var j=0;j<ats.length;j++){
							params.query[split[0]].push(decodeURIComponent(ats[j]));
						}
					}else{
						params.query[split[0]] = decodeURIComponent(split[1]);
					}
				}
			}
		}
	}else{
		params = {base:uri, method:'', query:{}};
	}
	return params;
};
_Proto.objectToUri = function(object, separator){
	if(!separator){separator=',';}
	var uri = object.base+object.method+'?';
	var fr = true;
	for (var i in object.query){
		if(fr){fr=false;}else{uri += '&';}
		if (typeof object.query[i] == 'object'){
			uri+= i+'='; var f=true;
			for (var j in object.query[i]){
				if(f){f=false;}else{uri += separator;}
				uri += encodeURIComponent(object.query[i][j]);
			}
		}else{
			uri+= i+'='+ encodeURIComponent(object.query[i]);
		}
	}
	if(uri.substring(uri.length -1) == '?'){uri = uri.replace('?', '');}
	return uri;
};
// Handles get requests
_Proto.get = function(query, feeds, func, opt){
	if(!feeds){feeds = query; query = {};}
	if (typeof feeds == 'string'){feeds = [feeds];}
	if(!opt){opt = {'save':false, 'del':false};}
	if(!opt || !opt.save){opt.save = false;}
	for (var i=0;i<feeds.length;i++){
		this[feeds[i]] = false;
		if (opt.save == false){
			var o = this.params[feeds[i]];
			var q = {'base':o.base, 'method':o.method,'query':{}};
			for(var j in o.query){
				q.query[j] = o.query[j];
			}
		}else{
			var q = this.params[feeds[i]];
		}
		if (query){
			for (var j in query){
				if (query[j] == ''){
					delete q.query[j];
				}else{
					if(opt.del == true && opt.append){
						var parts = q.query[j].split(opt.append);
						var nparts = [];
						for (k=0;k<parts.length;k++){
							if (query[j] != parts[k]){
								nparts.push(parts[k]);
							}
						}
						if (nparts.length == 0){
							delete q.query[j];
						}else if (nparts.length == 1){
							q.query[j] = nparts[0];
						}else{
							q.query[j] = nparts.join(opt.append);
						}
					}else if (q.query[j] && opt.append){
						q.query[j] += opt.append+query[j];
					}else{
						q.query[j] = query[j];
					}
				}
			}
		}
		if(opt.callbackName){
			this.opt.callbackName = opt.callbackName;
		}
		if (opt.action && opt.action == 'silent'){
			return q;
		}else{
			this.request(q, feeds[i], func, opt);
		}
	}
};
_Proto.request = function(q, feed, func, opt){
	if (typeof q == 'string'){
		var uri = this.objectToUri(this.params[q]);
		feed = q;
		func = feed;
	}else{
		var uri = this.objectToUri(q);
	}
	if(typeof func == 'function'){
		this.func[uri] = func;
	}else{
		(function(){
			  setTimeout("var d = document, s = d.createElement('script'); s.type = 'text/javascript'; s.src = '"+uri+"';s.id='"+uri+"'; d.getElementsByTagName('head')[0].appendChild(s);", 0);
		})();
		return false;
	}
	if (this.isCached(uri, feed, opt)){
		// trigger the callback immediately
		this.func[uri].call(this, this[feed]);
	}else{
		try{this.loadStart(uri);}catch(err){}
		var or = document.getElementById(uri);
		if (or) {or.parentNode.removeChild(or);}
		var cb = this.globalVar+'.'+this.id+'.'+feed+'Loaded';
		eval(cb+' = function(data){this.'+feed+' = data; window.protoCache["'+uri+'"] = data; try{this.loadEnd("'+uri+'");}catch(err){} this.func["'+uri+'"].call('+this.globalVar+'.'+this.id+', data);}');
		var cbp = this.opt.callbackName;
		var am = (uri.indexOf('?')==-1)?'?':'&';
		if(opt && opt.nocache){am += '_='+new Date().getTime()+'&';}
		(function(){
			  setTimeout("var d = document, s = d.createElement('script'); s.type = 'text/javascript'; s.src = '"+uri+am+cbp+'='+cb+"';s.id='"+uri+"'; d.getElementsByTagName('head')[0].appendChild(s);", 0);
		})();
	}
};
_Proto.isCached = function(uri, feed, opt){
	if(opt && opt.nocache){
		return false;
	}else if(window.protoCache && window.protoCache[uri]){
		this[feed] = window.protoCache[uri];
		return true;
	}else{
		return false;
	}
};
/**
 * Posts data to server using window.name transport
 * 
 * Usage:
 * protoInstance.post('http://myserver.com/path', {'postfield1':'value1', 'postField2':'value2'}, 'myCallbackFunctionName');
 * 
 * Method will post specified fields along with two aditional POST fields:
 * "callback" and "redirect" where "callback" contains name of the calback function and "redirect" contains URL to redirect.
 * 
 * Note:
 * Server must return html page in folowing format:
 * 
<!DOCTYPE html>
<html>
	<head>
		<script>
			window.name='{JSON_RESPONSE}'; 
			window.location='{REDIRECT}#_callback={CALLBACK}';
		</script>
	</head><body></body>
</html>
 *
 *In the above HTML server should replace {JSON_RESPONSE} with actual JSON response encoded in a string,
 *{REDIRECT} with URL from "redirect" POST field and {CALLBACK} with name of the callback function from "callback" POST field.
 */
_Proto.post = function(url, postData, callback, selfCallback){
	var dummyURL;
	if(this.opt.dummyURL){
		dummyURL = this.opt.dummyURL;
	}else{
		dummyURL = location.href;
	}
	if(typeof callback == 'function'){
		var inst = this;
		if(!inst.postCallbacks){inst.postCallbacks = {};}
		var cbn = url.replace(/[^a-zA-Z]/g, '');
		inst.postCallbacks[cbn] = callback;
		callback = this.globalVar+'.'+this.id+'.postCallbacks.'+cbn;
	}else if(selfCallback){callback = this.globalVar+'.'+this.id+'.'+callback;}
	if(url.toLowerCase().indexOf('http://') == -1){
		if(this.params[url]){
			url = this.objectToUri(this.params[url]);
		}else{
			return false;
		}
	}
	var html = '<!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"></head><body rel="forma" onload="document.forms[0].submit()"><form id="forma" method="POST" action="'+url+'">';
	for (var i in postData){
		html += '<textarea name="'+i+'">'+postData[i]+'</textarea>';
	}
	html += '<textarea name="callback">'+callback+'</textarea>';
	html += '<textarea name="redirect">'+dummyURL+'</textarea>';
	html += '</form></body></html>';
	hld = document.getElementById(callback+'holder');
	if(!hld){
		hld = document.createElement('div');
		hld.style.display = 'none';hld.id= callback+'holder';
		document.getElementsByTagName('body')[0].appendChild(hld);
	}
	try{this.loadStart(url);}catch(err){}
	var iframe;
	if (document.createElement && (iframe = document.createElement('iframe'))){
		iframe.name = iframe.id = callback.replace(/\W/g, '')+'name';iframe.width = 0;iframe.height = 0;iframe.style.border = 'none';iframe.style.overflow = 'hidden';
		iframe.scrolling = 'no';iframe.marginWidth = '0';iframe.marginHeight = '0';iframe.frameBorder = '0';iframe.vSpace = '0';iframe.hSpace = '0';
		iframe.src = 'about:blank';
		hld.innerHTML = '';
		hld.appendChild(iframe);
	}else{return false;}
	var iframeDoc;
	if (iframe.contentDocument){iframeDoc=iframe.contentDocument;}else if(iframe.contentWindow){iframeDoc=iframe.contentWindow.document;}else if(window.frames[iframe.name]){iframeDoc=window.frames[iframe.name].document;}else{return false;}
	if (iframeDoc) {
		iframeDoc.open();
		iframeDoc.write(html);
		window.setTimeout(function(){iframeDoc.close();}, 100);
	}else{return false;}
	return true;
};
_Proto.checkForDummyPage = function(){
	if(!this.opt.dummyURL){
		var parts = location.href.split("#");
		var hash = parts[parts.length-1];
		if(hash && hash.indexOf('_callback=') != -1){
			location.href = parts[0];
			cb = hash.replace('_callback=', '');
			cb = cb.replace(/[^a-zA-Z0-9\.]/g, '');
			var lecb = cb.split('.');
			lecb.pop();
			lecb = lecb.join('.')+'.loadEnd';
			eval('try{window.parent.'+lecb+'("'+cb+'")}catch(err){}');
			try {
				var resp = window.name;
				window.name = '';
				if(resp.substr(0, 1) == '{'){
					eval('window.parent.'+cb+'('+resp+')');
				}else{
					eval('window.parent.'+cb+'("'+resp+'")');
				}
			}catch(e1){
				try{console.log(e1);}catch(err){}
				try{
					eval('window.parent.'+cb+'(false)');
				}catch(e2){}
			}
			window.stop();
			return false;
		}
	}
};
/**
 * Request multiple feeds at once and trigger one callback when all feeds finish loading
 * Example:
 * protoInstance.multiget([
 * 	{
 * 		"query":{"q":"Indiana Jones"},
 * 		"feed":"movies",
 * 		"opt":{"nocache":true}
 *  },
 *  {
 * 		"query":{"q":"Orbital"},
 * 		"feed":"music"
 *  }
 * ], function(){
 * 		console.log(this.movies);
 * 		console.log(this.music);
 * });
 */
_Proto.multiget = function(querys, func){
	if(typeof querys == 'object'){
		var cb = 'cb'+Math.floor(Math.random()*100001);
		if(!this.callbacks){this.callbacks = {};}
		this.callbacks[cb] = func;
		var fa = [];
		for(var i=0 ;i<querys.length;i++){
			this[querys[i].feed] = false;
			fa.push('this["'+querys[i].feed+'"]');
		}
		var cbfn = this.globalVar+'.'+this.id+'.'+cb+' = function(){if('+fa.join(' && ')+'){this.callbacks.'+cb+'.call(this); this.callbacks.'+cb+' = null; this["'+cb+'"]=null;}}';
		eval(cbfn);
		for(var i=0 ;i<querys.length;i++){
			this.get(querys[i].query, querys[i].feed, this[cb], querys[i].opt?querys[i].opt:false);
		}
	}
};
var Proto = Class.extend(_Proto);





/**
*
*  Base64 encode / decode
*  http://www.webtoolkit.info/
*
**/
 
var Base64 = {
 
	// private property
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
 
	// public method for encoding
	encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;
 
		input = Base64._utf8_encode(input);
 
		while (i < input.length) {
 
			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);
 
			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;
 
			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}
 
			output = output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
 
		}
 
		return output;
	},
 
	// public method for decoding
	decode : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;
 
		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
 
		while (i < input.length) {
 
			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));
 
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
 
			output = output + String.fromCharCode(chr1);
 
			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}
 
		}
 
		output = Base64._utf8_decode(output);
 
		return output;
 
	},
 
	// private method for UTF-8 encoding
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
 
		for (var n = 0; n < string.length; n++) {
 
			var c = string.charCodeAt(n);
 
			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
 
		}
 
		return utftext;
	},
 
	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;
 
		while ( i < utftext.length ) {
 
			c = utftext.charCodeAt(i);
 
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
 
		}
 
		return string;
	}
 
}


