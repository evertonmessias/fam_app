/* ALTERÇÕES EVERTON 11-06-2020 */

function Submeter() {
    if ($("#nome").val() != "" && $("#sobrenome").val() != "" && $("#email").val() != "" && $("#celular").val() != "" && $("#curso").val() != "") {
        console.log("FOI");
        document.getElementById("form").submit();
    } else {
        console.log("FALTA >>>")
    }
}

function Valida() {
    if ($("#cpf").val().length > 11) {
        $(".oculto").slideDown(500);
    }
}

function mudacor(elemento) {
    if (elemento.value == "") {
        $(elemento).css({ "background-color": "#ccc" });
    } else {
        $(elemento).css({ "background-color": "#ffca30" });
    }
}

function cinza(elem){
    $(elem).css({ "background-color": "#ccc" });
}

function amarelo(elem){
    $(elem).css({ "background-color": "#ffca30" });
}

function validar() {
    var f = $(".js-form")[0];
    if (!f.checkValidity()) {
        alert("Erro, Preencha TODOS os campos antes de enviar !!!");
    } else if ($(".email1").val() != $(".email2").val()) {
        alert("Erro, E-Mail não confere !!!");
    } else {
        f.submit();
    }
}

function tipoprova() {
    var prova = $("#prova").val();
    if (prova == "vestibular") {
        $(".enem").css({ "display": "none" })
        $(".formado").css({ "display": "none" })
        $(".tecnico").css({ "display": "none" })
        $(".vestibular").slideDown(500);
        $(".confirmaemail").slideDown(500);
    }
    else if (prova == "enem") {
        $(".formado").css({ "display": "none" })
        $(".vestibular").css({ "display": "none" })
        $(".tecnico").css({ "display": "none" })
        $(".enem").slideDown(500);
        $(".confirmaemail").slideDown(500);
    }
    else if (prova == "tecnico") {
        $(".formado").css({ "display": "none" })
        $(".vestibular").css({ "display": "none" })        
        $(".enem").css({ "display": "none" })
        $(".tecnico").slideDown(500);
        $(".confirmaemail").slideDown(500);
    }
    else if (prova == "segunda_graduacao") {
        $(".vestibular").css({ "display": "none" })
        $(".enem").css({ "display": "none" })
        $(".tecnico").css({ "display": "none" })
        $(".formado").slideDown(500);
        $(".confirmaemail").slideDown(500);
    }

}

function tipodeficiencia() {
    var deficiencia = $("#deficiencia").val();
    if (deficiencia == "sim") {
        $(".deficiencia").slideDown(500);
        $(".defic").css({ "display": "none" }) 
    }
    else if (deficiencia == "nao") {
        $(".deficiencia").css({ "display": "none" })
        $(".defic").slideDown(500);
    }
}

function verificar(ficheiro){
    var extensoes = [".pdf", ".doc"];
    var fnome = ficheiro.value;
    var extficheiro = fnome.substr(fnome.lastIndexOf('.'));
    if(extensoes.indexOf(extficheiro) >= 0){
        if(ficheiro.files[0].size > 1000000){
            alert('Erro: Arquivo muito grande ! Máx. permitido é 1000KB');            
            ficheiro.value = "";
        }
    } else {
        alert('Erro: Extensao inválida (' + extficheiro + ') - Permitido somente PDF');        
        ficheiro.value = "";        
    }
    return false;
}

//exibe manuais 
function manual(x){
    if (x == 1){
        $('.poparq').css({'display':'block'});
        $('.imgarq1').attr('src','/assets/images/1.jpg').css({'display':'block'});
    }
    if (x == 2){
        $('.poparq').css({'display':'block'});
        $('.imgarq2').attr('src','/assets/images/2.jpg').css({'display':'block'});
    }
    if (x == 3){
        $('.poparq').css({'display':'block'});
        $('.imgarq3').attr('src','/assets/images/3.jpg').css({'display':'block'});
    }
    if (x == 4){
        $('.poparq').css({'display':'block'});
        $('.imgarq4').attr('src','/assets/images/4.jpg').css({'display':'block'});
    }
    if (x == 5){
        $('.poparq').css({'display':'block'});
        $('.imgarq5').attr('src','/assets/images/5.jpg').css({'display':'block'});
    }
    if (x == 6){
        $('.poparq').css({'display':'block'});
        $('.imgarq6').attr('src','/assets/images/6.jpg').css({'display':'block'});
    }
    if (x == 7){
        $('.poparq').css({'display':'block'});
        $('.imgarq7').attr('src','/assets/images/7.jpg').css({'display':'block'});
    }
    if (x == 8){
        $('.poparq').css({'display':'block'});
        $('.imgarq8').attr('src','/assets/images/8.jpg').css({'display':'block'});
    }
    if (x == 9){
        $('.poparq').css({'display':'block'});
        $('.imgarq9').attr('src','/assets/images/9.jpg').css({'display':'block'});
    }
    if (x == 10){
        $('.poparq').css({'display':'block'});
        $('.imgarq10').attr('src','/assets/images/10.jpg').css({'display':'block'});
    }
    if (x == 11){
        $('.poparq').css({'display':'block'});
        $('.imgarq11').attr('src','/assets/images/11.jpg').css({'display':'block'});
    }
    if (x == 12){
        $('.poparq').css({'display':'block'});
        $('.imgarq12').attr('src','/assets/images/12.jpg').css({'display':'block'});
    }
}

//esconde manual
function apagamanual(x){
    $('.poparq').css({'display':'none'});
    $('.imgarq'+x).css({'display':'none'});
}


function aceitar() { // adicionais 

    // pega o form todo
    var forml = $("#form1")[0];

    //verifica se é de menor
    var resp;
    const inputNasc = document.getElementById("data_nasc");
    let nasc = inputNasc.value.split("-").map(Number);
    let depois18Anos = new Date(nasc[0] + 18, nasc[1] - 1, nasc[2]);
    let agora = new Date();
    if (depois18Anos <= agora) {
        resp = true;// é maior
    } else {
        // é menor ? , então verifica campos do responsável   (FEIO mas foi o jeito ... :(  ) 
        if (document.getElementById("r1").value == "" || document.getElementById("r2").value == "" || document.getElementById("r3").value == "" || document.getElementById("r4").value == "" || document.getElementById("r5").value == "" || document.getElementById("r6").value == "" || document.getElementById("r7").value == "" || document.getElementById("r8").value == "" || document.getElementById("r9").value == "" || document.getElementById("r10").value == "" || document.getElementById("r11").value == "" || document.getElementById("r12").value == "" || document.getElementById("r13").value == "" || document.getElementById("r14").value == "") {
            resp = false;
        } else {
            resp = true;
        }
       //console.log(document.getElementById("r1").value);
    }

    //verifica se tudo foi preenchido (tudo q tem required)
    var checkval;
    if (!forml.checkValidity()) {
        checkval = false;
    } else {
        checkval = true;
    }

    // verifica se aceita os termos    
    var aceita;
    if (!($('#aceito1').prop("checked")) || !($('#aceito2').prop("checked"))) {
        alert("Aceite os termos do Contrato antes de enviar !!!");
        aceita = false;
    } else {
        aceita = true;
    }

    // verifica se TUDO está OK pra submeter
    if (resp && checkval && aceita) {
        forml.submit();
    } else {
        alert("Erro, Preencha TODOS os campos antes de enviar !!!");
    }
}