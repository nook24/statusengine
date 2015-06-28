App.Controllers.ServicesDetailsController = Frontend.AppController.extend({
	
	components: ['Ajax'],
	
	_initialize: function(){
		this.Ajax.setup(this.getVar('url'));
		var self = this;
		
		$('#reschedule').click(function(){
			var data = {
				commandId: 1,
				type: 'service',
				objectId: self.getVar('serviceObjectId')
			};
			self.Ajax.externalcommand(data);
		});
		
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
