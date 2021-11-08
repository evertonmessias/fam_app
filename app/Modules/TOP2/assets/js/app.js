// Inicializar Vue.js
(() => {
    // URL Default
    const WEB_API = window.WEB_API = function (path) {
        // Adicionar barra antes
        if (path[0] != '/') path = '/' + path;

        // Retorna a URL formatada
        return `${WEB_API.URL}${path}`;
    }
    WEB_API.URL = window.WEB_API_URL || 'https://top.fam.br/api';

    // Valores padrão
    window.$app = Object.assign({
        hasOfflineApi: false,
        apiToken: null,
        error_msg: '',
        screen: 'loading',
        question_current_index: 0,
        question_current: null,
        questions: [],
        results: {},
        areas: {},
        user: { nome: '', email: '' }
    }, window.$app || {});

    // Métodos
    let $api = {
        loadQuestion () {
            this.error_msg = '';
            this.question_current = this.questions[this.question_current_index];
        },
        prevQuestion() {
            if (this.question_current_index <= 0) return;
            this.question_current_index--;
            this.loadQuestion();
        },
        nextQuestion () {
            if (!this.question_current.answer) {
                this.error_msg = 'Por favor, escolha uma opção';
                return;
            }

            this.question_current_index++;
            
            if (this.question_current_index < this.questions.length) {
                this.loadQuestion();
            } else {
                this.finishTest();
            }
        },
        startTest () {
            this.screen = 'test';
            return false;
        },
        finishTest () {
            this.screen = 'saving';

            // Vamos obter os resultados
            let results = this.getResults();

            // Criar o lead
            let lead = {
                version: 2,
                nome: this.user.nome,
                email: this.user.email,
                resultado: results.winner.id,
                resultado_raw: this.questions.map(question => question.answer),
                score_raw: results.results
            }

            // Salvar no lugar apropriado
            if (this.hasOfflineApi) {
                // Caso tenhamos API offline, salvar offline
                window.createLead(lead, this.showResults);
            } else {
                // Caso não tenhamos, salvaremos online
                $.post(WEB_API('/app/upload-lead'), {
                    lead: lead,
                    token: this.apiToken
                }, this.showResults);
            }
        },
        showResults () {
            this.screen = 'results';
        },
        getResults () {
            let results = {};
            let sum = 0;

            // Passar por cada pergunta, extrair as respostas contendo os resultados
            for (let iQuestion = 0, question; question = this.questions[iQuestion++];) {
                for (let iAnswer = 0, answer; answer = question.answer[iAnswer++];) {
                    // Corrigir o 'arte' para 'artes'
                    if (answer == 'arte') answer = 'artes';

                    // Soma global
                    sum++;

                    if (!results[answer]) results[answer] = 1;
                    else results[answer]++;
                }
            }

            // Calcular qual a maior compatibilidade e organizar
            let ordered = [];
            let winner = { id: null, name: null, value: 0 };
            for (let id in results) {
                let area = this.areas[id];
                let name = area.nome;
                let value = results[id];

                ordered.push([value, id]);

                if (value > winner.value) {
                    winner.id = id;
                    winner.name = name;
                    winner.value = value;
                    winner.area = area;
                }
            }

            // Organizar
            ordered.sort((a, b) => {
                if (a[0] > b[0]) return -1;
                if (a[0] < b[0]) return 1;
                return 0;
            });

            this.results = { sum: sum, ordered: ordered, winner: winner, results: results };
            return this.results;
        }
    };

    // Inicializa
    const $app = window.$app = new Vue({
        el: '#app',
        data: window.$app,
        methods: $api
    })

    // Ao carregar a janela
    $(window).ready(() => {
        // Função para processar perguntas e carregar dados
        let processData = data => {
            // Setar token
            $app.apiToken = data.token;

            // Setar perguntas
            for (i in data.perguntas) {
                let question = data.perguntas[i];
                question.answer = null;

                $app.questions.push(question);
            }

            // Setar áreas
            for (id in data.areas) {
                let area = data.areas[id];
                area.paragrafos = area.descricao.split("\n").map(text => text.trim());

                $app.areas[id] = area;
            }

            // Debug
            console.log(data);

            // Iniciar home
            $app.screen = 'home';

            // Carregar primeira questão
            $app.loadQuestion();
        };

        // Verificar se estamos no modo offline (função window.fetchLocalData presente?)
        if (window.fetchLocalData) {
            // Modo offline: ativamos a API offline e obtemos dados salvos previamente do disco
            $app.hasOfflineApi = true;
            processData(window.fetchLocalData());
        } else {
            // Modo online: obtemos dados da web
            // fetch('sample-questions.json')
            fetch(WEB_API('/app/update-local-data'))
                .then(raw => raw.json())
                .then(processData);
        }
    });
})();