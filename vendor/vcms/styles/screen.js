function animateLasteInsertId(){
	document.querySelectorAll(".last-insert-id").forEach(function(element) {
		// The element is hidden by CSS, so it is revealed first and its target height is
		// measured afterwards, which parameterizes the slide down animation.
		element.classList.add("slide-down");
		element.style.setProperty("--slide-down-height", element.scrollHeight + "px");
	});
}

function configureScrolling(){
	document.addEventListener("click", function(event) {
		var element = event.target instanceof Element ? event.target : null;
		var anchor = element ? element.closest('a[href^="#"]') : null;

		if (!anchor || !anchor.hash) {
			return;
		}

		// getElementById is used instead of querySelector, because ids such as the numeric
		// record ids of the intranet are no valid CSS selectors.
		var target = document.getElementById(decodeURIComponent(anchor.hash.substring(1)));

		if (!target) {
			return;
		}

		event.preventDefault();
		target.scrollIntoView({behavior: "smooth"});
	});
}

function loadFacebookEventPlugins(){
	document.querySelectorAll(".facebookEventPlugin").forEach(function(div) {
		var eventId = div.dataset.eventid;

		fetch("api.php?iid=fb_event&eventid=" + encodeURIComponent(eventId)).then(function(response) {
			return response.text();
		}).then(function(html) {
			// A template parses the response without executing it and without the parser
			// dropping nodes that are invalid in the current position.
			var template = document.createElement("template");
			template.innerHTML = html;

			div.replaceWith(template.content);
		});
	});
}

function adjustFacebookPagePluginsSrc(){
	document.querySelectorAll("iframe.facebookPagePlugin").forEach(function(iframe) {
		var width = Math.round(iframe.getBoundingClientRect().width);
		var src = iframe.getAttribute("src");

		if (!src) {
			return;
		}

		iframe.setAttribute("src", src.replace(/width=[0-9]+/, "width=" + width));
	});
}

function configureNavigation(){
	var navbar = document.querySelector(".navbar");
	var content = document.getElementById("content");

	if (navbar && content) {
		content.style.paddingTop = navbar.offsetHeight + "px";
	}
}

function toggleNavbarState(){
	var navbar = document.querySelector("nav.navbar");

	if (navbar) {
		var scrolled = window.scrollY > 75;
		var wasScrolled = navbar.classList.contains("affix");

		navbar.classList.toggle("affix", scrolled);
		navbar.classList.toggle("affix-top", !scrolled);

		// back at the top the navbar is taller again, so the content offset is recalculated
		if (wasScrolled && !scrolled) {
			configureNavigation();
		}
	}
}

function reveal(){
	var io = null;

	var ioCallback = function(entries) {
		entries.forEach(function(entry) {
			if (entry.isIntersecting) {
				entry.target.classList.add("inview");
				io.unobserve(entry.target);
			}
		});
	};

	io = new IntersectionObserver(ioCallback);

	var items = document.querySelectorAll(".reveal");

	for (var i = 0; i < items.length; i++) {
		io.observe(items[i]);
	}
}


function adjustElementDimensions(){
	adjustFacebookPagePluginsSrc();
	configureNavigation();
}


// --------------------


function initializeScreen(){
	animateLasteInsertId();
	configureScrolling();
	reveal();

	loadFacebookEventPlugins();
	adjustElementDimensions();

	var resizeDebounce;

	function scheduleAdjustElementDimensions() {
		clearTimeout(resizeDebounce);
		resizeDebounce = setTimeout(adjustElementDimensions, 20);
	}

	window.addEventListener("orientationchange", scheduleAdjustElementDimensions);
	window.addEventListener("resize", scheduleAdjustElementDimensions);

	window.addEventListener("scroll", toggleNavbarState, {passive: true});
	toggleNavbarState();
}


if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", initializeScreen);
} else {
	initializeScreen();
}
