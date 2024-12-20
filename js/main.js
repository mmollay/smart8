$(document).ready(function () {
    $('.item').popup();
    $('.ui.dropdown').dropdown();

    var moduleName = $('#moduleName').val(); // Modulname aus einem versteckten Feld lesen

    // Vereinheitlichte Funktion zum Laden von Inhalten
    window.loadContent = function (module, page) {
        // Speichert die aktuelle Seite im Local Storage unter dem modulspezifischen Key
        localStorage.setItem(module + '_lastPage', page);

        $.ajax({
            url: "../../modules/" + module + "/pages/" + page + '.php',
            method: "GET",
            success: function (response) {
                $('#pageContent').html(response);
            },
            error: function () {
                alert('Seite konnte nicht geladen werden.');
            }
        });
    };

    // Initialseite laden
    var storageKey = moduleName + '_lastPage';
    var lastPage = localStorage.getItem(storageKey) || 'home';
    loadContent(moduleName, lastPage); // Vereinheitlichte Funktion verwenden

    $('.ui.menu .item').click(function (e) {
        var page = $(this).data('page');
        var moduleName = $(this).data('module');

        if (page && moduleName) {
            e.preventDefault();
            // Alle aktiven Klassen entfernen und die aktive Klasse auf das angeklickte Element setzen
            $('.ui.menu .item').removeClass('active');
            $(this).addClass('active');
            localStorage.setItem(storageKey, page);
            loadContent(moduleName, page); // Vereinheitlichte Funktion verwenden
        }

    });

    // Sidebar und Top Menu Initialisierung für aktives Item
    $('.ui.menu .item[data-page="' + lastPage + '"]').addClass('active');


    $('#toggleMenu').click(function () {
        $('.ui.sidebar').sidebar('toggle');
    });

    $('#logout').click(function (e) {
        e.preventDefault();
        $.ajax({
            url: '../../logout.php',
            method: 'POST',
            success: function () {
                localStorage.removeItem(storageKey);
                window.location.href = '../../login.php';
            }
        });
    });

    $('#dashboard').click(function (e) {
        e.preventDefault();
        $('.ui.menu .item').removeClass('active');
        $(this).addClass('active');
        window.location.href = '../../index.php';
    });
});

jQuery.fn.message = function (settings) {
    message(settings)
}

function message(settings) {
    settings = jQuery.extend({ title: "Gespeichert", delay: 10000, text: '', icon: 'info', }, settings);

    var title = settings.title;
    var text = settings.text;
    var delay = settings.delay;
    var icon = settings.icon;
    var type = settings.type;

    //$('.toast-container').hide();
    $('body').toast({ title: title, message: text, position: 'top center', class: icon });
}
