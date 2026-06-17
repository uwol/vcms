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
	var navbarHeight = $(".fixed-top").outerHeight() || $(".navbar").outerHeight() || 0;
	var paddingTop = navbarHeight;

	updateAffixState();
	$("#content").css("padding-top", paddingTop);
}

function updateAffixState(){
	var $nav = $("#nav");
	var offset = 75;

	if($(window).scrollTop() > offset){
		$nav.addClass("affix").removeClass("affix-top");
	} else {
		$nav.addClass("affix-top").removeClass("affix");
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

	$(window).on('scroll', function() {
		updateAffixState();
	});
});

$(window).on('load', function() {
	adjustElementDimensions();
});
