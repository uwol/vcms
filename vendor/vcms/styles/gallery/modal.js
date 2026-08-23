function loadGalleryModal(){
	var content = document.getElementById("content");

	if (!content) {
		return;
	}

	fetch("vendor/vcms/styles/gallery/modal.html").then(function(response) {
		return response.text();
	}).then(function(html) {
		content.insertAdjacentHTML("beforeend", html);

		// The carousel is initialized after the markup has been inserted, because
		// Bootstrap needs the element to be part of the document.
		var carousel = document.getElementById("modal-carousel");

		if (carousel) {
			bootstrap.Carousel.getOrCreateInstance(carousel, {interval: false});
		}
	});
}

function configureGalleryModal(){
	// The click is delegated, so that galleries rendered after this script ran are covered too.
	document.addEventListener("click", function(event) {
		var element = event.target instanceof Element ? event.target : null;
		var clickedAnchor = element ? element.closest(".gallery .card-img .img-frame a") : null;

		if (!clickedAnchor) {
			return;
		}

		event.preventDefault();
		showGalleryModal(clickedAnchor);
	});
}

function showGalleryModal(clickedAnchor){
	var carouselInner = document.querySelector(".carousel-inner");
	var modal = document.getElementById("gallery-modal");

	if (!carouselInner || !modal) {
		return;
	}

	carouselInner.replaceChildren();

	document.querySelectorAll(".gallery .card-img .img-frame a").forEach(function(anchor) {
		var itemDiv = createCarouselItem(carouselInner, anchor);

		if (anchor === clickedAnchor) {
			itemDiv.classList.add("active");
		}
	});

	bootstrap.Modal.getOrCreateInstance(modal).show();
}

function createCarouselItem(carouselInner, anchor){
	var imageHref = anchor.getAttribute("href");

	var itemDiv = document.createElement("div");
	itemDiv.classList.add("carousel-item");
	carouselInner.appendChild(itemDiv);

	var img = document.createElement("img");
	img.setAttribute("src", imageHref);
	img.classList.add("d-block", "mx-auto", "img-fluid");
	itemDiv.appendChild(img);

	return itemDiv;
}


// --------------------


function initializeGallery(){
	loadGalleryModal();
	configureGalleryModal();
}


if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", initializeGallery);
} else {
	initializeGallery();
}
