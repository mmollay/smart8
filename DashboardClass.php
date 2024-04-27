<?php
include (__DIR__ . "/config.php");

function getUserName()
{
    global $userDetails;
    return $userDetails['firstname'] . " " . $userDetails['secondname'];
}

//Bsp.:
// $dashboard = new Dashboard($title, $db, $userId, $version, $moduleName);

// $dashboard->addMenuItem("Home", "home icon", "home", "", true);
// $dashboard->addMenuItem("Kunden", "users icon", "list_clients");
// $dashboard->addMenuItem("Rechnungen", "file text icon", "list_earnings");
// $dashboard->addMenuItem("Ausgaben", "file text icon", "list_issues");
// $dashboard->addMenuItem("Artikel", "cubes icon", "list_article");
// $dashboard->addTopMenuItem("Einstellungen", "file text icon", "list_clients", "right");
// $dashboard->addJSVar("smart_form_wp", "../../smartform/");
// $dashboard->addScript("../../smartform/js/smart_list.js");
// $dashboard->addScript("../../smartform/js/smart_form.js");

//$dashboard->setSidebarClass('ui left vertical pointing sidebar menu'); //Sidebar klappt ein
//$dashboard->setSidebarClass('ui left vertical pointing menu'); //Menü immer sichtbar 

// $dashboard->setSidebarVisibleOnInit(true);
// $dashboard->setMenuClass('ui large  pointing fixed menu'); // Beispiel für eine andere Menüklasse

class Dashboard
{
    private $menuItems = [];
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

    public function addMenuItem($name, $icon, $page, $isHeader = false, $isDefault = false)
    {
        $this->menuItems[] = [
            'name' => $name,
            'icon' => $icon,
            'page' => $page,
            'isHeader' => $isHeader,
            'isDefault' => $isDefault
        ];
        if ($isDefault) {
            $this->defaultPage = $page;
        }
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

    public function renderMenu()
    {
        echo '<div class="' . htmlspecialchars($this->sidebarClass) . ' dashboard_sidebar">';
        foreach ($this->menuItems as $item) {
            if ($item['isHeader']) {
                echo '<div class="header">' . htmlspecialchars($item['name']) . '</div>';
            } else {
                echo $this->renderMenuItem($item);
            }
        }
        echo '</div>';
    }

    public function addTopMenuItem($name, $icon, $page, $position = "left")
    {
        $this->topMenuItems[] = [
            'name' => $name,
            'icon' => $icon,
            'page' => $page,
            'position' => $position
        ];
    }


    public function renderTopMenu()
    {
        $topMenu = '<div class="' . htmlspecialchars($this->menuClass) . '">';
        $topMenu .= "<a class='item' id='toggleMenu'><i class='sidebar icon'></i>Menü</a>";
        $topMenu .= "<a class='item' id='dashboard'><i class='tachometer alternate icon blue icon'></i></a>";
        $topMenu .= "<div class='item'>" . htmlspecialchars($this->pageTitle) . "</div>";

        foreach ($this->topMenuItems as $item) {
            if ($item['position'] === 'left') {
                $topMenu .= $this->renderMenuItem($item);
            }
        }

        $topMenu .= '<div class="right menu">';

        foreach ($this->topMenuItems as $item) {
            if ($item['position'] === 'right') {
                $topMenu .= $this->renderMenuItem($item);
            }
        }

        $topMenu .= '<div class="item">' . htmlspecialchars(getUserName()) . '</div>';
        $topMenu .= '<a class="item" id="logout"><i class="sign red out icon"></i>Logout</a>';
        $topMenu .= '</div>'; // Close right menu
        $topMenu .= '</div>'; // Close top menu

        echo $topMenu;
    }

    private function renderMenuItem($item)
    {
        $href = ($item['page'][0] == '/' || $item['page'][0] == '.') ? $item['page'] : '#';
        $dataPage = ($item['page'][0] == '/' || $item['page'][0] == '.') ? '' : $item['page'];
        return '<a class="item"  href="' . htmlspecialchars($href) . '" data-page="' . htmlspecialchars($dataPage) . '">'
            . '<i class="' . htmlspecialchars($item['icon']) . ' icon"></i>' . htmlspecialchars($item['name'])
            . '</a>';
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
        $this->renderMenu();
        echo "    <div class=\"pusher\">\n";
        $this->renderTopMenu();
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