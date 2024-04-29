<?php
include (__DIR__ . "/config.php");

//Top-Leister generell
$dashboard = new Dashboard($title, $db, $userId, $version, $moduleName);

$user = $userDetails['firstname'] . " " . $userDetails['secondname'];

$dashboard->addMenu('mainMenu', 'ui top large fixed  menu');
$dashboard->addMenuItem('mainMenu', "main", "", "tachometer alternate icon blue icon", "home", "left");
//$title
$dashboard->addMenuItem('mainMenu', "main", "$title", "building icon", "home", "left");
$dashboard->addMenuItem('mainMenu', "", "Menü", "sidebar icon", "toggleMenu", "left", true);
$dashboard->addMenuItem('mainMenu', "main", "$user", "", "", "right");
$dashboard->addMenuItem('mainMenu', "main", "Abmelden", "sign red out icon", "../../logout.php", "right");

$dashboard->addJSVar("smart_form_wp", "../../../smartform/");
$dashboard->addScript("../../../smartform/js/smart_list.js");
$dashboard->addScript("../../../smartform/js/smart_form.js");

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
    private $menus = [];
    private $topMenuItems = [];
    private $pageTitle;
    private $scripts = [];
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

    private $sidebarConfig = [
        'transition' => 'overlay',
        'dimPage' => true,
        'direction' => 'left',
        'closable' => true,
        'duration' => 500,
        'easing' => 'easeInOutQuad'
    ];

    public function __construct($title, $db, $userId = null, $version = "1.0", $moduleName = "")
    {
        $this->pageTitle = $title;
        $this->db = $db;
        $this->userId = $userId;
        $this->version = $version;
        $this->moduleName = $moduleName;
    }

    public function addMenu($menuId, $menuClass = 'ui menu')
    {
        if (!isset($this->menus[$menuId])) {
            $this->menus[$menuId] = [
                'menuClass' => $menuClass,
                'items' => []
            ];
        }
    }

    //Wenn kein $module angegeben wird, wird data-page zu einer ID, diese kann man dann über jQuery ansprechen (Bsp.: Menü - Sidebar)

    public function addMenuItem($menuId, $module, $name, $icon, $page, $position = 'left', $isDefault = false)
    {
        if (isset($this->menus[$menuId])) {
            $this->menus[$menuId]['items'][] = [
                'module' => $module, // 'main' or 'faktura
                'name' => $name,
                'icon' => $icon,
                'page' => $page,
                'position' => $position, // 'left' or 'right
                'isDefault' => $isDefault,
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

            // Links- und Rechtscontainer vorbereiten
            $leftItems = '';
            $rightItems = '';

            foreach ($menu['items'] as $item) {
                $itemHtml = '';
                if (!$item['page']) {
                    $itemHtml = '<div class="item">' . htmlspecialchars($item['name']) . '</div>';
                } else {
                    $itemHtml = $this->renderMenuItem($item);
                }

                // Position der Menüelemente prüfen und entsprechend zuweisen
                if ($item['position'] === 'right') {
                    $rightItems .= $itemHtml;
                } else {
                    $leftItems .= $itemHtml;
                }
            }

            // Ausgabe der Menüstruktur mit Links und Rechts getrennt
            echo "<div class='{$menuId} {$menuClass}'>\n";
            echo "    $leftItems " . "\n";
            if (isset($rightItems))
                echo "<div class='right menu'>" . $rightItems . "</div>";
            echo "</div>";
        }
    }

    private function renderMenuItem($item)
    {
        // Bestimmen des korrekten href-Attributwerts basierend auf der Seite
        $href = ($item['page'][0] === '/' || $item['page'][0] === '.') ? $item['page'] : '#';

        // Bestimmen des data-page-Attributwerts
        $dataPage = ($item['page'][0] === '/' || $item['page'][0] === '.') ? '' : $item['page'];

        // Überprüfen, ob ein Modul vorhanden ist, und entsprechend das id-Attribut setzen
        $addId = !empty($item['module']) ? "" : "id='" . htmlspecialchars($item['page']) . "'";

        // Erzeugen des HTML-Strings für das Menüelement
        return '<a class="item" ' . $addId . ' href="' . htmlspecialchars($href) . '" data-page="' . htmlspecialchars($dataPage) . '" data-module="' . htmlspecialchars($item['module']) . '">'
            . '<i class="' . htmlspecialchars($item['icon']) . ' icon"></i> ' . htmlspecialchars($item['name'])
            . '</a>';
    }

    public function configureSidebar(array $config)
    {
        foreach ($config as $key => $value) {
            if (array_key_exists($key, $this->sidebarConfig)) {
                $this->sidebarConfig[$key] = $value;
            }
        }
    }

    public function setSidebarClass($class)
    {
        $this->sidebarClass = $class;
    }

    public function setMenuClass($class)
    {
        $this->menuClass = $class;
    }

    public function addScript($scriptPath)
    {
        $this->scripts[] = $scriptPath;
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
        echo "    <link rel=\"stylesheet\" href=\"../../css/basis.css\">\n";
        foreach ($this->styles as $style) {
            echo "    <link rel=\"stylesheet\" href=\"" . htmlspecialchars($style) . "\">\n";
        }
        echo "    <script src=\"https://code.jquery.com/jquery-3.6.0.min.js\"></script>\n";
        echo "    <script src=\"https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.js\"></script>\n";
        $this->renderJSVars();
        foreach ($this->scripts as $script) {
            echo "    <script src=\"" . htmlspecialchars($script) . "\"></script>\n";
        }
        echo "</head>\n";
        echo "<body>\n";
        echo "    <input type=\"hidden\" id=\"moduleName\" value=\"" . htmlspecialchars($this->moduleName) . "\">\n";
        echo "    <input type=\"hidden\" id=\"defaultPage\" value=\"" . htmlspecialchars($this->getDefaultPage()) . "\">\n";
        foreach (array_keys($this->menus) as $menuId) {
            $this->renderMenu($menuId);
        }
        echo "    \n<div class=\"pusher\">\n";
        //$this->renderTopMenu();
        echo "        <div class=\"ui container\">\n";
        echo "            <div id=\"pageContent\"></div>\n";
        echo "        </div>\n";
        echo "        <div align=\"center\">\n";
        echo "            <div class=\"ui label basic\">Version " . htmlspecialchars($this->version) . "</div>\n";
        echo "        </div>\n";
        echo "    </div>\n";
        echo "    <script src=\"../../js/main.js\"></script>\n";
        $this->renderSidebarJS();
        echo "</body>\n";
        echo "</html>\n";
    }

    public function render()
    {
        $this->renderTemplate();
    }
}