App.Controllers.HostsDetailsController = Frontend.AppController.extend({
	components: ['Ajax'],
	_initialize: function(){
		this.Ajax.setup(this.getVar('url'));
		var self = this;
		
		$('#reschedule').click(function(){
			var data = {
				commandId: 2,
				type: 'host',
				objectId: self.getVar('hostObjectId')
			};
			self.Ajax.externalcommand(data);
		});
		
		$('#rescheduleServices').click(function(){
			var data = {
				commandId: 3,
				type: 'host',
				objectId: self.getVar('hostObjectId')
			};
			self.Ajax.externalcommand(data);
		});
		
		$('#submitPassiveResult').click(function(){
			var state = parseInt($('#PassiveResultState').val(), 10);
			var data = {
				commandId: 7,
				type: 'host',
				objectId: self.getVar('hostObjectId'),
				state: state,
				output: $('#PassiveResultOutput').val()
			};
			self.Ajax.externalcommand(data);
		});
		
		$('#submitCustomNotify').click(function(){
			var options = 0;
			var isBroadcast = $('#CustomNotifyBroadcast').prop('checked');
			var isForced = $('#CustomNotifyForced').prop('checked');
			
			if(isBroadcast){
				options = 1;
			}
			if(isForced){
				options = 2;
			}
			if(isBroadcast && isForced){
				options = 3;
			}
			var data = {
				commandId: 8,
				type: 'host',
				objectId: self.getVar('hostObjectId'),
				options: options,
				comment: $('#CustomNotifyComment').val()
			};
			self.Ajax.externalcommand(data);
		});
		
		$('#submitSetAck').click(function(){
			var sticky = 0;
			if($('#SetAckSticky').prop('checked')){
				sticky = 1;
			}
			var data = {
				commandId: 9,
				type: 'host',
				objectId: self.getVar('hostObjectId'),
				sticky: sticky,
				comment: $('#SetAckComment').val()
			};
			self.Ajax.externalcommand(data);
		});
	}
});
