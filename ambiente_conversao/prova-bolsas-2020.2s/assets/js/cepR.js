/*<html>
    <head>
    <title>ViaCEP Webservice</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <!-- Adicionando Javascript -->
    <script type="text/javascript" >
*/
var resp;

function limpa_formulário_cepR() {
    //Limpa valores do formulário de cep.
    document.getElementById('r8').value = ("");
    document.getElementById('r11').value = ("");
    document.getElementById('r12').value = ("");
    //document.getElementById('uf').value=("");
    //document.getElementById('ibge').value=("");
}

function meu_callbackR(conteudo) {
    if (!("erro" in conteudo)) {
        var campocidade = "";
        campocidade = conteudo.localidade + " / " + conteudo.uf;
        //Atualiza os campos com os valores.
        document.getElementById('r8').value = (conteudo.logradouro);
        document.getElementById('r11').value = (conteudo.bairro);
        document.getElementById('r12').value = (campocidade);
        document.getElementById('r8').style.backgroundColor = "#ffca30";
        document.getElementById('r11').style.backgroundColor = "#ffca30";
        document.getElementById('r12').style.backgroundColor = "#ffca30";
        //document.getElementById('uf').value=(conteudo.uf);
        //document.getElementById('ibge').value=(conteudo.ibge);
        resp = true;
    } //end if.
    else {
        //CEP não Encontrado.
        limpa_formulário_cepR();
        alert("CEP não encontrado.");
        resp = false;
    }
}

function pesquisacepR(valor) {

    //Nova variável "cep" somente com dígitos.
    var cep = valor.replace(/\D/g, '');

    //Verifica se campo cep possui valor informado.
    if (cep != "") {

        //Expressão regular para validar o CEP.
        var validacep = /^[0-9]{8}$/;

        //Valida o formato do CEP.
        if (validacep.test(cep)) {

            //Preenche os campos com "..." enquanto consulta webservice.
            document.getElementById('r8').value = "...";
            document.getElementById('r11').value = "...";
            document.getElementById('r12').value = "...";
            //document.getElementById('uf').value="...";
            //document.getElementById('ibge').value="...";

            //Cria um elemento javascript.
            var script = document.createElement('script');

            //Sincroniza com o callback.
            script.src = 'https://viacep.com.br/ws/' + cep + '/json/?callback=meu_callbackR';

            //Insere script no documento e carrega o conteúdo.
            document.body.appendChild(script);

        } //end if.
        else {
            //cep é inválido.
            limpa_formulário_cepR();
            alert("Formato de CEP inválido.");
            resp = false;
        }
    } //end if.
    else {
        //cep sem valor, limpa formulário.
        limpa_formulário_cepR();
    }
    return resp;
};
/*
</script>
</head>

<body>
<!-- Inicio do formulario -->
<form method="get" action=".">
<label>Cep:
<input name="cep" type="text" id="cep" value="" size="10" maxlength="9"
       onblur="pesquisacep(this.value);" /></label><br />
<label>Rua:
<input name="rua" type="text" id="rua" size="60" /></label><br />
<label>Bairro:
<input name="bairro" type="text" id="bairro" size="40" /></label><br />
<label>Cidade:
<input name="cidade" type="text" id="cidade" size="40" /></label><br />
<label>Estado:
<input name="uf" type="text" id="uf" size="2" /></label><br />
<label>IBGE:
<input name="ibge" type="text" id="ibge" size="8" /></label><br />
</form>
</body>

</html>
*/