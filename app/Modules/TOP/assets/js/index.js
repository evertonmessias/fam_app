window.preload = function() {
  var images = [];
  for (var i = 0; i < arguments.length; i++) {
    var img = new Image();
    img.src = arguments[i];
    images.push(img);
  }
  return images;
}

window.setCover = (capa) => {
  var e = $('#cover');

  if (capa.length == 0 || capa == undefined || !capa) {
    e.fadeOut(500);
  } else {
    var img = new Image();
    img.src = '/img/capa-' + capa;
    img.onload = function () {
      e.css('background-image', 'url(' + this.src + ')');
      e.fadeIn(500);
    }
  }
};

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
  
/*
  var questions = [{
    question: "<span style='font-size:.7em;color:#4267b2;'>1/26</span> Me considero uma pessoa criativa, artística:",
    choices: ["Sim", "Não", "Talvez / Concordo em partes"],
    correctAnswer: 2
  }, {
    question: "<span style='font-size:.7em;color:#4267b2;'>2/26</span> Tenho muita curiosidade em geologia e meio ambiente:",
    choices: ["Sim", "Não", "Talvez / Concordo em partes"],
    correctAnswer: 1
  }, {
    question: "<span style='font-size:.7em;color:#4267b2;'>3/26</span> Consigo tomar decisões baseadas na razão ao invés da emoção:",
    choices: ["Sim", "Não", "Talvez / Concordo em partes"],
    correctAnswer: 2
  }, {
   question: "<span style='font-size:.7em;color:#4267b2;'>4/26</span> Minha paixão são números... Matemática:",
    choices: ["Sim", "Não", "Talvez / Concordo em partes"],
    correctAnswer: 2
  }, {
    question: "<span style='font-size:.7em;color:#4267b2;'>5/26</span> Gosto de aprender outros idiomas:",
    choices: ["Sim", "Não", "Talvez / Concordo em partes"],
    correctAnswer: 2
  }, {
    question: "<span style='font-size:.7em;color:#4267b2;'>6/26</span> Me considero uma pessoa organizada e detalhista:",
    choices: ["Sim", "Não", "Talvez / Concordo em partes"],
    correctAnswer: 2
  }, {
    question: "<span style='font-size:.7em;color:#4267b2;'>7/26</span> Adoro desenhar e acho que levo jeito pra isso:",
    choices: ["Sim", "Não", "Talvez / Concordo em partes"],
    correctAnswer: 2
  }, {
    question: "<span style='font-size:.7em;color:#4267b2;'>8/26</span> Pra mim é muito importante estar cercado de pessoas:",
    choices: ["Sim", "Não", "Talvez / Concordo em partes"],
    correctAnswer: 2
  }, {
    question: "<span style='font-size:.7em;color:#4267b2;'>9/26</span> Gosto muito de história, religião e temas relacionados com a sociedade e a política:",
    choices: ["Sim", "Não", "Talvez / Concordo em partes"],
    correctAnswer: 2
  }, {
    question: "<span style='font-size:.7em;color:#4267b2;'>10/26</span> A mente humana é uma grande curiosidade para mim, gosto de saber como nossos pensamentos funcionam:",
    choices: ["Sim", "Não", "Talvez / Concordo em partes"],
    correctAnswer: 2
  }, {
	question: "<span style='font-size:.7em;color:#4267b2;'>11/26</span> Prefiro matemática do que português:",
    choices: ["Sim", "Não", "Talvez / Concordo em partes"],
    correctAnswer: 2
  }, {
	question: "<span style='font-size:.7em;color:#4267b2;'>12/26</span> Gosto de levar minha vida sem rotina:",
    choices: ["Sim", "Não", "Talvez / Concordo em partes"],
    correctAnswer: 2			   
 }, {
	question: "<span style='font-size:.7em;color:#4267b2;'>13/26</span> Costumo confiar em:",
    choices: ["Percepção imediata", "Costumes e tradições", "Intuição", "Razão e lógica"],
    correctAnswer: 2			   
 }, {
	question: "<span style='font-size:.7em;color:#4267b2;'>14/26</span> Me considero muito criativo! Gosto de inventar e imaginar coisas novas:",
    choices: ["Sim", "Não", "Talvez / Concordo em partes"],
    correctAnswer: 2		   
 }, {
	question: "<span style='font-size:.7em;color:#4267b2;'>15/26</span> Em uma escola você gostaria de ser:",
    choices: ["Professor de educação física", "Diretor", "Professor de matemática", "Concordo em partes"],
    correctAnswer: 2			   
 }, {
	question: "<span style='font-size:.7em;color:#4267b2;'>16/26</span> Sou uma pessoa muito organizada e pontual. Gosto de planejar tudo que vou fazer. Não gosto de ter surpresas pelo meio do caminho:",
    choices: ["Sim", "Não", "Talvez / Concordo em partes"],
    correctAnswer: 2	   
  }, {
  	question: "<span style='font-size:.7em;color:#4267b2;'>17/26</span> Sobre Você:",
      choices: ["É introspectivo: fica na sua", "Não gosta de rotina e regras", "É disciplinado", "Interage com todos os perfis de pessoa"],
      correctAnswer: 2		   
  }, {
  	question: "<span style='font-size:.7em;color:#4267b2;'>18/26</span> A vida é mais interessante:",
      choices: ["Quando há situações desafiadoras e mudanças", "Quando há segurança, estabilidade financeira e interação social", "Quando pode fazer algo para ajudar alguém ", "Quando pode ir além do comum "],
      correctAnswer: 2			   
  }, {
  	question: "<span style='font-size:.7em;color:#4267b2;'>19/26</span> Lá em casa sou eu quem entende da internet, computador, etc. Sempre ajudo todo mundo:",
      choices: ["Sim", "Não", "Talvez / Concordo em partes"],
      correctAnswer: 2			   
  }, {
  	question: "<span style='font-size:.7em;color:#4267b2;'>20/26 Com qual área de estudo você mais se identifica:",
      choices: ["Tenho dificuldade com números, gosto muito de lidar com pessoas.", "Gosto de cuidar de pessoas e da área da saúde", "Prefiro estar perto dos meu animais", "Sem duvida minha predileção são as ciências exatas, amo números "],
      correctAnswer: 2			   
  }, {
  	question: "<span style='font-size:.7em;color:#4267b2;'>21/26</span> Penso que nosso corpo é nosso templo. Quero saber sobre as funções do nosso organismo como um todo:",
      choices: ["Sim", "Não", "Talvez / Concordo em partes"],
      correctAnswer: 2
  }, {
  	question: "<span style='font-size:.7em;color:#4267b2;'>22/26</span> Gosto de cuidar de pessoas e ajudá-las em quaisquer necessidades:",
      choices: ["Sim", "Não", "Talvez / Concordo em partes"],
      correctAnswer: 2
  }, {
  	question: "<span style='font-size:.7em;color:#4267b2;'>23/26</span> Adoro programações e desenvolvimento de softwares, jogos, aplicativos e linguagens de programas:",
      choices: ["Sim", "Não", "Talvez / Concordo em partes"],
      correctAnswer: 2
  }, {
  	question: "<span style='font-size:.7em;color:#4267b2;'>24/26</span> Me preocupo com a preservação da natureza e proteção do meio ambiente. Estou sempre fazendo campanhas e postando matérias no Facebook:",
      choices: ["Sim", "Não", "Talvez / Concordo em partes"],
      correctAnswer: 2
  }, {
  	question: "<span style='font-size:.7em;color:#4267b2;'>25/26</span> Sou muito curioso, sempre estou mexendo em tomadas, placas, rádios. Adoro desmontar tudo:",
      choices: ["Sim", "Não", "Talvez / Concordo em partes"],
      correctAnswer: 2
  }, {
  	question: "<span style='font-size:.7em;color:#4267b2;'>26/26</span> Gosto de projetar e desenvolver coisas novas e criativas, mesmo que seja só em pensamento:",
      choices: ["Sim", "Não", "Talvez / Concordo em partes"],
      correctAnswer: 2
  }
    ];
*/
  
    var questionCounter = 0; //Tracks question number
    var selections = []; //Array containing user choices
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
      if (!selections[questionCounter]) {
        $('#warning').text('Por favor selecione uma opção.');
      } else {
        questionCounter++;
        displayNext();
  	  $('#warning').text('');
      }
    });

    // Click handler inputs
    $('body').on('click', '.answer', function (e) {
      
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

    $('input').on('click', function (e) {
      goNext(e);
    });
    
    
    // Click handler for the 'Start Over' button
    $('#start').on('click', function (e) {
      e.preventDefault();
      
      if(quiz.is(':animated')) {
        return false;
      }
      questionCounter = 0;
      selections = [];
      displayNext();
      $('#score').hide();
      $('#start').hide();
      setCover(false);
    });
    
    // Creates and returns the div that contains the questions and 
    // the answer selections
    function createQuestionElement(index) {
      var qElement = $('<div>', {
        id: 'question'
      });

      //$('#counter').text((questionCounter + 1) + ' de ' + questions.length);
      var progressBar = new ldBar("#quizProgress");
      progressBar.set((questionCounter+1) * (100 / questions.length));

      var qText = questions[index].question;
      var lChar = qText.substr(qText.length - 1);

      // Forçar afirmações
      if (lChar != '?' && lChar != ':' && lChar != '.')
        qText += '.';
          
      var question = $('<p>').append(qText);
      qElement.append(question);
      
      var radioButtons = createRadios(index);
      qElement.append(radioButtons);
  	// this is new
  	var warningText = $('<p id="warning">');
    qElement.append(warningText);
    
    //

  	return qElement;

    }
    
    // Creates a list of the answer choices as radio inputs
    function createRadios(index) {
      var radioList = $('<ul>');
      var item;
      var input = '';
      for (var choice in questions[index].choices) {
        var value = questions[index].choices[choice];
        var label = $('<label></label>').text(choice);
        item = $('<li>');
        input = $('<input type="radio" name="answer" value=' + choice + ' class="answer" />').data('values', value);
        item.append(input).append(label);
        radioList.append(item);
      }
      /*for (var i = 0; i < questions[index].choices.length; i++) {
        item = $('<li>');
        input = '<input type="radio" name="answer" value=' + i + ' />';
        input += questions[index].choices[i];
        item.append(input);
        radioList.append(item);
      }*/
      return radioList;
    }
    
    // Reads the user selection and pushes the value to an array
    function choose() {
      // selections[questionCounter] = +$('input[name="answer"]:checked').val();
      selections[questionCounter] = $('input[name="answer"]:checked').data('values');
    }
    
    // Displays next requested element
    function displayNext() {
      quiz.fadeOut(function() {
        $('#question').remove();
        
        if(questionCounter < questions.length){
          var nextQuestion = createQuestionElement(questionCounter);
          quiz.append(nextQuestion).fadeIn();
          if (!(isNaN(selections[questionCounter]))) {
            // $('input[value='+selections[questionCounter]+']').prop('checked', true);
          }
          
          // Controls display of 'prev' button
          if(questionCounter === 1){
            $('#prev').show();
          } else if(questionCounter === 0){
            
            $('#prev').hide();
            $('#next').show();
          }
         }else {
          var scoreElem = displayScore();
          quiz.append(scoreElem).fadeIn();
          $('#next').hide();
          $('#prev').hide();
          // $('#start').show();
        }
      });

       // Click handler inputs
    $('.answer').on('click', function (e) {
      
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
      var score = $('<h3>',{id: 'question'});

      score.append('Carregando seus resultados...');

      var form = $('#resultados')
        .append($('<textarea name="resultados"></textarea>').val(JSON.stringify(selections)))
        .serialize();
        // .submit();

        $.post('/resultados', form)
          .then((resultados) => {
            $('#score').remove();
            quiz.fadeOut(500);
            setTimeout(() => {
              quiz.html('');
              quiz.append(resultados).fadeIn();
              $('#next').hide();
              $('#prev').hide();
              $('#start').show();
            }, 500);
          });

    /*  
      var numCorrect = 0;
      for (var i = 0; i < selections.length; i++) {
        if (selections[i] === questions[i].correctAnswer) {
          numCorrect++;
        }
      }
  	// Calculate score and display relevant message
  	var percentage = numCorrect / questions.length;
  	if (percentage >= 0.9){
      	score.append('Você acertou ' + numCorrect + ' de ' +
                   questions.length + ' .Gostaria de fazer o teste novamente?');
  	}
  	
  	else if (percentage >= 0.7){
      	score.append('Você acertou ' + numCorrect + ' de ' +
                   questions.length + ' .Gostaria de fazer o teste novamente?');
  	}
  	
  	else if (percentage >= 0.5){
      	score.append('Você acertou ' + numCorrect + ' de ' +
                   questions.length + ' .Gostaria de fazer o teste novamente?');
  	}
  	
  	else {
      	score.append('Você acertou ' + numCorrect + ' de ' +
                   questions.length + ' .Gostaria de fazer o teste novamente?');
  	}*/
      return score;
    }
  });
});

preload (
  '/img/capa-humanas.jpg',
  '/img/capa-exatas.jpg',
  '/img/capa-biologicas.jpg',
  '/img/capa-artes.jpg',
  '/img/capa-engenharias.jpg',
  '/img/capa-saude.jpg'
);