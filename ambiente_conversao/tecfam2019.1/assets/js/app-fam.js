$(document).ready(function () {

	var isMobile = {
	    Android: function() {
	        return navigator.userAgent.match(/Android/i);
	    },
	    BlackBerry: function() {
	        return navigator.userAgent.match(/BlackBerry/i);
	    },
	    iOS: function() {
	        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
	    },
	    Opera: function() {
	        return navigator.userAgent.match(/Opera Mini/i);
	    },
	    Windows: function() {
	        return navigator.userAgent.match(/IEMobile/i);
	    },
	    any: function() {
	        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
	    }
	}; 

	// Inputs e Máscaras
	if (!isMobile.any()) {
		$('input[data-mask]').each(function (i, o) {
			$(this).mask($(this).data('mask'));
		});
	}

	// Inscrição	
	$('#inscricao-continuar').on('click', function () {
		$('#inscricao-dados').addClass('open');
	});

	// Conf. Data	
	$('#continuar-data').on('click', function () {
		$('#inscricao-datas').addClass('open');
	});

	// Condições especiais
	$('#deficiencia-qual').hide();
	$('#deficiencia-condicoes').hide();

	$('#deficiencia').on('change', function () {
		if ($(this).val() == 'sim') {
			// $('#deficiencia-container').removeClass('full');
			$('#deficiencia-qual').fadeIn();
			$('#deficiencia-condicoes').show();
		} else {
			$('#deficiencia-qual').fadeOut(function () {
				// $('#deficiencia-container').addClass('full');
			});
			$('#deficiencia-condicoes').hide();
		}
	});

	// Pop-up de erro
	$('#error_msg').each(function () {
		$(this).hide();
		var msg = $(this).text().trim();
		if (msg.length > 0) alert(msg);
	});

	// Menu
	$('#nav-toggle').on('click', function () {
		$('nav ul').toggleClass('open');
	});

	var enviar_form = () => {
		var valido = true;

		$('#home-form input').each((i, o) => {
			if ($(o).val().trim().length == 0)
				valido = false;
		});

		if (valido)
			$('#home-form').submit();
		else
			$('#home-proximo').show();
	}

	// Suprime botão próximo
	$('#home-proximo').hide();
	$('#home-curso').on('change', enviar_form);

	// YouTube pop-up
	$('#slider-videos').on('click', '.youtube-video', function () {
		if ($(this).find('a').css('display') == 'none') {
			$('#popup-video iframe').prop('src', 'http://youtube.com/embed/' + $(this).data('video'));
			$('#popup-video').addClass('open');
		}
	});
	$('#popup-close').on('click', function () {
		$('#popup-video iframe').prop('src', '');
		$('#popup-video').removeClass('open');
	});
});

// Analytics

(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-54225319-1', 'auto');
ga('require', 'displayfeatures');
ga('send', 'pageview');