// JavaScript Document
$(document).ready(function(){
    "use strict";
    
    $.get('/api/questions')
    .then((raw) => {
        var questions = [];
        
        for (var i = 0; i < raw.length; i++) {
            var q = raw[i];
            
            questions.push({
                question: q.pergunta,
                choices: q.respostas
            });
        }

        $.get('/api/questions-research')
            .then((raw) => {

            var questionsResearch = [];
        
            for (var i = 0; i < raw.length; i++) {
                var q = raw[i];
                var escolhas = q.respostas ? q.respostas.hasOwnProperty("opcoes") ? q.respostas.opcoes : q.respostas : null;

                questionsResearch.push({
                    question: q.pergunta,
                    choices: escolhas,
                    multipleChoice: q.multipla_escolha,
                    pesquisa: true
                });
            }

            var allQuestions = []
            // Pega as X primeiras questões direto das questões normais do TOP
            var perguntasNormaisIniciais = 10;
            allQuestions = allQuestions.concat(questions.slice(0, perguntasNormaisIniciais));

            // Pega todas da pesquisa e joga no meio, intercalando com as questões normais
            // Exceto a última questão da pesquisa
            for(var i = 0; i < questionsResearch.length - 1; i++) {
                allQuestions.push(questionsResearch[i]);
                allQuestions.push(questions[perguntasNormaisIniciais + i]);
            }

            // Pegar o resto das questões normais, se houver
            // Coloca a última da pesquisa, que é perguntando se não encontrou o curso desejado
            allQuestions = allQuestions.concat(questions.slice(perguntasNormaisIniciais + questionsResearch.length, questions.length + 1));
            allQuestions.push(questionsResearch[questionsResearch.length - 1]);

            var questionCounter = 0; //Tracks question number
            var selections = []; //Array containing user choices
            var respostasPesquisa = []; //Array containing user choices
            var quiz = $('.content'); //Quiz div object
            
            // Display initial question
            displayNext();
            
            // Click handler for the 'next' button
            $('#next').on('click', function (e) {
                e.preventDefault();
                
                // Suspend click listener during fade animation
                if(quiz.is(':animated')) {        
                    return false;
                }
                choose();
                
                // If no user selection, progress is stopped
                if (!selections[questionCounter] && !respostasPesquisa[questionCounter]) {
                    $('#warning').text('Por favor responda a questão.');
                } else {
                    questionCounter++;
                    displayNext();
                    $('#warning').text('');
                }
            });
            
            // Click handler inputs
            // Se for múltipla escolha ou de justificar, não deve executar
            $('body').on('click', '.answer:not(.multipleChoice):not(.justify)', function (e) {
                // Suspend click listener during fade animation
                if(quiz.is(':animated')) {        
                    return false;
                }
                choose();
                
                // If no user selection, progress is stopped
                if (!selections[questionCounter] && !respostasPesquisa[questionCounter]) {
                    $('#warning').text('Por favor responda a questão.');
                } else {
                    questionCounter++;
                    displayNext();
                    $('#warning').text('');
                }
            });

            $('body').on('click', '.answer.justify', function (e) {
                if ($(".typeField").length < 1) {
                    var typeField = $("<textarea name='justifyField' class='typeField' placeholder='Comente sua resposta por favor' rows=2></textarea>");
                    $("#question").append(typeField);
                }
                $(".answer.justify:checked").each((i, e) => {
                    $(e).prop("checked", false);
                })
                $(this).prop("checked", true);
            });
            
            // Click handler for the 'prev' button
            $('#prev').on('click', function (e) {
                e.preventDefault();
                
                if(quiz.is(':animated')) {
                    return false;
                }
                choose();
                questionCounter--;
                displayNext();
            });            
            
            // Click handler for the 'Start Over' button
            $('#start').on('click', function (e) {
                window.location.href = '/';
            });

            // Creates and returns the div that contains the questions and 
            // the answer selections
            var progressBar;
            function createQuestionElement(index) {
                var qElement = $('<div>', {
                    id: 'question'
                });
                
                progressBar = new ldBar("#quizProgress");
                //$('#counter').text((questionCounter + 1) + ' de ' + questions.length);
                progressBar.set((questionCounter) * (100 / allQuestions.length));
                
                var qText = allQuestions[index].question;
                var lChar = qText.substr(qText.length - 1);
                
                // Forçar afirmações
                if (lChar != '?' && lChar != ':' && lChar != '.')
                qText += '.';
                
                var question = $('<p class="title">').append(qText);
                qElement.append(question);
                
                var multiplaEscolha = allQuestions[index].hasOwnProperty("multipleChoice") ? allQuestions[index].multipleChoice : false;
                var radioButtons = createInputs(index, multiplaEscolha);
                qElement.append(radioButtons);
                // this is new
                var warningText = $('<p id="warning">');
                qElement.append(warningText);
                
                //
                
                return qElement;
                
            }

            function makeid(length) {
                var result           = '';
                var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                var charactersLength = characters.length;
                for ( var i = 0; i < length; i++ ) {
                    result += characters.charAt(Math.floor(Math.random() * charactersLength));
                }
                return result;
            }
            
            // Creates a list of the answer choices as radio inputs
            function createInputs(index, multiplaEscolha = false) {
                var radioList = $('<ul class="answer-list">');
                var item;
                var input = "";
                var choices = allQuestions[index].choices;
                var extraClass = multiplaEscolha ? "multipleChoice" : "";

                if (Array.isArray(choices)) {
                    for (var i = 0; i < choices.length; i++) {
                        var value = choices[i];
                        var id = makeid(8);
                        var name = 'answer';
                        var label = $('<label for="' + id + '" class="answer-option"></label>').text(value);
                        extraClass = choices[i].includes("..") ? extraClass + " justify" : extraClass;

                        item = $('<li class="answer-list-item">');
                        input = $('<input type="checkbox" name="' + name + '" class="answer ' + extraClass + '" id="' + id + '" value="' + value + '" class="answer" />').data('values', value);
                        item.append(input).append(label);
                        radioList.append(item);
                    }
                    if (multiplaEscolha)
                        radioList.append($('<span class="answer-info">(Selecione mais de um se necessário)</span>'));
                } else if (choices === null) {
                    return $("<textarea name='justifyField' class='typeField' placeholder='Digite aqui sua resposta' rows=2></textarea>");
                }
                else {
                    for (var choice in choices) {
                        var value = choices[choice];
                        var id = makeid(8);
                        var name = 'answer';
                        var label = $('<label for="' + id + '" class="answer-option"></label>').text(choice);
                        extraClass = choices[choice].includes("..") ? extraClass + " justify" : extraClass;

                        item = $('<li class="answer-list-item">');
                        input = $('<input type="checkbox" name="' + name + '" class="answer ' + extraClass + '" id="' + id + '" value="' + choice + '" class="answer" />').data('values', value);
                        item.append(input).append(label);
                        radioList.append(item);
                    }
                    if (multiplaEscolha)
                        radioList.append($('<span class="answer-info">(Selecione mais de um se necessário)</span>'));
                }
                return radioList;
            }
            
            // Reads the user selection and pushes the value to an array
            function choose() {
                var elementosResposta = $('input[name="answer"]:checked');
                var resposta = "";

                // Se for da pesquisa
                if (allQuestions[questionCounter].pesquisa) {
                    // Se forem várias respostas
                    if (elementosResposta.length > 1) {
                        elementosResposta.each((i, e) => {
                            resposta += e.value;
                            if (i < elementosResposta.length - 1)
                                resposta += "; "
                        });
                    }
                    // Se for 1 resposta
                    else if (elementosResposta.length > 0) {
                        resposta = elementosResposta.data('values');
                        // Se for com justificativa
                        if (resposta.includes("..")) {
                            resposta += " " + $(".typeField")[0].value;
                        }
                    }
                    // Se não achar inputs normais, ir para o textfield
                    else {
                        resposta = $(".typeField")[0].value;
                    }

                    if (resposta) {
                        respostasPesquisa[questionCounter] = { "pergunta": allQuestions[questionCounter].question, "resposta": resposta };
                    } else {
                        respostasPesquisa[questionCounter] = null;
                    }
                }

                // Se não for da pesquisa
                else {
                    selections[questionCounter] = elementosResposta.data('values');
                }
            }

            // Displays next requested element
            function displayNext() {
                quiz.fadeOut(function() {
                    $('#question').remove();
                    
                    if(questionCounter < allQuestions.length){
                        var nextQuestion = createQuestionElement(questionCounter);
                        quiz.append(nextQuestion).fadeIn();
                        if (!(isNaN(selections[questionCounter]))) {
                            // $('input[value='+selections[questionCounter]+']').prop('checked', true);
                        }
                        
                        // Controls display of 'prev' button
                        if(questionCounter === 1){
                            $('#prev').css("visibility", "visible");
                        } else if(questionCounter === 0){
                            
                            $('#prev').css("visibility", "hidden");
                            $('#next').css("visibility", "visible");
                        }
                    }else {
                        var scoreElem = displayScore();
                        quiz.append(scoreElem).fadeIn();
                        $('#next').css("visibility", "hidden");
                        $('#prev').css("visibility", "hidden");
                        // $('#start').show();
                    }
                });
                
                // Click handler inputs
                // Se for múltipla escolha ou de justificar, não deve executar
                $('.answer:not(.multipleChoice):not(.justify)').on('click', function (e) {
                    
                    // Suspend click listener during fade animation
                    if(quiz.is(':animated')) {        
                        return false;
                    }
                    choose();
                    
                    // If no user selection, progress is stopped
                    if (!selections[questionCounter]) {
                        $('#warning').text('Por favor selecione uma opção.');
                    } else {
                        questionCounter++;
                        displayNext();
                        $('#warning').text('');
                    }
                });
            }
            
            // Computes score and returns a paragraph element to be displayed
            function displayScore() {
                progressBar.set(100);
                var score = $('<h3 class="carregando">',{id: 'question'});
                
                score.append('Carregando seus resultados...');
                
                // Juntando e limpando as respostas
                var resultado = {
                    "areas": selections.filter(function (el) {
                        return el != null;
                    }),
                    "pesquisa": respostasPesquisa.filter(function (el) {
                        return el != null;
                    })
                }

                var form = $('#resultados')
                .append($('<textarea name="resultados"></textarea>').val(JSON.stringify(resultado)))
                .serialize();
                // .submit();
                
                $.post('/resultados', form)
                .then((resultados) => {
                    $('#score').remove();
                    quiz.fadeOut(500);
                    setTimeout(() => {
                        quiz.html('');
                        quiz.append(resultados).fadeIn();
                        $('#next').css("visibility", "hidden");
                        $('#prev').css("visibility", "hidden");
                        $('#start').show();
                    }, 500);
                });
                return score;
            }
        });
    });
});