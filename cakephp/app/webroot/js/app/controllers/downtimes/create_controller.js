App.Controllers.DowntimesCreateController = Frontend.AppController.extend({
	
	_initialize: function(){
		if($('#DowntimehistoryService').length){
			var self = this;
			$('#DowntimehistoryHost').change(function(){
				$serviceSelect = $('#DowntimehistoryService');
				$.ajax({
					url: self.getVar('url') + encodeURIComponent($(this).val()) +".json",
					type: "GET",
					error: function(){},
					success: function(){},
					complete: function(response){
						$serviceSelect.html('');
						if(Object.keys(response.responseJSON.services).length > 0){
							for(var key in response.responseJSON.services){
								var serviceObjectId = response.responseJSON.services[key].Service.service_object_id;
								var serviceName = response.responseJSON.services[key].Objects.name2;
								$serviceSelect.append($("<option />").val(serviceObjectId).text(serviceName));
							}
						}
					}
				});
			});
		}
	}
});
