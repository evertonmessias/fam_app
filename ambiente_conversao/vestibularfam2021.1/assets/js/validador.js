// JavaScript Validador , Everton - 09/06/2020

// ========================== MASCARAS ======================

//mascara ao RG
function MascaraRG(rg) {
        if ((rg) == false) {
                event.returnValue = false;
        }
        return formataCampo(rg, '00.000.000-0', event);
}

//mascara de CEP
function MascaraCep(cep) {
        if (mascaraInteiro(cep) == false) {
                event.returnValue = false;
        }
        return formataCampo(cep, '00.000-000', event);
}

// mascara de data
function MascaraData(data) {
        if (mascaraInteiro(data) == false) {
                event.returnValue = false;
        }
        return formataCampo(data, '00/00/0000', event);
}

//mascara do Telefone Celular
function MascaraTelefone(tel) {
        if (mascaraInteiro(tel) == false) {
                event.returnValue = false;
        }
        return formataCampo(tel, '(00) 00000-0000', event);
}

//mascara do Telefone Fixo
function MascaraFixo(tel) {
        if (mascaraInteiro(tel) == false) {
                event.returnValue = false;
        }
        return formataCampo(tel, '(00) 0000-0000', event);
}

//mascara do CPF
function MascaraCPF(cpf) {
        if (mascaraInteiro(cpf) == false) {
                event.returnValue = false;
        }
        return formataCampo(cpf, '000.000.000-00', event);
}

//mascara de CNPJ
function MascaraCNPJ(cnpj) {
        if (mascaraInteiro(cnpj) == false) {
                event.returnValue = false;
        }
        return formataCampo(cnpj, '00.000.000/0000-00', event);
}

// =============================== VALIDAÇÕES ===============================

// valida E-Mail
function ValidaMail(mail) {
        var exp = /^\w+([\.-]\w+)*@\w+\.(\w+\.)*\w{2,3}$/;
        if (!exp.test(mail.value)) // metacaracteres da exprex Regular : \w caracter , * repete 0 ou mais ,  + repete 1 ou mais , {} n-repetiÃ§Ãµes
        { alert('Endereço de E-Mail Inválido!');return false; }
        else{return true;}
}

//valida CEP
function ValidaCep(cep) {
        var exp = /\d{2}\.\d{3}\-\d{3}/;
        if (!exp.test(cep.value)) { alert('Numero de Cep Invalido!');return false; }
        else{return true;}
}

//valida data
function ValidaData(data) {
        var exp = /\d{2}\/\d{2}\/\d{4}/;
        if (!exp.test(data.value)) { alert('Data Invalida!');return false; }
        else{return true;}
}

//valida telefone Celular
function ValidaTelefone(tel) {
        var exp = /\(\d{2}\)\ \d{5}\-\d{4}/;
        if (!exp.test(tel.value)) { alert('Numero de Telefone Invalido!');return false; }
}

//valida o CPF digitado


function validaCPF(numero){

        var exp = /\.|\-/g;
        
        var cpf = numero.replace(exp,'').toString();
        
        if(cpf.length == 11 ){
        
                var v = [];
        
                //Calcula o primeiro dígito de verificação.
                v[0] = 1 * cpf[0] + 2 * cpf[1] + 3 * cpf[2];
                v[0] += 4 * cpf[3] + 5 * cpf[4] + 6 * cpf[5];
                v[0] += 7 * cpf[6] + 8 * cpf[7] + 9 * cpf[8];
                v[0] = v[0] % 11;
                v[0] = v[0] % 10;
        
                //Calcula o segundo dígito de verificação.
                v[1] = 1 * cpf[1] + 2 * cpf[2] + 3 * cpf[3];
                v[1] += 4 * cpf[4] + 5 * cpf[5] + 6 * cpf[6];
                v[1] += 7 * cpf[7] + 8 * cpf[8] + 9 * v[0];
                v[1] = v[1] % 11;
                v[1] = v[1] % 10;
        
                //Retorna Verdadeiro se os dígitos de verificação são os esperados.
                
                if ((v[0] != cpf[9]) || (v[1] != cpf[10])) {return false}        
                else if (cpf[0] == cpf[1] && cpf[1] == cpf[2] && cpf[2] == cpf[3] && cpf[3] == cpf[4] && cpf[4] == cpf[5] && cpf[5] == cpf[6] && cpf[6] == cpf[7] && cpf[7] == cpf[8] && cpf[8] == cpf[9] && cpf[9] == cpf[10]){return false} 
                else{return true}

        }else {return false} // != 11
    }


function ValidarCPF(Objcpf) {
        var cpf = Objcpf.value;
        var exp = /\.|\-/g;
        cpf = cpf.toString().replace(exp, "");
        var digitoDigitado = eval(cpf.charAt(9) + cpf.charAt(10));
        var soma1 = 0, soma2 = 0;
        var vlr = 11;

        for (i = 0; i < 9; i++) {
                soma1 += eval(cpf.charAt(i) * (vlr - 1));
                soma2 += eval(cpf.charAt(i) * vlr);
                vlr--;
        }
        soma1 = (((soma1 * 10) % 11) == 10 ? 0 : ((soma1 * 10) % 11));
        soma2 = (((soma2 + (2 * soma1)) * 10) % 11);

        var digitoGerado = (soma1 * 10) + soma2;
        if (digitoGerado != digitoDigitado) { alert('CPF Inválido!');return false; }
        else if (cpf[0] == cpf[1] && cpf[1] == cpf[2] && cpf[2] == cpf[3] && cpf[3] == cpf[4] && cpf[4] == cpf[5] && cpf[5] == cpf[6] && cpf[6] == cpf[7] && cpf[7] == cpf[8] && cpf[8] == cpf[9] && cpf[9] == cpf[10]) { alert('CPF Inválido!');return false; }
        else{return true;}
}

//valida o CNPJ digitado
function ValidarCNPJ(ObjCnpj) {
        var cnpj = ObjCnpj.value;
        var valida = new Array(6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2);
        var dig1 = new Number;
        var dig2 = new Number;

        exp = /\.|\-|\//g;
        cnpj = cnpj.toString().replace(exp, "");
        var digito = new Number(eval(cnpj.charAt(12) + cnpj.charAt(13)));

        for (i = 0; i < valida.length; i++) {
                dig1 += (i > 0 ? (cnpj.charAt(i - 1) * valida[i]) : 0);
                dig2 += cnpj.charAt(i) * valida[i];
        }
        dig1 = (((dig1 % 11) < 2) ? 0 : (11 - (dig1 % 11)));
        dig2 = (((dig2 % 11) < 2) ? 0 : (11 - (dig2 % 11)));

        if (((dig1 * 10) + dig2) != digito) { alert('CNPJ Invalido!'); form1.cnpj.value = ''; form1.rg.focus(); return false; }

}


//valida numero inteiro com mascara
function mascaraInteiro() {
        if (event.keyCode < 48 || event.keyCode > 57) {
                event.returnValue = false;
                return false;
        }
        return true;
}

//formata de forma generica os campos..
function formataCampo(campo, Mascara, evento) {
        var boleanoMascara;

        var Digitato = evento.keyCode;
        var exp = /\-|\.|\/|\(|\)| /g;
        campoSoNumeros = campo.value.toString().replace(exp, "");

        var posicaoCampo = 0;
        var NovoValorCampo = "";
        var TamanhoMascara = campoSoNumeros.length;;

        if (Digitato != 8) { // backspace 
                for (i = 0; i <= TamanhoMascara; i++) {
                        boleanoMascara = ((Mascara.charAt(i) == "-") || (Mascara.charAt(i) == ".")
                                || (Mascara.charAt(i) == "/"))
                        boleanoMascara = boleanoMascara || ((Mascara.charAt(i) == "(")
                                || (Mascara.charAt(i) == ")") || (Mascara.charAt(i) == " "))
                        if (boleanoMascara) {
                                NovoValorCampo += Mascara.charAt(i);
                                TamanhoMascara++;
                        } else {
                                NovoValorCampo += campoSoNumeros.charAt(posicaoCampo);
                                posicaoCampo++;
                        }
                }
                campo.value = NovoValorCampo;
                return true;
        } else {
                return true;
        }
}
