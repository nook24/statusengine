App.Controllers.UsersLoginController = Frontend.AppController.extend({

	_initialize: function(){
		$('#background-image').backgrounder({element : 'body'});
		if($(document).innerWidth() > 768){
			$('.login-box').css('top', Math.round($(document).innerHeight()/2 - $('.login-box').height() / 2)+'px');
		}
	}
});
