$(document).ready(function() {
	$.get("styles/gallery/modal.html", function(data) {
		$('#content').append(data);
		$('#modalCarousel').carousel({interval:false});
	});

	$('.gallery .thumbnail').click(function(e){
		e.preventDefault();
		
		var clickedThumbnail = this;

		$('.carousel-inner').empty();
		$('.gallery .thumbnail').each(function() {
			var currentThumbnail = this;
			var itemDiv = createCarouselItem(currentThumbnail);

			if(currentThumbnail == clickedThumbnail){
				itemDiv.addClass('active');
			}
		});

		$('#galleryModal').modal('show');
		$('#modalCarousel').carousel();
	});
});

function createCarouselItem(thumbnail){
	var a = $(thumbnail).parent();
	var thumbnailHref = $(a).attr('href');

	var itemDiv = $('<div class="item"></div>');
	itemDiv.appendTo('.carousel-inner');

	var img = $('<img />');
	img.attr('src', thumbnailHref);
	img.addClass('center-block');
	img.addClass('img-responsive');
	img.appendTo(itemDiv);
	
	return itemDiv;
}