document.addEventListener("DOMContentLoaded", function(event) {
    let campoCPF = document.querySelectorAll(".js-cpf")[0];
    // Ao focar o campo de cpf
    campoCPF.addEventListener("focus", function(){
        retirarFormatacao(this);
    });

    // Ao tirar o foco do campo de cpf
    campoCPF.addEventListener("blur", function(){
        formatarCampo(this);
    });

    function formatarCampo(campoTexto) {
        if (campoTexto.value.length <= 11) {
            campoTexto.value = mascaraCpf(campoTexto.value);
        }
    }
    function retirarFormatacao(campoTexto) {
        campoTexto.value = campoTexto.value.replace(/(\.|\/|\-|\(|\)|\s)/g,"");
    }
    function mascaraCpf(valor) {
        return valor.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/g,"\$1.\$2.\$3\-\$4");
    }

    // Validar CPF ao enviar
    document.querySelectorAll(".js-form")[0].addEventListener("submit", function(e) {
        event.preventDefault();
        formatarCampo(campoCPF);

        if((new CPF().valida(campoCPF.value)) === "CPF Inválido") {
            alert("CPF inválido!\nVerifique e tente novamente.");
        } else {
            this.setAttribute("action", "inscricao");
            if (this.getAttribute("action") == "inscricao") {
                this.submit();
            } else {
                console.log("Timeout!");
                setTimeout(function() {
                    this.submit();
                }, 500);
            }
        }
    });

    function CPF(){"user_strict";function r(r){for(var t=null,n=0;9>n;++n)t+=r.toString().charAt(n)*(10-n);var i=t%11;return i=2>i?0:11-i}function t(r){for(var t=null,n=0;10>n;++n)t+=r.toString().charAt(n)*(11-n);var i=t%11;return i=2>i?0:11-i}var n="CPF Inválido",i="CPF Válido";this.gera=function(){for(var n="",i=0;9>i;++i)n+=Math.floor(9*Math.random())+"";var o=r(n),a=n+"-"+o+t(n+""+o);return a},this.valida=function(o){for(var a=o.replace(/\D/g,""),u=a.substring(0,9),f=a.substring(9,11),v=0;10>v;v++)if(""+u+f==""+v+v+v+v+v+v+v+v+v+v+v)return n;var c=r(u),e=t(u+""+c);return f.toString()===c.toString()+e.toString()?i:n}}

    // Mostrar o botão de enviar
    // Se já deixar visível de cara, o usuário pode tentar enviar, mas esse script pode ainda não ter carregado, gerando erro;
    document.querySelectorAll(".js-enviar")[0].removeAttribute("disabled");
});