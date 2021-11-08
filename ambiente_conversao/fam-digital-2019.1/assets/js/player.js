$(document).ready(function () {
	$('.video.youtube').each(function (i, o) {
		var id = $(this).data('video');
		var limpa_antes = function (texto, remover) {
			if (~texto.indexOf(remover)) texto = texto.substring(texto.indexOf(remover) + remover.length);
			return texto;
		}

		id = limpa_antes (id, '/embed/');
		id = limpa_antes (id, 'watch?v=');

		var autoplay = $(this).data('autoplay') || 0;
		$(this).pPlayer({
			youtubeVideoId: id,
			autoplay: autoplay,
			origin: document.location.href,
			features: ["playpause", "progress", "mute", "fullscreen"]
		});
	});
});