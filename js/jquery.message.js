/*!
 * jQuery Message
 * version: 0.4 (25-Nov-2016)
 * Update: 30.10.2010  - default "status: info", Doc eingerichtet
 * Update: 25.02.2011  - "nowrap" Zeilenumbruch verhindern
 * Update: 30.09.2016  - Umstellung auf semantic-ui, Icon als settingwert verfügbar
 * @requires jQuery v1.3.2 or later
 *
 * Examples and documentation at: http://www.ssi-source.org/
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Examples : $('#ProzessBarBox').message({ status:'info', title: 'Profil wurde gespeichert!' });
 *
 * ---------------------------------------------------------------------------------------------------
 * Settings:
 * ---------------------------------------------------------------------------------------------------
 */

jQuery.fn.message = function(settings) {
	message(settings)
}


function message(settings) {
	settings = jQuery.extend({ title : "Gespeichert", delay : 10000, text : '', icon : 'info', }, settings);

	var title = settings.title;
	var text = settings.text;
	var delay = settings.delay;
	var icon = settings.icon;
	var type = settings.type;

	//$('.toast-container').hide();
	$('body').toast({title: title, message: text, position: 'top center', class: icon });
	
}
