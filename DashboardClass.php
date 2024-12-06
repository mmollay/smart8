<?php
include(__DIR__ . "/config.php");

// In einer zentralen config.php oder ähnlich
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    define('WEB_ROOT', '/smart8');  // Lokale Entwicklung
} else {
    define('WEB_ROOT', '');  // Produktionsserver
}

define('SMARTFORM_PATH', WEB_ROOT . '/smartform2');

//Top-Leister generell
$dashboard = new Dashboard($title, $db, $userId, $version, $moduleName);

if ($userDetails['firstname'] || $userDetails['secondname'])
    $user = $userDetails['firstname'] . " " . $userDetails['secondname'];
else
    $user = $userDetails['user_name'];

$dashboard->addMenu('mainMenu', 'ui huge  top fixed menu', false);
$dashboard->addMenuItem('mainMenu', "main", "../main/index.php", "", "tachometer alternate icon blue icon", "Dashboard", "left", "", false, "", true);

$dashboard->addMenuItem('mainMenu', $moduleName, "home", $title, "building icon", "Startseite laden");
$dashboard->addMenuItem('mainMenu', "main", "settings", $user, "", "User Einstellungen (" . $userDetails['user_name'] . ")", "right");
//$dashboard->addMenuItem('mainMenu', "main", "../../logout.php", "Abmelden", "sign red out icon", "Abmelden", "right");
$dashboard->addMenuItem('mainMenu', "main", "../../auth/logout.php", "Abmelden", "sign red out icon", "Abmelden", "right", "", false, "", true);

//$dashboard->addJSVar("smart_form_wp", "../../../smartform/");
//$dashboard->addScript("../../../smartform/js/smart_list.js");
//$dashboard->addScript("../../../smartform/js/smart_form.js");

$dashboard->addScript(SMARTFORM_PATH . "/js/formGenerator.js");
$dashboard->addScript(SMARTFORM_PATH . "/js/listGenerator.js");
$dashboard->addScript(SMARTFORM_PATH . "/js/ckeditor-init.js");
$dashboard->addScript(SMARTFORM_PATH . "/js/fileUploader.js");


//$dashboard->addScript("alert('test');", true);  //Inline-Script

$dashboard->setSidebarClass('ui left vertical pointing menu'); //Menü immer sichtbar 
$dashboard->setSidebarVisibleOnInit(true);

// Konfiguriere die Sidebar mit einem Array von Einstellungen
$dashboard->configureSidebar([
    'transition' => 'overlay',
    'dimPage' => false,
    'direction' => 'top',
    'closable' => true,
    'duration' => 500,
    'easing' => 'easeInOutQuad'
]);

class Dashboard
{
    private $webRoot = '';
    private $jsEnabledMenus = [];
    private $menus = [];
    private $topMenuItems = [];
    private $pageTitle;
    private $scripts = [];
    private $inlineScripts = [];
    private $styles = [];
    private $jsVars = [];
    private $userId;
    private $db;
    private $version;
    private $moduleName;
    private $defaultPage = 'home';
    private $sidebarClass = 'ui vertical labeled icon sidebar menu';
    private $sidebarVisibleOnInit = false;
    private $menuClass = 'ui green top massive fixed menu';

    private $userPermissions = [];

    private $footerContent = '';

    // Neue Methode zum Hinzufügen von Footer-Inhalten
    public function addFooterContent($content)
    {
        $this->footerContent .= $content;
    }


    public function __construct($title, $db, $userId = null, $version = "1.0", $moduleName = "")
    {
        // Bestimme den Web-Root basierend auf dem Server
        if ($_SERVER['SERVER_NAME'] === 'developsmart8.ssi.at') {
            $this->webRoot = '';  // Root-Pfad für Produktion
        } else {
            $this->webRoot = '/smart8';  // Root-Pfad für lokale Entwicklung
        }

        $this->pageTitle = $title;
        $this->db = $db;
        $this->userId = $userId;
        $this->version = $version;
        $this->moduleName = $moduleName;

        if ($userId) {
            $this->loadUserPermissions($userId);
        }
    }

    // In DashboardClass.php korrigieren:

    private function loadUserPermissions($userId)
    {
        // Erst prüfen ob Benutzer Superuser ist
        $stmt = $this->db->prepare("
        SELECT superuser 
        FROM user2company 
        WHERE user_id = ?
    ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if ($row['superuser'] == 1) {
                $this->userPermissions['is_superuser'] = true;
                return;
            }
        }

        // Vereinfachte Version für den Anfang
        // Hier prüfen wir nur ob der User die Grundberechtigungen hat
        $stmt = $this->db->prepare("
        SELECT 
            m.identifier as module_identifier,
            um.status
        FROM user_modules um
        JOIN modules m ON m.module_id = um.module_id
        WHERE um.user_id = ? 
        AND um.status = 1
    ");

        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $moduleId = $row['module_identifier'];
            // Standardberechtigungen setzen
            $this->userPermissions[$moduleId] = ['view', 'edit', 'delete'];
        }
    }

    // Die hasUserPermission Methode bleibt gleich
    public function hasUserPermission($module, $permission)
    {
        // Superuser hat immer alle Rechte
        if (isset($this->userPermissions['is_superuser'])) {
            return true;
        }

        // Wenn noch keine Module zugewiesen sind, erstmal Standardrechte für 'main' setzen
        if (empty($this->userPermissions)) {
            $this->userPermissions['main'] = ['view', 'edit', 'delete'];
        }

        // Prüfen ob das Modul überhaupt zugewiesen ist
        if (!isset($this->userPermissions[$module])) {
            // Für das 'main' Modul immer true zurückgeben
            if ($module === 'main') {
                return true;
            }
            return false;
        }

        // Prüfen ob die spezifische Berechtigung vorhanden ist
        return in_array($permission, $this->userPermissions[$module]);
    }

    /**
     * Helper Methode um den aktuellen Berechtigungsstatus zu sehen
     */
    public function getCurrentPermissions()
    {
        return $this->userPermissions;
    }

    /**
     * Gibt alle Berechtigungen eines Benutzers für ein Modul zurück
     */
    public function getUserModulePermissions($module)
    {
        if (isset($this->userPermissions['is_superuser'])) {
            // Superuser hat alle verfügbaren Berechtigungen
            $stmt = $this->db->prepare("
                SELECT permission_key 
                FROM module_permissions 
                WHERE module_id = (SELECT module_id FROM modules WHERE identifier = ?)
            ");
            $stmt->bind_param('s', $module);
            $stmt->execute();
            $result = $stmt->get_result();
            $permissions = [];
            while ($row = $result->fetch_assoc()) {
                $permissions[] = $row['permission_key'];
            }
            return $permissions;
        }

        return $this->userPermissions[$module] ?? [];
    }

    /**
     * Prüft ob der Benutzer Superuser ist
     */
    public function isSuperUser()
    {
        return isset($this->userPermissions['is_superuser']);
    }

    private $sidebarConfig = [
        'transition' => 'overlay',
        'dimPage' => true,
        'direction' => 'left',
        'closable' => true,
        'duration' => 500,
        'easing' => 'easeInOutQuad'
    ];

    private function checkActiveSession()
    {

        $userId = $this->userId;

        if (!isset($userId)) {
            return false;
        }

        $stmt = $this->db->prepare("SELECT user_id FROM user2company WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return false;
        }

        $row = $result->fetch_assoc();
        return $row['user_id'];
    }




    public function addMenu($menuId, $menuClass = 'ui compact menu', $toggleButton = false)
    {
        $validPositions = ['left', 'right', 'top', 'bottom'];
        $positionClasses = explode(" ", $menuClass);
        $position = reset(array_intersect($positionClasses, $validPositions));

        if (!isset($this->menus[$menuId])) {
            $this->menus[$menuId] = [
                'menuClass' => $menuClass,
                'items' => [],
                'position' => $position,
                'toggleButton' => $toggleButton
            ];
        }
    }

    // Existierende addScript Methode erweitern oder neue Methode erstellen
    public function addSmartformScript($scriptName)
    {
        $this->addScript(SMARTFORM_PATH . "/js/" . $scriptName);
    }

    public function addMenuItem(
        $menuId,
        $module,
        $page,
        $name,
        $icon,
        $popup = '',
        $position = 'left',
        $class = '',
        $isDefault = false,
        $onClick = '',
        $isExternalLink = false  // Neuer Parameter
    ) {
        if (isset($this->menus[$menuId])) {
            $this->menus[$menuId]['items'][] = [
                'module' => $module,
                'page' => $page,
                'name' => $name,
                'icon' => $icon,
                'popup' => $popup,
                'position' => $position,
                'isDefault' => $isDefault,
                'class' => $class,
                'onClick' => $onClick,
                'isExternalLink' => $isExternalLink  // Neues Feld
            ];
            if ($isDefault) {
                $this->defaultPage = $page;
            }
        }
    }

    public function renderMenu($menuId)
    {
        if (isset($this->menus[$menuId])) {
            $menu = $this->menus[$menuId];
            $menuClass = htmlspecialchars($menu['menuClass']);
            $toggleButtonId = "toggle-button-$menuId";

            $leftItems = '';
            $rightItems = '';

            foreach ($menu['items'] as $item) {
                $itemHtml = !$item['page']
                    ? '<div class="header item">' . $item['name'] . '</div>'
                    : $this->renderMenuItem($item);

                if ($item['position'] === 'right') {
                    $rightItems .= $itemHtml;
                } else {
                    $leftItems .= $itemHtml;
                }
            }

            //Wenn toggleButton nicht gesetzt, dann return
            if ($this->menus[$menuId]['toggleButton'] == true) {
                $toggleButtonHtml = "<button class='ui button' id='{$toggleButtonId}'>&#9776;</button>";
            }

            echo "<div class='{$menuClass}' id='{$menuId}'>\n";
            echo $toggleButtonHtml;
            echo $leftItems;
            if (!empty($rightItems)) {
                echo "<div class='right menu'>{$rightItems}</div>";
            }
            echo "</div>";
        }
    }
    private function renderMenuItem($item)
    {
        // Wenn es ein Button mit onClick Handler ist, nutzen wir einen Button statt einem Link
        if ($item['onClick']) {
            $html = "<button {$addPopup} class='item " . htmlspecialchars($item['class']) . "' ";
            $html .= "onclick='" . htmlspecialchars($item['onClick']) . "'>";
            if (isset($item['icon']) && $item['icon'] !== '') {
                $html .= "<i class='" . htmlspecialchars($item['icon']) . "'></i> ";
            }
            $html .= $item['name'] . '</button>' . "\n";
            return $html;
        }

        // Für externe Links
        if ($item['isExternalLink']) {
            $html = "<a class='item " . htmlspecialchars($item['class']) . "' ";
            if ($item['popup']) {
                $html .= "data-content='" . htmlspecialchars($item['popup']) . "' ";
            }
            $html .= "href='" . htmlspecialchars($item['page']) . "'>";
            if (isset($item['icon']) && $item['icon'] !== '') {
                $html .= "<i class='" . htmlspecialchars($item['icon']) . "'></i> ";
            }
            $html .= $item['name'] . '</a>' . "\n";
            return $html;
        }

        // Normales Menü-Item als Link
        $dataPage = ($item['page'][0] === '/' || $item['page'][0] === '.') ? '' : htmlspecialchars($item['page']);
        $addId = $item['module'] ? '' : "id='" . htmlspecialchars($item['page']) . "'";
        $addPopup = $item['popup'] ? "data-content='" . htmlspecialchars($item['popup']) . "'" : '';

        $html = "<a {$addPopup} class='item " . htmlspecialchars($item['class']) . "' {$addId} href='#'";
        if ($dataPage !== '') {
            $html .= " data-page='{$dataPage}'";
        }
        if (isset($item['module'])) {
            $html .= " data-module='" . htmlspecialchars($item['module']) . "'";
        }
        $html .= '>';
        if (isset($item['icon']) && $item['icon'] !== '') {
            $html .= "<i class='" . htmlspecialchars($item['icon']) . "'></i> ";
        }
        $html .= $item['name'] . '</a>' . "\n";

        return $html;
    }

    public function configureSidebar(array $config)
    {
        foreach ($config as $key => $value) {
            if (array_key_exists($key, $this->sidebarConfig)) {
                $this->sidebarConfig[$key] = $value;
            }
        }
    }

    public function enableJSForMenu($menuId)
    {
        $this->jsEnabledMenus[$menuId] = true;
    }

    public function setSidebarClass($class)
    {
        $this->sidebarClass = $class;
    }

    public function setMenuClass($class)
    {
        $this->menuClass = $class;
    }

    public function addScript($scriptPath, $isInline = false)
    {
        if ($isInline) {
            $this->inlineScripts[] = $scriptPath;
        } else {
            $this->scripts[] = $scriptPath;
        }
    }

    public function addStyle($stylePath)
    {
        $this->styles[] = $stylePath;
    }

    public function addJSVar($varName, $value)
    {
        $this->jsVars[$varName] = $value;
    }

    public function renderJSVars()
    {
        foreach ($this->jsVars as $varName => $value) {
            echo "<script>var $varName = " . json_encode($value) . ";</script>\n";
        }
    }

    public function getDefaultPage()
    {
        return $this->defaultPage;
    }

    public function setSidebarVisibleOnInit($visible)
    {
        $this->sidebarVisibleOnInit = (bool) $visible;
    }

    public function isSidebarVisibleOnInit()
    {
        return $this->sidebarVisibleOnInit;
    }

    private function generateMenuJS($menuId)
    {
        //Wenn toggleButton nicht gesetzt, dann return
        if ($this->menus[$menuId]['toggleButton'] !== true) {
            return;
        }

        $position = $this->menus[$menuId]['position'];

        if ($position === 'right' or $position === 'left') {
            $cssSide = ($position === 'right' ? 'right' : 'left');
            echo "<script>
        $(document).ready(function () {
            var menuSelector = '#{$menuId}';
            var toggleButton = '#toggle-button-{$menuId}';
            var menuVisible = localStorage.getItem('menuVisible{$menuId}') === 'true';
            var contentMargin = $(menuSelector).width();
            var cssSide = '{$cssSide}';

            if (menuVisible) {
                $(menuSelector).css(cssSide, '0');
                $('#pageContent').css('margin-' + cssSide, contentMargin + 'px');
            } else {
                $(menuSelector).css(cssSide, '-' + contentMargin + 'px');
                $('#pageContent').css('margin-' + cssSide, '0px');
            }

            $(toggleButton).click(function () {
            
                menuVisible = !menuVisible;
                if (menuVisible) {
                    $(menuSelector).css(cssSide, '0');
                    $('#pageContent').css('margin-' + cssSide, contentMargin + 'px');
                } else {
                    $(menuSelector).css(cssSide, '-' + contentMargin + 'px');
                    $('#pageContent').css('margin-' + cssSide, '0px');  
                }
                localStorage.setItem('menuVisible{$menuId}', menuVisible);
            });
        });
    </script>";
        } elseif ($position === 'top' or $position === 'bottom') {
            $cssSide = ($position === 'bottom' ? 'bottom' : 'top');
            echo "<script>
        $(document).ready(function () {
        var menuSelector = '#{$menuId}';
        var toggleButton = '#toggle-button-{$menuId}';
        var menuVisible = localStorage.getItem('menuVisible{$menuId}') === 'true';
        var contentMargin = $(menuSelector).height();
        var cssSide = '{$cssSide}';

        if (menuVisible) {
            $(menuSelector).css(cssSide, '0');
            $('#pageContent').css('margin-' + cssSide, contentMargin + 'px');
        } else {
            $(menuSelector).css(cssSide, '-' + contentMargin + 'px');
            $('#pageContent').css('margin-' + cssSide, '0px');
        }

        $(toggleButton).click(function () {
        
            menuVisible = !menuVisible;
            if (menuVisible) {
                $(menuSelector).css(cssSide, '0');
                $('#pageContent').css('margin-' + cssSide, contentMargin + 'px');
            } else {
                $(menuSelector).css(cssSide, '-' + contentMargin + 'px');
                $('#pageContent').css('margin-' + cssSide, '0px');  
            }
            localStorage.setItem('menuVisible{$menuId}', menuVisible);
        });
    });
    </script>";
        }

    }
    private function generateMenuCSS($menuId)
    {
        //Wenn toggleButton nicht gesetzt, dann return
        if ($this->menus[$menuId]['toggleButton'] != true) {
            return;
        }


        if (isset($this->menus[$menuId]) && in_array($this->menus[$menuId]['position'], ['left', 'right'])) {
            $position = $this->menus[$menuId]['position'];
            $cssSide = ($position === 'right' ? 'right' : 'left');
            $togglePosition = $position == 'left' ? 'right: -30px;' : 'left: -26px';

            echo "<style>
            #$menuId {
                $cssSide: -200px;
                transition: $cssSide 0.5s;
            }

            #toggle-button-$menuId {
                background-color: #2185d0;
                color: white;
                border-radius: " . ($position === 'right' ? '5px 0 0 5px' : '0 5px 5px 0') . ";
                width: 25px;
                text-align: center;
                position: absolute;
                $togglePosition;
                top: 60px;
                padding: 10px 0;
                cursor: pointer;
                font-size: 16px;
            }
        </style>";
            //bei top und bottom
        } elseif (isset($this->menus[$menuId]) && in_array($this->menus[$menuId]['position'], ['top', 'bottom'])) {
            $position = $this->menus[$menuId]['position'];
            $cssSide = ($position === 'bottom' ? 'bottom' : 'top');
            $togglePosition = $position == 'top' ? 'bottom: -25px;' : 'top: -25px';

            echo "<style>
            #$menuId {
                $cssSide: 0px;
                transition: $cssSide 0.5s;
            }
            #toggle-button-$menuId {
                background-color: #2185d0;
                color: white;
                border-radius: " . ($position === 'bottom' ? '5px 5px 0 0' : '0 0 5px 5px') . ";
                width: 30px;
                text-align: center;
                position: absolute;
                $togglePosition;
                left: 50%;
                padding: 4px 0;
                cursor: pointer;
                font-size: 16px;
            }
        </style>";
        }
    }

    public function renderSidebarJS()
    {
        echo "<script>
            $(document).ready(function () {
                var sidebar = $('.ui.sidebar').sidebar({
                    context: $('.bottom.segment'),
                    transition: '" . htmlspecialchars($this->sidebarConfig['transition']) . "',
                    dimPage: " . ($this->sidebarConfig['dimPage'] ? 'true' : 'false') . ",
                    direction: '" . htmlspecialchars($this->sidebarConfig['direction']) . "',
                    closable: " . ($this->sidebarConfig['closable'] ? 'true' : 'false') . ",
                    duration: " . (int) $this->sidebarConfig['duration'] . ",
                    easing: '" . htmlspecialchars($this->sidebarConfig['easing']) . "'
                });

                " . ($this->sidebarVisibleOnInit ? "sidebar.sidebar('show');" : "") . "
            });
        </script>";
    }

    private function renderTemplate()
    {
        echo "<!DOCTYPE html>\n";
        echo "<html lang=\"de\">\n";
        echo "<head>\n";
        echo "    <meta charset=\"UTF-8\">\n";
        echo "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
        echo "    <title>" . htmlspecialchars($this->pageTitle) . "</title>\n";
        echo "    <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.css\">\n";
        foreach ($this->styles as $style) {
            echo "    <link rel=\"stylesheet\" href=\"" . htmlspecialchars($style) . "\">\n";
        }
        echo "    <link rel=\"stylesheet\" href=\"../../css/basis.css\">\n";

        // Styles für stabile Sidebar-Breite
        echo "    <style>\n";

        echo "    </style>\n";

        echo "    <script src=\"https://code.jquery.com/jquery-3.6.0.min.js\"></script>\n";
        echo "    <script src=\"https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.js\"></script>\n";

        $this->renderJSVars();
        foreach ($this->scripts as $script) {
            echo "    <script src=\"" . htmlspecialchars($script) . "\"></script>\n";
        }
        foreach ($this->inlineScripts as $inlineScript) {
            echo "    <script>{$inlineScript}</script>\n";
        }

        // JavaScript für stabilere Modal- und Sidebar-Interaktion
        echo "<script>
            $(document).ready(function() {
                let originalBodyPadding;
                let originalSidebarPosition;
                
                // Modal-Handler
                $(document).on('show.modal', function() {
                    originalBodyPadding = $('body').css('padding-right');
                    originalSidebarPosition = $('.ui.sidebar').css('transform');
                    
                    // Verhindere das Springen durch das Scrollbar-Entfernen
                    $('body').css({
                        'padding-right': (window.innerWidth - document.documentElement.clientWidth) + 'px'
                    });
                    
                    // Fixiere die Sidebar-Position
                    if($('.ui.sidebar').hasClass('visible')) {
                        $('.ui.sidebar').css('transform', originalSidebarPosition);
                    }
                });
    
                $(document).on('hide.modal', function() {
                    // Stelle Original-Padding wieder her
                    $('body').css('padding-right', originalBodyPadding);
                    
                    // Stelle Sidebar-Position wieder her
                    if($('.ui.sidebar').hasClass('visible')) {
                        $('.ui.sidebar').css('transform', originalSidebarPosition);
                    }
                });
    
                // Verbesserte Sidebar-Initialisierung
                $('.ui.sidebar').sidebar({
                    context: $('body'),
                    dimPage: false,
                    onVisible: function() {
                        $(this).css('position', 'fixed');
                    },
                    onShow: function() {
                        $('body').addClass('sidebar-visible');
                    },
                    onHide: function() {
                        $('body').removeClass('sidebar-visible');
                    }
                });
            });
        </script>";

        echo "</head>\n";
        echo "<body>\n";

        // Wrapper für bessere Struktur
        echo "<div class=\"ui wrapper\">\n";

        foreach (array_keys($this->menus) as $menuId) {
            $this->renderMenu($menuId);
        }

        echo "    <div class=\"pusher\">\n";
        echo "        <div id=\"pageContent\"></div>\n";

        // Footer
        echo "        <div class=\"ui container footer\" align=\"center\">\n";
        echo $this->footerContent;
        if (!$this->footerContent) {
            echo "           <div class='ui label'> Version " . htmlspecialchars($this->version) . "</div>\n";
        }
        echo "        </div>\n";

        echo "    </div>\n";
        echo "</div>\n";

        echo "    <script src=\"../../js/main.js\"></script>\n";

        foreach (array_keys($this->menus) as $menuId) {
            $this->generateMenuCSS($menuId);
            $this->generateMenuJS($menuId);
        }

        $this->renderSidebarJS();
        echo "    <input type=\"hidden\" id=\"moduleName\" value=\"" . htmlspecialchars($this->moduleName) . "\">";
        echo "    <input type=\"hidden\" id=\"defaultPage\" value=\"" . htmlspecialchars($this->getDefaultPage()) . "\">";
        echo "</body>\n";
        echo "</html>\n";
    }

    public function render()
    {
        if (!$this->checkActiveSession()) {
            // Session is not active, redirect to login page
            header("Location: ../../auth/login.php");

            exit;
        }

        $this->renderTemplate();
    }



}
