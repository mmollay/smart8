<script>
    $(document).ready(() => {
        updateServerInfo(); // Initialer Aufruf, um die Profite beim Laden der Seite zu aktualisieren
        loadEmaFormOld();
    });

    if (typeof window.serverInfoInterval === 'undefined') {
        window.serverInfoInterval = setInterval(updateServerInfo, 20000);
    }

</script>

<div id='formEmaContainer'>
    <div class='ui active inverted dimmer'>
        <div class='ui text loader'>Loading...</div>
    </div>
</div>