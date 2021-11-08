$(document).ready(function () {
	$('.tab-container').each(function () {
		var the_container = $(this);
		var the_links = $('<div class="tab-links"></div>');
		the_links.prependTo(the_container);
		$(this).children('.tab').each(function (i, o) {
			var the_tab = $(this).attr('data-index', i);
			var the_link = $('<a class="tab-link"></a>').html(the_tab.attr('title')).attr('data-index', i);
			the_link.appendTo(the_links);
			the_link.on('click', function () {
				the_container.children('.active').removeClass('active');
				the_tab.addClass('active');
			});
		});
	});
});