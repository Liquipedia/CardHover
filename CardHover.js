/* Card Hover */
$(document).ready(function () {
	$('.hovercard').hover(function() {
		var imagelink = $(this).attr('data-img');
		if (imagelink) {
			imagelink = imagelink.replace(' ', '_');
			var left = $(this).position().left + 20;
			var top = $(this).position().top + 20;
			if (($(window).width() - (left + 480)) < 0) {
				left = left - 280;
			}
			if ((($(this).offset().top + 20) - $(window).scrollTop()) > ($(window).height()/2)) {
				top = top - 416;
			}
			var imagediv = $('<div id="hoverimage"><img src="' + imagelink + '"></div>');
			$(this).append(imagediv);
 			imagediv.css('top', top);
			imagediv.css('left', left);
		}
	},function () {
		$('#hoverimage').remove();
	});
});