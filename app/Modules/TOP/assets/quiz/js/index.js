var lastQ = "This is the last question. Try the submit button.";
var firstQ = "You are on the first question.";
var unansweredQ = "Please make a selection.";
var earlySubmit = "Please complete all questions.";
/*$(document).ready( function() {
$.getJSON('https://gist.github.com/catherinemaldonado/0c72e27f347dc5204214.js', function(data) {
  for (var i = 0; i < data.questions.length; i++) {
            var question = '<h3>'+data.questions[i].question+'</h3>';
            var thisNumber = Number(i)+1;
            var answers = '<ol id="answers_'+thisNumber+'"></ol>';
            var answersID = $(answers).attr('id');
    
    var build = '<div class="question"><div class="content">'+question+'</div></div>';
    $("#questions").append(build);
  }
 });
 });*/

$(".question").css({right: '-705px'});

$("#questions div:first").addClass("active").show().animate({right: '0px'});

var questions = $("#questions > div");

for (var i = 0; i < questions.length; i++) {
  $("#progress").append("<li></li>");
}

var progressItems = $("#progress li").length;
    var percent = (1/progressItems)*92;
    $("#progress li").css("width",percent+"%");

function checkIndex (){
  
  var current = $(".active").index();
  var total = $("#questions > div").length;
  var currentNum = Number(current);
  
  $("#progress li").removeClass("status");
  $("#progress li").slice(0,currentNum).addClass("status");
  
  if (current == total){
    $("#next").addClass("disabled");
  }
  
  if (current == 1){
    $("#previous").addClass("disabled");
  }else{
    $("#previous").removeClass("disabled");
  }
  
  
}

checkIndex();


function checkChecked() {
  var current = $(".active ol input:checked");
  if ($(current).length == 1){
    $("#next").removeClass("disabled"); 
  }else{
    $("#next").addClass("disabled");
  }
  
  if ($(current).length == 1 && $(".active").index() == $("#questions > div").length){
    $("#submit").removeClass("disabled"); 
  }
  
  if ($(".active").index() == $("#questions > div").length){
      $("#next").addClass("disabled"); 
  }

}

$("#next").on("click", function () {
    $("#error").fadeOut("slow");
    if ($(this).hasClass("disabled") && $(".active").index() == $("#questions > div").length){
      $("#error").html(lastQ);
      $("#error").fadeIn("slow");
      return false;
    }else if ($(this).hasClass("disabled")){
      $("#error").html(unansweredQ);
      $("#error").fadeIn("slow");
      return false;
    }

    var slide = $(".active");
    var next = $(".active").next(".question");
    // SLIDE ANIMATION TRANSITION
    $(slide).animate({left: '-705px'},450).toggleClass("active");
    $(next).animate({right: '0px'},450).toggleClass("active");
  
    function resetDiv() {
      $(".active").prev(".question").css({position: "absolute",width: "100%", left: "-705px",right:"auto"});
    }

    setTimeout(resetDiv, 900);
    
    checkChecked();
    checkIndex();
    
});

$("#previous").on("click", function () {
  if ($(this).hasClass("disabled")){
      $("#error").html(firstQ);
      $("#error").fadeIn("slow");
      return false;
    }
  /*event.preventDefault()*/
  var slide = $(".active");
  var prev = $(".active").prev(".question");

  // SLIDE ANIMATION TRANSITION
  $(slide).css({left: "auto"}).animate({right: '-705px',left:'auto'},350).toggleClass("active");
  $(prev).animate({left: '0px'},450).toggleClass("active");
  $("#error").fadeOut("slow");
  checkIndex();
  checkChecked();
});

$("ol li").on("click", function () {
    var $this = $(this).find("input:radio");
    var all = $(this).parent().find("input:radio").not($this);
    var val = $this.val();

    $this.prop("checked", true);

    if ($this.prop("checked") === true) {
        all.filter(function () {
            return $(this).val() === val;
        }).prop("checked", false);
    }
  $("#error").fadeOut("slow");

  checkChecked();
});

$("#reset").on("click", function () {
  $("#error").fadeOut("slow");
  $("#submit").addClass("disabled");
  $("input:radio").each( function() {
      $(this).attr('checked',false);
  });
  checkChecked();
   $(".question").removeClass("active").css({right: '-705px',left: 'auto'});

  $("#questions div:first").fadeIn("slow").css({left: "auto"}).animate({right: '0',left:'auto'},350).toggleClass("active");
  checkIndex();
});

$(document).keyup(function(e) {
    /*checkIndex();*/
    if(e.which == 13 || e.which == 39) {
       $("#next").trigger('click');
    }else if(e.which == 37) {
       $("#previous").trigger('click');
    }
});


$("#submit").on("click", function () {
if ($(this).hasClass("disabled")){
      $("#error").html(earlySubmit);
      $("#error").fadeIn("slow");
      return false;
    }else{
      alert("This will eventually do something awesome!");
    }
});