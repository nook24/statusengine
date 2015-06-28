App.Controllers.HostsDetailsController = Frontend.AppController.extend({
	components: ['Ajax'],
	_initialize: function(){
		this.Ajax.setup(this.getVar('url'));
		var self = this;
		
		$('#reschedule').click(function(){
			var data = {
				commandId: 2,
				type: 'host',
				objectId: self.getVar('hostObectId')
			};
			self.Ajax.externalcommand(data);
		});
		
		$('#rescheduleServices').click(function(){
			var data = {
				commandId: 3,
				type: 'host',
				objectId: self.getVar('hostObectId')
			};
			self.Ajax.externalcommand(data);
		});
	}
});
