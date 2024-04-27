<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.css">
    <link rel="stylesheet" href="../../css/basis.css">
    <?php foreach ($this->styles as $style): ?>
        <link rel="stylesheet" href="<?php echo $style; ?>">
    <?php endforeach; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.9.3/semantic.min.js"></script>
    <?php $this->renderJSVars(); ?>
    <?php foreach ($this->scripts as $script): ?>
        <script src="<?php echo $script; ?>"></script>
    <?php endforeach; ?>
</head>
<body>
    <input type="hidden" id="moduleName" value="<?php echo htmlspecialchars($this->moduleName); ?>">
    <input type="hidden" id="defaultPage" value="<?php echo htmlspecialchars($this->getDefaultPage()); ?>">
    <!-- Sidebar Menu -->
    <?php $this->renderMenu(); ?>

    <div class="pusher">
        <!-- Top Menu -->
        <?php $this->renderTopMenu(); ?>

        <!-- Main Content Area -->
        <div class="ui container">
            <div id="pageContent"></div>
        </div>

        <!-- Footer Version Info -->
        <div align="center">
            <div class="ui label basic">Version <?php echo htmlspecialchars($this->version); ?></div>
        </div>
    </div>
    <script src="../../js/main.js"></script>
    <?php echo $this->renderSidebarJS(); ?>
</body>
</html>