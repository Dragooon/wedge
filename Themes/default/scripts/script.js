/*!
 * This file is under the SMF license.
 * All code changes compared against SMF 2.0 are protected
 * by the Wedge license, http://wedgeforum.com/license/
 */

var
	smf_formSubmitted = false,
	lastKeepAliveCheck = new Date().getTime(),
	smf_editorArray = new Array();

// Basic browser detection
var ua = navigator.userAgent.toLowerCase();

var
	is_opera = ua.indexOf('opera') != -1,
	is_opera5 = is_opera6 = is_opera7 = is_opera8 = false,
	is_opera10up = is_opera && (ua.indexOf('opera/9.8') != -1 || ua.indexOf('opera 9.8') != -1),
	is_opera9 = is_opera && !is_opera10up && (ua.indexOf('opera/9') != -1 || ua.indexOf('opera 9') != -1),
	is_opera95 = is_opera9 && ua.match(/opera[ \/]9\.[5-7]/),
	is_opera105up = is_opera10up && !!ua.match(/[ \/]1(?:0\.[5-9]|[1-9]\.[0-9]+)/),
	is_opera10 = is_opera10up && !is_opera105up,
	is_opera95up = is_opera95 || is_opera10up;

var
	is_ff = ua.indexOf('gecko/') != -1 && ua.indexOf('like gecko') == -1 && !is_opera,
	is_gecko = !is_opera && ua.indexOf('gecko') != -1;

var
	is_chrome = ua.indexOf('chrome') != -1,
	is_webkit = ua.indexOf('applewebkit') != -1,
	is_iphone = is_webkit && ua.indexOf('iphone') != -1 || ua.indexOf('ipod') != -1,
	is_android = is_webkit && ua.indexOf('android') != -1,
	is_safari = is_webkit && !is_chrome && !is_iphone && !is_android;

var
	is_ie4 = is_ie5 = is_ie50 = is_ie55 = false,
	is_ie = is_ie5up = is_ie6up = ua.indexOf('msie') != -1 && !is_opera,
	is_ie6 = is_ie6down = is_ie && ua.indexOf('msie 6') != -1,
	is_ie7 = is_ie && ua.indexOf('msie 7') != -1, is_ie7up = is_ie && !is_ie6, is_ie7down = is_ie7 || is_ie6,
	is_ie8 = is_ie && ua.indexOf('msie 8') != -1, is_ie8up = is_ie && !is_ie7down, is_ie8down = is_ie8 || is_ie7down,
	is_ie9 = is_ie && ua.indexOf('msie 9') != -1, is_ie9up = is_ie && !is_ie8down;

var ajax_indicator_ele = null;

if (is_ie && !('XMLHttpRequest' in window) && 'ActiveXObject' in window)
	window.XMLHttpRequest = function () { return new ActiveXObject('MSXML2.XMLHTTP'); };

// Load an XML document using XMLHttpRequest.
function getXMLDocument(sUrl, funcCallback)
{
	if (!('XMLHttpRequest' in window))
		return null;

	var oMyDoc = new XMLHttpRequest();
	var bAsync = typeof(funcCallback) != 'undefined';
	var oCaller = this;
	if (bAsync)
	{
		oMyDoc.onreadystatechange = function () {
			if (oMyDoc.readyState != 4)
				return;

			if (oMyDoc.responseXML != null && oMyDoc.status == 200)
				funcCallback.call(oCaller, oMyDoc.responseXML);
		};
	}
	oMyDoc.open('GET', sUrl, bAsync);
	oMyDoc.send(null);

	return oMyDoc;
}

// Send a post form to the server using XMLHttpRequest.
function sendXMLDocument(sUrl, sContent, funcCallback)
{
	if (!window.XMLHttpRequest)
		return false;

	var oSendDoc = new window.XMLHttpRequest();
	var oCaller = this;
	if (typeof(funcCallback) != 'undefined')
	{
		oSendDoc.onreadystatechange = function () {
			if (oSendDoc.readyState != 4)
				return;

			funcCallback.call(oCaller, oSendDoc.responseXML != null && oSendDoc.status == 200 ? oSendDoc.responseXML : false);
		};
	}
	oSendDoc.open('POST', sUrl, true);
	if ('setRequestHeader' in oSendDoc)
		oSendDoc.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	oSendDoc.send(sContent);

	return true;
}

// Convert a string to an 8 bit representation (like in PHP).
String.prototype.php_to8bit = function ()
{
	var n, sReturn = '';

	for (var i = 0, iTextLen = this.length; i < iTextLen; i++)
	{
		n = this.charCodeAt(i);
		if (n < 128)
			sReturn += String.fromCharCode(n);
		else if (n < 2048)
			sReturn += String.fromCharCode(192 | n >> 6) + String.fromCharCode(128 | n & 63);
		else if (n < 65536)
			sReturn += String.fromCharCode(224 | n >> 12) + String.fromCharCode(128 | n >> 6 & 63) + String.fromCharCode(128 | n & 63);
		else
			sReturn += String.fromCharCode(240 | n >> 18) + String.fromCharCode(128 | n >> 12 & 63) + String.fromCharCode(128 | n >> 6 & 63) + String.fromCharCode(128 | n & 63);
	}

	return sReturn;
}

// Character-level replacement function.
String.prototype.php_strtr = function (sFrom, sTo)
{
	return this.replace(new RegExp('[' + sFrom + ']', 'g'), function (sMatch) {
		return sTo.charAt(sFrom.indexOf(sMatch));
	});
}

// Simulate PHP's strtolower (in SOME cases, PHP uses ISO-8859-1 case folding.)
String.prototype.php_strtolower = function ()
{
	return typeof(smf_iso_case_folding) == 'boolean' && smf_iso_case_folding == true ? this.php_strtr(
		'ABCDEFGHIJKLMNOPQRSTUVWXYZ\x8a\x8c\x8e\x9f\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdb\xdc\xdd\xde',
		'abcdefghijklmnopqrstuvwxyz\x9a\x9c\x9e\xff\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf7\xf8\xf9\xfa\xfb\xfc\xfd\xfe'
	) : this.php_strtr('ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
}

String.prototype.php_urlencode = function ()
{
	return escape(this).replace(/\+/g, '%2b').replace('*', '%2a').replace('/', '%2f').replace('@', '%40');
}

String.prototype.php_htmlspecialchars = function ()
{
	return this.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

String.prototype.php_unhtmlspecialchars = function ()
{
	return this.replace(/&quot;/g, '"').replace(/&gt;/g, '>').replace(/&lt;/g, '<').replace(/&amp;/g, '&');
}

String.prototype.php_addslashes = function ()
{
	return this.replace(/\\/g, '\\\\').replace(/'/g, '\\\'');
}

String.prototype._replaceEntities = function (sInput, sDummy, sNum)
{
	return String.fromCharCode(parseInt(sNum));
}

String.prototype.removeEntities = function ()
{
	return this.replace(/&(amp;)?#(\d+);/g, this._replaceEntities);
}

String.prototype.easyReplace = function (oReplacements)
{
	var sResult = this;
	for (var sSearch in oReplacements)
		sResult = sResult.replace(new RegExp('%' + sSearch + '%', 'g'), oReplacements[sSearch]);

	return sResult;
}

var helpFrame = null;

// Open a new window.
function reqWin(from, alternateWidth, alternateHeight, noScrollbars)
{
	var desktopURL = typeof(from) == 'object' && from.href ? from.href : from;
	if ((alternateWidth && self.screen.availWidth * 0.8 < alternateWidth) || (alternateHeight && self.screen.availHeight * 0.8 < alternateHeight))
	{
		noScrollbars = false;
		alternateWidth = Math.min(alternateWidth, self.screen.availWidth * 0.8);
		alternateHeight = Math.min(alternateHeight, self.screen.availHeight * 0.8);
	}
	else
		noScrollbars = typeof(noScrollbars) == 'boolean' && noScrollbars == true;

	var aPos = typeof(from) == 'object' ? smf_itemPos(from) : [10, 10];

	if (helpFrame != null)
	{
		var previousTarget = helpFrame.src;
		document.body.removeChild(helpFrame);
		helpFrame = null;
		if (previousTarget == desktopURL)
			return false;
	}

	helpFrame = document.createElement('iframe');
	helpFrame.src = desktopURL;
	helpFrame.id = 'helpFrame';
	with (helpFrame.style)
	{
		if (noScrollbars)
			overflow = 'hidden';
		position = 'absolute';
		width = (alternateWidth ? alternateWidth : 480) + 'px';
		height = (alternateHeight ? alternateHeight : 220) + 'px';
		left = (aPos[0] + 15) + 'px';
		top = (aPos[1] + 15) + 'px';
		border = '1px solid #999';
	}
	document.body.appendChild(helpFrame);

	// Return false so the click won't follow the link ;)
	return false;
}

// Checks if the passed input's value is nothing.
function isEmptyText(theField)
{
	// Copy the value so changes can be made..
	var theValue = theField.value;

	// Strip whitespace off the left side.
	while (theValue.length > 0 && (theValue.charAt(0) == ' ' || theValue.charAt(0) == '\t'))
		theValue = theValue.substring(1, theValue.length);
	// Strip whitespace off the right side.
	while (theValue.length > 0 && (theValue.charAt(theValue.length - 1) == ' ' || theValue.charAt(theValue.length - 1) == '\t'))
		theValue = theValue.substring(0, theValue.length - 1);

	if (theValue == '')
		return true;
	else
		return false;
}

// Only allow form submission ONCE.
function submitonce()
{
	smf_formSubmitted = true;

	// If there are any editors warn them submit is coming!
	for (var i = 0; i < smf_editorArray.length; i++)
		smf_editorArray[i].doSubmit();
}

function submitThisOnce(oControl)
{
	// Hateful, hateful fix for Safari 1.3 beta.
	if (is_safari)
		return !smf_formSubmitted;

	// oControl might also be a form.
	var oForm = 'form' in oControl ? oControl.form : oControl;

	var aTextareas = oForm.getElementsByTagName('textarea');
	for (var i = 0, n = aTextareas.length; i < n; i++)
		aTextareas[i].readOnly = true;

	return !smf_formSubmitted;
}

// Set the "outer" HTML of an element.
function setOuterHTML(oElement, sToValue)
{
	if ('outerHTML' in oElement)
		oElement.outerHTML = sToValue;
	else
	{
		var range = document.createRange();
		range.setStartBefore(oElement);
		oElement.parentNode.replaceChild(range.createContextualFragment(sToValue), oElement);
	}
}

// Checks for variable in theArray.
function in_array(variable, theArray)
{
	for (var i in theArray)
		if (theArray[i] == variable)
			return true;

	return false;
}

// Checks for variable in theArray.
function array_search(variable, theArray)
{
	for (var i in theArray)
		if (theArray[i] == variable)
			return i;

	return null;
}

// Find a specific radio button in its group and select it.
function selectRadioByName(oRadioGroup, sName)
{
	if (!('length' in oRadioGroup))
		return oRadioGroup.checked = true;

	for (var i = 0, n = oRadioGroup.length; i < n; i++)
		if (oRadioGroup[i].value == sName)
			return oRadioGroup[i].checked = true;

	return false;
}

// Invert all checkboxes at once by clicking a single checkbox.
function invertAll(oInvertCheckbox, oForm, sMask, bIgnoreDisabled)
{
	for (var i = 0; i < oForm.length; i++)
	{
		if (!('name' in oForm[i]) || (typeof(sMask) == 'string' && oForm[i].name.substr(0, sMask.length) != sMask && oForm[i].id.substr(0, sMask.length) != sMask))
			continue;

		if (!oForm[i].disabled || (typeof(bIgnoreDisabled) == 'boolean' && bIgnoreDisabled))
			oForm[i].checked = oInvertCheckbox.checked;
	}
}

// Keep the session alive - always!
function smf_sessionKeepAlive()
{
	var curTime = new Date().getTime();

	// Prevent a Firefox bug from hammering the server.
	if (smf_scripturl && curTime - lastKeepAliveCheck > 900000)
	{
		var tempImage = new Image();
		tempImage.src = smf_prepareScriptUrl(smf_scripturl) + 'action=keepalive;time=' + curTime;
		lastKeepAliveCheck = curTime;
	}

	window.setTimeout('smf_sessionKeepAlive();', 1200000);
}
window.setTimeout('smf_sessionKeepAlive();', 1200000);

// Set a theme option through javascript.
function smf_setThemeOption(option, value, theme, cur_session_id, cur_session_var, additional_vars)
{
	if (!cur_session_id)
		cur_session_id = smf_session_id;
	if (!cur_session_var)
		cur_session_var = 'sesc';
	if (!additional_vars)
		additional_vars = '';

	var tempImage = new Image();
	tempImage.src = smf_prepareScriptUrl(smf_scripturl) + 'action=jsoption;var=' + option + ';val=' + value + ';' + cur_session_var + '=' + cur_session_id + additional_vars + (theme == null ? '' : '&id=' + theme) + ';time=' + (new Date().getTime());
}

function smf_avatarResize()
{
	var possibleAvatars = document.getElementsByTagName('img');

	for (var i = 0; i < possibleAvatars.length; i++)
	{
		var tempAvatars = [], j = 0;
		if (possibleAvatars[i].className != 'avatar')
			continue;

		tempAvatars[j] = new Image();
		tempAvatars[j].avatar = possibleAvatars[i];

		tempAvatars[j].onload = function ()
		{
			this.avatar.width = this.width;
			this.avatar.height = this.height;
			if (smf_avatarMaxWidth != 0 && this.width > smf_avatarMaxWidth)
			{
				this.avatar.height = (smf_avatarMaxWidth * this.height) / this.width;
				this.avatar.width = smf_avatarMaxWidth;
			}
			if (smf_avatarMaxHeight != 0 && this.avatar.height > smf_avatarMaxHeight)
			{
				this.avatar.width = (smf_avatarMaxHeight * this.avatar.width) / this.avatar.height;
				this.avatar.height = smf_avatarMaxHeight;
			}
		};
		tempAvatars[j].src = possibleAvatars[i].src;
		j++;
	}

	if (window_oldAvatarOnload)
	{
		window_oldAvatarOnload();
		window_oldAvatarOnload = null;
	}
}


function hashLoginPassword(doForm, cur_session_id)
{
	if (!cur_session_id)
		cur_session_id = smf_session_id;
	if (typeof(hex_sha1) == 'undefined')
		return;
	// Are they using an email address?
	if (doForm.user.value.indexOf('@') != -1)
		return;

	// Unless the browser is Opera, the password will not save properly.
	if (!('opera' in window))
		doForm.passwrd.autocomplete = 'off';

	doForm.hash_passwrd.value = hex_sha1(hex_sha1(doForm.user.value.php_to8bit().php_strtolower() + doForm.passwrd.value.php_to8bit()) + cur_session_id);

	// It looks nicer to fill it with asterisks, but Firefox will try to save that.
	doForm.passwrd.value = is_ff != -1 ? '' : doForm.passwrd.value.replace(/./g, '*');
}

function hashAdminPassword(doForm, username, cur_session_id)
{
	if (!cur_session_id)
		cur_session_id = smf_session_id;
	if (typeof(hex_sha1) == 'undefined')
		return;

	doForm.admin_hash_pass.value = hex_sha1(hex_sha1(username.php_to8bit().php_strtolower() + doForm.admin_pass.value.php_to8bit()) + cur_session_id);
	doForm.admin_pass.value = doForm.admin_pass.value.replace(/./g, '*');
}

// Shows the page numbers by clicking the dots (in compact view).
function expandPages(spanNode, baseURL, firstPage, lastPage, perPage)
{
	var replacement = '', i, oldLastPage = 0, perPageLimit = 50;

	// The dots were bold, the page numbers are not (in most cases).
	spanNode.style.fontWeight = 'normal';
	spanNode.onclick = '';

	// Prevent too many pages to be loaded at once.
	if ((lastPage - firstPage) / perPage > perPageLimit)
	{
		oldLastPage = lastPage;
		lastPage = firstPage + perPageLimit * perPage;
	}

	// Calculate the new pages.
	for (i = firstPage; i < lastPage; i += perPage)
		replacement += '<a class="navPages" href="' + baseURL.replace(/%1\$d/, i).replace(/%%/g, '%') + '">' + (1 + i / perPage) + '</a> ';

	if (oldLastPage > 0)
		replacement += '<span style="font-weight: bold; cursor: pointer;" onclick="expandPages(this, \'' + baseURL + '\', ' + lastPage + ', ' + oldLastPage + ', ' + perPage + ');"> ... </span> ';

	// Replace the dots by the new page links.
	spanNode.innerHTML = replacement;
}

function smc_preCacheImage(sSrc)
{
	if (!('smc_aCachedImages' in window))
		window.smc_aCachedImages = [];

	if (!in_array(sSrc, window.smc_aCachedImages))
	{
		var oImage = new Image();
		oImage.src = sSrc;
	}
}


// *** smc_Cookie class.
function smc_Cookie(oOptions)
{
	this.opt = oOptions;
	this.oCookies = {};
	this.init();
}

smc_Cookie.prototype.init = function ()
{
	if ('cookie' in document && document.cookie != '')
	{
		var aCookieList = document.cookie.split(';');
		for (var i = 0, n = aCookieList.length; i < n; i++)
		{
			var aNameValuePair = aCookieList[i].split('=');
			this.oCookies[aNameValuePair[0].replace(/^\s+|\s+$/g, '')] = decodeURIComponent(aNameValuePair[1]);
		}
	}
}

smc_Cookie.prototype.get = function (sKey)
{
	return sKey in this.oCookies ? this.oCookies[sKey] : null;
}

smc_Cookie.prototype.set = function (sKey, sValue)
{
	document.cookie = sKey + '=' + encodeURIComponent(sValue);
}


// *** smc_Toggle class.
function smc_Toggle(oOptions)
{
	this.opt = oOptions;
	this.bCollapsed = false;
	this.oCookie = null;
	this.init();
}

smc_Toggle.prototype.init = function ()
{
	// The master switch can disable this toggle fully.
	if ('bToggleEnabled' in this.opt && !this.opt.bToggleEnabled)
		return;

	// If cookies are enabled and they were set, override the initial state.
	if ('oCookieOptions' in this.opt && this.opt.oCookieOptions.bUseCookie)
	{
		// Initialize the cookie handler.
		this.oCookie = new smc_Cookie({});

		// Check if the cookie is set.
		var cookieValue = this.oCookie.get(this.opt.oCookieOptions.sCookieName);
		if (cookieValue != null)
			this.opt.bCurrentlyCollapsed = cookieValue == '1';
	}

	// If the init state is set to be collapsed, collapse it.
	if (this.opt.bCurrentlyCollapsed)
		this.changeState(true, true);

	// Initialize the images to be clickable.
	if ('aSwapImages' in this.opt)
	{
		for (var i = 0, n = this.opt.aSwapImages.length; i < n; i++)
		{
			var oImage = document.getElementById(this.opt.aSwapImages[i].sId);
			if (typeof(oImage) == 'object' && oImage != null)
			{
				// Display the image in case it was hidden.
				if (oImage.style.display == 'none')
					oImage.style.display = '';

				oImage.instanceRef = this;
				oImage.onclick = function () {
					this.instanceRef.toggle();
					this.blur();
				};
				oImage.style.cursor = 'pointer';

				// Preload the collapsed image.
				smc_preCacheImage(this.opt.aSwapImages[i].srcCollapsed);
			}
		}
	}

	// Initialize links.
	if ('aSwapLinks' in this.opt)
	{
		for (var i = 0, n = this.opt.aSwapLinks.length; i < n; i++)
		{
			var oLink = document.getElementById(this.opt.aSwapLinks[i].sId);
			if (typeof(oLink) == 'object' && oLink != null)
			{
				// Display the link in case it was hidden.
				if (oLink.style.display == 'none')
					oLink.style.display = '';

				oLink.instanceRef = this;
				oLink.onclick = function () {
					this.instanceRef.toggle();
					this.blur();
					return false;
				};
			}
		}
	}
}

// Collapse or expand the section.
smc_Toggle.prototype.changeState = function (bCollapse, bInit)
{
	// Default bInit to false.
	bInit = !!bInit;

	// Handle custom function hook before collapse.
	if (!bInit && bCollapse && 'funcOnBeforeCollapse' in this.opt)
		this.opt.funcOnBeforeCollapse.call(this);

	// Handle custom function hook before expand.
	else if (!bInit && !bCollapse && 'funcOnBeforeExpand' in this.opt)
		this.opt.funcOnBeforeExpand.call(this);

	// Loop through all the images that need to be toggled.
	if ('aSwapImages' in this.opt)
	{
		for (var i = 0, n = this.opt.aSwapImages.length; i < n; i++)
		{
			var oImage = document.getElementById(this.opt.aSwapImages[i].sId);
			if (typeof(oImage) == 'object' && oImage != null)
			{
				// Only (re)load the image if it's changed.
				var sTargetSource = bCollapse ? this.opt.aSwapImages[i].srcCollapsed : this.opt.aSwapImages[i].srcExpanded;
				if (oImage.src != sTargetSource)
					oImage.src = sTargetSource;

				oImage.alt = oImage.title = bCollapse ? this.opt.aSwapImages[i].altCollapsed : this.opt.aSwapImages[i].altExpanded;
			}
		}
	}

	// Loop through all the links that need to be toggled.
	if ('aSwapLinks' in this.opt)
	{
		for (var i = 0, n = this.opt.aSwapLinks.length; i < n; i++)
		{
			var oLink = document.getElementById(this.opt.aSwapLinks[i].sId);
			if (typeof(oLink) == 'object' && oLink != null)
				oLink.innerHTML = bCollapse ? this.opt.aSwapLinks[i].msgCollapsed : this.opt.aSwapLinks[i].msgExpanded;
		}
	}

	// Now go through all the sections to be collapsed.
	for (var i = 0, n = this.opt.aSwappableContainers.length; i < n; i++)
	{
		if (this.opt.aSwappableContainers[i] == null)
			continue;

		var oContainer = document.getElementById(this.opt.aSwappableContainers[i]);
		if (typeof(oContainer) == 'object' && oContainer != null)
			oContainer.style.display = bCollapse ? 'none' : '';
	}

	// Update the new state.
	this.bCollapsed = bCollapse;

	// Update the cookie, if desired.
	if ('oCookieOptions' in this.opt && this.opt.oCookieOptions.bUseCookie)
		this.oCookie.set(this.opt.oCookieOptions.sCookieName, this.bCollapsed ? '1' : '0');

	if ('oThemeOptions' in this.opt && this.opt.oThemeOptions.bUseThemeSettings)
		smf_setThemeOption(this.opt.oThemeOptions.sOptionName, this.bCollapsed ? '1' : '0', 'sThemeId' in this.opt.oThemeOptions ? this.opt.oThemeOptions.sThemeId : null, this.opt.oThemeOptions.sSessionId, this.opt.oThemeOptions.sSessionVar, 'sAdditionalVars' in this.opt.oThemeOptions ? this.opt.oThemeOptions.sAdditionalVars : null);
}

smc_Toggle.prototype.toggle = function ()
{
	// Change the state by reversing the current state.
	this.changeState(!this.bCollapsed);
}


function ajax_indicator(turn_on)
{
	if (!ajax_indicator_ele)
	{
		ajax_indicator_ele = document.getElementById('ajax_in_progress');
		if (!ajax_indicator_ele && ajax_notification_text !== null)
			create_ajax_indicator_ele();
	}

	if (ajax_indicator_ele)
	{
		if (is_ie6)
		{
			ajax_indicator_ele.style.position = 'absolute';
			ajax_indicator_ele.style.top = (document.documentElement.scrollTop ? document.documentElement : document.body).scrollTop;
		}
		ajax_indicator_ele.style.display = turn_on ? 'block' : 'none';
	}
}

function create_ajax_indicator_ele()
{
	// Create the div for the indicator.
	ajax_indicator_ele = document.createElement('div');

	// Set the id so it'll load the style properly.
	ajax_indicator_ele.id = 'ajax_in_progress';

	// Add the image in and link to turn it off.
	var cancel_link = document.createElement('a');
	cancel_link.href = 'javascript:ajax_indicator(false)';
	var cancel_img = document.createElement('img');
	cancel_img.src = smf_images_url + '/icons/quick_remove.gif';

	if (ajax_notification_cancel_text)
	{
		cancel_img.alt = ajax_notification_cancel_text;
		cancel_img.title = ajax_notification_cancel_text;
	}

	// Add the cancel link and image to the indicator.
	cancel_link.appendChild(cancel_img);
	ajax_indicator_ele.appendChild(cancel_link);

	// Set the text. (Note: you MUST append here and not overwrite.)
	ajax_indicator_ele.innerHTML += ajax_notification_text;

	// Finally, attach the element to the body.
	document.body.appendChild(ajax_indicator_ele);
}

function createEventListener(oTarget)
{
	if (!('addEventListener' in oTarget))
	{
		if (oTarget.attachEvent)
		{
			oTarget.addEventListener = function (sEvent, funcHandler, bCapture) {
				oTarget.attachEvent('on' + sEvent, funcHandler);
			};
			oTarget.removeEventListener = function (sEvent, funcHandler, bCapture) {
				oTarget.detachEvent('on' + sEvent, funcHandler);
			};
		}
		else
		{
			oTarget.addEventListener = function (sEvent, funcHandler, bCapture) {
				oTarget['on' + sEvent] = funcHandler;
			};
			oTarget.removeEventListener = function (sEvent, funcHandler, bCapture) {
				oTarget['on' + sEvent] = null;
			};
		}
	}
}

// This function will retrieve the contents needed for the jump to boxes.
function grabJumpToContent()
{
	var
		oXMLDoc = getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + 'action=xmlhttp;sa=jumpto;xml'),
		aBoardsAndCategories = new Array();

	ajax_indicator(true);

	if (oXMLDoc.responseXML)
	{
		var items = oXMLDoc.responseXML.getElementsByTagName('smf')[0].getElementsByTagName('item');
		for (var i = 0, n = items.length; i < n; i++)
		{
			aBoardsAndCategories[aBoardsAndCategories.length] = {
				id: parseInt(items[i].getAttribute('id')),
				isCategory: items[i].getAttribute('type') == 'category',
				name: items[i].firstChild.nodeValue.removeEntities(),
				is_current: false,
				childLevel: parseInt(items[i].getAttribute('childlevel'))
			}
		}
	}

	ajax_indicator(false);

	for (var i = 0, n = aJumpTo.length; i < n; i++)
		aJumpTo[i].fillSelect(aBoardsAndCategories);
}

// This'll contain all JumpTo objects on the page.
var aJumpTo = new Array();

// *** JumpTo class.
function JumpTo(oJumpToOptions)
{
	this.opt = oJumpToOptions;
	this.dropdownList = null;
	this.showSelect();
}

// Show the initial select box (onload). Method of the JumpTo class.
JumpTo.prototype.showSelect = function ()
{
	var sChildLevelPrefix = '';
	for (var i = this.opt.iCurBoardChildLevel; i > 0; i--)
		sChildLevelPrefix += this.opt.sBoardChildLevelIndicator;
	document.getElementById(this.opt.sContainerId).innerHTML = this.opt.sJumpToTemplate.replace(/%select_id%/, this.opt.sContainerId + '_select').replace(/%dropdown_list%/, '<select name="' + this.opt.sContainerId + '_select" id="' + this.opt.sContainerId + '_select" ' + ('onbeforeactivate' in document ? 'onbeforeactivate' : 'onfocus') + '="grabJumpToContent();"><option value="?board=' + this.opt.iCurBoardId + '.0">' + sChildLevelPrefix + this.opt.sBoardPrefix + this.opt.sCurBoardName.removeEntities() + '</option></select>&nbsp;<input type="button" value="' + this.opt.sGoButtonLabel + '" onclick="window.location.href = \'' + smf_prepareScriptUrl(smf_scripturl) + 'board=' + this.opt.iCurBoardId + '.0\';" />');
	this.dropdownList = document.getElementById(this.opt.sContainerId + '_select');
}

// Fill the jump to box with entries. Method of the JumpTo class.
JumpTo.prototype.fillSelect = function (aBoardsAndCategories)
{
	// Create an option that'll be above and below the category.
	var oDashOption = document.createElement('option'), iIndexPointer = 0;

	oDashOption.appendChild(document.createTextNode(this.opt.sCatSeparator));
	oDashOption.disabled = 'disabled';
	oDashOption.value = '';

	if ('onbeforeactivate' in document)
		this.dropdownList.onbeforeactivate = null;
	else
		this.dropdownList.onfocus = null;

	// Create a document fragment that'll allowing inserting big parts at once.
	var oListFragment = document.createDocumentFragment();

	// Loop through all items to be added.
	for (var i = 0, n = aBoardsAndCategories.length; i < n; i++)
	{
		var j, sChildLevelPrefix, oOption;

		// If we've reached the currently selected board add all items so far.
		if (!aBoardsAndCategories[i].isCategory && aBoardsAndCategories[i].id == this.opt.iCurBoardId)
		{
			this.dropdownList.insertBefore(oListFragment, this.dropdownList.options[0]);
			oListFragment = document.createDocumentFragment();
			continue;
		}

		if (aBoardsAndCategories[i].isCategory)
			oListFragment.appendChild(oDashOption.cloneNode(true));
		else
			for (j = aBoardsAndCategories[i].childLevel, sChildLevelPrefix = ''; j > 0; j--)
				sChildLevelPrefix += this.opt.sBoardChildLevelIndicator;

		oOption = document.createElement('option');
		oOption.appendChild(document.createTextNode((aBoardsAndCategories[i].isCategory ? this.opt.sCatPrefix : sChildLevelPrefix + this.opt.sBoardPrefix) + aBoardsAndCategories[i].name));
		oOption.value = aBoardsAndCategories[i].isCategory ? '#c' + aBoardsAndCategories[i].id : '?board=' + aBoardsAndCategories[i].id + '.0';
		oListFragment.appendChild(oOption);

		if (aBoardsAndCategories[i].isCategory)
			oListFragment.appendChild(oDashOption.cloneNode(true));
	}

	// Add the remaining items after the currently selected item.
	this.dropdownList.appendChild(oListFragment);

	// Internet Explorer needs this to keep the box dropped down.
	this.dropdownList.style.width = 'auto';
	this.dropdownList.focus();

	// Add an onchange action
	this.dropdownList.onchange = function () {
		if (this.selectedIndex > 0 && this.options[this.selectedIndex].value)
			window.location.href = smf_scripturl + this.options[this.selectedIndex].value.substr(smf_scripturl.indexOf('?') == -1 || this.options[this.selectedIndex].value.substr(0, 1) != '?' ? 0 : 1);
	};
}

// Short function for finding the actual position of an item.
function smf_itemPos(itemHandle)
{
	var itemX = 0, itemY = 0;

	if ('offsetParent' in itemHandle)
	{
		itemX = itemHandle.offsetLeft;
		itemY = itemHandle.offsetTop;
		while (itemHandle.offsetParent && typeof(itemHandle.offsetParent) == 'object')
		{
			itemHandle = itemHandle.offsetParent;
			itemX += itemHandle.offsetLeft;
			itemY += itemHandle.offsetTop;
		}
	}
	else if ('x' in itemHandle)
	{
		itemX = itemHandle.x;
		itemY = itemHandle.y;
	}

	return [itemX, itemY];
}

// This function takes the script URL and prepares it to allow the query string to be appended to it.
// It also replaces the host name with the current one. Which is required for security reasons.
function smf_prepareScriptUrl(sUrl)
{
	var finalUrl = sUrl.indexOf('?') == -1 ? sUrl + '?' : sUrl + (sUrl.charAt(sUrl.length - 1) == '?' || sUrl.charAt(sUrl.length - 1) == '&' || sUrl.charAt(sUrl.length - 1) == ';' ? '' : ';');
	return finalUrl.replace(/:\/\/[^\/]+/g, '://' + window.location.host);
}

var aOnloadEvents = new Array();
function addLoadEvent(fNewOnload)
{
	// If there's no event set, just set this one
	if (typeof(fNewOnload) == 'function' && (!('onload' in window) || typeof(window.onload) != 'function'))
		window.onload = fNewOnload;

	// If there's just one event, setup the array.
	else if (aOnloadEvents.length == 0)
	{
		aOnloadEvents[0] = window.onload;
		aOnloadEvents[1] = fNewOnload;
		window.onload = function () {
			for (var i = 0, n = aOnloadEvents.length; i < n; i++)
			{
				if (typeof(aOnloadEvents[i]) == 'function')
					aOnloadEvents[i]();
				else if (typeof(aOnloadEvents[i]) == 'string')
					eval(aOnloadEvents[i]);
			}
		};
	}

	// This isn't the first event function, add it to the list.
	else
		aOnloadEvents[aOnloadEvents.length] = fNewOnload;
}

// Get the text in a code tag.
function smfSelectText(oCurElement, bActOnElement)
{
	// The place we're looking for is one div up, and next door - if it's auto detect.
	if (typeof(bActOnElement) == 'boolean' && bActOnElement)
		var oCodeArea = document.getElementById(oCurElement);
	else
		var oCodeArea = oCurElement.parentNode.nextSibling;

	if (typeof(oCodeArea) != 'object' || oCodeArea == null)
		return false;

	// Start off with IE
	if ('createTextRange' in document.body)
	{
		var oCurRange = document.body.createTextRange();
		oCurRange.moveToElementText(oCodeArea);
		oCurRange.select();
	}
	// Firefox et al.
	else if (window.getSelection)
	{
		var oCurSelection = window.getSelection();
		// Safari is special!
		if (oCurSelection.setBaseAndExtent)
		{
			var oLastChild = oCodeArea.lastChild;
			oCurSelection.setBaseAndExtent(oCodeArea, 0, oLastChild, 'innerText' in oLastChild ? oLastChild.innerText.length : oLastChild.textContent.length);
		}
		else
		{
			var oCurRange = document.createRange();
			oCurRange.selectNodeContents(oCodeArea);

			oCurSelection.removeAllRanges();
			oCurSelection.addRange(curRange);
		}
	}

	return false;
}

// A function needed to discern HTML entities from non-western characters.
function smc_saveEntities(sFormName, aElementNames, sMask)
{
	if (typeof(sMask) == 'string')
	{
		for (var i = 0, n = document.forms[sFormName].elements.length; i < n; i++)
			if (document.forms[sFormName].elements[i].id.substr(0, sMask.length) == sMask)
				aElementNames[aElementNames.length] = document.forms[sFormName].elements[i].name;
	}

	for (var i = 0, n = aElementNames.length; i < n; i++)
	{
		if (aElementNames[i] in document.forms[sFormName])
			document.forms[sFormName][aElementNames[i]].value = document.forms[sFormName][aElementNames[i]].value.replace(/&#/g, '&#38;#');
	}
}

/**
 *
 * Dropdown menus, Wedge style.
 * � 2008-2010 Ren�-Gilles Deberdt (http://wedgeforum.com)
 * Released under the Wedge license, http://wedgeforum.com/license/
 *
 * Uses portions � 2004 by Batiste Bieler (http://dosimple.ch/), released
 * under the LGPL license (http://www.gnu.org/licenses/lgpl.html)
 *
 */

var baseId = 0, hoverable = 0, rtl = document.dir && document.dir == 'rtl';
var timeoutli = new Array();
var ieshim = new Array();

function initMenu(menu)
{
	menu.style.display = 'block';
	menu.style.visibility = 'visible';
	menu.style.opacity = 1;
	var lis = menu.getElementsByTagName('li');
	var h4s = menu.getElementsByTagName('h4');
	for (var i = 0, j = h4s.length; i < j; i++)
		if (h4s[i].innerHTML.indexOf('<a ') == -1)
			h4s[i].innerHTML = '<a href="#" onclick="hoverable = 1; showMe.call(this.parentNode.parentNode); hoverable = 0; return false;">' + h4s[i].innerHTML + '</a>';

	for (var i = 0, j = lis.length; i < j; i++)
	{
		if (lis[i].getElementsByTagName('ul').length > 0)
		{
			var k = baseId + i;
			lis[i].setAttribute('id', 'li' + k);
			if (is_ie6)
			{
				lis[i].onkeyup = showMe;
				document.write('<iframe src="" id="shim' + k + '" class="iefs" frameborder="0" scrolling="no"></iframe>');
				ieshim[k] = document.getElementById('shim' + k);
			}
			lis[i].onmouseover = showMe;
			lis[i].onmouseout = timeoutHide;
			lis[i].onclick = function () { hideAllUls(menu); };
			lis[i].onblur = timeoutHide;
			lis[i].onfocus = showMe;
		}
	}
	baseId += lis.length;
}

// Hide the first ul element of the current element
function timeoutHide(e)
{
	if (!e) var e = window.event;
	var insitu, targ = e.relatedTarget || e.toElement;
	while (targ && !insitu)
	{
		insitu = targ.parentNode && targ.parentNode.className == 'menu';
		targ = targ.parentNode;
	}
	insitu ? hideUlUnder(this.id) : timeoutli[this.id.substring(2)] = window.setTimeout('hideUlUnder("' + this.id + '")', 242);
}

// Hide the ul elements under the element identified by id
function hideUlUnder(id)
{
	var eid = document.getElementById(id), eids = eid.getElementsByTagName('ul')[0];
	eids.style.visibility = 'hidden';
	eids.style.opacity = 0;
	var h4s = eid.getElementsByTagName('h4');
	if (h4s.length > 0)
		h4s[0].className = '';
	var as = eid.getElementsByTagName('a');
	for (var i = 0, j = as.length; i < j; i++)
		as[i].className = '';
	if (is_ie6)
		showShim(false, id);
}

// Without this, IE6 would show form elements in front of the menu. Bad IE6.
function showShim(showsh, ieid, iemenu)
{
	iem = ieid.substring(2);
	if (!(ieshim[iem]))
		return;
	if (showsh)
	{
		ieshim[iem].style.top = iemenu.offsetTop + iemenu.offsetParent.offsetTop + 'px';
		ieshim[iem].style.left = iemenu.offsetLeft + iemenu.offsetParent.offsetLeft + 'px';
		ieshim[iem].style.width = iemenu.offsetWidth + 'px';
		ieshim[iem].style.height = iemenu.offsetHeight + 'px';
	}
	ieshim[iem].style.display = showsh ? 'block' : 'none';
}

// Show the first ul element found under this element
function showMe(e)
{
	var showul = this.getElementsByTagName('ul')[0];
	if (hoverable)
	{
		if (!e) var e = window.event;
		e.cancelBubble = true;
		if (e.stopPropagation) e.stopPropagation();
		if (showul.style.visibility == 'visible')
			return hideUlUnder(this.id);
	}
	showul.style.visibility = 'visible';
	showul.style.opacity = 1;
	showul.style['margin' + (rtl ? 'Right' : 'Left')] = (this.parentNode.className == 'menu' ? 0 : this.parentNode.clientWidth - 5) + 'px';

	if (is_ie6)
		showShim(true, this.id, showul);
	var h4s = this.getElementsByTagName('h4');
	if (h4s.length > 0)
		h4s[0].className = 'linkOver';
	else
	{
		var currentNode = this;
		while (currentNode)
		{
			if (currentNode.nodeName == 'LI' && currentNode.parentNode.className != 'menu')
				currentNode.getElementsByTagName('a')[0].className = 'linkOver';
			currentNode = currentNode.parentNode;
			if (currentNode.className == 'menu')
				break;
		}
	}
	clearTimeout(timeoutli[this.id.substring(2)]);
	hideAllOthersUls(this);
}

// Hide all ul's on the same level as this list item
function hideAllOthersUls(currentLi)
{
	var lis = currentLi.parentNode;
	for (var i = 0, len = lis.childNodes.length; i < len; i++)
		if (lis.childNodes[i].nodeName == 'LI' && lis.childNodes[i].id != currentLi.id)
			hideUlUnderLi(lis.childNodes[i]);
}

function hideAllUls(menu)
{
	for (var i = 0, len = menu.childNodes.length; i < len; i++)
		if (menu.childNodes[i].nodeName == 'LI')
			hideUlUnderLi(menu.childNodes[i]);
}

// Hide all ul's in the li element
function hideUlUnderLi(li)
{
	var h4s = li.getElementsByTagName('h4');
	if (h4s.length > 0)
		h4s[0].className = '';
	else
	{
		var as = li.getElementsByTagName('a');
		for (var i = 0, j = as.length; i < j; i++)
			as[i].className = '';
	}
	var uls = li.getElementsByTagName('ul');
	for (var i = 0, j = uls.length; i < j; i++)
	{
		uls[i].style.visibility = 'hidden';
		uls[i].style.opacity = 0;
	}
}

/* --------------------------------------------------------
   End of dropdown menu code */

// Moving all data-* protected inline JS to their proper events. Trick and treats!
function clickMagic()
{
	var divs = document.querySelectorAll ? document.querySelectorAll('*[data-onclick], .hitme') : document.getElementsByTagName('*');
	for (var i = 0, j = divs ? divs.length : 0; i < j; i++)
	{
		var div = divs[i], cls = div.className ? div.className : '';

		// In most cases, we only need to set the onclick handler...
		if (cls.indexOf('hitme') == -1)
		{
			div['onclick'] = new Function(div.getAttribute('data-onclick'));
			continue;
		}

		var att = div.attributes, here = [];
		for (var k = 0, m = att.length; k < m; k++)
			if (att[k].name.substr(0, 7) == 'data-on')
				here[k] = att[k].name.substr(5);
		for (var k in here)
			div[here[k]] = new Function(div.getAttribute('data-' + here[k]));
	}
}

// This will add an extra class to any external links.
// Ignored for now because it needs some improvement to the domain name detection.
function linkMagic()
{
	var i, a, hre;
	var n = document.getElementsByTagName('a'), hre, i, a;
	for (i = 0; a = n[i]; i++)
	{
		// Leave a way out to external links.
		if (a.getAttribute('title') == '-')
			continue;

		hre = a.getAttribute('href');
		if (typeof hre == 'string' && hre.length > 0)
		{
			if ((hre.indexOf(window.location.hostname) == -1) && (hre.indexOf('://') != -1))
			{
				a.setAttribute('class', 'xt');
				a.setAttribute('className', 'xt');
			}
		}
	}
}

function testStyle(sty)
{
	var uc = sty.charAt(0).toUpperCase() + sty.substr(1), stys = [ sty, 'Moz'+uc, 'Webkit'+uc, 'Khtml'+uc, 'ms'+uc, 'O'+uc ];
	for (var i in stys) if (wedgerocks.style[stys[i]] !== undefined) return true;
	return false;
}

// Has your browser got the goods?
// These variables aren't used, but you can now use them in your custom scripts.
// In short: if (!can_borderradius) inject_rounded_border_emulation_hack();
var
	wedgerocks = document.createElement('wedgerocks'),
	can_borderradius = testStyle('borderRadius'),
	can_boxshadow = testStyle('boxShadow');

// And now we turn clickme classes into proper onclicks.
// We can do it immediately, because all of the DOM is loaded at this point.
clickMagic();

/* Optimize:
smf_formSubmitted = sfs
ajax_indicator_ele = aie
lastKeepAliveCheck = lka
aOnloadEvents = aoe
alternateWidth = w
alternateHeight = h
previousTarget = p
helpFrame = f
noScrollbars = n
oReplacements = o
sReturn = s
oMyDoc = d
bAsync = b
sUrl = u
sContent = c
funcCallback = f
oCaller = o
sFrom = f
sTo = t
sMatch = m
theField = f
theValue = v
currentNode = n
currentLi = c
sFormName = f
aElementNames = e
sMask = m
oCurSelection = c
oCodeArea = a
oCurElement = e
bActOnElement = t
oCurRange = r
fNewOnload = f
itemHandle = h
aBoardsAndCategories = b
oDashOption = d
sChildLevelPrefix = p
cur_session_id = s
cur_session_var = v
additional_vars = a
oRadioGroup = r
theArray = a
*/
