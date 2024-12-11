$(document).ready(function () {
    // Intervall nur einmal setzen falls noch nicht vorhanden
    if (!window.newsletterStatsInterval) {
        window.newsletterStatsInterval = setInterval(loadAllNewsletterStats, 10000);
    }

    // Update-Intervall für pending und active Newsletter
    if (($('.ui.active.progress').length > 0 || $('.ui.small.text:contains("Warte auf Verarbeitung")').length > 0)
        && !window.progressInterval) {
        window.progressInterval = setInterval(updateProgress, 5000);
    }
});

function loadAttachments() {
    $('.attachment-info').each(function () {
        const contentId = $(this).data('content-id');

        $.ajax({
            url: 'ajax/get_attachment_info.php',
            data: { content_id: contentId },
            success: function (response) {
                if (response.count > 0) {
                    $(`.attachment-info[data-content-id="${contentId}"]`).html(`
                        <span class="ui gray text" data-tooltip="${response.count} ${response.count === 1 ? 'Anhang' : 'Anhänge'} (${response.size} MB)">
                            <i class="paperclip icon"></i>
                        </span>
                    `).find('[data-tooltip]').popup();
                }
            }
        });
    });
}

// Callback für Newsletter-Duplizierung
window.cloneNewsletter = function (params) {
    $.ajax({
        url: 'ajax/clone_newsletter.php',
        method: 'POST',
        data: { content_id: params.content_id },
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                $('body').toast({
                    class: 'success',
                    message: response.message || 'Newsletter erfolgreich dupliziert'
                });
                reloadTable();
            } else {
                $('body').toast({
                    class: 'error',
                    message: response.message || 'Fehler beim Duplizieren'
                });
            }
        }
    });
};

// Globale Funktion für das Senden
window.sendNewsletter = function (contentId) {
    if (confirm('Möchten Sie diesen Newsletter jetzt versenden?')) {
        $('body').toast({
            class: 'info',
            message: 'Newsletter wird zum Versand vorbereitet...',
            showProgress: 'bottom'
        });

        $.ajax({
            url: 'ajax/send_newsletter.php',
            method: 'POST',
            data: { content_id: contentId },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('body').toast({
                        class: 'success',
                        message: response.message || 'Newsletter wird versendet'
                    });
                    // Kurz warten und dann Liste neu laden
                    setTimeout(function () {
                        window.reloadTable();
                    }, 1000);
                } else {
                    $('body').toast({
                        class: 'error',
                        message: response.message || 'Fehler beim Versenden'
                    });
                }
            },
            error: function (xhr, status, error) {
                $('body').toast({
                    class: 'error',
                    message: 'Fehler beim Versenden: ' + error
                });
            }
        });
    }
};

function updateProgress() {
    // Bestehende Progress-Updates
    $('.ui.active.progress').each(function () {
        var contentId = $(this).data('content-id');
        if (contentId) {
            checkNewsletterStatus($(this));
        }
    });

    // Neue Überprüfung für pending-Newsletter
    $('tr').each(function () {
        if ($(this).find('.ui.small.text:contains("Warte auf Verarbeitung")').length > 0) {
            var contentId = $(this).find('[data-content-id]').first().data('content-id');
            if (contentId) {
                checkNewsletterStatus($(this));
            }
        }
    });
}

function checkNewsletterStatus($element) {
    var contentId = $element.find('[data-content-id]').first().data('content-id') ||
        $element.data('content-id');

    if (!contentId) return;

    $.ajax({
        url: 'ajax/get_send_progress.php',
        data: { content_id: contentId },
        success: function (data) {
            if (data.success) {
                if (data.status === 'running' || data.status === 'completed') {
                    window.reloadTable();
                }
            }
        }
    });
}

function loadAllNewsletterStats() {
    $('.newsletter-stats').each(function () {
        const contentId = $(this).data('content-id');
        loadNewsletterStats(contentId);
    });
}

function loadNewsletterStats(contentId) {
    $.ajax({
        url: 'ajax/get_newsletter_stats.php',
        data: { content_id: contentId },
        success: function (response) {
            if (response.success) {
                const stats = response.data;
                const $container = $(`.newsletter-stats[data-content-id="${contentId}"]`);

                const labels = [];
                const total = parseInt(stats.total_recipients);

                // Gesamt
                if (total > 0) {
                    labels.push(`
                        <div class="ui tiny gray label" data-tooltip="Gesamt">
                            <i class="users icon"></i>${total}
                        </div>
                    `);
                }

                // Versendet
                if (stats.sent_count > 0) {
                    const percent = Math.round((stats.sent_count / total) * 100);
                    labels.push(`
                        <div class="ui tiny yellow label" data-tooltip="Versendet">
                            <i class="paper plane icon"></i>${stats.sent_count} <small>(${percent}%)</small>
                        </div>
                    `);
                }

                // Geöffnet
                if (stats.opened_count > 0) {
                    const percent = Math.round((stats.opened_count / total) * 100);
                    labels.push(`
                        <div class="ui tiny blue label" data-tooltip="Geöffnet">
                            <i class="eye icon"></i>${stats.opened_count} <small>(${percent}%)</small>
                        </div>
                    `);
                }

                // Geklickt
                if (stats.clicked_count > 0) {
                    const percent = Math.round((stats.clicked_count / total) * 100);
                    labels.push(`
                        <div class="ui tiny teal label" data-tooltip="Geklickt">
                            <i class="mouse pointer icon"></i>${stats.clicked_count} <small>(${percent}%)</small>
                        </div>
                    `);
                }

                // Fehler/Bounces
                if (stats.failed_count > 0) {
                    const percent = Math.round((stats.failed_count / total) * 100);
                    labels.push(`
                        <div class="ui tiny red label" data-tooltip="Fehler/Bounces">
                            <i class="exclamation triangle icon"></i>${stats.failed_count} <small>(${percent}%)</small>
                        </div>
                    `);
                }

                // Abgemeldet
                if (stats.unsub_count > 0) {
                    const percent = Math.round((stats.unsub_count / total) * 100);
                    labels.push(`
                        <div class="ui tiny orange label" data-tooltip="Abgemeldet">
                            <i class="user times icon"></i>${stats.unsub_count} <small>(${percent}%)</small>
                        </div>
                    `);
                }

                // Auf Blacklist
                if (stats.blacklisted_count > 0) {
                    const percent = Math.round((stats.blacklisted_count / total) * 100);
                    labels.push(`
                        <div class="ui tiny black label" data-tooltip="Auf Blacklist">
                            <i class="ban icon"></i>${stats.blacklisted_count} <small>(${percent}%)</small>
                        </div>
                    `);
                }

                $container.html(
                    labels.length > 0
                        ? '<div class="ui small labels">' + labels.join('') + '</div>'
                        : '<span class="ui grey text">-</span>'
                );

                // Tooltips initialisieren
                $container.find('.ui.label').popup();
            }
        }
    });
}