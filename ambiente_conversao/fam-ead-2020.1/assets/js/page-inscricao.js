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
		}
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