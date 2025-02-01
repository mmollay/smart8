function syncData() {
    const syncButton = $('.sync.icon').addClass('loading');

    $.ajax({
        url: 'exec/fetch_trades.php',
        method: 'GET',
        dataType: 'json',
        success: handleSyncResponse,
        error: () => showToast('Verbindungsfehler bei der Synchronisation', 'error'),
        complete: function () {
            syncButton.removeClass('loading');
        }
    });
}

function handleSyncResponse(response) {
    if (response.success) {
        const detailsHtml = buildSyncDetailsHtml(response);
        showSyncModal('Sync Status', detailsHtml);

        if (typeof reloadTable === 'function') {
            reloadTable();
        }
    } else {
        showToast('Fehler bei der Synchronisation', 'error');
    }
}

function buildSyncDetailsHtml(response) {
    let detailsHtml = `
        <div class="ui statistics tiny">
            ${buildStatistic('Trades', response.total_stats.trades)}
            ${buildStatistic('Balances', response.total_stats.balances)}
            ${buildStatistic('Positionen', response.total_stats.positions)}
            ${buildStatistic('Transaktionen', response.total_stats.transactions)}
        </div>
        <div class="ui divider"></div>`;

    response.users.forEach(user => {
        const color = user.success === false ? 'red' : 'green';
        detailsHtml += `
            <div class="ui segment ${color} inverted">
                <h4>${user.name}</h4>
                <div class="ui mini horizontal statistics inverted">
                    ${buildStatistic('Trades', user.stats.trades)}
                    ${buildStatistic('Pos.', user.stats.positions)}
                </div>
            </div>`;
    });

    detailsHtml += `
        <div class="ui label">
            <i class="clock icon"></i>
            ${response.execution_time} Sekunden
        </div>`;

    return detailsHtml;
}

function buildStatistic(label, value) {
    return `
        <div class="statistic">
            <div class="value">${value}</div>
            <div class="label">${label}</div>
        </div>`;
}

function showSyncModal(title, content) {
    let modal = $('#syncResultModal');
    if (modal.length === 0) {
        modal = $(`
            <div class="ui tiny modal" id="syncResultModal">
                <div class="header"></div>
                <div class="content"></div>
                <div class="actions">
                    <div class="ui positive button">OK</div>
                </div>
            </div>
        `).appendTo('body');
    }

    modal.find('.header').text(title);
    modal.find('.content').html(content);
    modal.modal('show');
}

function showToast(message, type = 'info') {
    $('.ui.toast-container').toast({
        message,
        class: type,
        position: 'top right',
        displayTime: 3000
    });
}