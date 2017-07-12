var filter_wp_query_admin_panel;
( function( $ ) {
	filter_wp_query_admin_panel = {
		init : function() {
			
			$('#filter_wp_query-admin-panel .filter_wp_query-admin-panel-menu-link:first').addClass('visible');
			$('#filter_wp_query-admin-panel .filter_wp_query-admin-panel-content-box:first').addClass('visible');
			$('.filter_wp_query-admin-panel-menu-link').click(function(event) {
				event.preventDefault();
			});

			$('.filter_wp_query-admin-panel-menu-link').click(function() {
				wpi_title = $(this).attr("id").replace('filter_wp_query-admin-panel-menu-', '');
				$('.filter_wp_query-admin-panel-menu-link').removeClass('visible');
				$('#filter_wp_query-admin-panel-menu-' + wpi_title).addClass('visible');
				$('.filter_wp_query-admin-panel-content-box').removeClass('visible');
				$('.filter_wp_query-admin-panel-content-box').hide();
				$('#filter_wp_query-admin-panel-content-' + wpi_title).fadeIn("fast");
				$('.filter_wp_query-admin-panel-content-box').removeClass('visible');
			});
			
			//the settings page id
			var data_name = $('#filter_wp_query-admin-panel-footer input').attr('id');

			//on form submit function
			$('#filter_wp_queryform').submit(function(){
				if(!$(this).hasClass('noclick')) {
					$('#filter_wp_query__settings_array').removeClass('button-primary');
					$('#filter_wp_query__settings_array').addClass('button-disabled');
				
					//add class noclick for disabling the user to save multiple times that eats a lot of cpu power
					$(this).addClass('noclick');
					var serializedReturn = $('#filter_wp_queryform').serialize();
					
					var data = {
						id: data_name,
						action: 'filter_wp_query_admin_save',
						data: serializedReturn,
						filter_wp_query_nonce: $('#filter_wp_query_nonce').val()
					};
					
					$.post(ajaxurl, data, function(response){
						if(response != '1') {
							alert(response);
						}
						$('#filter_wp_queryform').removeClass('noclick');
						$('#filter_wp_query__settings_array').removeClass('button-disabled');
						$('#filter_wp_query__settings_array').addClass('button-primary');
					});
				}
				return false;
			});   	
		}
	};

	$( document ).ready( function( $ ) { filter_wp_query_admin_panel.init(); } );
} ) ( jQuery );