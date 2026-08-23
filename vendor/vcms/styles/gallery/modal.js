$(document).ready(function() {
	$.get("vendor/vcms/styles/gallery/modal.html", function(data) {
		$('#content').append(data);

		var carousel = $('#modal-carousel')[0];
		bootstrap.Carousel.getOrCreateInstance(carousel, {interval: false});
	});

	$('.gallery .card-img .img-frame a').click(function(e){
		e.preventDefault();

		var clickedA = this;

		$('.carousel-inner').empty();
		$('.gallery .card-img .img-frame a').each(function() {
			var currentA = this;
			var itemDiv = createCarouselItem(currentA);

			if(currentA == clickedA){
				itemDiv.addClass('active');
			}
		});

		var modal = $('#gallery-modal')[0];
		bootstrap.Modal.getOrCreateInstance(modal).show();
	});
});

function createCarouselItem(a){
	var imageHref = $(a).attr('href');

	var itemDiv = $('<div class="carousel-item"></div>');
	itemDiv.appendTo('.carousel-inner');

	var img = $('<img />');
	img.attr('src', imageHref);
	img.addClass('d-block');
	img.addClass('mx-auto');
	img.addClass('img-fluid');
	img.appendTo(itemDiv);

	return itemDiv;
}
