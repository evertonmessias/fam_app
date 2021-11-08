$(document).ready(() => {
    // Métodos Computados
	let computed = {
		cursos_sort() {
            console.log(_.orderBy(this.cursos, 'nome'));
			return _.orderBy(this.cursos, 'nome');
		}
    };
    
    // Métodos
	let methods = {
		setCursos(cursos) {
			this.cursos = cursos;
		}
    };
    

	// Inicializar VueJS
	let $app = acfam.init({
        cursos: null
    }, {
		computed: computed,
		methods: methods
	});

    // Carregar lista de cursos
	$.getJSON('./api/cursos')
    .then((cursos) => {
        cursos = ((cursos) => {
            // Esta função irá preparar os cursos para exibição correta
            var result = {}

            // Loop principal
            cursos.forEach((curso) => {
                result[curso.id] = curso;
            });

            // Retornar
            return result;
        })(cursos);

        // Atualizar no app
        $app.setCursos(cursos);
    });

	var form = $('.form-home');
	var extraFields = form.find('.inputs-hidden');
	var ignoreExtraFields = false;
	var isFacebookLogin = false;

	var submitForm = () => {

		if (extraFields.hasClass('visible') || ignoreExtraFields || isFacebookLogin) {
			// Estamos fazendo um submit do form com Nome e E-mail, apenas deixamos passar

			// Caso o form seja válido, deixar em estado de processamento
			if (form[0].checkValidity && form[0].checkValidity()) {
				form.addClass('processing');
			} else {
				extraFields.addClass('visible');
				form.removeClass('processing');
			}

			return true;
		} else {
			// Deixar form em estado de processando (personalizar via CSS - page-index.css)
			form.addClass('processing');

			// Estamos fazendo um submit do form apenas com CPF
			var cpf = form.find('input[name="cpf"]').val();

			// Consultar o CPF via ajax
			$.get('/api/public/onestep/cpf/' + cpf, (result) => {
					console.log('DATA:', result);
					if (result.error) {
						// Recebemos um erro, exibir e remover estado de processando
						alert('Erro: ' + result.error);
						form.removeClass('processing');
					}
					else if (result != null && result.nome && result.email && result.celular) {
						// Resultado contém Nome e E-mail, preenchemos estes campos e finalizamos o submit
						form.find('input[name="candidato[nome]"]').val(result.nome);
						form.find('input[name="candidato[email]"]').val(result.email);
                        form.find('input[name="candidato[email]"]').attr("readonly", "true");
                        form.find('input[name="candidato[celular]"]').val(result.celular);
                        ignoreExtraFields = true;
                        extraFields.addClass('visible');
						form.removeClass('processing');
                        return false
                        // form.submit();
					}
					else if (result != null && result.nome && result.email) {
						// Resultado contém Nome e E-mail, preenchemos estes campos e finalizamos o submit
						form.find('input[name="candidato[nome]"]').val(result.nome);
                        form.find('input[name="candidato[email]"]').val(result.email);
                        form.find('input[name="candidato[email]"]').attr("readonly", "true");
                        ignoreExtraFields = true;
                        extraFields.addClass('visible');
						form.removeClass('processing');
                        return false
					}
					else {
						// Resultado é nulo, ou seja, o candidato não consta na base de dados.
						// Exibir campos de Nome e E-mail e remover estado de processando
						extraFields.addClass('visible');
						form.removeClass('processing');
					}
				});
				
			return false;
		}
	};

	window.fb_login = function (x) {
		if (x.status == 'connected') {
			// Colocamos o form em estado de processamento
			form.addClass('processing');
	
			// Rodamos query no Facebook para coletar Nome e E-mail
			FB.api('/me?fields=id,name,email', (response) => {
				// Caso encontrados, preenchemos estes campos temporariamente
				if (response.email) {
					// Ativar rotina do Facebook Login
					ignoreExtraFields = isFacebookLogin = true;
					form.find('input[name="candidato[email]"]').val(response.email);
					
					if (response.name)
						form.find('input[name="candidato[nome]"]').val(response.name);
						
					// Escondemos o Facebook Login
					$('.fb_iframe_widget').addClass('hidden');

					// Rodamos query na API pública do Ambiente de Conversão, para coletar Nome, CPF e E-mail
					$.get('/api/public/onestep/' + response.email, (result) => {
						if (result != null && result && result.cpf) {
							// Caso encontrados, preenchemos estes campos
							form.find('input[name="cpf"]').val(result.cpf).attr('readonly', true);
	
							if (result.nome)
								form.find('input[name="candidato[nome]"]').val(result.nome).attr('readonly', true);

							// Damos submit no form
							form.submit();
						} else {
							// Caso não encontrados, já abrimos os campos de Nome e E-mail e removemos o estado de processamento
							extraFields.addClass('visible');
							form.removeClass('processing');
						}
					});
				}
			});
		}
	}

	form.find('.cta').on('click', submitForm);
	form.find('input').on('keydown', (e) => {
		if (e.keyCode == 13){
			submitForm();
		}
	});
	form.on('submit', submitForm);
});