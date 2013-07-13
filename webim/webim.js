//custom
(function(webim){
	var path = _IMC.path;
	webim.extend(webim.setting.defaults.data, _IMC.setting );
	var webim = window.webim;

	webim.route( {
		online: path + "im.php?webim_action=online&domain=webim20.cn",
		offline: path + "im.php?webim_action=offline",
		deactivate: path + "im.php?webim_action=refresh",
		message: path + "im.php?webim_action=message",
		presence: path + "im.php?webim_action=presence",
		status: path + "im.php?webim_action=status",
		setting: path + "im.php?webim_action=setting",
		history: path + "im.php?webim_action=history",
		clear: path + "im.php?webim_action=clear_history",
		download: path + "im.php?webim_action=download_history",
		members: path + "im.php?webim_action=members",
		join: path + "im.php?webim_action=join",
		leave: path + "im.php?webim_action=leave",
		buddies: path + "im.php?webim_action=buddies",
		notifications: path + "im.php?webim_action=notifications"
	} );

	webim.ui.emot.init({"dir": path + "static/images/emot/default"});
	var soundUrls = {
		lib: path + "static/assets/sound.swf",
		msg: path + "static/assets/sound/msg.mp3"
	};
	var ui = new webim.ui(document.body, {
		imOptions: {
			jsonp: _IMC.jsonp
		},
		soundUrls: soundUrls
	}), im = ui.im;

	if( _IMC.user ) im.setUser( _IMC.user );
	if( _IMC.menu ) ui.addApp("menu", { "data": _IMC.menu } );
	if( _IMC.enable_shortcut ) ui.layout.addShortcut( _IMC.menu );

	ui.addApp("buddy", {
		is_login: _IMC['is_login'],
		loginOptions: _IMC['login_options']
	} );
	ui.addApp("room");
	ui.addApp("notification");
	ui.addApp("setting", {"data": webim.setting.defaults.data});
	//if( !_IMC.disable_chatlink )ui.addApp("chatlink", { off_link_class: /r_option|spacelink/i });
	ui.render();
	_IMC['is_login'] && im.autoOnline() && im.online();
})(webim);
