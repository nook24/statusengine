App.Controllers.PnpIndexController = Frontend.AppController.extend({
	
	_initialize: function(){
		$('.pnpFrame').css('height', $(window).innerHeight()+'px');
	}
});
