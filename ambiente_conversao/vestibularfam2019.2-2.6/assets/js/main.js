/**
 * Ambiente de Conversão FAM v2.0.0
 */

window.acfam = (() => {
	let $app = this;

	// Permite criação de filtros personalizáveis por página
	this.filters = function (filters) {
		for (var filter in filters) {
			var fn = filters[filter];
			Vue.filter(filter, fn);
		}
	}

	// Inicializa o VueJS quando necessário
	this.init = function (data, options) {
		// Opções do VueJS
		options = options || {};
		Object.assign(options, {
			el: '#app',
			data: data
		});

		// Inicializar
		$app.vue = new Vue(options);

		// Retornar VueJS
		return $app.vue;
	}

	// Preparar filtros padrão para o VueJS
	this.filters({
		// Debug
		debug (value) { console.log(value); return value },

		// Monetário
		currency (value) { return value ? value.toFixed(2) : value; }
	});

	// Finalizar pré-inicialização
	return this;
})();