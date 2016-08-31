$(document).ready(function() {
	$('video').on('play', function(){
		var header = $('header');
		header.css('background-image', 'url("")');
	});
});