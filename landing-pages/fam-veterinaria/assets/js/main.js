// Ativa o CSS para quando a página tiver Javascript ativado
$('body').addClass('js');

$('.docente').each((i, o) => {
    var docente = $(o);
    var btn = $('<button class="ver-mais">Leia Mais</button>');

    btn.click(() => {
        docente.toggleClass('open');

        if (docente.hasClass('open'))
            btn.text('Voltar');
        else
            btn.text('Leia Mais');
    });

    docente.append(btn);
});
