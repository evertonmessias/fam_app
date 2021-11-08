<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php include ("includes/head.php"); ?>
</head>

	<body> 
<section class="full" style="background-image: url(img/bg-home.png) !important;background-size:cover;"> 
    <div class="container">
        <div class="row">
            <div class="col-lg-1">
                <a href="index.php">
                    <img border="0" src="img/seta-left.png" align="left" class="seta" style="margin-top:300px;" />
                </a>    
            </div>    
            <div class="col-lg-8 col-lg-offset-1 inicio">
                <img border="0" src="img/fam.png" />
                <p>Responda as perguntas:</p>				
				<form>
					<h1 id="question"></h1>
					<input type="radio" name="answer" value="0" id="ans0"> <span id="choice0"></span>
					<input type="radio" name="answer" value="1" id="ans1"> <span id="choice1"></span>
					<input type="radio" name="answer" value="2" id="ans2"> <span id="choice2"></span>
					<input type="radio" name="answer" value="3" id="ans3"> <span id="choice3"></span>
					<br>
					<input type="button" name="next" value="Próxima Pergunta" id="next" />
				</form>				
            </div>
        </div>    
    </div>
    <div class="col-lg-6 col-lg-offset-3">
        <center>          
            <br><br>
            <a href="#">Sobre o Curso</a> &nbsp; Desenvolvido por <a href="http://www.b2s.marketing" target="_blank">B2S Marketing</a>
        </center>    
    </div>
</section> 
	
<script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script  src="js/index.js"></script>
	
</body>
</html>
