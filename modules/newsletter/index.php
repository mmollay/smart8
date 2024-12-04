<?php
$versions = require(__DIR__ . "/version.php");
$title = "SSI Newsletter";
$moduleName = "newsletter";
$version = $versions['version'];

require(__DIR__ . "/../../DashboardClass.php");

$dashboard->addMenu('leftMenu', 'ui labeled icon left fixed menu mini vertical', true);
$dashboard->addMenuItem('leftMenu', "newsletter", "home", "Home", "home icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "list_newsletters", "Newsletter", "newspaper icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "list_recipients", "Empfänger", "address card icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "import_recipients", "Empfänger importieren", "upload icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "list_groups", "Gruppen", "users icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "list_senders", "Absender", "at icon");
$dashboard->addMenuItem('leftMenu', "newsletter", "list_templates", "Vorlagen", "file alternate icon");

if ($_SERVER['SERVER_NAME'] === 'localhost') {
    $dashboard->addMenuItem(
        'leftMenu',
        "newsletter",
        "#",
        "Manueller Versand",
        "paper plane blue icon",
        "Manueller E-Mail-Versand starten",
        "left",
        "ui blue button manual-send",
        false,
        "triggerManualSend()"
    );

    $dashboard->addScript('
        var sendingInProgress = false;
        
        function triggerManualSend() {
            if (sendingInProgress) {
                return;
            }
            
            if (confirm("Möchten Sie den manuellen E-Mail-Versand jetzt starten?")) {
                sendingInProgress = true;
                $(".manual-send").addClass("loading disabled");
                
                $.ajax({
                    url: "exec/send_email_background.php",
                    method: "POST",
                    dataType: "json",
                    success: function(response) {
                        console.log("Server Response:", response); // Debug-Log
                        if (response.success) {
                            if (response.statistics && response.statistics.content_id) {
                                showProgressModal();
                                monitorProgress(response.statistics.content_id);
                            } else {
                                alert(response.message || "Keine ausstehenden E-Mails gefunden.");
                                $(".manual-send").removeClass("loading disabled");
                            }
                        } else {
                            alert("Fehler: " + response.message);
                            $(".manual-send").removeClass("loading disabled");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Ajax Error:", {xhr: xhr, status: status, error: error});
                        alert("Fehler beim Starten des Versands: " + error);
                        $(".manual-send").removeClass("loading disabled");
                    },
                    complete: function() {
                        sendingInProgress = false;
                    }
                });
            }
        }
        
        function showProgressModal() {
            // Entferne existierendes Modal falls vorhanden
            $("#progress-modal").remove();
            
            $("body").append(`
                <div class="ui modal" id="progress-modal">
                    <div class="header">
                        Newsletter-Versand
                    </div>
                    <div class="content">
                        <div class="ui segment">
                            <div class="ui indicating progress" id="send-progress">
                                <div class="bar">
                                    <div class="progress"></div>
                                </div>
                                <div class="label">Versand läuft...</div>
                            </div>
                            
                            <div class="ui three statistics">
                                <div class="blue statistic">
                                    <div class="value" id="stats-total">0</div>
                                    <div class="label">Gesamt</div>
                                </div>
                                <div class="green statistic">
                                    <div class="value" id="stats-sent">0</div>
                                    <div class="label">Gesendet</div>
                                </div>
                                <div class="red statistic">
                                    <div class="value" id="stats-failed">0</div>
                                    <div class="label">Fehler</div>
                                </div>
                            </div>
                        </div>
                        <div class="ui small message" id="status-message">
                            <p>Status wird alle 2 Sekunden aktualisiert...</p>
                        </div>
                    </div>
                </div>
            `);
            
            // Progress Bar initialisieren
            $("#send-progress").progress({
                percent: 0,
                text: {
                    active: "Versende {value} von {total} E-Mails",
                    success: "{total} E-Mails versendet"
                }
            });
            
            // Modal anzeigen
            $("#progress-modal")
                .modal({
                    closable: false,
                    onVisible: function() {
                        console.log("Modal geöffnet");
                    }
                })
                .modal("show");
        }

        function monitorProgress(contentId) {
            if (!contentId) {
                console.error("Keine Content ID verfügbar!");
                $("#status-message").html(\'<p class="ui red text">Fehler: Keine Newsletter-ID verfügbar</p>\');
                return;
            }
            
            console.log("Starte Monitoring für Content ID:", contentId);
            
            var progressInterval = setInterval(function() {
                $.ajax({
                    url: "ajax/check_queue_progress.php",
                    method: "GET",
                    data: { content_id: contentId },
                    dataType: "json",
                    success: function(response) {
                        console.log("Progress Response:", response);
                        
                        if (response.success) {
                            // Progress Bar aktualisieren
                            $("#send-progress").progress({
                                percent: response.percentage || 0
                            });
                            
                            // Statistiken aktualisieren
                            $("#stats-total").text(response.total_emails || 0);
                            $("#stats-sent").text(response.processed_emails || 0);
                            $("#stats-failed").text(response.failed_emails || 0);
                            
                            // Status-Message aktualisieren
                            $("#status-message").html(`<p>Status: ${response.processed_emails} von ${response.total_emails} E-Mails versendet (${response.percentage}%)</p>`);
                            
                            if (response.is_completed) {
                                console.log("Versand abgeschlossen");
                                clearInterval(progressInterval);
                                $("#progress-modal .label").text("Versand abgeschlossen");
                                
                                setTimeout(function() {
                                    $("#progress-modal").modal("hide");
                                    location.reload();
                                }, 3000);
                            }
                        } else {
                            console.error("Fehler in der Response:", response.message);
                            $("#status-message").html(`<p class="ui red text">Fehler: ${response.message}</p>`);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Monitor Ajax Error:", {
                            status: status,
                            error: error,
                            response: xhr.responseText
                        });
                        $("#status-message").html(\'<p class="ui red text">Verbindungsfehler beim Abrufen des Status</p>\');
                    }
                });
            }, 2000);

            // Speichere Interval ID für mögliche manuelle Bereinigung
            window.currentProgressInterval = progressInterval;
            
            // Bereinigung beim Schließen des Modals
            $("#progress-modal").on("hidden", function() {
                if (window.currentProgressInterval) {
                    clearInterval(window.currentProgressInterval);
                }
            });
        }
        
        // Hilfsfunktion zum manuellen Abbrechen des Monitorings
        function stopMonitoring() {
            if (window.currentProgressInterval) {
                clearInterval(window.currentProgressInterval);
                console.log("Monitoring gestoppt");
            }
        }
    ', true);
}

$dashboard->addScript("js/form_after.js");
//$dashboard->addScript('js/send_emails.js');
$dashboard->addScript("https://cdn.ckeditor.com/ckeditor5/38.0.1/decoupled-document/ckeditor.js");
$dashboard->addScript("https://cdn.ckeditor.com/ckeditor5/38.0.1/decoupled-document/translations/de.js");

require(__DIR__ . "/changelog_modal.php");
$dashboard->addFooterContent('
    <button class="ui basic tiny compact button" onclick="$(\'#changelog-modal\').modal(\'show\')" style="margin: 0; opacity: 0.7;">
        <i class="history icon"></i>
        v' . $versions['version'] . ' - Changelog
    </button>
');

$dashboard->render();