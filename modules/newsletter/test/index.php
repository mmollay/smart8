<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>E-Mail-Versand</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.js'></script>
    <style>
        #file-list li {
            margin: 5px 0;
        }

        #file-list li button {
            margin-left: 10px;
            cursor: pointer;
        }
    </style>
    <script>
        $(document).ready(function () {
            const allowedFormats = ['image/jpeg', 'image/png', 'application/pdf'];

            $('#attachments').change(function () {
                const fileInput = document.getElementById('attachments');
                const fileList = $('#file-list');

                fileList.empty(); // Clear the list

                for (let i = 0; i < fileInput.files.length; i++) {
                    const file = fileInput.files[i];

                    if (allowedFormats.includes(file.type)) {
                        const li = $('<li>').text(file.name);
                        const removeButton = $('<button class="ui button red small">').text('Löschen').click(function () {
                            $(this).parent().remove();
                            return false;
                        });
                        li.append(removeButton);
                        fileList.append(li);
                    } else {
                        $('.ui.toast').toast({
                            message: file.name + ' hat ein ungültiges Format. Erlaubte Formate: ' + allowedFormats.join(', '),
                            class: 'red'
                        });
                    }
                }
            });

            $('#emailForm').submit(function (e) {
                e.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: 'save_email_data.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        $('.ui.progress').progress('reset');
                        $('.ui.dimmer').dimmer('show');
                    },
                    success: function (response) {
                        $('.ui.dimmer').dimmer('hide');
                        $('.ui.toast').toast({
                            message: 'E-Mail-Daten wurden erfolgreich gespeichert. Der Versand erfolgt im Hintergrund.',
                            class: 'green'
                        });
                    },
                    error: function (xhr, status, error) {
                        $('.ui.dimmer').dimmer('hide');
                        $('.ui.toast').toast({
                            message: 'Fehler beim Speichern der E-Mail-Daten: ' + xhr.responseText,
                            class: 'red'
                        });
                    }
                });
            });

            $('#triggerCron').click(function () {
                $.ajax({
                    url: 'send_emails_background.php',
                    type: 'POST',
                    beforeSend: function () {
                        $('.ui.dimmer').dimmer('show');
                    },
                    success: function (response) {
                        $('.ui.dimmer').dimmer('hide');
                        $('.ui.toast').toast({
                            message: 'E-Mails wurden im Hintergrund gesendet!',
                            class: 'green'
                        });
                    },
                    error: function (xhr, status, error) {
                        $('.ui.dimmer').dimmer('hide');
                        $('.ui.toast').toast({
                            message: 'Fehler beim Senden der E-Mails im Hintergrund: ' + xhr.responseText,
                            class: 'red'
                        });
                    }
                });
            });
        });
    </script>
</head>

<body>
    <div class="ui container">
        <h2 class="ui header">E-Mail-Versand</h2>
        <form id="emailForm" class="ui form" enctype="multipart/form-data">
            <div class="field">
                <label for="subject">Betreff:</label>
                <input type="text" id="subject" name="subject" required>
            </div>
            <div class="field">
                <label for="message">Nachricht:</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            <div class="field">
                <label for="attachments">Anhänge:</label>
                <input type="file" id="attachments" name="attachments[]" multiple>
            </div>
            <ul id="file-list" class="ui list"></ul>
            <button type="submit" class="ui button primary">E-Mails speichern</button>
        </form>
        <div class="ui progress">
            <div class="bar"></div>
        </div>
        <div class="ui dimmer">
            <div class="ui text loader">Senden...</div>
        </div>
        <button id="triggerCron" class="ui button secondary">Cron-Job simulieren</button>
    </div>
</body>

</html>