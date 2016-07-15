App.Components.AjaxComponent = Frontend.Component.extend({
	url: null,
	currentUrl: null,

	setup: function(url, currentUrl){
		this.url = url;
		this.currentUrl = currentUrl;
	},

	externalcommand: function(data){
		var self = this;
		$.ajax({
			url: this.url + '.json',
			type: 'post',
			data: data,
			dataType: 'json',
			error: function(){},
			success: function(){},
			complete: function(response){
				if($(window).scrollTop() < 250){
					$('#nonFixed').show();
				}else{
					$('#fixed').show();
				}
				var $counter = $('.externalcommand-counter');
				setInterval(function(){
					var value = parseInt($counter.html(), 10);
					if(value == 0){
						window.location.href = self.currentUrl;
					}
					value--;
					$counter.html(value);
				}, 1000);
			}.bind(self)
		});
	}

});
