(function ($, window, document, undefined) {
    $(document).ready(function () {
        $("#imprimir").click(function () {
            $("#formcadastro").attr('action',$("#imprimir-url").val());
            $("#formcadastro").attr('method','POST');
            $("#formcadastro").submit();
        });
    });
})
(window.jQuery, window, document);