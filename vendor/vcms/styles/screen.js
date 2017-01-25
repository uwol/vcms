/* inserted elements */

function animateLasteInsertId(){
	$(".lastInsertId").slideDown(1000);
}

/* smooth scrolling */

function configureScrolling(){
	$('a[href^="#"]').on('click', function(event){
	  event.preventDefault();
	  $('html,body').animate({scrollTop:$(this.hash).offset().top}, 'slow', 'swing');
	});
}

/* facebook event plugin */

function loadFacebookEventPlugins(){
	$(".facebookEventPlugin").each(function() {
		var div = $(this);
		var eventid = div.attr('data-eventid');

		$.ajax({
			url: "api.php?iid=fb_event&eventid=" + eventid,
			context: document.body
		}).done(function(html) {
			div.replaceWith(html);
			adjustThumbnailImgsOnLoad();
		});
	});
}

/* facebook plugins */

function adjustFacebookPagePluginsSrc(){
	$("iframe.facebookPagePlugin").each(function() {
		var iframe = $(this);
		var width = iframe.width();
		var src = iframe.attr('src');
		var newSrc = src.replace(/width=[0-9]+/, 'width=' + width);

		iframe.attr('src', newSrc);
	});
}

/* navigation */

function configureNavigation(){
	var navbarHeight = $(".navbar-fixed-top").height();
	var paddingTop = navbarHeight;

    $('nav').affix({
        offset: {
            top: 75
        }
    });

    $("#content").css("padding-top", paddingTop);
}

// --------------------

function adjustElementDimensions(){
	adjustFacebookPagePluginsSrc();
	configureNavigation();
}

// --------------------

$(document).ready(function() {
	animateLasteInsertId();
	configureScrolling();

	loadFacebookEventPlugins();
	adjustElementDimensions();

	var resizeDebounce;

	$(window).on('orientationchange resize', function(event) {
		clearTimeout(resizeDebounce);
		resizeDebounce = setTimeout(function(){
			adjustElementDimensions();
		}, 20);
	});

	$('.navbar').on('affixed-top.bs.affix', function(event) {
		configureNavigation();
	});
});

$(document).load(function() {
	adjustElementDimensions();
});
