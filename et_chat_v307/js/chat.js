/* ##########################################################################
# ET-Chat v3.x.x
# Lizenz: CCPL - http://creativecommons.org/licenses/by-nc/2.0/de/
# Autor: Evgeni Tcherkasski <SEDesign />
# E-mail: info@s-e-d.de
# WWW: http://www.sedesign.de
############################################################################*/

function ET_Chat(){

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// (Start) Deklaration globaler Attribute im Class ------------------------------------------------------------
var self = this;
this.time_last_req=0;						//(privat) Zeit im Milisek seit der letzten AJAX-Anfrage
this.time_last_send=0;						//(privat) Zeit im Milisek seit der letzten Message from User
this.inactivity_message_flag=false;			//(privat) TRUE wenn bereits eine Systemwarnmeldung an den User gesendet wurde
this.active_get_message_req=false;			//(privat) TRUE wenn gerade eine ReloaderMessage Anfrage ueber AJAX laeuft
this.interval_for_inactivity=1800000;		//(public) Wie lange darf der User nichts schreiben bis er aus dem Chat rausfliegt
this.allowed_privates_in_separate_win = true;
this.allowed_privates_in_chat_win = true;
this.anbindung_an_userverwaltung;			//(privat) Wenn die Userverwaltung benutzt wird, soll der Mon kein PW ändern
this.reload_interval;						//(public) Reloadzeit
this.show_history_all_user;					//(public) [bool] Soll die History fuer alle Gezeigt wrden oder nur Admin/Mod-Team
this.allow_nick_registration;				//(public) [bool] Registrierung der Namen erlauben
this.set_sys_messages = true;				//(public) [bool] Show sys messages
this.messages_im_chat;						//(public) Anz. der Mess. im Fenster
this.username="";							//(public)
this.user_id="";							//(public)
this.set_dynamic_height;					//(public)
this.textcolor;								//(public)
this.mouse_top=0;							//(privat) Cursorkoordueberwachung
this.mouse_left=0;							//(privat) Cursorkoordueberwachung
this.win_block = Array();					//(privat) Window-Object-Array zum Userblokieren
this.win_block_ids = Array();			
this.win_private = Array();					//(privat) Window-Object-Array private message window
this.win_admin_user = Array();				//(privat) Window-Object-Array zum Useradministrrieren
this.win_color;								//(privat) Window-Object zum Darstellen der Colorauswahl
this.win_color_content;						//(privat) Inhalt des Colorfensters wird ueber AJAX onLoad gefuellt
this.win_smileys;							//(privat) Window-Object zum Darstellen der Smileys
this.win_smileys_content;					//(privat) Inhalt des Smileysfensters wird ueber AJAX onLoad gefuellt
this.win_style;								//(public) Festlegung der Windowstyle fuer alle Windows im Chat
this.win_prop;								//(privat) Window Zusatzfeatures
this.win_history;							//(privat) Window History
this.jsonObjUserGlobal;						//(privat) Message JSON Array.
this.userPrivilegienGlobal;					//(public) Privilegien z.B.: Gast,User,Moderator,Admin
this.privat_an;								//(privat) Privat an User-ID
this.sound_status="none";					//(privat) Wann soll der Sound kommen, bei allen Messages oder nur Privat
this.soundManager;
this.random_user_number;					//(public) generated user number to protect http GET requests
this.title = document.title;
this.intv_title_blink; 
this.window_focused = true;
// (Stop) Deklaration globaler Attribute im Class -------------------------------------------------------------
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// (Start) Konstruktor der Class ET_Chat wird onLoad ausgefuehrt -----------------------------------------------
this.start = function(){

	if ( Prototype.Browser.IE ) {
		document.onfocusin = function(e) {
			self.window_focused = true;
			try{ window.clearInterval(self.intv_title_blink) } catch (e) {/*nix*/}
			document.title = self.title;
		}
		document.onfocusout = function(e) {
			self.window_focused = false;
		}
	} else {	
		window.onfocus = function(e) {
			self.window_focused = true;
			try{ window.clearInterval(self.intv_title_blink) } catch (e) {/*nix*/}
			document.title = self.title;
		}
		window.onblur = function(e) {
			self.window_focused = false;
		}
	}
					
	// Adminbereich
	if (self.userPrivilegienGlobal=="admin"){
		$("form_right").innerHTML+="&nbsp;&nbsp;&nbsp;<img id=\"link_admin\" class=\"img_button\" src=\"img/admin.png\" width=\"32\" height=\"32\" border=\"0\" alt=\"Admin\" title=\"Admin\">";
    	$("link_admin").onclick = function(){
    	var hoehe = $('chatinhalt').getHeight();
   		var breite = $('chatinhalt').getWidth();
    	var win_admin = new Window({url: "./?AdminIndex", className: self.win_style, width:breite, height:hoehe, top:20, left:10, resizable: true, showEffect:Effect.Appear, hideEffect: Effect.Fade, showEffectOptions: {duration:0.5}, hideEffectOptions: {duration:0.5}, draggable: true, minimizable: true, maximizable: true, destroyOnClose: true });
    	//win_prop.maximize();
    	win_admin.show();
    }
    }

	AjaxReadRequest();	// Erste Messageabfrage beim Start
	setInterval(AjaxReadRequest, this.reload_interval); // Interval fuer regelmaesige Abfragen setzen.

	// autocomplete="off" ist nicht XHTML valide deshalb ueber JS
	$("message").setAttribute("autocomplete", "off");

	// Passt die Hoehe des Chatinhalts immer auf die Hoehe des fensters. Also height 100%
	if(this.set_dynamic_height){
		window_height();
		setInterval(window_height, 200);
	}

	$('message').focus();
	$("message_form").onsubmit = function(){return self.send();} // Wichtig damit beim Submit das Dokument nicht neu geladen wird.
	$("link_sagen").onclick = function(){return self.send();} // s.o. Zeile.

	$("link_prop").onclick = function(){

			// Wenn das Fenster noch nicht existiert, muss es erzeugt und befuellt werden
			if (typeof self.win_prop!="object"){
			// Fensterinstanz
		    self.win_prop = new Window({className: self.win_style, width:260, height:170, top:eval(self.mouse_top-235), left:eval(self.mouse_left-120), resizable: false, showEffect:Effect.Appear, hideEffect: Effect.Fade, showEffectOptions: {duration:0.5}, hideEffectOptions: {duration:0.5}, draggable: true, minimizable: false, maximizable: false, destroyOnClose: false, opacity: 1});

			// History anzeigen?
			if(self.show_history_all_user || self.userPrivilegienGlobal=="admin" || self.userPrivilegienGlobal=="mod")
				var history_link_content = '<div style=\"margin-bottom:3px;\"><img src="img/time.png" align="left" />&nbsp;&nbsp;<a href="#" id="history">'+lang_start_prop_link_2+'</a></div>';
			else
				var history_link_content = '';

			// PW aenderung fuer Mods und User anzeigen?
			if( self.userPrivilegienGlobal=="mod" || self.userPrivilegienGlobal=="user" ){
				var pwchange_link_content = '<div style=\"margin-bottom:3px;\"><img src="img/textfield_key.png" align="left" />&nbsp;&nbsp;<a href="#" id="pwchange_mod">'+lang_start_prop_link_6+'</a>\
											<form id="pwchange_form" style="display:inline;"><div id="pwchange_div" style="display:none;"><div style="padding-top: 2px;">'+lang_start_prop_link_7+' <input type="password" id="pwchange_field" size="6" /> <a href="#" id="pwchange_mod_ok">OK</a></div></div></form></div>';
				if(self.userPrivilegienGlobal=="user")
					pwchange_link_content+='<div style=\"margin-bottom:3px;\"><img src="img/key_delete.png" align="left" />&nbsp;&nbsp;<a href="#" id="unregister_name">'+lang_start_prop_link_10+'</a></div>';
			
			}else if( self.userPrivilegienGlobal=="gast" && self.allow_nick_registration)
				var pwchange_link_content = '<div style=\"margin-bottom:3px;\"><img src="img/textfield_key.png" align="left" />&nbsp;&nbsp;<a href="#" id="register_name">'+lang_start_reg_link+'</a></div>';
			else
				var pwchange_link_content = '';
				
			// Status Auswahl generieren
			var status_link_content ='<div><img src="img/status_online.png" align="left" />&nbsp;&nbsp;<a href="#" id="stat_mod">'+lang_statuslink+'</a>\
									<div style="display:none;" id="stat_change_div"><div style="padding-top: 2px; padding-bottom: 4px; padding-left: 12px;">\n';
			for(var i=0; i<lang_status_text.length; i++){
				if (lang_status_rights[i]=='all') status_link_content +='<img src="img/'+lang_status_imgname[i]+'.png"/> <a href="#" id="'+lang_status_imgname[i]+'">'+lang_status_text[i]+'</a><br />\n';
				if (lang_status_rights[i]=='admin' && self.userPrivilegienGlobal=="admin") status_link_content +='<img src="img/'+lang_status_imgname[i]+'.png"/> <a href="#" id="'+lang_status_imgname[i]+'">'+lang_status_text[i]+'</a><br />\n';
				if (lang_status_rights[i]=='mod' && self.userPrivilegienGlobal=="mod") status_link_content +='<img src="img/'+lang_status_imgname[i]+'.png"/> <a href="#" id="'+lang_status_imgname[i]+'">'+lang_status_text[i]+'</a><br />\n';
			}
			status_link_content +='</div></div><div style=\"margin-bottom:3px;\"></div>';


			var sys_mess_checked = (self.set_sys_messages) ? 'checked' : '';
			
			// Fenster fuellen
			self.win_prop.setHTMLContent('<div id="prop_list"><div style=\"margin-bottom:3px;\"><img src="img/monitor_lightning.png" align="left" />&nbsp;&nbsp;<a href="#" id="cls">'+lang_start_prop_link_1+'</a></div>\
			'+history_link_content+'\
			'+pwchange_link_content+'\
			'+status_link_content+'\
			<div style="padding-bottom:5px;"><form id="set_sys_messages_form" style="display:inline;"><input type="checkbox" id="set_sys_messages" value="1" '+sys_mess_checked+' /> <a href="#" id="set_sys_messages_a"> '+lang_start_prop_link_9+'</a></form></div>\
			<div style=\"margin-bottom:1px;\"><img src="img/sound_'+self.sound_status+'.png" align="left" id="sound_icon" />&nbsp;&nbsp;'+lang_start_prop_link_3+'</div>\
			<div><img src="img/space.gif" align="left" width="16"/>&nbsp;&nbsp;<a href="#" id="sound_on">'+lang_start_prop_link_4+'</a> | <a href="#" id="sound_privat">'+lang_start_prop_link_8+'</a> | <a href="#" id="sound_off">'+lang_start_prop_link_5+'</a></div></div>\
			');
            // Das befuellte Fenster ueberwachen
            Event.observe('prop_list', 'click', function(event){
            	if(Event.element(event).id!="" && Event.element(event).id!='prop_list' && Event.element(event).id!='sound_icon'){
					if (Event.element(event).id=="cls") { $('chatinhalt').innerHTML=''; self.win_prop.close(); $('message').focus(); }
					if (Event.element(event).id=="pwchange_mod_ok") submit_pw();
					if (Event.element(event).id=="history") { self.historyWindow(1); self.win_prop.close(); }
					if (Event.element(event).id=="stat_mod") Effect.toggle('stat_change_div', 'blind', {duration: 0.4});
					if (Event.element(event).id=="set_sys_messages" || Event.element(event).id=="set_sys_messages_a") {
						if (Event.element(event).id=="set_sys_messages_a"){
							if($("set_sys_messages").checked) 
								$("set_sys_messages").checked=false;
							else 
								$("set_sys_messages").checked=true;							
						}
						var sys_mess = ($("set_sys_messages").checked) ? 1 : 0;
						new Ajax.Request("./?ChangeStatus",	{ postBody: "sys_messages="+sys_mess });
						self.win_prop.close(); 
						$('message').focus();
					}
					if (Event.element(event).id.slice(0, 7)=="status_") submit_status(Event.element(event).id, $(Event.element(event).id).innerHTML);
					
					if (Event.element(event).id=="pwchange_mod") {
						Effect.toggle('pwchange_div', 'blind', {duration: 0.4});
						$("pwchange_form").onsubmit = function(){return submit_pw();}
					}
					

					if (Event.element(event).id=="register_name") {
						
						var temp_user_name = (self.username.length > 20) ? self.username.slice(0, 20)+"..." : self.username;
						
						var win_register_user = new Window({className: self.win_style, title:lang_start_reg_title+' "'+temp_user_name+'"', width:250, height:130, top:eval(self.mouse_top-185), left:eval(self.mouse_left-120), resizable: false, showEffect:Effect.Appear, hideEffect: Effect.Fade, showEffectOptions: {duration:0.3, afterFinish:function(effect){self.win_prop.close(); $('pw_register_field').focus(); }}, hideEffectOptions: {duration:0.3}, draggable: true, minimizable: false, maximizable: false, destroyOnClose: true, opacity: 1});
						win_register_user.setHTMLContent('<div id="register_formular"><form id="register_form" style="display:inline;"><div>'+lang_start_reg_befor_registering+'<hr size="1"><table><tr><td>'+lang_start_reg_pw1+'</td><td><input type="password" id="pw_register_field" size="6" /></td></tr><tr><td>'+lang_start_reg_pw2+'</td><td><input type="password" id="pw_register_field2" size="6" /></td></tr><tr><td colspan="2"><br /><a href="#" id="make_register">'+lang_start_reg_button_register+'</a>&nbsp;&nbsp;&nbsp;<a href="#" id="cancel_register">'+lang_start_reg_button_cancel+'</a></td></tr></div></form></div>');
						Event.observe('register_formular', 'click', function(event){
							if(Event.element(event).id=="cancel_register") win_register_user.close();
							if(Event.element(event).id=="make_register") {
							
								if ($('pw_register_field').value==$('pw_register_field2').value)
									new Ajax.Request(
										"./?ChangePw",
										{
										onSuccess: function(result){
											if (result.responseText==1){
												win_register_user.setHTMLContent('<div id="register_formular">'+lang_start_reg_after_registering+'<br /><br /><a href="./?Logout&random_user_number='+self.random_user_number+'&r='+$("room").value+'">'+lang_start_reg_after_registering_link+'</a></div>');
											}
											else alert('Error!\n\n'+result.responseText);
										},
										postBody: "user_pw="+$('pw_register_field').value
										}
									);
								else{
									alert(lang_start_reg_error);
									$('pw_register_field').value='';
									$('pw_register_field2').value='';
									$('pw_register_field').focus();
								}
							}
						});
						win_register_user.show();
						$("register_form").onsubmit = function(){return false;}
					}
					
					
					if (Event.element(event).id=="unregister_name") {
						
						var temp_user_name = (self.username.length > 20) ? self.username.slice(0, 20)+"..." : self.username;
						
						var win_unregister_user = new Window({className: self.win_style, title:lang_remove_pw_win_title+' "'+temp_user_name+'"', width:320, height:180, top:eval(self.mouse_top-225), left:eval(self.mouse_left-170), resizable: false, showEffect:Effect.Appear, hideEffect: Effect.Fade, showEffectOptions: {duration:0.3, afterFinish:function(effect){self.win_prop.close(); $('pw_unregister_field').focus(); }}, hideEffectOptions: {duration:0.3}, draggable: true, minimizable: false, maximizable: false, destroyOnClose: true, opacity: 1});
						win_unregister_user.setHTMLContent('<div id="unregister_formular"><form id="unregister_form" style="display:inline;"><div>'+lang_remove_pw_win_text_1+'<br><br><b>'+lang_remove_pw_win_text_2+'</b><br>'+lang_remove_pw_win_text_3+'<hr size="1"><table><tr><td>'+lang_start_reg_pw1+'</td><td><input type="password" id="pw_unregister_field" size="9" /></td></tr><tr><td colspan="2"><br /><a href="#" id="make_unregister">'+lang_remove_pw_win_button+'</a>&nbsp;&nbsp;&nbsp;<a href="#" id="cancel_unregister">'+lang_start_reg_button_cancel+'</a></td></tr></div></form></div>');
						Event.observe('unregister_formular', 'click', function(event){
							if(Event.element(event).id=="cancel_unregister") win_unregister_user.close();
							if(Event.element(event).id=="make_unregister") {
							
								if (!$('pw_unregister_field').value.empty()){
									var pw_unregister = $('pw_unregister_field').value;
									$("unregister_formular").update('<img src="img/ajax-loader.gif">');
									new Ajax.Request(
										"./?UnregisterPw",
										{
										onSuccess: function(result){
											$("unregister_formular").update('Logout...');
											if (result.responseText==1){
												location.href="./?Logout&random_user_number="+self.random_user_number+"&r="+$("room").value;
											}
											else $("unregister_formular").update(''+result.responseText);
										},
										postBody: "user_pw="+pw_unregister
										}
									);
								}
							}
						});
						win_unregister_user.show();
						$("unregister_form").onsubmit = function(){return false;}
					}
					
					
					
					if (Event.element(event).id=="sound_off") {self.sound_status='none'; $('sound_icon').src="img/sound_none.png"; self.win_prop.close(); $('message').focus(); }
					if (Event.element(event).id=="sound_on") {self.sound_status='all'; $('sound_icon').src="img/sound_all.png"; self.win_prop.close(); $('message').focus(); }
					if (Event.element(event).id=="sound_privat") {self.sound_status='privat'; $('sound_icon').src="img/sound_privat.png"; self.win_prop.close(); $('message').focus(); }
                }
			});
			}

			// ANFANG - Innere Funktionen  im Class Constructor start()  >>> $("link_prop").onclick - Event ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
			//Versenet neues MOD PW
			var submit_pw = function(){
				if ($('pwchange_field').value.length<1) return false;
				new Ajax.Request(
                 		"./?ChangePw",
                		 {
               		  		onSuccess: function(result){
               		  			if (result.responseText==1){
               		  				Effect.toggle('pwchange_div', 'blind', {duration: 0.4});
               		  				self.win_prop.close();
               		  			}
               		  			else alert('Error!\n\n'+result.responseText);
               		  		},
               		  		postBody: "modpw="+$('pwchange_field').value
                		 }
                 );
				return false;
			}


			//Verändert Userstatus
			var submit_status = function(img, text){

				new Ajax.Request(
                 		"./?ChangeStatus",
                		 {
               		  		onSuccess: function(result){
               		  			if (result.responseText==1){
               		  				Effect.toggle('stat_change_div', 'blind', {duration: 0.4});
               		  				self.win_prop.close();
									setTimeout( updateUserOnlineAnzeigeAfterRoomChange ,300);
               		  			}
               		  			else alert('Error!\n\n'+result.responseText);
               		  		},
               		  		postBody: "img="+img+"&text="+text
                		 }
                 );
				return false;
			}
			// ENDE - Innere Funktionen  im Class Constructor start()  >>> $("link_prop").onclick - Event ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

            // Ist dass Fenster bereits sichtbar?
            if($(self.win_prop.getId()).style.display=='none'){
                	self.win_prop.show();
                	self.win_prop.toFront();
            }
            else self.win_prop.close();
	}

	// Logout
	$("link_logout").onclick = function(){
		 location.href="./?Logout&random_user_number="+self.random_user_number+"&r="+$("room").value;
	}

	// MausKoord fuer Fenster und Tooltips
	Event.observe(document, 'mousemove', function(event){
		 self.mouse_left = Event.pointerX(event);
		 self.mouse_top = Event.pointerY(event);
		 });

	// ueberwachung aller Elemente der OnlineList, Chatinhalts,
	// also beim Private, Roomchange oder Usersperren wirds dadurch ausgeloest						
   	Event.observe('onlinelist', 'click', function(event){
						if (Event.element(event).id.slice(0, 10)!="infoblock_")	close_info_win();
                    	// send Private Messages and Change Rooms
                    	changeUserEvent(Event.element(event).id);
                    	} );
    Event.observe('chatinhalt', 'click', function(event){
						if (Event.element(event).id.slice(0, 10)!="inflblock_") close_info_win();
                    	// send Private Messages and Change Rooms
                    	changeUserEvent(Event.element(event).id);
                    	} );
	Event.observe('form', 'click', function(event){
							close_info_win();
                    	} );
	Event.observe('kopf', 'click', function(event){
							close_info_win();
                    	} );
	Event.observe('splitpane', 'click', function(event){
							close_info_win();
                    	} );
    // ueberwachung zum ausschalten der Privatanzeige
	Event.observe('privat_anzeige', 'click', function(event){
                    	if(Event.element(event).id=="close_privat"){
                    		$("privat").value = "0";
							$("privat_anzeige").innerHTML=lang_start_1;
							//$("message").style.backgroundColor="#ffffff";
							$("message").focus();
                    	}
                    	} );

	//Lade Fensterinhalt zum Darstellen des Smileys
	new Ajax.Request("./?Smileys",{onSuccess:function(result){self.win_smileys_content=result.responseText;}});
	//Click auf Smiley-Icon
	$("link_smileys").onclick = function(){ open_close_smileys_win('message'); }

    //Lade Fensterinhalt zum Darstellen des Farbenfensters
	new Ajax.Request("./?Colorizer",{onSuccess:function(result){self.win_color_content=result.responseText;}});

	$('message').style.color = "#"+self.textcolor;

	//Click auf Color-Icon
	$("link_color").onclick = function(){

		// Wenn das Fenster noch nicht existiert, muss es erzeugt und befuellt werden
		if (typeof self.win_color!="object"){
		    self.win_color = new Window({className: self.win_style, width:350, height:205, top:eval(self.mouse_top-265), left:eval(self.mouse_left-180), resizable: false, showEffect:Effect.Appear, hideEffect: Effect.Fade, showEffectOptions: {duration:0.5}, hideEffectOptions: {duration:0.5}, draggable: true, minimizable: false, maximizable: false, destroyOnClose: false, opacity: 1});
			self.win_color.setHTMLContent(self.win_color_content);

			//(Start) Font Art auswaehlen und in hidden-Inputs eintragen --------------------
			$("kursiv").onclick = function(){
				$("italic").value = ($("kursiv").checked) ? "italic" : "normal";
				$('message').style.fontStyle = ($("kursiv").checked) ? "italic" : "normal";
				}
			$("fett").onclick = function(){
				$("bold").value = ($("fett").checked) ? "bold" : "normal";
				$('message').style.fontWeight = ($("fett").checked) ? "bold" : "normal";
				}
			//(Stop) Font Art auswaehlen und in hidden-Inputs eintragen ---------------------

			// Startfarbe des Textes
			var r = self.textcolor.slice(0,2);
			var g = self.textcolor.slice(2,4);
			var b = self.textcolor.slice(4,6);

			//(Start) Init Slider zum RGB-Mischen ------------------------------
			var slider_red = new Control.Slider('handle_red', 'track_red', {
				onSlide: function(v) { /* Nix */ },
				onChange: function(v){
					r = dec2hex(v*255);
					$('message').style.color=$('color').value=$('farbenvorschau').style.backgroundColor="#"+r+g+b;
					}
			});
			var slider_green = new Control.Slider('handle_green', 'track_green', {
				onSlide: function(v) { /* Nix */ },
				onChange: function(v){
					g = dec2hex(v*255);
					$('message').style.color=$('color').value=$('farbenvorschau').style.backgroundColor="#"+r+g+b;
					}
			});
			var slider_blue = new Control.Slider('handle_blue', 'track_blue', {
				onSlide: function(v) { /* Nix */ },
				onChange: function(v){
					b = dec2hex(v*255);
					$('message').style.color=$('color').value=$('farbenvorschau').style.backgroundColor="#"+r+g+b;
				}
			});
           	//(Stop) Init Slider zum RGB-Mischen -------------------------------

			// Slider laut aktueller Farbe stellen
			slider_red.setValue(hex2dec(r)/255);
            slider_green.setValue(hex2dec(g)/255);
            slider_blue.setValue(hex2dec(b)/255);

			// ueberwachung des Klicks auf die Farbentabelle
           	Event.observe('farben_tab', 'click', function(event){
                    	if(Event.element(event).id!=""){
                    		// Farbe Hex in Dec umwandeln
                    		var rd = hex2dec(Event.element(event).id.slice(0, 2));
                    		var gd = hex2dec(Event.element(event).id.slice(2, 4));
                    		var bd = hex2dec(Event.element(event).id.slice(4, 6));
                    		// Slider entsprechend der Auswahl verschieben
                    		slider_red.setValue(rd/255);
                    		slider_green.setValue(gd/255);
                    		slider_blue.setValue(bd/255);
                    		}
                    	} );
            }

            // Ist dass Fenster bereits sichtbar?
            if($(self.win_color.getId()).style.display=='none'){
            	self.win_color.show();
            	self.win_color.toFront();
            }
            else self.win_color.close();


	}
};
// (Stop) Konstruktor der Class ET_Chat wird onLoad ausgefuehrt ------------------------------------------------
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++




var open_close_smileys_win = function(input_field_name){

			// Wenn das Fenster noch nicht existiert, muss es erzeugt und befuellt werden
			if (typeof self.win_smileys!="object"){
				// Fensterinstanz
				self.win_smileys = new Window({className: self.win_style, width:210, height:100, top:eval(self.mouse_top-165), left:eval(self.mouse_left-120), resizable: false, showEffect:Effect.Appear, hideEffect: Effect.Fade, showEffectOptions: {duration:0.5}, hideEffectOptions: {duration:0.5}, draggable: true, minimizable: false, maximizable: false, destroyOnClose: false, opacity: 1});
				// Fenster fuellen
				self.win_smileys.setHTMLContent('<div id="smileys_list">'+self.win_smileys_content+'</div>');
			} else {
				if($(self.win_smileys.getId()).style.display=='none')
					self.win_smileys.setLocation(eval(self.mouse_top-165), eval(self.mouse_left-120))
			}

			try {Event.stopObserving('smileys_list', 'click');} catch(e){}
			
			// Das befuellte Fenster ueberwachen
            Event.observe('smileys_list', 'click', function(event){
            	if(Event.element(event).id!="" && Event.element(event).id!='smileys_list'){
											// Nach Auswahl des Smileys einfach Fenster schliessen
                                           self.win_smileys.close();
                                           $(input_field_name).value +=Event.element(event).id;
                                           $(input_field_name).focus();
                                           
                }
			});

            // Ist dass Fenster bereits sichtbar?
            if($(self.win_smileys.getId()).style.display=='none'){
                	self.win_smileys.show();
                	self.win_smileys.toFront();
            }
            else self.win_smileys.close();
}			
			
			




// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// (Start) Schliesse alle info wins ------------------------------------------------------
var close_info_win = function(dont_close_id){
	// remove win_block if open
	self.win_block_ids.each(function(uid) {

			if ($(self.win_block[uid].getId()).visible() && self.win_block[uid].getId()!=dont_close_id) self.win_block[uid].close();

	});
}
// (Stop) Schliesse alle info wins -------------------------------------------------------
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// (Start) Das Schreibfeld nach Versand wieder freigeben ------------------------------------------------------
var let_write = function(){
    $('message').disabled=false
	$('message').value = "";
	$('message').focus();
};
// (Stop) Das Schreibfeld nach Versand wieder freigeben -------------------------------------------------------
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// (Start) Zum Ausdehnen auf height 100% ----------------------------------------------------------------------
var window_height = function(){

	var flag_innerHeight=false;

	if (window.innerHeight) {

		flag_innerHeight=true;

    	if($('chatinhalt').style.height != eval(window.innerHeight-150)+"px"){
			$('chatinhalt').style.height = eval(window.innerHeight-150)+"px";
			$('onlinelist').style.height = eval(window.innerHeight-150)+"px";
			$('splitpane').style.height = eval(window.innerHeight-150)+"px";
			//$('chatinhalt').innerHTML+="FF_Opera_Safari_win_opt<br>";
			}
    }
    else
    if (document.documentElement && document.documentElement.clientWidth) {
  	try{
  		if($('chatinhalt').style.height != eval(document.documentElement.clientHeight-150)+"px"){
    		$('chatinhalt').style.height = eval(document.documentElement.clientHeight-150)+"px";
    		$('onlinelist').style.height = eval(document.documentElement.clientHeight-150)+"px";
    		$('splitpane').style.height = eval(document.documentElement.clientHeight-150)+"px";
    		//$('chatinhalt').innerHTML+="IE7_6_win_opt<br>";
    	}
    	else return 0;
    } catch(e){ return 0; }
  	} else {
      	return 0;
  	}
};
// (Stop) Zum Ausdehnen auf height 100% -----------------------------------------------------------------------
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// (Start) Draging von Splitpane ------------------------------------------------------------------------------
this.dragSplitpane=function(){

	var Offset;
	var wi;

	var dragspliter=new Draggable('splitpane',{
    constraint: 'horizontal',
    zindex: 0,
    snap: function(x,y) {
    	var v=Offset - $('splitpane').offsetLeft;
    	v=eval(v)+ wi;
    	if(v <= 0 && x > 0) return[0,y];
      	return[x,y]
    },
    onStart: function() {
    	Offset = $('splitpane').offsetLeft;
		wi = eval($('onlinelist').getStyle('width').replace(/px/g, ''));
    },
    onDrag: function() {
    	var verschiebung=Offset - $('splitpane').offsetLeft;
    	verschiebung=eval(verschiebung)+ wi;
    	if(verschiebung <= 0) verschiebung=0;
		$('onlinelist').setStyle({width: verschiebung+'px'});
    },
    onEnd: function() {
		$('splitpane').setStyle({left: '0px'});
    },
    revert:false
	});
}
// (Stop) Draging von Splitpane -------------------------------------------------------------------------------
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// (Start) Hierhin wird das Erg. der AJAx Abfrage nach neuen Messages im Chat uebertragen und dargestellt -----
var receiveResultJSON = function(ajaxResultJSON) {

	// Die Uebertragung via AJAX ist beendet, weitere Anfragen duerfen gestartet werden
	self.active_get_message_req=false;
	
	// uebertragenes als Text klassifizieren
    var jsonInhalt = ajaxResultJSON.responseText;

	// Wenn UserIP in der Blacklist, wird der User rausgeworfen.
	if (jsonInhalt=='blacklist'){
		location.href="./?AfterBlacklistInsertion";
	}
	// Wenn UserID in der Kicklist, wird der User rausgeworfen.
	if (jsonInhalt=='kick'){
		location.href="./?AfterKicklistInsertion";
	}
	// Wenn Spammer erkannt, wird der User rausgeworfen.
	if (jsonInhalt=='spam'){
		location.href="./?AfterSpamlistInsertion";
	}

    // Wenn neue Datensaetze in der DB vorhanden sind, ist ein Inhalt vorhanden
    if (jsonInhalt!=''){

		// Erzeugt JSON-Literal-Object aus dem Text
        var jsonObj = jsonInhalt.evalJSON();

		// SoundFlag damit der Sound nur gespielt wird wenn dies eine fremde Message ist.
		var play_sound=false;

		
		var win_id1 = new Array();
		var count_priv_win = 0;
		
		// Alle Elemende des uebertragenen MessageArrays durchlaufen und ausgeben
      	for (var i=0; i<jsonObj.data.length; i++){
        	with(jsonObj.data[i]){
				
        		// Jede neue Message wird in ein neu erzeugtes DIV verpackt.
        		// Dies ist wichtig da sonst das Grow-Effect sich mit dem innerHTML ueberschneiden und dadurch der
        		// Effekt nicht vollstaendig abgearbeitet wird. Follge: Messages nicht Sichtbar oder nur zur Haelfte.
				if (!id.empty()){
					var newDIV = new Element('div', { id : "a"+id });
					$('chatinhalt').appendChild(newDIV);
				}
				
				// Das oben angelegte DIV mit Inhalt auf dem JSON-Object befuellen
				if (sex=="m") var gender_icon="<img src=\"img/user_"+sex+".png\" align=\"absbottom\" />";
                if (sex=="f") var gender_icon="<img src=\"img/user_"+sex+".png\" align=\"absbottom\" />";
                if (sex=="n") var gender_icon="<img src=\"img/user_"+sex+".png\" align=\"absbottom\" />";
				if (user_id==1) var gender_icon="";
						
				if (priv =="admin") var priv_icon="<img src=\"img/"+priv+"_i_small.png\" align=\"absbottom\" title=\""+priv+"\" alt=\""+priv+"\" />";
				else if (priv =="mod") var priv_icon="<img src=\"img/"+priv+"_i_small.png\" align=\"absbottom\" title=\""+priv+"\" alt=\""+priv+"\" />";
				else var priv_icon="";
        		if (privat=='0'){
					if (user_id==self.user_id) var userstyle_clickable ="";
					else var userstyle_clickable = "id=\"inflblock_"+user_id+"\" style=\"cursor:pointer\"";

					$("a"+id).innerHTML = "<div class=\"mess_back\"><i>("+time+")</i> "+gender_icon+priv_icon+" <b "+userstyle_clickable+">"+user+"</b>: <span style=\""+css+"\">"+message+"</span></div>";
					
				}else{
					if (!id.empty()){
						if (user_id==self.user_id)
							$("a"+id).innerHTML = "<div class=\"privat_ausg_von\"><i>("+time+")</i> "+gender_icon+priv_icon+" <b>"+user+"</b> <i>("+lang_receiveResultJSON_1+" "+self.privat_an+")</i>: <span style=\""+css+"\">"+message+"</span></div>";
						else
							$("a"+id).innerHTML = "<div class=\"privat_ausg_an\"><i>("+time+")</i> "+gender_icon+priv_icon+" <b id=\"inflblock_"+user_id+"\" style=\"cursor:pointer\">"+user+"</b> <i>("+lang_receiveResultJSON_2+")</i>: <span style=\""+css+"\">"+message+"</span></div>";
					}
					else{
						// privat win
						win_id1[count_priv_win]=user_id+'00000'+privat;
						var win_id2=privat+'00000'+user_id;
					
						if (typeof self.win_private[win_id1[count_priv_win]]!="object" && typeof self.win_private[win_id2]!="object"){
						
							//window:kskdskd
							if (user_id==self.user_id) { var privat_win_opponent = self.privat_an; var privat_win_opponent_id = privat;}
							else { var privat_win_opponent = user; var privat_win_opponent_id = user_id;}
							
							self.win_private[win_id1[count_priv_win]] = new Window({className: self.win_style, title: "Privat mit "+privat_win_opponent,  width:380, height:200, top:eval(50 + Math.round(Math.random()*50)), left:eval(50 + Math.round(Math.random()*50)), resizable: true, showEffect:Effect.Appear, hideEffect: Effect.Fade, showEffectOptions: {duration:0.5, afterFinish:function(effect){ $('pivate_win_'+int_id).scrollTop = $('pivate_win_'+int_id).scrollHeight }}, hideEffectOptions: {duration:0.5}, draggable: true, minimizable: false, maximizable: false, destroyOnClose: false, opacity: 1});
							self.win_private[win_id1[count_priv_win]].setHTMLContent('<div id="pivate_win_'+win_id1[count_priv_win]+'" class="privat_mesages_window"></div><div><form style="display:inline" id="win_form_'+win_id1[count_priv_win]+'"><table cellspacing="0" cellpadding="0"><tr><td><input type="hidden" id="this_win_'+self.win_private[win_id1[count_priv_win]].getId()+'" value="'+win_id1[count_priv_win]+'"><input type="text" class="private_message_field" id="message_win_'+win_id1[count_priv_win]+'" ></td><td><img src="img/Checked_small.png" id="submit_img_'+win_id1[count_priv_win]+'" style="padding-left: 4px; cursor:pointer;"></td><td><img src="img/Smiley_small.png" id="smileys_img_'+win_id1[count_priv_win]+'" style="padding-left: 4px; cursor:pointer;"></td></tr></table></form></div></div>');

							// Set up a windows observer, check ou debug window to get messages
							myObserver = {
								onEndResize: function(eventName, win) {
								//alert(win.getId());
								//if (win == self.win_private[win_id1[count_priv_win]]) 
									$("pivate_win_"+$F('this_win_'+win.getId())).style.height=eval(win.getSize().height - 33)+"px";								
								},
								onClose: function(eventName, win) { $("message").focus(); }
							}
							Windows.addObserver(myObserver);
							
							$('win_form_'+win_id1[count_priv_win]).onsubmit = function(e) {
								var tid = this.id.replace('win_form_','message_win_');
								self.send2privatwin(tid, user_id);
								return false;
							};
							$('submit_img_'+win_id1[count_priv_win]).onclick = function(e) {
								var tid = this.id.replace('submit_img_','message_win_');
								self.send2privatwin(tid, user_id);
								return false;
							};
							$("smileys_img_"+win_id1[count_priv_win]).onclick = function(e){
								var tid = this.id.replace('smileys_img_','message_win_');
								open_close_smileys_win(tid); 
							}
							$('message_win_'+win_id1[count_priv_win]).onfocus = function(e) {
								this.style.color = $('message').style.color;
								this.style.fontWeight = $('message').style.fontWeight;
								this.style.fontStyle = $('message').style.fontStyle;				
							}
						}
						
						var int_id = (typeof self.win_private[win_id1[count_priv_win]]=="object") ? win_id1[count_priv_win] : win_id2;
						$('pivate_win_'+int_id).innerHTML+="<div>"+gender_icon+priv_icon+" <b>"+user+"</b>: <span style=\""+css+"\">"+message+"</span></div>";

						try{self.win_private[int_id].show()} catch(w){}
						
						count_priv_win++;
					}
				}
                // Zuerst das neue DIV verstecken um es spaeter mit Effekt zu visualisieren
                if (!id.empty()) Element.hide('a'+id);
				
                // Hier wird festgestellt ob die angekommenen Messages alle von dem Benutzer sind. Damit der Sound nur abgespielt wird wenn
                // ein Anderer eine Message gesenden hat.
                if (user_id!=self.user_id) {
					if (self.sound_status=="all") {
						play_sound=true;
						sound_file = (privat!='0') ? "sound_privat" : "sound_all";		
					}
					if (self.sound_status=="privat" && privat!='0') {
						play_sound=true;
						sound_file = "sound_privat";
					}
					if (self.sound_status=="none") 
						play_sound=false;
						
					// Aktiviere Blink im Title
					if(!self.window_focused && play_sound){
						try{ window.clearInterval(self.intv_title_blink) } catch (e) {/*nix*/}
						self.intv_title_blink = window.setInterval(function () {
							document.title = (document.title == lang_titleAlert) ? self.title : lang_titleAlert;
						}, 1000);
					}
				}

        	}
        }

		// sound ---------------------------
		//alert(self.sound_status+ ' '+ play_sound);
        if(play_sound){ //Sound.play('sound/sound.mid',{replace:false});
			self.soundManager.play('mySound_'+sound_file,'sound/'+sound_file+'.mp3');
		}
		// Alle erzeugten neuen Datensaetze der Rheie nach visualisieren
		// Effect.Grow
		// Effect.SlideDown
 		for (var i=0; i<jsonObj.data.length; i++) 
			if (!jsonObj.data[i].id.empty())
				new Effect.Grow('a'+jsonObj.data[i].id,
					{
					duration: 0.5,
					afterFinish:function(effect){ 
					$('chatinhalt').scrollTop = $('chatinhalt').scrollHeight; 
					}
				});

        // Die alten DIVs mit Effekt verstecken. Je nach Anz. Mess. im Chat
    	for (var i=0; i<jsonObj.data.length; i++){
       		div_id = eval(jsonObj.data[i].id - self.messages_im_chat);
			
       		if (div_id>0){
       			Effect.SwitchOff( 'a'+div_id, {
       			duration: 0.3,
       			afterFinish:function(effect){
       			// Versuch IE6 zum Loeschen zu bewegen, der ist etwas langsam. (unklar warum)
      			try{
       			if ($('a'+eval(div_id-jsonObj.data.length)).style.display!="none")
       				$('a'+eval(div_id-jsonObj.data.length)).style.display="none";
       			if ($('a'+eval((div_id-jsonObj.data.length)-1)).style.display!="none")
       				$('a'+eval((div_id-jsonObj.data.length)-1)).style.display="none";
       			if ($('a'+eval((div_id-jsonObj.data.length)-2)).style.display!="none")
       				$('a'+eval((div_id-jsonObj.data.length)-2)).style.display="none";
       			}
       			catch(e){}
       			}
       			});
       		}
		}
    }
};
// (Stop) Hierhin wird das Erg. der AJAX Abfrage nach neuen Messages im Chat uebertragen und dargestellt -------
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// (Start) Anfrage an den Reloader wird im Interval this.reload_interval aufgerufen ---------------------------
var AjaxReadRequest = function(){

		var AktuellesDatum=new Date();
		var time_now = Date.parse(AktuellesDatum);

		// Wenn gerade eingetroffen
		if(self.time_last_send==0) self.time_last_send=time_now;

		// wenn kein Admin || Mod
		if (self.userPrivilegienGlobal!="admin" && self.userPrivilegienGlobal!="mod"){
			// Pruefung wie lange der User schweigt und Meldung
			if(!self.inactivity_message_flag && time_now - self.time_last_send > self.interval_for_inactivity - 30000) {
				self.sendSysMessages ( $("room").value, lang_AjaxReadRequest_1, self.user_id);
				self.inactivity_message_flag=true;
			};
			// Pruefung wie lange der User schweigt und rauswurf
			if(time_now - self.time_last_send > self.interval_for_inactivity) location.href="./?Logout&random_user_number="+self.random_user_number+"&reason=timeout&r="+$("room").value;
		}

		// Dies verhindert dass nach einem DatenversendeRequest die Schleife sofort einen Datenholrequest
		// veranlasst. Spart etwas an Traffic und an Serverlast
		if ((time_now - self.time_last_req) > eval(self.reload_interval/2)){

			self.time_last_req = time_now;

			// wenn keine AJAX-Anfrage aktiv ist, kann eine ausgeloest werden, sonst warten
			if(!self.active_get_message_req){
				self.active_get_message_req = true;
			
				var myAjaxObj= new Ajax.Request(
					"./?ReloaderMessages",
					{
					onSuccess: receiveResultJSON,
					onFailure: function() { self.active_get_message_req = false; },
					postBody: "room="+$("room").value+"&privat="+encodeURIComponent($("privat").value)
					}
				);
			}
		}
};
// (Stop) Anfrage an den Reloader wird im Interval this.reload_interval aufgerufen ----------------------------
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++



// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// (Start) Anfrage an den Reloader mit Datenuebergabe vom User, also beim Sender der Massage in den Chat -------
this.send = function()
{
		var AktuellesDatum=new Date();
		this.time_last_req = this.time_last_send = Date.parse(AktuellesDatum);
		// Damit in Falle einer zu langen Inaktivitaet der User informiert wird.
		// Ohme einen Flag bekommt er staendige Mlendungen siehe Zeile 373
		this.inactivity_message_flag=false;

		
		//check, ob der User online ist
		if($F("privat")>0 && !self.userOnlineNow($F("privat"))){
			var win_warning_user_away = new Window({className: self.win_style, title: lang_warning_user_away_2_1, width:280, height:70, resizable: false, showEffect:Effect.Appear, hideEffect: Effect.Fade, showEffectOptions: {duration:0.5}, hideEffectOptions: {duration:0.5}, draggable: true, minimizable: false, maximizable: false, destroyOnClose: true, opacity: 1});
			win_warning_user_away.setHTMLContent("<div style=\"padding:3px;\"><span><img src=\"img/messagebox_warning.png\" style=\"padding-right:10px;\" width=\"22\" height=\"22\" align=\"left\" />"+lang_warning_user_away_2_2+"</span></div>");
			win_warning_user_away.showCenter();
			setTimeout(function(){ try{win_warning_user_away.close();} catch(e){/*nix */} }, 10000);
		}
		
		
		/*
		try{
			if (!$('privat_modus').value.empty())
				$('message').value = $('privat_modus').value + $('message').value;
        } catch(e){}
		*/
		
		var myAjaxObj= new Ajax.Request(
                 "./?ReloaderMessages",
                 {
                 onSuccess: function(erg) {
					// Das Schreibfeld nach Versand wieder freigeben und noch 0,3 Sek abwarten, wirkt besser. ;-)
					if ($('message').disabled) setTimeout(let_write, 300);
					
					receiveResultJSON(erg);
				},
                 postBody: $("message_form").serialize()
                 }
		);

		// Nach Versand Feld deaktivieren
         $('message').value = lang_send_1;
         $('message').blur();
         $('message').disabled = true;
	return false;
};
// (Stop) Anfrage an den Reloader mit Datenuebergabe vom User, also beim Sender der Massage in den Chat --------
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++




// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// (Start) Anfrage an den Reloader mit Datenuebergabe vom User, nur für PrivatWin -------
this.send2privatwin = function(message_input_field, privat)
{
	
		//check, ob der User online ist
		if(!self.userOnlineNow(privat)){
			var win_p = message_input_field.replace(/message_win_/g, 'pivate_win_');
			$(win_p).innerHTML+="<div class=\"privat_ausg_an\" style=\"padding:3px;\"><span><b>"+lang_warning_user_away_1_1+"</b><br />"+lang_warning_user_away_1_2+"</span></div>";
		}
		
		var AktuellesDatum=new Date();
		this.time_last_req = this.time_last_send = Date.parse(AktuellesDatum);
		// Damit in Falle einer zu langen Inaktivitaet der User informiert wird.
		// Ohme einen Flag bekommt er staendige Mlendungen siehe Zeile 373
		this.inactivity_message_flag=false;

		if($(message_input_field).disabled) return false;
		
		var message = "/window: " + $(message_input_field).value; 
		
		$(message_input_field).value='';
		
		message = decodeURIComponent((message + '').replace(/\&/g, '%26').replace(/\+/g, '%2B').replace(/\%/g, '%25'));
		
		var myAjaxObj= new Ajax.Request(
                "./?ReloaderMessages",
                {
                onSuccess: function(erg) {
					if ($(message_input_field).disabled) setTimeout(function(){
						$(message_input_field).disabled = false;
						$(message_input_field).value = '';
						$(message_input_field).focus();	
					}, 300);
					
					receiveResultJSON(erg);
				},
                postBody: "room="+$('room').value+"&message="+message+"&privat="+privat+"&bold="+$('bold').value+"&italic="+$('italic').value+"&color="+$('color').value
                }
		);
		
		// Nach Versand Feld deaktivieren
         $(message_input_field).value = lang_send_1;
         $(message_input_field).blur();
         $(message_input_field).disabled = true;
		 
	return false;
};
// (Stop) Anfrage an den Reloader mit Datenuebergabe vom User, nur für PrivatWin -------
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// (Start) Ist dieser User in der aktuellen OnlineList? -------------------------------------------------------
this.userOnlineNow = function (user_id){

	for (var i=0; i < self.jsonObjUserGlobal.userOnline.length; i++)
	   if (self.jsonObjUserGlobal.userOnline[i].user_id==user_id && self.jsonObjUserGlobal.userOnline[i].user_simg!="status_invisible")
			return true;
	
	return false;

}
// (Start) Ist dieser User in der aktuellen OnlineList? -------------------------------------------------------
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++



// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// (Start) Anfrage an den Reloader mit Datenuebergabe vom System -----------------------------------------------
this.sendSysMessages = function(room, inhalt, privat, roomChange)
{
		 if(!privat) privat = 0;
		 if(!roomChange) roomChange = false;
         var myAjaxObj= new Ajax.Request(
                 "./?ReloaderMessages",
                 {
                 onSuccess: receiveResultJSON,
                 postBody: "room="+room+"&sysmess=1&privat="+privat+"&roomchange="+roomChange+"&message="+encodeURIComponent(inhalt)
                 }
		);
};
// (Stop) Anfrage an den Reloader mit Datenuebergabe vom System ------------------------------------------------
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// (Start) UseronlineUpdater - uebertragung der Chatrooms und Onlineuser im Interval ---------------------------
this.userOnline = function()
{
         new Ajax.PeriodicalUpdater(
         		 "",
                 "./?ReloaderUserOnline",
                 {
                 onSuccess: updateUserOnlineAnzeige,
                 postBody: "reloadsequenz="+this.reload_interval,
                 frequency: eval(this.reload_interval*0.004)
                 }
		);
	return false;
};
// (Stop) UseronlineUpdater - uebertragung der Chatrooms und Onlineuser im Interval ----------------------------
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// (Start) Historywindow --------------------------------------------------------------------------------------
this.historyWindow = function(seite){
	// Wenn das Fenster noch nicht existiert, muss es erzeugt und befuellt werden
	if (typeof self.win_history!="object"){
	// Fensterinstanz
	var hoehe = $('chatinhalt').getHeight();
   	var breite = $('chatinhalt').getWidth();
	self.win_history = new Window({className: self.win_style,width:eval(breite-10), title: lang_historyWindow_1, height:eval(hoehe-10), top:20, left:20, resizable: true, showEffect:Effect.Appear, hideEffect: Effect.Fade, showEffectOptions: {duration:0.5}, hideEffectOptions: {duration:0.5}, draggable: true, minimizable: false, maximizable: true, destroyOnClose: false});
	self.win_history.setHTMLContent('<div id="history_content">Loading...</div>');
	}
	self.win_history.show();
	self.win_history.toFront();

	//RaumID auslesen falls bereits vorhanden
	var raumID='';
	try{ raumID = $("raum_in_history").value; } catch(e) {/*nix*/}

	new Ajax.Request("./?History",
	{
         onSuccess:function(result){
         	$("history_content").innerHTML=result.responseText;
			$("site_selecter").onchange = function(){ self.historyWindow($("site_selecter").value); }
         	Event.observe('history_seiten', 'click', function(event){
         				if (Event.element(event).id.slice(0, 12)=="historysite_"){
         					Event.stop(event);
                    		self.historyWindow(Event.element(event).id.slice(12, Event.element(event).id.length));
                    		}
                    	if (Event.element(event).id=="export_excel"){
         					window.open('./?HistoryExport&format=xls&roomid='+raumID);
                    		}
                    	if (Event.element(event).id=="export_csv"){
         					window.open('./?HistoryExport&format=csv&roomid='+raumID);
                    		}
                    	if (Event.element(event).id=="export_xml"){
         					window.open('./?HistoryExport&format=xml&roomid='+raumID);
                    		}
                    	} );
            Event.observe('raum_in_history', 'change', function(event){
            			Event.stop(event);
            			self.historyWindow(1);
            			} );
         	},
         postBody: "site="+seite+"&roomid="+raumID
    });
}
// (Stop) Historywindow  --------------------------------------------------------------------------------------
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++



//#####################################################################################
// UserOnline Anzeige
var updateUserOnlineAnzeige = function(ajaxResultJSON) {

    var aktuelle_room_id;

  	// Hier wird der Array des Updaters ausgewertet
	var jsonObj = self.jsonObjUserGlobal = ajaxResultJSON.responseText.evalJSON();
	if(jsonObj==0){
		//setTimeout( updateUserOnlineAnzeigeAfterRoomChange ,1000);
	}
	else{
				var anzahl_der_user_im_chat=0;
				// aktuelle Rechte aus der DB Erhalten und als Globaler Wert festlegen
				for (var i=0; i<jsonObj.userOnline.length; i++){
				    if (jsonObj.userOnline[i].user_id==self.user_id) self.userPrivilegienGlobal = jsonObj.userOnline[i].user_priv;
				    if (jsonObj.userOnline[i].user_simg!='status_invisible') anzahl_der_user_im_chat++;
				    }

					var inner_html ="<b>"+lang_updateUserOnlineAnzeige_1+"</b><br />";
            		inner_html +=lang_updateUserOnlineAnzeige_2+" "+ anzahl_der_user_im_chat+"<br/><br/>";

                 	for (var i=0; i<jsonObj.userOnline.length; i++){
                                if (aktuelle_room_id != jsonObj.userOnline[i].room_id)
                                {
									aktuelle_room_id = jsonObj.userOnline[i].room_id;
									
									var anz_im_room=0;
									for (var ii=0; ii<jsonObj.userOnline.length; ii++){
										if (aktuelle_room_id == jsonObj.userOnline[ii].room_id && jsonObj.userOnline[ii].user_simg!='status_invisible') 
											anz_im_room++;
									}
									
									var show_anz_pro_room = (anz_im_room>0) ? " ("+anz_im_room+")" : "";
									
									
									var allowed = jsonObj.userOnline[i].room_allowed;
									if (allowed==1) inner_html+="<div class=\"rooms\" id=\"room_"+aktuelle_room_id+"\">"+jsonObj.userOnline[i].room+show_anz_pro_room+"</div>";
									else{
										if (allowed==2) 
											inner_html+="<div class=\"rooms_not_allowed\" id=\"pwroom_"+aktuelle_room_id+"\">"+jsonObj.userOnline[i].room+show_anz_pro_room+"<img id=\"pwroomimg_"+aktuelle_room_id+"\" src=\"img/keylayer.png\" width=\"11\" height=\"11\" style=\"margin-left:5px;\" /></div>";
										else 
											inner_html+="<div class=\"rooms_not_allowed\" id=\"notallowedroom_"+aktuelle_room_id+"\">"+jsonObj.userOnline[i].room+show_anz_pro_room+"<img id=\"notallowedroomimg_"+aktuelle_room_id+"\" src=\"img/locklayer.png\" width=\"11\" height=\"10\" style=\"margin-left:5px;\" /></div>";
									}
                                }

                                 // Wenn admin
                                 var admin_user = (self.userPrivilegienGlobal=="admin" || self.userPrivilegienGlobal=="mod") ? " <img src=\"img/wand.png\" id=\"adminu_"+jsonObj.userOnline[i].user_id+"\" /> " : "";
                                 if ((self.userPrivilegienGlobal=="admin" && self.user_id==jsonObj.userOnline[i].user_id)||(self.userPrivilegienGlobal=="mod" && self.user_id==jsonObj.userOnline[i].user_id)) admin_user = " <img src=\"img/space.gif\" weight=\"16\" height=\"16\" /> ";
                                 //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                                 var usr_priv="";
                                 if (jsonObj.userOnline[i].user_priv=="admin") usr_priv=" <img src=\"img/admin_i.png\" alt=\"admin\" title=\"admin\">";
                                 if (jsonObj.userOnline[i].user_priv=="mod") usr_priv=" <img src=\"img/mod_i.png\" alt=\"mod\" title=\"mod\">";
                                 // -----------------

								 //status ----
								 if (!jsonObj.userOnline[i].user_simg.empty()) var user_status_anz = " <img src=\"img/"+jsonObj.userOnline[i].user_simg+".png\" alt=\""+jsonObj.userOnline[i].user_stext+"\" title=\""+jsonObj.userOnline[i].user_stext+"\">";
								 else var user_status_anz = "";

							     //hide User
							     if (!jsonObj.userOnline[i].user_simg.empty() && jsonObj.userOnline[i].user_simg=='status_invisible') var user_style="display:none;";
							     else var user_style="";
								 
								 // strike user
								if (jsonObj.userOnline[i].user.slice(0, 8) == '<strike>'){
										jsonObj.userOnline[i].user = jsonObj.userOnline[i].user.slice(8, jsonObj.userOnline[i].user.length);
										var user_style2="text-decoration: line-through;";
								}else var user_style2="";

                                 if (self.user_id==jsonObj.userOnline[i].user_id){
                                 	if (jsonObj.userOnline[i].user_sex=="m") var gender_icon="user_comment_m_self.png";
                                 	if (jsonObj.userOnline[i].user_sex=="f") var gender_icon="user_comment_w_self.png";
                                 	if (jsonObj.userOnline[i].user_sex=="n") var gender_icon="user_comment_n_self.png";
                                 	inner_html+="<div id=\"user_div_"+jsonObj.userOnline[i].user_id+"\" style=\""+user_style+"\">\
													<div style=\"float:left;\">\
														<img src=\"img/"+gender_icon+"\" width=\"16\" height=\"16\" /> \
														<span id=\"user_"+jsonObj.userOnline[i].user_id+"\"><b>"+admin_user+jsonObj.userOnline[i].user+"</b></span>\
													</div>\
													<div style=\"float:right; margin-right: 2px;\">"+usr_priv+user_status_anz+"</div>\
													<div style=\"clear:both\"></div>\
												</div>";
                                 }
                                 else{
                                 	if (jsonObj.userOnline[i].user_sex=="m") var gender_icon="user_comment_m.png";
                                 	if (jsonObj.userOnline[i].user_sex=="f") var gender_icon="user_comment_w.png";
                                 	if (jsonObj.userOnline[i].user_sex=="n") var gender_icon="user_comment_n.png";
                                 	inner_html+="<div id=\"user_div_"+jsonObj.userOnline[i].user_id+"\" style=\""+user_style+"\">\
													<div style=\"float:left;\">\
														<img src=\"img/"+gender_icon+"\" id=\"user_"+jsonObj.userOnline[i].user_id+"\" style=\"cursor:pointer\" title=\""+lang_updateUserOnlineAnzeige_3+"\" alt=\""+lang_updateUserOnlineAnzeige_3+"\" />\
														<span id=\"infoblock_"+jsonObj.userOnline[i].user_id+"\" style=\""+user_style2+"cursor:pointer\" >"+admin_user+jsonObj.userOnline[i].user+"</span>\
													</div>\
													<div style=\"float:right; margin-right: 2px;\">"+usr_priv+user_status_anz+"</div>\
													<div style=\"clear:both\"></div>\
												</div>";
                                 }
                                 if (self.user_id==jsonObj.userOnline[i].user_id) var aktuell_room="room_"+aktuelle_room_id;

        			}

					try{
        			for (var i=0; i<jsonObj.all_empty_rooms.length; i++)
        				if (jsonObj.all_empty_rooms[i].room_allowed==1) inner_html+="<div class=\"rooms\" id=\"room_"+jsonObj.all_empty_rooms[i].room_id+"\">"+jsonObj.all_empty_rooms[i].room+"</div>";
        				else {
        					if (jsonObj.all_empty_rooms[i].room_allowed==2) inner_html+="<div class=\"rooms_not_allowed\" id=\"pwroom_"+jsonObj.all_empty_rooms[i].room_id+"\">"+jsonObj.all_empty_rooms[i].room+"<img id=\"pwroomimg_"+jsonObj.all_empty_rooms[i].room_id+"\" src=\"img/keylayer.png\" width=\"11\" height=\"11\" style=\"margin-left:5px;\" /></div>";
        					else inner_html+="<div class=\"rooms_not_allowed\" id=\"notallowedroom_"+jsonObj.all_empty_rooms[i].room_id+"\">"+jsonObj.all_empty_rooms[i].room+"<img id=\"notallowedroomimg_"+jsonObj.all_empty_rooms[i].room_id+"\" src=\"img/locklayer.png\" width=\"11\" height=\"10\" style=\"margin-left:5px;\" /></div>";
        				}
        			} catch(e){/*nix*/}

				$("onlinelist").innerHTML = inner_html;
				$(aktuell_room).style.fontWeight="bold";

    }
}


var changeUserEvent = function(ereignis){

//##############################################################################################################
	if (ereignis.slice(0, 7)=="privat_") {

		$("privat").value = ereignis.slice(7, ereignis.length);


		// Hier ird anhand der User_ID der entsprechende Username aus dem JSON Resultarray herausgefischt
		for (var i=0; i < self.jsonObjUserGlobal.userOnline.length; i++)
			if (self.jsonObjUserGlobal.userOnline[i].user_id==$("privat").value)
				self.privat_an = self.jsonObjUserGlobal.userOnline[i].user;
				
		// Privat window
		var user_name_p = (self.privat_an.length > 20) ? self.privat_an.slice(0, 20)+"..." : self.privat_an;
		
		$("privat_anzeige").innerHTML=lang_changeUserEvent_privat_1+" <b>"+user_name_p+"</b>&nbsp;&nbsp;&nbsp;<span id=\"close_privat\">"+lang_changeUserEvent_privat_2+"</span>";

		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		new Effect.Highlight("message",{startcolor:"#ff6666"});
		$("message").focus();

		//alert(self.jsonObjUserGlobal.userOnline[0].room);
	}
	
	
	
//##############################################################################################################	
	
	
	
	if (ereignis.slice(0, 10)=="privatwin_") {

		var privat2id = ereignis.slice(10, ereignis.length);
		
		// Hier ird anhand der User_ID der entsprechende Username aus dem JSON Resultarray herausgefischt
		for (var i=0; i < self.jsonObjUserGlobal.userOnline.length; i++)
			if (self.jsonObjUserGlobal.userOnline[i].user_id==privat2id)
				var privat2name = self.jsonObjUserGlobal.userOnline[i].user;
				
		// Privat window
		//var user_name_p = (privat2name.length > 20) ? privat2name.slice(0, 20)+"..." : privat2name;

		var win_id1=self.user_id+'00000'+privat2id;
		var win_id2=privat2id+'00000'+self.user_id;
		if (typeof self.win_private[win_id1]!="object" && typeof self.win_private[win_id2]!="object"){

							var privat_win_opponent = privat2name; 
							var privat_win_opponent_id = privat2id;

							self.win_private[win_id1] = new Window({className: self.win_style, title: "Privat mit "+privat_win_opponent,  width:380, height:200, top:eval(50 + Math.round(Math.random()*50)), left:eval(50 + Math.round(Math.random()*50)), resizable: true, showEffect:Effect.Appear, hideEffect: Effect.Fade, showEffectOptions: {duration:0.5, afterFinish:function(effect){ $('pivate_win_'+int_id).scrollTop = $('pivate_win_'+int_id).scrollHeight}}, hideEffectOptions: {duration:0.5}, draggable: true, minimizable: false, maximizable: false, destroyOnClose: false, opacity: 1});
							self.win_private[win_id1].setHTMLContent('<div id="pivate_win_'+win_id1+'" class="privat_mesages_window"></div><div><form style="display:inline" id="win_form_'+win_id1+'"><table cellspacing="0" cellpadding="0"><tr><td><input type="text" class="private_message_field" id="message_win_'+win_id1+'" ></td><td><img src="img/Checked_small.png" id="submit_img_'+win_id1+'" style="padding-left: 4px; cursor:pointer;"></td><td><img src="img/Smiley_small.png" id="smileys_img_'+win_id1+'" style="padding-left: 4px; cursor:pointer;"></td></tr></table></form></div></div>');

							// Set up a windows observer, check ou debug window to get messages
							myObserver = {
								onEndResize: function(eventName, win) {
								if (win == self.win_private[win_id1]) 
									$("pivate_win_"+win_id1).style.height=eval(win.getSize().height - 33)+"px";								
								},
								onClose: function(eventName, win) { $("message").focus(); }
							}
							Windows.addObserver(myObserver);
							
			$('win_form_'+win_id1).onsubmit = function() {
				self.send2privatwin('message_win_'+win_id1, privat2id);
				return false;
			}	
			$('submit_img_'+win_id1).onclick = function() {
				self.send2privatwin('message_win_'+win_id1, privat2id);
				return false;
			}
			$("smileys_img_"+win_id1).onclick = function(){
				open_close_smileys_win('message_win_'+win_id1); 
			}
			$('message_win_'+win_id1).onfocus = function() {
				$('message_win_'+win_id1).style.color = $('message').style.color;
				$('message_win_'+win_id1).style.fontWeight = $('message').style.fontWeight;
				$('message_win_'+win_id1).style.fontStyle = $('message').style.fontStyle;				
			}	
		}

		var int_id = (typeof self.win_private[win_id1]=="object") ? win_id1 : win_id2;
		try{self.win_private[int_id].show()} catch(w){}	
		
		// $('message_win_'+win_id1).focus() war zuerst in win_private[int_id].showEffectOptions.afterFinisch, 
		// jedoch wird das immer nach win_private[int_id].show() ausgefuehrt. D.h. jedes mal nach einer Message und das geht nicht.
		// Loesung einfach separat ein Timeout setzen mit 600ms laenger als Effekt Duration-Time von 0.5s
		setTimeout(function(){ try{$('message_win_'+win_id1).focus();} catch(e){} }, 600);
		
	}
	
//##############################################################################################################
	if (ereignis.slice(0, 5)=="room_"){

		if ($("room").value==ereignis.slice(5, ereignis.length)) return false;
		$('chatinhalt').innerHTML='';
		$("room").value=ereignis.slice(5, ereignis.length);

	   	// Hier ird anhand der Room_ID der entsprechende Roomname aus dem JSON Resultarray herausgefischt
		for (var i=0; i < self.jsonObjUserGlobal.userOnline.length; i++)
			if (self.jsonObjUserGlobal.userOnline[i].room_id==ereignis.slice(5, ereignis.length)){
       			var roomName = self.jsonObjUserGlobal.userOnline[i].room;
         	}

		// try weil wenn es keine freien Raeume gibt, entsteht error
        try{
        for (var i=0; i < self.jsonObjUserGlobal.all_empty_rooms.length; i++)
			if (self.jsonObjUserGlobal.all_empty_rooms[i].room_id==ereignis.slice(5, ereignis.length)){
         		var roomName = self.jsonObjUserGlobal.all_empty_rooms[i].room;
         	}
        } catch(e){ /* nix */ }

        self.sendSysMessages ( $("room").value, lang_changeUserEvent_room_1, 0, true);
		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

         setTimeout( updateUserOnlineAnzeigeAfterRoomChange ,700);
	}
//##############################################################################################################
// User Blockieren. ANFANG
	if (ereignis.slice(0, 10)=="infoblock_" || ereignis.slice(0, 10)=="inflblock_"){

		if (ereignis.slice(0, 10)=="inflblock_"){
			var pos_top = eval(self.mouse_top-25);
			var pos_left = eval(self.mouse_left+10);
		}
		if (ereignis.slice(0, 10)=="infoblock_"){
			var pos_top = eval(self.mouse_top-10);
			var pos_left = eval(self.mouse_left-280);
		}
		
       // Hier ird anhand der User_ID der entsprechende Username aus dem JSON Resultarray herausgefischt
	   for (var i=0; i < self.jsonObjUserGlobal.userOnline.length; i++)
	       if (self.jsonObjUserGlobal.userOnline[i].user_id==ereignis.slice(10, ereignis.length)){
                   var user_name = self.jsonObjUserGlobal.userOnline[i].user;
				   var user_priv = self.jsonObjUserGlobal.userOnline[i].user_priv;
                }
				
			// kein User in der Onlinelist mehr vorhanden
			if (user_name==undefined) return false;

			var id = ereignis.slice(10, ereignis.length);

			if (typeof self.win_block[id]!="object"){
			
			if (user_name.length > 20) user_name = user_name.slice(0, 20)+"...";
			
            self.win_block[id] = new Window({className: self.win_style, title:lang_changeUserEvent_infoblock_1+' '+user_name, width:250, height:90, top:pos_top, left:pos_left, resizable: false, showEffect:Effect.Appear, hideEffect: Effect.Fade, showEffectOptions: {duration:0.4, afterFinish:function(effect){ close_info_win(self.win_block[id].getId()) }}, hideEffectOptions: {duration:0.4}, draggable: true, minimizable: false, maximizable: false, destroyOnClose: false, opacity: 1});
            
			var disabled_block = (user_priv=='admin' || user_priv=='mod') ? 'disabled' : '';
			
			var separate_win_privat_menue = (self.allowed_privates_in_separate_win) ? '<img src="img/privat_win.png" align="left" />&nbsp;&nbsp;<a href="#" id="info_privatf_'+id+'" >'+lang_changeUserEvent_infoblock_4+'</a><br />' : '';
			var chat_privat_menue = (self.allowed_privates_in_chat_win) ? '<img src="img/privat_chat.png" align="left" />&nbsp;&nbsp;<a href="#" id="info_privatm_'+id+'" >'+lang_changeUserEvent_infoblock_3+'</a><br />' : '';
			var block_option4privat = (!self.allowed_privates_in_separate_win && !self.allowed_privates_in_chat_win) ? '' : '<input type="Checkbox" id="blokiere_user_priv_'+id+'" '+disabled_block+'> '+lang_changeUserEvent_infoblock_7;
			
			self.win_block[id].setHTMLContent('\
			<img src="img/set_name.png" align="left" />&nbsp;&nbsp;<a href="#" id="info_set_name_'+id+'" >'+lang_changeUserEvent_infoblock_2+'</a><br />\
			'+chat_privat_menue+'\
			'+separate_win_privat_menue+'\
			<img src="img/delete.png" align="left" />&nbsp;&nbsp;<a href="#" id="info_blockform_'+id+'" >'+lang_changeUserEvent_infoblock_5+'</a>\
			<div id=\"block_form_div_'+id+'\" style="display:none;margin-top:2px;"><form name="user_block_'+id+'" style="display:inline;" >\
			<input type="Checkbox" id="blokiere_user_all_'+id+'" '+disabled_block+'> '+lang_changeUserEvent_infoblock_6+'&nbsp;&nbsp;&nbsp;\
			'+block_option4privat+'</form></div>');
			
			var make_ajax_request_of_blocking = true;

			// Sonst hat IE Problemmen mit "Luecken" im Array
			self.win_block_ids.push(id);
			
           	}else{
				self.win_block[id].setLocation(pos_top, pos_left);
				var make_ajax_request_of_blocking = false;
			}		
            self.win_block[id].show();
			self.win_block[id].toFront();


			$("info_set_name_"+id).onclick = function(){
				$('block_form_div_'+id).hide();
				changeUserEvent('user_'+id);
				self.win_block[id].close();
			}
			
			if((self.allowed_privates_in_chat_win))
				$("info_privatm_"+id).onclick = function(){
					$('block_form_div_'+id).hide();
					changeUserEvent('privat_'+id);
					self.win_block[id].close();
				}
			
			if((self.allowed_privates_in_separate_win))
				$("info_privatf_"+id).onclick = function(){
					$('block_form_div_'+id).hide();
					changeUserEvent('privatwin_'+id);
					self.win_block[id].close();
				}
			
			$("info_blockform_"+id).onclick = function(){
				$('block_form_div_'+id).toggle();
				
				// Ist leider noetig im falle einer Aktualisierung der Seite (F5). Sonst ist es nicht eindeutig, was zufor bereits ausgewaehlt wurde.
				if (make_ajax_request_of_blocking)
					new Ajax.Request(
                		"./?BlockUser",
						{
               			onSuccess: function(result) {
               				if (result.responseText == "all") $("blokiere_user_all_"+ereignis.slice(10, ereignis.length)).checked=true;
               				else $("blokiere_user_all_"+ereignis.slice(10, ereignis.length)).checked=false;
               				if (result.responseText == "priv") $("blokiere_user_priv_"+ereignis.slice(10, ereignis.length)).checked=true;
               				else $("blokiere_user_priv_"+ereignis.slice(10, ereignis.length)).checked=false;
               			},
               			postBody: "show="+ereignis.slice(10, ereignis.length)
                		}
					);
					make_ajax_request_of_blocking = false;
			}
			
            $("blokiere_user_all_"+id).onclick = function(){
            	try { $("blokiere_user_priv_"+id).checked=false; } catch(e){}

                 new Ajax.Request(
                 		"./?BlockUser",
                		 {
               		  	onSuccess: function(){ $('block_form_div_'+id).hide(); self.win_block[id].close(); updateUserOnlineAnzeigeAfterRoomChange(); },
               		  	postBody: "block_all="+id
                		 }
				);
			}

			if((self.allowed_privates_in_separate_win || self.allowed_privates_in_chat_win))
				$("blokiere_user_priv_"+id).onclick = function(){
					$("blokiere_user_all_"+id).checked=false;

					new Ajax.Request(
                 		"./?BlockUser",
                		 {
               		  	onSuccess: function(){ $('block_form_div_'+id).hide(); self.win_block[id].close(); updateUserOnlineAnzeigeAfterRoomChange(); },
               		  	postBody: "block_priv="+id
                		 }
					);
				}
         }
// User Blokieren. ENDE
//##############################################################################################################
	// Wenn der User zu anministrieren ist.
	if (ereignis.slice(0, 7)=="adminu_"){

	            // Hier ird anhand der User_ID der entsprechende Username aus dem JSON Resultarray herausgefischt
	   for (var i=0; i < self.jsonObjUserGlobal.userOnline.length; i++)
	       if (self.jsonObjUserGlobal.userOnline[i].user_id==ereignis.slice(7, ereignis.length)){
                   var user_ip = self.jsonObjUserGlobal.userOnline[i].user_ip;
                   var user_name = self.jsonObjUserGlobal.userOnline[i].user;
                   var user_priv = self.jsonObjUserGlobal.userOnline[i].user_priv;
                }

		var id = ereignis.slice(7, ereignis.length);

			if (typeof self.win_admin_user[id]!="object"){
            self.win_admin_user[id] = new Window({className: self.win_style,  width:350, height:180, top:eval(self.mouse_top-10), left:eval(self.mouse_left-390), resizable: false, showEffect:Effect.Appear, hideEffect: Effect.Fade, showEffectOptions: {duration:0.5}, hideEffectOptions: {duration:0.5}, draggable: true, minimizable: false, maximizable: false, destroyOnClose: false});
        	}
        	if (user_priv!='admin' && user_priv!='mod')
            	self.win_admin_user[id].setHTMLContent('<b>'+user_name+'</b><br /><i>'+user_ip+'</i><br /><br />'+lang_changeUserEvent_adminu_1+'<form>\
            	<select id="blacklist_time" size="1">\
				<option value="0">'+lang_changeUserEvent_adminu_opt_1+'</option>\
				<option value="600">'+lang_changeUserEvent_adminu_opt_2+'</option>\
				<option value="1800">'+lang_changeUserEvent_adminu_opt_3+'</option>\
				<option value="3600">'+lang_changeUserEvent_adminu_opt_4+'</option>\
				<option value="10800">'+lang_changeUserEvent_adminu_opt_5+'</option>\
				<option value="86400">'+lang_changeUserEvent_adminu_opt_6+'</option>\
				<option value="604800">'+lang_changeUserEvent_adminu_opt_7+'</option>\
				<option value="94348800">'+lang_changeUserEvent_adminu_opt_8+'</option>\
				</select> &nbsp;&nbsp;&nbsp;<a href="#" id="sperre_user_'+ereignis.slice(7, ereignis.length)+'">'+lang_changeUserEvent_adminu_2+'</a></form>');
			else
				self.win_admin_user[id].setHTMLContent('<b>'+user_name+'</b> '+lang_changeUserEvent_adminu_3+' ' + user_priv + ' '+lang_changeUserEvent_adminu_4);

            self.win_admin_user[id].show();


			try{
			$("sperre_user_"+id).onclick = function(){
			 new Ajax.Request(
                 		"./?Insert2Blacklist",
                		 {
               		  		onSuccess: function(){ self.win_admin_user[id].close() },
               		  		postBody: "user_id="+id+"&time="+$("blacklist_time").value
                		 }
                 );
			}
			}
			catch(e){/* nix */}

	}
	
//##############################################################################################################	
	if (ereignis.slice(0, 9)=="smilchat_") {
		$('message').value += ereignis.slice(9, ereignis.length);
		$('message').focus();
	}
//##############################################################################################################
	if (ereignis.slice(0, 5)=="user_" || ereignis.slice(0, 5)=="usch_"){

		for (var i=0; i < self.jsonObjUserGlobal.userOnline.length; i++)
	       if (self.jsonObjUserGlobal.userOnline[i].user_id==ereignis.slice(5, ereignis.length))
              var user_name = self.jsonObjUserGlobal.userOnline[i].user;
        	  $("message").value += user_name.unescapeHTML();
        	  $("message").focus();
		}
//##############################################################################################################
	if (ereignis.slice(0, 15)=="notallowedroom_" || ereignis.slice(0, 18)=="notallowedroomimg_") {

		var win_allowed = new Window({className: self.win_style,  width:240, height:80, top:eval(self.mouse_top-10), left:eval(self.mouse_left-280), resizable: false, showEffect:Effect.Appear, hideEffect: Effect.Fade, showEffectOptions: {duration:0.5}, hideEffectOptions: {duration:0.5}, draggable: true, minimizable: false, maximizable: false, destroyOnClose: true, opacity: 1});
        win_allowed.setHTMLContent(lang_changeUserEvent_notallowedroom_1+'<br><br><div style="width:90px;cursor:pointer;border:1px solid black;text-align:center;" id="close_room_warning">'+lang_changeUserEvent_notallowedroom_2+'</div>');
        win_allowed.show();

        try{
			$("close_room_warning").onclick = function(){
				win_allowed.close();
			}
		}catch(e){/* nix */}
	}
//##############################################################################################################

	if (ereignis.slice(0, 7)=="pwroom_" || ereignis.slice(0, 10)=="pwroomimg_") {

        var room_id_now = (ereignis.slice(0, 7)=="pwroom_") ? ereignis.slice(7, ereignis.length) : ereignis.slice(10, ereignis.length);

	   	// Hier ird anhand der Room_ID der entsprechende Roomname aus dem JSON Resultarray herausgefischt
	   	for (var i=0; i < self.jsonObjUserGlobal.userOnline.length; i++)
			if (self.jsonObjUserGlobal.userOnline[i].room_id==room_id_now)
       			var roomName = self.jsonObjUserGlobal.userOnline[i].room;

		// try weil wenn es keine freien Raeume gibt, entsteht error
		try{
		for (var i=0; i < self.jsonObjUserGlobal.all_empty_rooms.length; i++)
			if (self.jsonObjUserGlobal.all_empty_rooms[i].room_id==room_id_now)
				var roomName = self.jsonObjUserGlobal.all_empty_rooms[i].room;
		} catch(e){ /* nix */ }

		var win_laypw = new Window({className: self.win_style, title: roomName, width:240, height:100, top:eval(self.mouse_top-10), left:eval(self.mouse_left-280), resizable: false, showEffect:Effect.Appear, hideEffect: Effect.Fade, showEffectOptions: {duration:0.5, afterFinish:function(){$('layerpw').focus();}}, hideEffectOptions: {duration:0.5}, draggable: true, minimizable: false, maximizable: false, destroyOnClose: true, opacity: 1});
        win_laypw.setHTMLContent('<form id="layerpw_form">'+lang_changeUserEvent_pwroom_1+' <div id="pwroom_error"></div><br /><input type="password" name="layerpw" id="layerpw" style="float:left" />'+'<div style="width:60px;cursor:pointer;border:1px solid black;text-align:center;float:left; margin:2px;" id="send_room_pw">'+lang_changeUserEvent_notallowedroom_2+'</div></form>');
        win_laypw.show();

		$('pwroom_error').innerHTML = "";

        try{
			$("send_room_pw").onclick = function(){
				check_room_pw(win_laypw, room_id_now, roomName);
			}
			$("layerpw_form").onsubmit = function(){
				check_room_pw(win_laypw, room_id_now, roomName);
				return false;
			}
		}catch(e){/* nix */}
	}
//##############################################################################################################

}

var check_room_pw = function(win_laypw,room_id_now, roomName){
				new Ajax.Request(
                 		"./?CheckRoomPw",
                		 {
               		  		onSuccess: function(result){
               		  			if (result.responseText==1){
               		  				win_laypw.close();

               		  				$('chatinhalt').innerHTML='';
									$("room").value=room_id_now;

               		  				self.sendSysMessages ( $("room").value, lang_changeUserEvent_room_1, 0, true );
               		  				setTimeout(updateUserOnlineAnzeigeAfterRoomChange, 400);
               		  			}
               		  			else{$('pwroom_error').innerHTML = "<b>"+lang_changeUserEvent_pwroom_2+"</b>";}
               		  		},
               		  		postBody: "layerpw="+$('layerpw').value+"&roomid="+room_id_now
                		 }
                 );
}



var updateUserOnlineAnzeigeAfterRoomChange = function(){
new Ajax.Request(
                 		"./?ReloaderUserOnline",
                		 {
               		  	onSuccess: updateUserOnlineAnzeige,
               		  	postBody: "reloadsequenz="+self.reload_interval
                		 }
    		)
	}

//###############################################################################################################
// Little helpers
var dec2hex = function(n){
    n = parseInt(n); var c = 'ABCDEF';
    var b = n / 16; var r = n % 16; b = b-(r/16);
    b = ((b>=0) && (b<=9)) ? b : c.charAt(b-10);
    return ((r>=0) && (r<=9)) ? b+''+r : b+''+c.charAt(r-10);
}
var hex2dec = function(n) { return parseInt( n, 16 ); }

}

//#####################################################################################
//############## END ##################################################################
//#####################################################################################