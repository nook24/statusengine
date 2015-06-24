App.Controllers.ServicesDetailsController = Frontend.AppController.extend({
	
	_initialize: function(){
		
		$('.selectGraphTimespan').click(function(){
			$('.selectGraphTimespan').removeClass('active');
			var $this = $(this);
			var timespan = $this.attr('timespan');
			$('.serviceGraphImg').each(function(index, imgObject){
				var $imgObject = $(imgObject);
				var url = $imgObject.attr('org-src') + '/timespan:' + timespan;
				$imgObject.attr('src', url);
			});
			$this.addClass('active');
		});
	}
});
