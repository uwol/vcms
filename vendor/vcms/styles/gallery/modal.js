function loadGalleryModal(){
	var content = document.getElementById("content");

	if (!content) {
		return;
	}

	fetch("vendor/vcms/styles/gallery/modal.html").then(function(response) {
		return response.text();
	}).then(function(html) {
		content.insertAdjacentHTML("beforeend", html);

		var modal = document.getElementById("gallery-modal");

		if (!modal) {
			return;
		}

		modal.addEventListener("click", function(event) {
			var element = event.target instanceof Element ? event.target : null;
			var control = element ? element.closest("[data-carousel-slide]") : null;

			if (control) {
				slideCarousel(control.getAttribute("data-carousel-slide") === "next" ? 1 : -1);
			} else if (event.target === modal) {
				// A click on the backdrop closes the dialog.
				modal.close();
			}
		});

		modal.addEventListener("keydown", function(event) {
			if (event.key === "ArrowLeft") {
				slideCarousel(-1);
			} else if (event.key === "ArrowRight") {
				slideCarousel(1);
			}
		});
	});
}

function configureGalleryModal(){
	// The click is delegated, so that galleries rendered after this script ran are covered too.
	document.addEventListener("click", function(event) {
		var element = event.target instanceof Element ? event.target : null;
		var clickedAnchor = element ? element.closest(".gallery .ui-card-img .img-frame a") : null;

		if (!clickedAnchor) {
			return;
		}

		event.preventDefault();
		showGalleryModal(clickedAnchor);
	});
}

function showGalleryModal(clickedAnchor){
	var carouselInner = document.querySelector("#gallery-modal .ui-carousel-inner");
	var modal = document.getElementById("gallery-modal");

	if (!carouselInner || !modal) {
		return;
	}

	carouselInner.replaceChildren();

	document.querySelectorAll(".gallery .ui-card-img .img-frame a").forEach(function(anchor) {
		var itemDiv = createCarouselItem(carouselInner, anchor);

		if (anchor === clickedAnchor) {
			itemDiv.classList.add("active");
		}
	});

	modal.showModal();
}

function slideCarousel(direction){
	var items = Array.prototype.slice.call(document.querySelectorAll("#gallery-modal .ui-carousel-item"));
	var current = items.findIndex(function(item) {
		return item.classList.contains("active");
	});

	if (items.length === 0) {
		return;
	}

	// The carousel wraps around at both ends.
	var next = (current + direction + items.length) % items.length;

	items.forEach(function(item, index) {
		item.classList.toggle("active", index === next);
	});
}

function createCarouselItem(carouselInner, anchor){
	var imageHref = anchor.getAttribute("href");

	var itemDiv = document.createElement("div");
	itemDiv.classList.add("ui-carousel-item");
	carouselInner.appendChild(itemDiv);

	var img = document.createElement("img");
	img.setAttribute("src", imageHref);
	img.classList.add("block", "mx-auto", "max-w-full", "h-auto");
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
