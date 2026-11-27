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
	var navbar = document.querySelector(".ui-navbar");
	var content = document.getElementById("content");

	if (navbar && content) {
		content.style.paddingTop = navbar.offsetHeight + "px";
	}
}

function toggleNavbarState(){
	var navbar = document.querySelector("nav.ui-navbar");

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


/*
* Collapse: a button with data-ui-toggle="collapse" shows and hides the elements selected by
* its data-ui-target. The height is animated from 0 to the content height and back.
*/
function toggleCollapse(button){
	var targets = document.querySelectorAll(button.getAttribute("data-ui-target"));
	var expand = button.getAttribute("aria-expanded") !== "true";

	button.setAttribute("aria-expanded", expand ? "true" : "false");

	targets.forEach(function(target) {
		if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
			target.classList.toggle("show", expand);
			return;
		}

		var startHeight = expand ? 0 : target.getBoundingClientRect().height;

		target.classList.add("show");
		var endHeight = expand ? target.scrollHeight : 0;

		target.classList.add("ui-collapsing");
		target.style.height = startHeight + "px";
		// Reading the height applies the start height before the transition starts.
		target.getBoundingClientRect();
		target.style.height = endHeight + "px";

		var finished = false;

		var finish = function() {
			if (finished) {
				return;
			}

			finished = true;
			target.classList.remove("ui-collapsing");
			target.classList.toggle("show", expand);
			target.style.height = "";
		};

		target.addEventListener("transitionend", function onEnd(event) {
			if (event.target === target) {
				target.removeEventListener("transitionend", onEnd);
				finish();
			}
		});

		// Fallback, in case no transitionend is fired, e.g. for an element without transition.
		setTimeout(finish, 400);
	});
}

/*
* Dropdown: a link with data-ui-toggle="dropdown" opens the next .ui-dropdown-menu. A click
* outside or Escape closes it again.
*/
function closeDropdowns(except){
	document.querySelectorAll('[data-ui-toggle="dropdown"].show').forEach(function(toggle) {
		if (toggle === except) {
			return;
		}

		toggle.classList.remove("show");
		toggle.setAttribute("aria-expanded", "false");

		var menu = toggle.parentElement.querySelector(".ui-dropdown-menu");

		if (menu) {
			menu.classList.remove("show");
		}
	});
}

function toggleDropdown(toggle){
	var menu = toggle.parentElement.querySelector(".ui-dropdown-menu");
	var open = !toggle.classList.contains("show");

	closeDropdowns(toggle);

	toggle.classList.toggle("show", open);
	toggle.setAttribute("aria-expanded", open ? "true" : "false");

	if (menu) {
		menu.classList.toggle("show", open);
	}
}

function configureToggles(){
	document.addEventListener("click", function(event) {
		var element = event.target instanceof Element ? event.target : null;
		var toggle = element ? element.closest("[data-ui-toggle]") : null;

		if (toggle && toggle.getAttribute("data-ui-toggle") === "collapse") {
			event.preventDefault();
			toggleCollapse(toggle);
		} else if (toggle && toggle.getAttribute("data-ui-toggle") === "dropdown") {
			event.preventDefault();
			toggleDropdown(toggle);
		} else {
			closeDropdowns(null);
		}
	});

	document.addEventListener("keydown", function(event) {
		if (event.key === "Escape") {
			var open = document.querySelector('[data-ui-toggle="dropdown"].show');

			closeDropdowns(null);

			if (open) {
				open.focus();
			}
		}
	});
}


function adjustElementDimensions(){
	adjustFacebookPagePluginsSrc();
	configureNavigation();
}


// --------------------


function initializeScreen(){
	animateLasteInsertId();
	configureToggles();
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
