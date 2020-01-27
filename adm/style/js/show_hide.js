jQuery(function() {
	$(document).ready(function()
	{
     	$('.showkey').on('click', function()
		{
     		var key = $(this).attr('key');
			$('#' + key).toggle();
		});
  	});
});
