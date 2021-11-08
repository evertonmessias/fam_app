function fb_login (x) {
	if (x.status == 'connected') {
		$('#loader').fadeIn(350);

		FB.api('/me?fields=id,name,gender,email', (response) => {
			if (response.gender) {
				var iGender = $('<input type="hidden" name="aluno[sexo]" />');
				if (response.gender == 'male') { iGender.val('Masculino'); iGender.appendTo($('#home-form')); }
				if (response.gender == 'female') { iGender.val('Feminino'); iGender.appendTo($('#home-form')); }
			}

			$.get('/api/public/onestep/' + response.email, (data) => {
				if (response.name)
					$('#input-nome').val(response.name);

				if (response.email)
					$('#input-email').val(response.email);

				if (response.nome)
					$('#input-nome').val(data.nome);

				if (data.cpf)
					$('#input-cpf').val(data.cpf);

				if (data.celular)
					$('#input-cel').val(data.celular);

				if (data.cpf)
					window.go_step (4);
				else
					window.go_step (3);

				$('#loader').fadeOut(350);

				/*if (data.curso_latest) {
					setTimeout(() => {
						if ($('#home-curso').val().length == 0) {
							$('#home-curso').val(data.curso_latest);
							$('#home-form').submit();
						}
					}, 2000);
				}*/
			});
		});
	}
}

// Helper keypress enter
function e_on_enter (el, fn) {
	$(el).keypress(function(e) {
		console.log(e, e.which);
		var key = e.which;
		if(key == 13 && fn)
			fn (e);
	});
}

$(document).ready(() => {
	$('body').data('step', 1);

	var go_step = window.go_step = (step) => {
		var current = $('body').data('step');

		if ($('section.step-' + current + ' input')[0].checkValidity()) {
			$('body').data('step', step);

			$('body').addClass('step' + step).removeClass('step' + current);

			setTimeout(() => {
				$('section.step-' + step + ' input').focus();
			}, 450);
		}
	}

	// Processar
	$('main input[placeholder]').each((i, o) => {
		var input = $(o);
		var e = $('<div></div>')
			.addClass('input')
			.append($('<label></label>').text(input.attr('placeholder')))
			.insertAfter(input);
		input.prependTo(e);
	});

	// Step 1
	e_on_enter('#input-nome', () => { go_step(2); });

	// Step 2
	e_on_enter('#input-email', () => { go_step(3); });

	// Step 3
	e_on_enter('#input-cpf', () => { go_step(4); });

	// Step4
	$('#home-curso').on('change', () => {
		$('#loader').fadeIn(350);
		$('main form').submit();
	});
});

function oneclick_start () {
	$('body').addClass('open');
}