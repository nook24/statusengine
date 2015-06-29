App.Components.AjaxComponent = Frontend.Component.extend({
	url: null,
	
	setup: function(url){
		this.url = url;
	},
	
	externalcommand: function(data){
		$.ajax({
			url: this.url + '.json',
			type: 'post',
			data: data,
			dataType: 'json',
			error: function(){},
			success: function(){},
			complete: function(response){
				var $externalcommand = $('.externalcommand');
				$externalcommand.show();
				var $counter = $externalcommand.find('span');
				setInterval(function(){
					var value = parseInt($counter.html(), 10);
					if(value == 0){
						location.reload();
					}
					value--;
					$counter.html(value);
				}, 1000);
			}
		});
	}
	
});
