$(document).ready(function() {
	var modalInstance = null;
	var carouselInstance = null;

	$.get("vendor/vcms/styles/gallery/modal.html", function(data) {
		$('#content').append(data);

		var modalEl = document.getElementById('gallery-modal');
		var carouselEl = document.getElementById('modal-carousel');

		if(modalEl && carouselEl){
			modalInstance = new bootstrap.Modal(modalEl);
			carouselInstance = new bootstrap.Carousel(carouselEl, { interval: false });
		}
	});

	$('.gallery .card .img-frame a').click(function(e){
		e.preventDefault();

		var clickedA = this;
		var activeIndex = 0;
		var currentIndex = 0;

		$('.carousel-inner').empty();
		$('.gallery .card .img-frame a').each(function() {
			var currentA = this;
			var itemDiv = createCarouselItem(currentA);

			if(currentA == clickedA){
				itemDiv.addClass('active');
				activeIndex = currentIndex;
			}

			currentIndex += 1;
		});

		if(carouselInstance){
			carouselInstance.to(activeIndex);
		} else if(document.getElementById('modal-carousel')){
			carouselInstance = bootstrap.Carousel.getOrCreateInstance(document.getElementById('modal-carousel'), { interval: false });
			carouselInstance.to(activeIndex);
		}

		if(modalInstance){
			modalInstance.show();
		} else if(document.getElementById('gallery-modal')){
			modalInstance = bootstrap.Modal.getOrCreateInstance(document.getElementById('gallery-modal'));
			modalInstance.show();
		}

		$('.carousel-control-next').focus();
	});
});

$(document).keydown(function(e) {
    if (e.keyCode === 37) {
       // Previous
       $(".carousel-control-prev").click();
       return false;
    }
    if (e.keyCode === 39) {
       // Next
       $(".carousel-control-next").click();
       return false;
    }
});

function createCarouselItem(a){
	var thumbnailHref = $(a).attr('href');

	var itemDiv = $('<div class="carousel-item"></div>');
	itemDiv.appendTo('.carousel-inner');

	var img = $('<img />');
	img.attr('src', thumbnailHref);
	img.addClass('d-block');
	img.addClass('mx-auto');
	img.addClass('img-fluid');
	img.appendTo(itemDiv);

	return itemDiv;
}
