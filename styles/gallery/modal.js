$(document).ready(function() {
	$.get("styles/gallery/modal.html", function(data) {
		$('#content').append(data);
		$('#modalCarousel').carousel({interval:false});
	});

	$('.gallery .thumbnail img').click(function(e){
		e.preventDefault();
		
		var clickedThumbnailImg = this;

		$('.carousel-inner').empty();
		$('.gallery .thumbnail img').each(function() {
			var currentThumbnailImg = this;
			var itemDiv = createCarouselItem(currentThumbnailImg);

			if(currentThumbnailImg == clickedThumbnailImg){
				itemDiv.addClass('active');
			}
		});

		$('#galleryModal').modal('show');
		$('#modalCarousel').carousel();
	});
});

function createCarouselItem(thumbnailImg){
	var a = $(thumbnailImg).parent();
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