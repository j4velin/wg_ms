# WG ManagementSystem

Ein einfaches WG Management System zur Verwaltung von WG Einkäufen etc.


Setup mit Docker
-----
* docker und docker-compose müssen installiert sein
* die Datei docker-compose.yml herunterladen
* docker-compose up -d -f "Pfad zur docker-compose.yml"
* Docker erzeugt und startet einen Container mit einem Webserver ([wg_app](https://hub.docker.com/r/j4velin/wg_app)) und einen Container mit einer MySQL Datenbank ([wg_db](https://hub.docker.com/r/j4velin/wg_db))
* Sobald die Container gestartet wurden, ist das WG MS über http://localhost:8080 erreichbar
* initiales Passwort für den "admin" Account lautet "admin" und sollte nach dem Login geändert werden
