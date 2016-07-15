App.Controllers.ServicesDetailsController = Frontend.AppController.extend({

	$nonFixed: null,
	$extFixed: null,

	components: ['Ajax'],

	_initialize: function(){
		this.Ajax.setup(this.getVar('url'), this.getVar('currentUrl'));
		var self = this;

		this.$nonFixed = $('#nonFixed');
		this.$extFixed = $('#fixed');

		//width fix
		this.$extFixed.width(this.$nonFixed.width()+'px');
		this.$nonFixed.hide();
		this.$extFixed.hide();


		$(document).on("scroll", function(e){
			//Fix display fixed message on page load and scrolling
			if(this.$nonFixed.css('display') != 'none'){
				if($(window).scrollTop() > 250){
					//Show fixed message
					//Hide static one
					this.$nonFixed.css('visibility', 'hidden');
					this.$extFixed.show();
				}else{
					//Hide fixed message
					//Show static one
					this.$nonFixed.css('visibility', 'visible');
					this.$extFixed.hide();
				}
			}else{
				//Fix, on scroll up if an event was triggert
				// while the static message was hidden
				if($(window).scrollTop() < 250){
					if(this.$extFixed.css('display') != 'none'){
						this.$extFixed.hide();
						this.$nonFixed.show();
					}
				}
			}
		}.bind(this));


		$('.extClickCommand').click(function(){
			var data = {
				commandId: parseInt($(this).attr('ext-command'), 10),
				type: 'service',
				objectId: self.getVar('serviceObjectId')
			};
			self.Ajax.externalcommand(data);
		});

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
