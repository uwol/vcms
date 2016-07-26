/* inserted elements */

function animateLasteInsertId(){
	$(".lastInsertId").slideDown(1000);
}

/* facebook event plugin */

function loadFacebookEventPlugins(){
	$(".facebookEventPlugin").each(function() {
		var div = $(this);
		var eventid = div.attr('data-eventid');

		$.ajax({
			url: "inc.php?iid=fb_event&eventid=" + eventid,
			context: document.body
		}).done(function(html) {
			div.replaceWith(html);
			adjustThumbnailImgsOnLoad();
		});
	});
}

/* thumbnails */

function adjustThumbnailImgsOnLoad(){
	$(".thumbnail .thumbnailOverflow img").load(function() {
		adjustThumbnailImg($(this));
	}).each(function() {
	  if(this.complete) $(this).load();
	});
}

function adjustThumbnailImgs(){
	$(".thumbnail .thumbnailOverflow img").each(function() {
		adjustThumbnailImg($(this));
	});
}

function adjustThumbnailImg(thumbnailImg){
	adjustThumbnailOverflowHeight(thumbnailImg);
	adjustThumbnailImgMarginTop(thumbnailImg);
}

function adjustThumbnailOverflowHeight(thumbnailImg){
	var thumbnailOverflow = thumbnailImg.closest('.thumbnailOverflow');
	var width = thumbnailOverflow.width();
	var height = width / 3 * 2;
	thumbnailOverflow.height(height);
}

function adjustThumbnailImgMarginTop(thumbnailImg){
	var thumbnailOverflow = thumbnailImg.closest('.thumbnailOverflow');
	var thumbnailOverflowHeight = thumbnailOverflow.height();
	var thumbnailImgHeight = thumbnailImg.height();
	
	if(thumbnailImgHeight > 0){
		var marginTop = (thumbnailOverflowHeight - thumbnailImgHeight) / 2;
		thumbnailImg.css('margin-top', marginTop + 'px');
	}
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
	var gap = 15;
	var paddingTop = navbarHeight + gap;

    $('nav').affix({
        offset: {
            top: paddingTop
        }
    });

    $("#container").css("padding-top", paddingTop);
}

// --------------------

function adjustElementDimensions(){
	adjustThumbnailImgs();
	adjustFacebookPagePluginsSrc();
	configureNavigation();
}

$(document).ready(function() {
	animateLasteInsertId();
	loadFacebookEventPlugins();
	adjustThumbnailImgsOnLoad();

	adjustElementDimensions();
});

$(document).load(function() {
	adjustElementDimensions();
});

var resizeDebounce;

$(window).resize(function() {
	clearTimeout(resizeDebounce);
	resizeDebounce = setTimeout(function(){
		adjustElementDimensions();
	}, 200);
});