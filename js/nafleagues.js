jQuery(document).ready(function() {

jQuery("#nafleague_form").submit(function( event ) {
	event.preventDefault();
	
	jQuery('#nafleague_messages').html('<img src="wp-admin/images/loading.gif">');
	var form = jQuery(this);
	jQuery.ajax({ 
		url   : form.attr('action'),
		type  : form.attr('method'),
		data  : form.serialize(), // data to be submitted
		success: function(response){
			jQuery('#nafleague_messages').html(response); // do what you like with the response
			form.remove();
			window.scrollTo(0,0);
		}
	});
});

});

