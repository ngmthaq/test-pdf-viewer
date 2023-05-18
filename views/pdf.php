<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?php assets('img/favicon.ico') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?php assets('libs/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?php assets('libs/icons/font/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?php assets('css/pdf.css') ?>">
    <title>PDF</title>
</head>

<body>
    <main id="pdf-app">
        <header id="controller">
            <div class="left-controller">
                <div id="sidebar-controller" class="focus-btn">
                    <i class="bi bi-layout-sidebar-inset"></i>
                </div>
                <div id="search-controller" class="focus-btn">
                    <i class="bi bi-search"></i>
                </div>
                <div id="prev-btn" class="hover-btn">
                    <i class="bi bi-chevron-up"></i>
                </div>
                <div id="next-btn" class="hover-btn">
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div id="page-container" class="d-none d-lg-block">
                    <input type="text" id="current-page">
                    <span id="separator">of</span>
                    <span id="total-pages">0</span>
                </div>
            </div>
            <div class="mid-controller">
                <div id="zoom-out" class="hover-btn">
                    <i class="bi bi-dash"></i>
                </div>
                <div id="zoom-separator"></div>
                <div id="zoom-in" class="hover-btn">
                    <i class="bi bi-plus"></i>
                </div>
                <select id="zoom-select-options" class="d-none d-lg-block"></select>
            </div>
            <div class="right-controller"></div>
        </header>
        <section id="pdf-container"></section>
    </main>

    <script src="<?php assets('libs/jquery/index.min.js') ?>"></script>
    <script src="<?php assets('libs/jquery/numeric.plugin.min.js') ?>"></script>
    <script src="<?php assets('libs/bootstrap/js/bootstrap.min.js') ?>"></script>
    <script src="<?php assets('libs/pdfjs/build/pdf.js') ?>"></script>
    <script src="<?php assets('js/pdf.js') ?>"></script>
</body>

</html>
