function blinkElement(selector) {
    $(selector)
        .fadeOut(100) // Schnell ausblenden
        .fadeIn(100); // Schnell einblenden
}

function updateServerInfo() {
    $.ajax({
        url: 'inc/getServerInfo.php', // Adjust as needed
        type: 'GET',
        dataType: 'json',
        success: function (servers) {
            servers.forEach(function (server) {
                var numberOfPositions = server.numberOfPositions ?? 0;
                var positionsElement = $('#server-positions-' + server.server_id);
                var colorClass = getColorClassForPositions(numberOfPositions);

                // Farbklasse anwenden
                positionsElement.removeClass('ui red text ui yellow text ui green text').addClass(colorClass);

                // Inhalt aktualisieren
                var totalLots = server.totalLots ? parseFloat(server.totalLots) : 0.00;
                var sumOfValuesAtPositionPrice15 = server.sumOfValuesAtPositionPrice15 ? parseFloat(server.sumOfValuesAtPositionPrice15) : 0.00;
                var margin = server.margin ? parseFloat(server.margin) : 0.00;

                var priceColorClass = sumOfValuesAtPositionPrice15 >= 0 ? 'ui green text' : 'ui red text';

                positionsElement.html(`${numberOfPositions} (${totalLots.toFixed(2)} Lot)<br>
                Margin: ${margin.toFixed(2)}<br>
                <span class='${priceColorClass}'>P/L : ${sumOfValuesAtPositionPrice15.toFixed(2)}</span>
                `);

                // Visual feedback with a blink effect
                blinkElement('#server-positions-' + server.server_id);
            });
        },
        error: function (xhr, status, error) {
            console.error("An error occurred: " + error);
        }
    });
}

function getColorClassForPositions(numberOfPositions) {
    if (numberOfPositions > 6) {
        return 'ui red text'; // High urgency
    } else if (numberOfPositions > 3) {
        return 'ui yellow text'; // Medium urgency
    } else {
        return 'ui green text'; // Low urgency
    }
}


let lastUpdateTime = 0;

function updateDaxValue(data) {
    const now = Date.now();

    if (!data) {
        $('#daxValue').text('No data available'); // Nachricht, wenn keine Daten empfangen wurden
        return; // Frühzeitiger Abbruch, wenn keine Daten vorhanden sind
    }

    if (now - lastUpdateTime < 1000) {
        return; // Verhindert zu häufige Aktualisierungen
    }

    try {
        const parsedData = JSON.parse(data);
        if (parsedData.SYMBOL === "GER30" && parsedData.ASK) {
            const daxValueElement = $('#daxValue'); // Speichern der Referenz auf das Element
            daxValueElement.fadeOut('fast', function () {
                daxValueElement.text(parsedData.ASK).fadeIn('fast');
            });

            lastUpdateTime = now; // Aktualisieren der Zeit des letzten Updates
        }
    } catch (e) {
        console.error('Error parsing message:', e);
        $('#daxValue').text('Error processing data'); // Nachricht, wenn ein Fehler beim Verarbeiten der Daten auftritt
    }
}

function post_ema(strategyValue, strategy, server_id) {
    console.log(strategyValue);

    $.ajax({
        url: 'ajax/post.php',
        type: 'POST',
        data: { strategy_value: strategyValue, strategy: strategy, server_id: server_id },
        success: (data) => {
            after_post_ema(data);
            loadEmaForm();
        },
        error: (xhr, status, error) => {
            showToast(`Ein Fehler ist aufgetreten: ${error}`, 'error');
        }
    });
}


function post_ema_general(ema_value, server_id) {

    $.ajax({
        url: 'ajax/post.php',
        type: 'POST',
        data: { ema_value: ema_value, server_id: server_id },
        success: (data) => {
            after_post_ema(data);
            loadEmaForm();
        },
        error: (xhr, status, error) => {
            showToast(`Ein Fehler ist aufgetreten: ${error}`, 'error');
        }
    });
}



//function for stopAuto 
function stopAuto(server_id, $auto_value) {
    $.ajax({
        url: 'ajax/post.php',
        type: 'POST',
        data: { stopAuto: true, server_id: server_id, auto_value: $auto_value },
        success: (data) => {
            after_post_ema(data);
            loadEmaForm();
        },
        error: (xhr, status, error) => {
            showToast(`Ein Fehler ist aufgetreten: ${error}`, 'error');
        }
    });
}

//function for pauseAuto
function pauseAuto(server_id) {
    $.ajax({
        url: 'ajax/post.php',
        type: 'POST',
        data: { pauseAuto: true, server_id: server_id },
        success: (data) => {
            after_post_ema(data);
            loadEmaForm();
        },
        error: (xhr, status, error) => {
            showToast(`Ein Fehler ist aufgetreten: ${error}`, 'error');
        }
    });
}



function loadEmaForm() {
    $('#formEmaContainer .dimmer').addClass('active');
    $.ajax({
        url: 'inc/ema_form.php',
        type: 'GET',
        success: (data) => {
            $('#formEmaContainer .dimmer').removeClass('active');
            $('#formEmaContainer').html(data);
        },
        error: (err) => {
            $('#formEmaContainer .dimmer').removeClass('active');
            console.error('Fehler beim Laden der Daten:', err);
            $('#formEmaContainer').html('<div class="ui error message">Fehler beim Laden des Inhalts.</div>');
        }
    });
}

function after_post_ema(json) {
    console.log(json);

    try {
        json = JSON.parse(json);
    } catch (e) {
        showToast('Fehler bei der Verarbeitung der Antwort.', 'error');
        return;
    }

    if (json.error) {
        showToast(json.error, 'error');
    } else {
        showToast(json.message, 'info');
    }
}


function after_start_strategy(json) {
    after_post_ema(json);
    loadEmaForm();
}