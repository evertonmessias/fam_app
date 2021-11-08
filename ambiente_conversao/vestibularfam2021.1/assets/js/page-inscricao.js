$(document).ready(() => {
	// Métodos Computados ,Everton 21/10/2020
	let computed = {
		cursos_sort() {
			return _.orderBy(this.cursos, 'nome');
		},
		cursos2() {
			let cursos = Vue.util.extend({}, this.cursos_sort);
			for (var i in cursos) {
				if (cursos[i].id == this.opcao1) {
					delete cursos[i];
				}
			}
			return cursos;
		},
		cursos3() {
			let cursos = Vue.util.extend({}, this.cursos_sort);
			for (var i in cursos) {
				if (cursos[i].id == this.opcao1 || cursos[i].id == this.opcao2) {
					delete cursos[i];
				}
			}
			return cursos;
		},
		cursos2_semTec() {
			let cursos = Vue.util.extend({}, this.cursos_sort);
			for (var i in cursos) {
				if (cursos[i].id == this.opcao1 || cursos[i].id == 49 || cursos[i].id == 63 || cursos[i].id == 67 || cursos[i].id == 56 || cursos[i].id == 57 || cursos[i].id == 52 || cursos[i].id == 50 || cursos[i].id == 65 || cursos[i].id == 54 || cursos[i].id == 59 || cursos[i].id == 61) {
					delete cursos[i];
				}
			}
			return cursos;
		},
		cursos3_semTec() {
			let cursos = Vue.util.extend({}, this.cursos_sort);
			for (var i in cursos) {
				if (cursos[i].id == this.opcao1 || cursos[i].id == this.opcao2 || cursos[i].id == 49 || cursos[i].id == 63 || cursos[i].id == 67 || cursos[i].id == 56 || cursos[i].id == 57 || cursos[i].id == 52 || cursos[i].id == 50 || cursos[i].id == 65 || cursos[i].id == 54 || cursos[i].id == 59 || cursos[i].id == 61) {
					delete cursos[i];
				}
			}
			return cursos;
		},

		cursos2_tec() {
			let cursos = Vue.util.extend({}, this.cursos_sort);
			for (var i in cursos) {
				if (cursos[i].id == this.opcao1 || (cursos[i].id != 49 && cursos[i].id != 63 && cursos[i].id != 67 && cursos[i].id != 56 && cursos[i].id != 57 && cursos[i].id != 52 && cursos[i].id != 50 && cursos[i].id != 65 && cursos[i].id != 54 && cursos[i].id != 59 && cursos[i].id != 61)) {
					delete cursos[i];
				}
			}
			return cursos;
		},
		cursos3_tec() {
			let cursos = Vue.util.extend({}, this.cursos_sort);
			for (var i in cursos) {
				if (cursos[i].id == this.opcao1 || cursos[i].id == this.opcao2 || (cursos[i].id != 49 && cursos[i].id != 63 && cursos[i].id != 67 && cursos[i].id != 56 && cursos[i].id != 57 && cursos[i].id != 52 && cursos[i].id != 50 && cursos[i].id != 65 && cursos[i].id != 54 && cursos[i].id != 59 && cursos[i].id != 61)) {
					delete cursos[i];
				}
			}
			return cursos;
		},

		cursos2_so_presencial() {
			let cursos = Vue.util.extend({}, this.cursos_sort);
			for (var i in cursos) {
				if (cursos[i].id == this.opcao1 || cursos[i].id == 69 || cursos[i].id == 77 || cursos[i].id == 76 || cursos[i].id == 74 || cursos[i].id == 75 || cursos[i].id == 49 || cursos[i].id == 63 || cursos[i].id == 67 || cursos[i].id == 56 || cursos[i].id == 57 || cursos[i].id == 52 || cursos[i].id == 50 || cursos[i].id == 65 || cursos[i].id == 54 || cursos[i].id == 59 || cursos[i].id == 61) {
					delete cursos[i];
				}
			}
			return cursos;
		},
		cursos3_so_presencial() {
			let cursos = Vue.util.extend({}, this.cursos_sort);
			for (var i in cursos) {
				if (cursos[i].id == this.opcao1 || cursos[i].id == this.opcao2 || cursos[i].id == 69 || cursos[i].id == 77 || cursos[i].id == 76 || cursos[i].id == 74 || cursos[i].id == 75 || cursos[i].id == 49 || cursos[i].id == 63 || cursos[i].id == 67 || cursos[i].id == 56 || cursos[i].id == 57 || cursos[i].id == 52 || cursos[i].id == 50 || cursos[i].id == 65 || cursos[i].id == 54 || cursos[i].id == 59 || cursos[i].id == 61) {
					delete cursos[i];
				}
			}
			return cursos;
		},
		cursos2_ead() {
			let cursos = Vue.util.extend({}, this.cursos_sort);
			for (var i in cursos) {
				if (cursos[i].id == this.opcao1 || (cursos[i].id != 69 && cursos[i].id != 77 && cursos[i].id != 76 && cursos[i].id != 74 && cursos[i].id != 75)) {
					delete cursos[i];
				}
			}
			return cursos;
		},
		cursos3_ead() {
			let cursos = Vue.util.extend({}, this.cursos_sort);
			for (var i in cursos) {
				if (cursos[i].id == this.opcao1 || cursos[i].id == this.opcao2 || (cursos[i].id != 69 && cursos[i].id != 77 && cursos[i].id != 76 && cursos[i].id != 74 && cursos[i].id != 75)) {
					delete cursos[i];
				}

			}
			return cursos;
		},

		cidades_estado() {
			if (!this.estados || !this.estado)
				return [];

			return this.estados[this.estado];
		},
		/*candidato_idade: {
			cache: false,
			get () {
				if (!(this.aluno && this.aluno.data_nascimento))
					return

				let dataNascimentoRaw = this.aluno.data_nascimento
				let dataNascimentoProc = dataNascimentoRaw.substr(0, 2) + '/' + dataNascimentoRaw.substr(2, 2) + '/' + dataNascimentoRaw.substr(4, 4)
				let dataNascimento = new Date(dataNascimentoProc.split('/').reverse().join('-'))
				let dataNascimentoDiff = (new Date()).getTime() - dataNascimento.getTime()

				let idade = Math.floor(dataNascimentoDiff / (365.25 * 24 * 3600 * 1000))
				return idade
			},
		}*/
	};

	// Métodos
	let methods = {
		triggerUpdate() {
			this.$forceUpdate();
		},
		setCursos(cursos) {
			this.cursos = cursos;
		},
		setEstados(estados) {
			this.estados = estados;
		},
		setEstado(estado) {
			this.estado = estado;
		}
	};

	let cursoInicial = curso ? curso : null;

	// Inicializar VueJS
	let $app = acfam.init({
		aluno: aluno || {},
		cursos: null,
		opcao1: cursoInicial,
		opcao2: null,
		opcao3: null,
		estado: null,
		estados: null,
		cidade: aluno.cidade_id || 4724, // Por padrão, Americana (só exibirá caso selecionar SP)
		usar_enem: null,
		data_prova: null,
		midias: midias,
		pcd: null // Possui Condições/Deficiências?
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

	// Carregar lista de cidades
	$.getJSON('./assets/cidades.json')
		.then((estados) => {
			// Atualizar no app
			$app.setEstados(estados);

			for (var uf in estados) {
				var cidades = estados[uf];
				cidades.forEach((cidade) => {
					if (cidade.id == aluno.cidade_id)
						$app.setEstado(uf);
				})
			}
		});

	$('select').on('change', $app.triggerUpdate);
});