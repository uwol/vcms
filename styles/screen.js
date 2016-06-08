// animate inserted elements
$(document).ready(function() {
	$(".lastInsertId").slideDown(1000);
});

$(window).resize(function() {
	adjustThumbnailImgs();
});

$(document).ready(function() {
	$(".thumbnail .thumbnailOverflow img").load(function() {
		adjustThumbnailImg($(this));
	}).each(function() {
	  if(this.complete) $(this).load();
	});
});

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