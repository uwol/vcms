function animateLasteInsertId(){
	$(".last-insert-id").slideDown(1000);
}

function configureScrolling(){
	$('a[href^="#"]').on('click', function(event){
	  event.preventDefault();
	  $('html,body').animate({scrollTop:$(this.hash).offset().top}, 'slow', 'swing');
	});
}

function loadFacebookEventPlugins(){
	$(".facebookEventPlugin").each(function() {
		var div = $(this);
		var eventid = div.attr('data-eventid');

		$.ajax({
			url: "api.php?iid=fb_event&eventid=" + eventid,
			context: document.body
		}).done(function(html) {
			div.replaceWith(html);
		});
	});
}

function adjustFacebookPagePluginsSrc(){
	$("iframe.facebookPagePlugin").each(function() {
		var iframe = $(this);
		var width = iframe.width();
		var src = iframe.attr('src');
		var newSrc = src.replace(/width=[0-9]+/, 'width=' + width);

		iframe.attr('src', newSrc);
	});
}

function configureNavigation(){
	var navbarHeight = $(".navbar").height();
	var paddingTop = navbarHeight;

    $("#content").css("padding-top", paddingTop);
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
	window.sr = ScrollReveal();
  sr.reveal('.reveal', { scale: 1.0 });
}


function adjustElementDimensions(){
	adjustFacebookPagePluginsSrc();
	configureNavigation();
}


// --------------------


$(document).ready(function() {
	animateLasteInsertId();
	configureScrolling();
	reveal();

	loadFacebookEventPlugins();
	adjustElementDimensions();

	var resizeDebounce;

	$(window).on('orientationchange resize', function(event) {
		clearTimeout(resizeDebounce);
		resizeDebounce = setTimeout(function(){
			adjustElementDimensions();
		}, 20);
	});

	window.addEventListener('scroll', toggleNavbarState, {passive: true});
	toggleNavbarState();
});
