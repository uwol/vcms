$(document).ready(function() {
	$('.gallery .thumbnail').on('load', function() {}).each(function(i) {
		var a = $(this).parent();
		var imgHref = $(a).attr('href');
		var img = $('<img />');
		img.attr('src', imgHref);
		img.addClass('center-block');
		img.addClass('img-responsive');

		var itemDiv = $('<div class="item"></div>');
		img.appendTo(itemDiv);
		itemDiv.appendTo('.carousel-inner');

		if (i==0){
			itemDiv.addClass('active');
		}
	});

	$('#modalCarousel').carousel({interval:false});

	$('.gallery .thumbnail').click(function(e){
		e.preventDefault();

		var idx = $(this).parents('div').index();
		var id = parseInt(idx);

		$('#myModal').modal('show');
		$('#modalCarousel').carousel(id);
	});
});