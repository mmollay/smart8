> Version 8.2 (09.01.2023)

-add: New finder

> Version 7.10 (12.09.2023)

-mod: ckeditor 4.23 (end of support)
-fix: errors in ckeditor for icons


> Version 7.9 (10.10.2022)

-add: Global Option -> Google Analytics
-mod: fomatic-ui 2.9.0 (new design)
-mod: db
-fix: Clone and Add news sites 


> Version 7.8 (10.09.2022)

- mod: admin -> menu -> top -> label on the right 


> Version 7.7 (21.02.2022)

-add: gadget -> paneon_plan


> Version 7.6 (18.01.2021)

-add: admin -> view last change inner select link for finder
-add: admin -> laod menu after editing
-mod: admin -> redesign templates-generator for new pages
-fix: parallax-efect as well in the Public-page
-


 
> Version 7.5.2 (23.09.2020)
    
-add: new globalsettings


> Version 7.5 (23.09.2020)

-add: fomantic-ui 2.8.7
-add: new page-generator with feedback during the progress
-add: new upoadify
-add: new options: index-setting (robot form searchengines) for seperate site

> Version 7.4.1 (02.04.2020)

-add: fomantic-ui 2.8.4
-fix: correct position vor Button for the sidebar
-fix: menu-structure, reload after use checkbox "show structure"


> Version 7.4 (05.12.2019)

-add: fomantic-ui 2.8.2
-add: admin -> new save achitecture for elemen options
-fix: element -> toast -> show info after user clone&move correct
-fix: element -> clone&move

#mysql
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `smart_element_options` (
  `option_id` int(11) NOT NULL,
  `element_id` int(11) NOT NULL,
  `option_name` varchar(200) NOT NULL,
  `option_value` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `smart_element_options`
  ADD PRIMARY KEY (`option_id`),
  ADD UNIQUE KEY `element_id` (`element_id`,`option_name`) USING BTREE;

ALTER TABLE `smart_element_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;


> Version 7.3 (19.11.2019)

-add: fomantic-ui 2.8.1
-mod: Button-Fixed on the botton, looks great now
-mod: element -> gallery -> carousel -> setting height (crope) for individuel Images

> Version 7.2 (08.11.2019)

ALTER TABLE `smart_options` ADD `site_id` INT(11) NOT NULL AFTER `page_id`;
ALTER TABLE `smart_options` ADD INDEX(`site_id`);  
ALTER TABLE `smart_id_site2id_page` ADD `uuid` VARCHAR(40) NOT NULL AFTER `site_id`, ADD INDEX (`uuid`);  
UPDATE smart_id_site2id_page SET uuid = UUID() 
-add: Auto-Popup

> Version 7.1 (21.10.2019)

-add: ALTER TABLE `smart_langSite` ADD `menu_url` VARCHAR(255) NOT NULL AFTER `menu_text`; 
-add: ALTER TABLE `smart_id_site2id_page` ADD `menu_newpage` INT(1) NOT NULL AFTER `favorite`;
-add: Link setting for menu -> redirection for publichpages 
-add: gadget -> formular -> Placeholder inner subject possible {%firstname%},...
-add: admin -> design -> improve design -> menu top -> pulldown icon
-add: element -> clone es well possible for splitter!


> Version 7.0(rc7) (02.10.2019)

-add: element -> newsletter 
-add: include new ssi_finder

> Version 7.0(rc6) (07.09.2019)

-add: element -> gallery -> flexbox -> set height for differents viewmodes
-add: design -> element -> linear-gradient -> background-color
-mod: js -> admin -> optimize -> no reload element

> Version 7.0(rc5) (05.09.2019)

-add: element width in %
-mod: Smartphone design optimazion

> Version 7.0(rc4) (02.09.2019) 

-mod: geneterate seperate compress_xxx.css for sites

> Version 7.0(3) (30.08.2019)
-add: New Dynamic - Elements with Info and Link to the original Elements
-add: Menu-bar included inner Elements 
-add: Menu-bar "Semantic-ui"
-add: Semanic-Ui 2.7.7
-add: jquery-ui (slim)
-add: jquery 
-add: Toast for Information
-add: Colorpicker with transparent
-add: individual Backround-Image for every page with news settings
-add: Redesignet Designer :)  

CREATE TABLE `log_change_site` (
  `log_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `site_id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hole_page` int(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `log_change_site`
--
ALTER TABLE `log_change_site`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `site_id` (`site_id`,`page_id`,`user_id`,`hole_page`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `log_change_site`
--
ALTER TABLE `log_change_site`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;COMMIT;


DROP TABLE `email_setting`, `explorer`, `feedback_fields`, `gadget_feedback`, `gadget_gallery`, `gadget_guestbook`, `gadget_shop`, `gcm_logfile`, `gcm_users`, `id_layer2id_page`, `id_layer2id_seite`, `id_seite2id_page`, `LangFeedback_fields`, `LangLayer`, `LangLayout`, `LangMenu`, `LangSeite`, `menu`, `tbl_layer`, `tbl_menu`, `tbl_page`, `tbl_profil`, `tbl_profil_menu`, `tbl_seite`, `tbl_useralias`, `voting`, `voting_error`, `voting_group`;
-add: mysql -> ALTER TABLE `smart_layer` ADD `page_id` INT(11) NOT NULL AFTER `layer_id`, ADD INDEX (`page_id`);
-add: mysql -> ALTER TABLE `smart_id_site2id_page` ADD `favorite` INT(1) NOT NULL AFTER `set_update`;
-add: admin -> set page favroite 
-add: admin -> search page 4 editing


> Version 6.7 (15.02.2019)

-add: design -> many more options like background-image for menu and menu-button padding
-add: admin -> global_setting -> Info as well for the menu_text
-fix: admin -> Link for public-domain
-fix: admin -> save css inner design fix using "'" inner css 

> Version 6.6 (07.02.2019)

-add: New Ckeditor 4.11.1
-add: admin -> check global options setting
-mod: gadgets -> everything with "ckeditor" 
-mod: cleaning Ventor
-fix: Global options save 

> Version 6.5.2 (30.01.2019)

-fix: load view-page link "Preview"

> Version 6.5.1 (11.01.2019)

-mod: background-image -> repeat-no

> Version 6.5 (09.01.2019)

-add: admi -> Button for actual page -> public link
-add: global options -> Google Adsense
-add: gadget -> newsletter -> button_stretch and size 
-mod: structure-field much bridger
-mod: admin-> search structure filter
-fix: set cookie for site_id

> Version 6.4.3 (24.12.2018)

-add: set_dynamic_load for every page 
-fix: remove site_id after change page_id

> Version 6.4.2 (24.12.2018)

-fix: some bug-fixes

> Version 6.4.1 (10.11.2018)

-add: ckeditor 4.11.1
-mod: admin -> menu sturcture -> better view
-fix: gadget -> formular -> submit as well first line inner select 0 = isset()


> Version 6.4 (31.10.2018)

-add: Template generator for Elements

> Version 6.3.1 (25.10.2018)

-fix: some fixes for get index_id

> Version 6.3 (18.10.2018)

-mod: semantic-ui 2.4.1
-fix: show logo inner menu
-fix: admin -> delete page -> close button works now
-fix: admin -> show mneu as list

> Version 6.2.5 (12.10.2018)

-add: admin -> edit menustructure (List-View for hidden sites inner remove struktue_points
-mod: gadget -> fruitmap 
-mod: semantic-ui 2.4.0
-mod: ckeditor v4.10.1

> Version 6.2.4 (16.09.2018)

-mod: ckeditor 4.11.1
-mod: admin -> gadget -> splitter 
-fix/mod: gadget -> searchengine

> Version 6.2.3 (04.09.2018)

-add: gadget -> starttime for video

> Version 6.2.2 (31.08.2018)

-add: cookie - consent https://cookieconsent.insites.com/download/

> Version 6.2.1 (31.07.2018)

-add: ckeditor 4.10.0

> Version 6.2 (03.07.2018)

-add: semantic-ui 2.3.3
-add: page -> options -> "no index for robot
-add: db -> options -> smart_options instead off smart_page for save global parameters
-mod: remove all pages for generate hole page after edit header or footer
-fix: admin -> del site -> structure menu reload
 
> Version 6.1 (9.5.2018)

-add: semantic-ui 2.3.1
-add: Followup sequence for newsletter system
ALTER TABLE `logfile` ADD `followup_id` INT(11) NOT NULL AFTER `status`;

> Version 6.0

ALTER TABLE `smart_langLayer` ADD FULLTEXT(`title`);
ALTER TABLE `smart_langLayer` ADD FULLTEXT(`text`);
ALTER TABLE `smart_langSite` ADD FULLTEXT(`title`);
ALTER TABLE `smart_formular` ADD `newsletter_field` VARCHAR(20) NOT NULL AFTER `set_email`;
ALTER TABLE `smart_formular` ADD `position` INT(11) NOT NULL AFTER `newsletter_field`;
ALTER TABLE `smart_formular` ADD `splitter_field_id` INT(11) NOT NULL AFTER `position`;
ALTER TABLE `smart_formular` ADD `default_value` VARCHAR(255) NOT NULL AFTER `splitter_field_id`;

-add: element -> earchengine
-add: semantic-ui 2.3.0
-add: uploader
-add: redesign
-add: new - laod-progress fpr static page (special for firefox)
-add: admin: scrolling for sitebars
-add: > Versioning vor scripts, news loading page - optimizing
-add: element -> gallery -> show title and text 
-mod: change structure scrollUp from element to js
-mod: cleaning and setting config.inc.php

> Version 5.8.9 (ß)

(DEV.-add Menüstruktur - Unterpunkte auf gleiche Seite verlinken mit Flaggen 

> Version 5.8 (21.02.2018)

-mod: semantic-ui 2.3.0

> Version 5.7.1 (12.02.2018)

-add: admin -> sortable -> background -> blue for move area
-mod: New sitemap - generator 
-fix: element -> formular-generator
-fix: gallery -> list -> click resize img





> Version 5.6 (11.01.2018)

-add: gadget -> icons
-mod: on the top "Zum Anfang"
-fix: sortable -> elements
-fix: background for element

> Version 5.5 (04.01.2018)

-add: jquery 2.1
-add: header - background "cover" mode and repeat - once
-mod: cleaning js - code change "hover" prepare for jquery 3.x
-mod: Designer modified and some bug fixes
-fix: sitebar -> pusher

> Version 5.4 (27.12.2017)

-add: ckeditor 4.8
-fix: Modal-Structure-Menu

> Version 5.3 (20.12.2017)

ALTER TABLE `smart_id_site2id_page` ADD `funnel` INT(1) NOT NULL AFTER `parent_id`;
-add: Funnel List
-add: Set Anker for Elements
-mod: Formulardigner -> Edit Laben now as well inner Formular-Editor

> Version 5.2 (04.12.2017)

-add: admin -> keyup-change for url_name and menu_title

> Version 5.1 (02.12.2017)

-add: New Element -> Learning
-add: New Explorer-> Version (some bugs fixed and new design), generate Thumbnail inkl. rotate image
-add: Design -> choose Background Color with colorpicker
-add: Element -> Photo -> Show text in Modal-Design  
-mod: Remove Elements without request
-mod: z-index-structure for Sitebar 
-mod: modal now always on the top
-mod: better design formular, in special checkboxes (same font-size)

> Version 5.0 (14.11.2017)

-add: admin -> element -> cloning from field (exept splitter)
-add: element -> Button -> target (same page or new page)
-add: element -> line
-add: Element-Settings changing in realtime 
-add: element -> Amazon
-add: element -> PDF -> Message if the browser doesn't support inline PDF
-add: element -> photo -> set your own "style"
-add: background - Parallax-Effect, Background-color
-add: Sidebars
-add: 100% width - Modus
-add: New Button-Structure
-add: Element -> Formular -> Settings -> Submitbutton -> choose icon and color 
-mod: element -> button -> add "one line" and align 
-mod: element -> newsletter "alert" for admin
-mod: Update: ckeditor, fancybox3
-mod: some modifications for modal
-mod: cleaning css and js
-mod: optimization designer
-fix: z-index managed

> Version 4.4 (25.09.2017)

-change: mysql -> mysqli
-mod: change URL set --- -> -
-mod: admin -> gadget->dropdown -> is holding for view sek. for better use it
-
-fix: admin -> show Settingtitle after call ajax
-fix: hide splitter-buttons during edit Text
-fix: admin -> designer -> top_smart_content
-fix: admin -> gadget -> galery -> fleximages -> laod correkt after move and laod  
-fix: gadget -> formular -> add class 'formular'

> Version 4.3 (20.09.2017)

-add: gadget -> photo -> more function and better style (Button for Link and resize)
-add: admin -> New Editbar for Elements
 

> Version 4.2.2 (09.09.2017)

-add: admin -> menu -> open there the site
-mod: gadget -> gallery -> felximage v2.6.4

> Version 4.2.1 (01.09.2017)

-add: gadget -> photo -> variations

> Version 4.2 (30.08.2017)

-add: admin -> refresh button for reload the page
-add: secondname next to firstname possible (toggle)
-mod: admin -> smart_form 1.7
-mod: admin -> splitter field better visible
-fix: gadget -> newsletter -> formular -> radio button for intro works now

> Version 4.1 (28.08.2017)

-add: admin -> gadget -> newletter -> new Setting-button for "Listbuilding"

> Version 4.0 (20.08.2017)

-add: all important Button on the right side
-add: new Design 
-add: new Element - Bar
-mod: cleaning code
-fix: generate new page (clone)

> Version 3.7 (19.08.2017)

-add: elements (Grid) now view in a streched modus possible
-add: admin -> when remove split-element all other elements inner splitter are removed as well 
-add: admin -> Elements now in the Sidebar -> easier using now!
-mod: admin -> Delete field now with Infotext
-mod: code - redesign for show elements

> Version 3.6.1 (15.08.2017)

-add: admin -> templatesgenerator -> new form
-fix: admin -> design -> menu -> save using checkbox for header and border
-fix: admin -> explorer -> show link - Button for "photo - gadged"

> Version 3.5 (13.08.2017)

-add: admin -> edit Menu (hole sturcture and new design)
-add: admin -> Option Edit redsigned
-add: gadget -> newsletter -> multiadding now possible
-mod: exploerer update
-mod: admin -> remove Edit-Sites Structure (using now Menu-Structure)

> Version 3.4.2 (10.08.2017)

-add: admin -> New Edit-Buttons on the right site

> Version 3.4.1 (07.08.2017)

-add: Label -> different styles (tag, corner, ribbon, attachent)
-add: smart_form -> semantic-ui 2.2.13
-add: Show - ID from Page inner dropdown - Domains
-mod: facebook -> pixel 
-fix: gadgets -> formular -> checkbox edit (admin)


> Version: 3.4 (31.07.2017)

-add: new layout-Design (Sidabar)
-add: gadget -> photo -> Link now Window in Window possible
-add: new class: modal-video
-mod: smart-phone - show Logo inner Menu however is disabled 
-mod: smart-phone css -padding 10px; (distance to the border)

> Version: 3.3 (20.07.2017)

-add: layer -> hiddemodus for elements for smartphones 
-add: Logo for Menu - Header for smartphone mode
-mod: ckeditor 
-mod: smart_form
-mod: smart menu v1.0.1

> Version: 3.2.5 (25.06.2017)

- add: Clone copy spitter and Button as well
- fix: empty content before load modal

> Version 3.2.4 (8.06.2017)

- add: verify_key -> for newsletter complete

> Version 3.2.3 (27.05.2017)

- fix: site-option -> save correct "meta_title 


> Version 3.2.2 (24.05.2017)

- add: gadget -> newsletter -> to copmplete

> Version 3.2.1 (20.05.2017)

- add: gadget -> button -> no fluid setting possible
- fix: gadget -> map -> session for public-mode works now (filter)

> Version 3.2 (09.04.2017)

- mod: Splitter -> add "1 column"

> Version 3.1 (20.04.2017)

- mod: Add-Site Button bigger now and on the bottom on the page
- fix: Generate Template correct setting for Guestbook

> Version 3.0 (16.04.2017)

ALTER TABLE `smart_layer` ADD `splitter_layer_id` INT NOT NULL AFTER `from_id`;
ALTER TABLE `smart_layer` CHANGE `position` `position` VARCHAR(10) NOT NULL; 
UPDATE smart_id_site2id_page SET `split_representation`='double' WHERE `split_representation` = '1' or `split_representation` = ''
-add: New - Element -> splitter
-add: Admin -> Button -> Call public-page (view)
-mod: Admin -> Template -> generate
-fix: Element -> photo -> choose from explorer (direct change)

> Version 2.2 (05.04.2017)

ALTER TABLE `smart_id_site2id_page` ADD `set_update` INT(1) NOT NULL AFTER `timestamp`;
-add: admin -> Faster release page with rsync
-add: admin -> set_update -> for faster Update

> Version 2.1 (04.04.2017)

-add: Hide Breadgrumb if is just home  page 
-mod: Site - Modal -> large 
-mod: Add-Site Button again includet inner Menu-Stucture (top)
-fix: gadget=textfield with empty field possible


> Version 2.0 (02.04.2017)

Remove config_public.php now gadgets/config.inc
-add: New Top-Menu bar
-add: Gadget -> Buttons with fixed on the bottom
-add: Design -> Head -> Background-Color
-add: Site -> Clone
-add: Site-List -> Order by "timestamp,nach,release
-mod: gadgets -> portal 
-mod: Replace Title  to URL
-mod: Newslettefield just active, when User has rights
-fix: Break-routine if user_id or page_id does not exist

> Version 1.5.3 (30.12.2016)

ALTER TABLE `smart_id_site2id_page` ADD `timestamp` TIMESTAMP NOT NULL ; 
ALTER TABLE `smart_id_layer2id_page` ADD `timestamp` TIMESTAMP NOT NULL ; 
ALTER TABLE `smart_layer` ADD `archive` INT(1) NOT NULL ; 
ALTER TABLE `smart_layer` ADD `hidden` INT(1) NOT NULL ; 
ALTER TABLE `smart_layer` ADD `from_id` INT(11) NOT NULL ;
-fix: set hiddenfield for image_upload
-fix: header -> segment -> "attached top" now DIV ist closed 

> Version 1.5.2

-add: css -> background -> now apsolute path posibele (example: http://www.ssi.at/background.jpg)
-add: setting -> site -> column reverse position possible

> Version 1.5.1

-add: Redesign admin-code
-add: Fields can be archived
-add: List for archived - Fields
-add: Field can be visible now
-add: Fields can be moved
-add: New massage-design for Infos after action 
-add: gadget -> embed -> autostart
-add: gadget -> ticker -> Send direct-Messages to the user
-mod: css -> width-max -> tablet -> 960px

> Version 1.4.9 (28.11.2016)

-fix: gadget -> gallery -> slideshow2 

> Version 1.4.8 (28.11.2016)

-fix: semantic-view

> Version 1.4.7 (26.11.2016)

-add: gadgets -> all -> label -> modivication
-add: gadgets -> all -> segment -> cirular and other types

> Version 1.4.6 (25.11.2016)

-add: gadgets -> new function -> label for all gadgets (design, color & Text)
-mod: gadgets -> newsletter,feedback.formular -> New email send function (look into ../funtion.inc.php)

> Version 1.4.5 (24.11.2016)

-add: gadget -> all -> new parameter -> "align, style"
-fix: gadget -> textfield -> edit text after call options and cleaning <div>

> Version 1.4.4 (23.11.2016)

-add: gadget -> Counter -> add time 
-mod: smart_form -> Version 1.2.7

> Version 1.4.3 (22.11.2016)

-mod: gadget -> Formular -> Textfield -> Newfunction -> rows
-mod: gadget -> Newsletter -> submit -> verify_link now ist ab link with Text

> Version 1.4.2. (20.11.2016)

-mod: Gadget -> Newsletter -> Config (disable firstname, secondname, and more)
-fix: Gadget -> Formular -> Move-Field the right layer_id will be taken
-fix: Gadget -> Contact-Formular -> Call user_id from confing_public.php

> Version 1.4.1 (17.11.2016)

-mod: Gadget -> Newsletter -> New functions (Textbausteine, Email-Text)
-mod: Admin -> Buttons bigger

> Version 1.4.0 (10.11.2016)

-add: Admin -> Gadget -> Login-bar -> New Editfunctions
-add: Admin -> Gadget -> Newsletter -> New Editfunctions
-add: Admin -> Gadget -> Feedback -> New Editfunctions 
-add: Admin -> Globalsetting -> now on the top
-add: Admin -> Speed-Buttons (Add-Site and Site-Setting)
-mod: Gadget -> Login-bar -> call user_id form cookies
-mod: Gadget -> Formualer & Feedback -> cleaning and fixing
-mod: Admin -> Remove "Setting" now is called "More"
-fix: Admin -> Add Dynamic-Sites 
-fix: Admin -> Disable Edit-Button when Page is locked

> Version 1.3.8 (08.11.2016)

-Add: Admin -> Global Setting -> New Modal & Formalar
-Add: Admin -> Global Setting -> Setting for Recaptcher
-Add: Fomular -> Recaptcher for save submit for pots
-Add: Feedbackformular -> Recaptcher for save submit for pots

> Version 1.3.7 (05.11.2016)

-Add: Global-Setting -> FacebookPixel

> Version 1.3.6 (04.11.2016)

-Add: Design -> Menu -> Color from seperation-line changeable
-Mod: Admin -> Menu -> Admin-Button now in green and smaller 

> Version 1.3.5 (01.11.2016)

-Mod: Map v2.3 

> Version 1.3.4 (31.10.2016)

-Mod: semantic-ui 2.2.6

> Version 1.3.3 (26.10.2016)

-Mod: Fruit-Map v2.2
-Mod: Menu -> Backgroundcolor Supmenu has the same like usual bg
-Fix: Admin -> EditMenu -> Backgroundcolor select

> Version 1.3.2 (26.10.2016)

-Mod: Fruitmap -> Search for Fruits inner map
-Fix: Slider2 -> carussel in combine with fancybox works now

> Version 1.3.1 (25.10.2016)

-Add: Meta -> cache-control -> nocash
-Add: Meta -> show > Versionnumber from SmartKit
-Mod: Slider2 -> resize thumbnails for carusell
-Fix: Slider2 -> padding 0px;
-Fix: Menu -> font-family -> same like config

> Version 1.3 (12.10.2016)

-Add: CkEditor -> Image2 (leichter verschiebbar und vergrößerbar + Bildunterschrift
-Add: Hide Menu 
-Mod: New Menu for Smart-Phone view "Menü"
-Mod: FlexImage2 => Version 2.6.1
-Mod: padding-top -> smart_body
-Mod: bazar => Version 0.6
-Fix: Login with facebook in special for bazar

> Version 1.2.4 (10.10.2016)

-Add: New SmartMenu (1.0) (Hide in Smart-Modus)
-Add: Erweiterung für MenüDesign
-Add: Segment-Erweiterung Size
-Mod: Admin-Button für Menü
-Mod: Seitenverwaltung
-Mod: Modul "Fruicity" v2.1
-Mod: FlexSlide
-Fix: Elemente-Button verbessert

> Version 1.2.3 (30.05.2016)

-Fix: Anzeigen von Gadgets auch wenn keine "smartLayer_lang eingetragen ist (Bsp.: Formular)

> Versions 1.2.2 (09.09.2016)

- Add: Modul -> Fomular
- Add: Modul -> Script
- Mod: SmartForm 

> Version 1.2.1

- Add: Gallery -> Interne Verlinkung der Bilder möglich
- Add: Formular -> Es kann eine eigene Email als Empfänger gewählt werden

> Version 1.2

- Add: Gallery -> Link zum direkten aufrufen des Folders
- Add: Iframe -> Höhe eintstellbar
- Add: 100% Breite auswählbar
- Add: Hintergrundbild von der Kopfzeile individuell auswählbar
- Add: Gallery -> Link kann angegeben werden und bei klick auf Bild direkt weiter geleitet werden
- Add: Gallery -> Listendarstellung speziell für Logos mit max Größe
- Mod: Abstand zwischen den Feldern bis zu 100px erweiterbar
- Mod: Explorer -> Auswahl der Bilder für Hintergrund in "Modal"-Fenster 
- Fix: Menüfeldbearbeitung ladet nach Änderung die Felder neu

> Version 1.1.9

- Add: Ladeprozess wird angezeigt
- Add: Photo -> Style bearbeitbar, Setting: vergößerbar 
- Add: Gallery -> Neuauflage der gesamte Gallerystruktur mit Vorbereitung aus Sortierfunktion, Setting: vergößerbar
- Add: Gadget -> Feedback-Form smart-form
- Add: Gadget -> Lauftext
- Add: Formular -> Neues Feld eingefügt: Telefon
- Add: Include Minify für JS und CSS (40% schnellere Ladezeiten
- Mod: Hintergrundbild jetzt auch mit content verschiebar + mit Doppelklick wird Ursprungsposition wieder bezogen 0px 0px
- Mod: Gadget -> Feedback-Form "response@ssi.at" eingeführt + Info "Do not use response Email"
- Mod: Semantic-ui 2.2.
- Mod: Elemente in einem Popup übersichtlich gestaltet
- Mod: New Flexlider 2.6.1
- Mod: Quellcode für HTML-Seiten optimiert (js und css aufgeräumt - und werden nur modulspezifisch geladen)
- Fix: Darstellung Fleximage

> Version 1.1.8
 
- Dev: Bestätigungsroutine einbauen für die Registrierung!! - eventuell Iframe und dir Anmeldung von CENTER.ssi.at verwenden!

> Version 1.1.7 (5.12.2015)

- Mod: Gadget => Zählt die geführten Meditationen mit

> Version 1.1.6

- Fix: Formular "\n"
- Add: Bazar (v01)
- Mod: Spalten -> Grid 
- Mod: css-Modifikationen

> Version 1.1.5

- Add: Neues Modul: Meditation-Timer
- Add: Bazaz (Develop)
- Mod: Counter
- Mod: semantic-ui 2.1.6

> Version 1.1.4

- Add: Neues Modul: Gästebuch
- Add: Neues Modul: Countdown
- Add: Felder können mit Farben und Darstellungsarten verbessert dargestellt werden
- Mod: semantic-ui 2.1.5 

> Version 1.1.3

- Add: Neues Login bei Administration
- Mod: New breadcrumb
- Mod: Sitebar ausgehängt - Smart-Phonemenü bleibt über Smart-Menu
- Mod: Smart-Phone - View (Meta-Tag eingbunden) - Darstellung nun korrekt
- Fix: Border - für alle Gadgets in korrekter Darstellung

> Version 1.1.2

- Add: Neue Darstellung - Segment für alle Elemente
- Mod: Textfeld wurde als Gadget in das System integriert
- Fix: padding wurde dur margin ersetzt (Abstand zum Rand)

> Version 1.1.1

- Add: Neue Module: Links mit Thumbnails, Gästebuch und PDF
- Mod: Einstellungen der Seiten (Layout, ein-zweispaltig) vereinfacht, Autolaod nach Einstellungen ändern
- Mod: Smart-Form erweitert - mit INFO - Feld
- Mod: Icon - bei Autospeichern vereinfacht
- Mod: semantic-ui 2.1.4
- Mod: Gallery - Update (Verbesserte Darstellung)
- Fix: Nach hineinziehen eines Modules werden die Ediit-Button angezeigt

> Version 1.1.0

- Add: Smart-Phone optimized
- Add: Menu fixe on the top after page scroll
- Add: Manuel setting for jquery
- Mod: semantic-ui 2.0.7

> Version 1.9.1

- Mod: Some changes an Bug-Fixes for 21days - Modules 
- Mod: semantic-ui 2.0.3
- Mod: (adm) Edit-Menu - bottom after call 
- Fix: (adm) Edit-Menu-Button (hide/show) 
- Fix: Border - dashed blue for the textfields
- Fix: Close button works after <3 

> Version 1.9

- Mod: Hintergrund vom Header wird bei laden eines neuen Bildes auf 0px 0px positioniert
- Mod: Fancybox Zoom Button 
- Fix: alt='' remove if is empty
- Add: Element -> Placeholder

> Version 1.8

- Mod: Gallery (choose just images)
- Mod: ckeditor -> mr format, copy, paste direct
- Add: MiniPlayer for MP3

> Version 1.7

- Add: Menu (Mobile)

> Version 1.6

- Add: New Modal for Exporer
- Add: New slideshow (gesamte Bilderdarstellung überarbeitet
- Add: List

 > Version 1.5 
 
- Add: Admin -> Layout -> width content (left, right) in % with slider
- Add: Semantic-ui (implementet)
- Mod: New - Collage + (http://goodies.pixabay.com/jquery/flex-images/demo.html)
- Fix: Edit - Layout (Close function)

> Version 1.4

- Add: Client-datas inner dynamic-Elements
- Add: Tooltip für more Infomation 
- Mod: Design improved


> Version 1.3

- Fix: Move image inner headline, do not move automaticly after click "edit-Layout-form"
- Fix: jsTree do not show Save - Button form dialog or some other buttons!
- Fix: Call Tooltip - function just one time - > faster load
- Update: Backgroundimage-move 1.3


> Version 1.2

- New Tooltip
- New Module "dynamilce element
- Social - Network Facebook Like and Share
- Fix: Dialog on fix position and z-index higher then Top-Bar

> Version 1.1

- New Module
- Update 21Days -> More functions (Auto load, Progressbar)
- Module -> Photo -> Fast Chose and Autosave


> Version 1.0(beta) (30 houers)

- Single und DoubleColumn
- New Module -> 21Days
- ReProgramming Code for Module AddOne
- Align for modules (facebook, title,...)

> Version 1.0(alpha) (30 houres)

- New Site -> After after generate a textfield, chose position inner menu
- Edit/newSite -> Check URL-name and rename automaticaliy
- TourGuide for using smart-kit
- New layout posibilties for the menu-style (padding, gradient and more)
- New Design for DataTables
- New Modules -> Facebook, Sitemap Newsletter (Form), Date, Countdown, ..)
- New Module -> Image with rezise options
- New Module -> BreadCrumb (Setting: padding)
- New! Autoresize Images Fast Loading the page

> Version 0.13 (4 Stunden)

- Edit Background Header
- New Module -> SingleImage with text and resize


> Version 0.12 (10 Stunden)

- SmartMenu 
- Sortablefunction for menus
- List for all Sites
- Invisible funtion for menu-fields
- Layout -> Choose Menu

> Version 0.11 (10 Stunden)

- Css parameter
- Modules (Counter, Date, Title, Newsletter)
- New Menu-structure

Verion 0.10 (20 Stunden)

- AutoResize Uploaded Images
- ckeditor 4.4.5
- Modules Drag and Drop + Sortable

> Version 0.9 (25 Stunden)

- Modulerweiterungen (Einbinden von Youtuber,Vimeo und Iframes
- Gallery wurde optimiert und in der Datenbank vereincht gespeichert
- Verkn�pfen von Links mit intern Seiten m�glich
- Design: Hindergrundbilder von Body, Kopf, Mittel und Fussteil einstellbar, weitere Parameter wie H�he einstellbar

> Version 0.8 (20 Stunden)

- more designfunctions 
- Text for menu


> Version 0.7 (30 Stunden)

- Refresh the hole gallery
- new Login
- Gadget -> Gallery now with Icons for easyer choose
- Reload-Button on the right corner
- load dialog fadeIn/fadeOut

> Version 0.6 (17 Stunden)

- Sortable Layer 
- Include Gallery
- CollagePlus v0.3.3

> Version 0.5 (20 Stunden)

- Explorer (Multiupload images)

> Version 0.4 (16 Stunden)

- Insert Layer
- Edit CSS

> Version 0.3 (15 Stunden)

- Link f�r generater Public-site with Meta and Title
- Generate "Static"-Page
- Page Options

> Version 0.2 (20 Stunden)

- Add other sites
- Add new pages
- Menu
- Screen optimizing f�r Tampletes and Smartphones

> Version 0.1 (12 Stunden)

- Inline Editor
- Autosave
- Button Edit "ON" and "OFF"


