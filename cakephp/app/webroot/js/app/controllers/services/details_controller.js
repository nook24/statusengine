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
		
		$('#submitPassiveResult').click(function(){
			var state = parseInt($('#PassiveResultState').val(), 10);
			var data = {
				commandId: 4,
				type: 'service',
				objectId: self.getVar('serviceObjectId'),
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
				commandId: 5,
				type: 'service',
				objectId: self.getVar('serviceObjectId'),
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
				commandId: 6,
				type: 'service',
				objectId: self.getVar('serviceObjectId'),
				sticky: sticky,
				comment: $('#SetAckComment').val()
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
