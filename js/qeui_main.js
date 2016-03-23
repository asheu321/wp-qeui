/* WP-Qeui JS*/
jQuery(document).ready(function($) {
	jQuery('.qeui-thumb-remove').click(function(e) {
		/* Act on the event */
		e.preventDefault();

		var loading = jQuery(this).find('i.qeui-ajax-loading');
		loading.fadeIn();
		var thumb_id = jQuery(this).attr('href');
		var post_id = jQuery(this).attr('data-id');
		jQuery.ajax({
			url: qeui_obj.ajax_url,
			type: 'post',
			dataType: 'html',
			data: {
				action: 'qeui_delete_thumbnail',
				thumb_id: thumb_id,
				post_id: post_id
			},
			success: function(response) {
				jQuery('i.qeui-ajax-loading').fadeOut();
				
				jQuery('span.pid-'+response).after('<a class="button-secondary qeui-upload-button" href="#" id="qeui-button-'+response+'" data-id="'+response+'"><i class="qeui-ajax-loading" style="display:none"><img src="http://www.myfriendrecommends.com.au/wp-content/plugins/quick-edit-upload-image/images/ajax-loader.gif">  </i>Upload Image</a>');
				jQuery('span.pid-'+response).remove(); 
			}
		});		
	});


	/* Upload Media */
	var file_frame;
	jQuery('.qeui-upload-button').live('click', function( event ){
		event.preventDefault();

		// If the media frame already exists, reopen it.
		/*if ( file_frame ) {
			file_frame.open();
			return;
		}*/

		var pid = jQuery(this).attr('data-id');

		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: jQuery( this ).data( 'uploader_title' ),
			button: {
				text: jQuery( this ).data( 'uploader_button_text' ),
			},
			multiple: false // Set to true to allow multiple files to be selected
		});

		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {

			// We set multiple to false so only get one image from the uploader
			attachment = file_frame.state().get('selection').first().toJSON();
			// Do something with attachment.id and/or attachment.url here

			//console.log(pid);
			jQuery('#qeui-button-'+pid+' > i.qeui-ajax-loading').fadeIn();
			
			jQuery.ajax({
				url: qeui_obj.ajax_url,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'qeui_add_thumbnail',
					thumb_id: attachment.id,
					post_id: pid
				},
				success: function(response) {
					jQuery('#qeui-button-'+response.post_id+' > i.qeui-ajax-loading').fadeOut();
					jQuery('#qeui-button-'+response.post_id).after('<span class="pid-'+response.post_id+'" id="'+response.thumb_id+'"><img width="150" height="150" alt="garden" class="attachment-thumbnail wp-post-image" src="'+response.img_url+'"><br/><a class="qeui-thumb-remove button-secondary" data-id="'+response.post_id+'" href="'+response.thumb_id+'"><i class="qeui-ajax-loading" style="display:none"><img src="http://www.myfriendrecommends.com.au/wp-content/plugins/quick-edit-upload-image/images/ajax-loader.gif"> </i>remove</a></span>');
					jQuery('#qeui-button-'+response.post_id).remove();
					
					jQuery('.qeui-thumb-remove').click(function(e) {
						/* Act on the event */
						e.preventDefault();

						var loading = jQuery(this).find('i.qeui-ajax-loading');
						loading.fadeIn();
						var thumb_id = jQuery(this).attr('href');
						var post_id = jQuery(this).attr('data-id');
						jQuery.ajax({
							url: qeui_obj.ajax_url,
							type: 'post',
							dataType: 'html',
							data: {
								action: 'qeui_delete_thumbnail',
								thumb_id: thumb_id,
								post_id: post_id
							},
							success: function(response) {
								jQuery('i.qeui-ajax-loading').fadeOut();
								
								jQuery('span.pid-'+response).after('<a class="button-secondary qeui-upload-button" href="#" id="qeui-button-'+response+'" data-id="'+response+'"><i class="qeui-ajax-loading" style="display:none"><img src="'+qeui_obj.plugin_url+'/images/ajax-loader.gif">  </i>Upload Image</a>');
								jQuery('span.pid-'+response).remove(); 
							}
						});		
					});
				}
			});
			
		});

		// Finally, open the modal
		file_frame.open();
		}); 
});