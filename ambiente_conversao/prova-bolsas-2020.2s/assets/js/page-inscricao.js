$(document).ready(() => {
	// Métodos Computados
	let computed = {
		cursos_sort() {
			return _.orderBy(this.cursos, 'nome');
		},
		cursos2() {
			let cursos = Vue.util.extend({}, this.cursos_sort);
			for (var i in cursos) {
				if (cursos[i].id == this.opcao1)
					delete cursos[i];
			}
			return cursos;
		},
		cursos3() {
			let cursos = Vue.util.extend({}, this.cursos_sort);
			for (var i in cursos) {
				if (cursos[i].id == this.opcao1 || cursos[i].id == this.opcao2)
					delete cursos[i];
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