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
    <!-- Main application -->
    <main id="pdf-app">
        <header id="controller">
            <div class="left-controller">
                <div id="sidebar-controller" class="focus-btn d-none d-lg-flex">
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
            <div class="right-controller">
                <div id="open-fullscreen-btn" class="hover-btn d-none d-lg-flex">
                    <i class="bi bi-easel2"></i>
                </div>
                <div id="download-btn" class="hover-btn d-none d-lg-flex">
                    <i class="bi bi-download"></i>
                </div>
                <div id="print-btn" class="hover-btn d-none d-lg-flex">
                    <i class="bi bi-printer"></i>
                </div>
                <div id="dropdown-separator"></div>
                <div id="dropdown-btn" class="focus-btn">
                    <i class="bi bi-gear"></i>
                </div>
            </div>
        </header>
        <section id="section-container">
            <aside id="mini-pdf-container" class="close"></aside>
            <div id="pdf-container" class="fullwidth"></div>
        </section>
    </main>

    <!-- Loading element -->
    <div id="loading">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only"></span>
        </div>
    </div>

    <!-- Tools element -->
    <aside id="tools-drawer">
        <div class="tool" id="tools-firstpage">
            <i class="bi bi-arrow-bar-up"></i>
            <span>Go to first page</span>
        </div>
        <div class="tool" id="tools-lastpage">
            <i class="bi bi-arrow-bar-down"></i>
            <span>Go to last page</span>
        </div>
        <div class="tool" id="tools-rotate-right">
            <i class="bi bi-arrow-clockwise"></i>
            <span>Rotate right</span>
        </div>
        <div class="tool" id="tools-rotate-left">
            <i class="bi bi-arrow-counterclockwise"></i>
            <span>Rotate left</span>
        </div>
        <div class="tool" id="tools-presenter">
            <i class="bi bi-easel2"></i>
            <span>Open presenter mode</span>
        </div>
        <div class="tool" id="tools-download">
            <i class="bi bi-download"></i>
            <span>Download</span>
        </div>
        <div class="tool" id="tools-printer">
            <i class="bi bi-printer"></i>
            <span>Print</span>
        </div>
        <div class="tool" id="tools-info" data-bs-toggle="modal" data-bs-target="#document-info">
            <i class="bi bi-info-circle"></i>
            <span>Document information</span>
        </div>
    </aside>

    <!-- Document info modal -->
    <div class="modal fade" id="document-info" tabindex="-1" aria-labelledby="document-info-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content custom-modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fs-5" id="document-info-label">Document information</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm">
                        <tr id="document-name">
                            <th>Document name</th>
                            <td></td>
                        </tr>
                        <tr id="document-size">
                            <th>Filesize</th>
                            <td></td>
                        </tr>
                        <tr id="document-tile">
                            <th>Title</th>
                            <td></td>
                        </tr>
                        <tr id="document-author">
                            <th>Author</th>
                            <td></td>
                        </tr>
                        <tr id="document-category">
                            <th>Cateogory</th>
                            <td></td>
                        </tr>
                        <tr id="document-keywords">
                            <th>Keywords</th>
                            <td></td>
                        </tr>
                        <tr id="document-created-at">
                            <th>Create at</th>
                            <td></td>
                        </tr>
                        <tr id="document-updated-at">
                            <th>Updated at</th>
                            <td></td>
                        </tr>
                        <tr id="document-pdf-viewer-version">
                            <th>PDF viewer version</th>
                            <td></td>
                        </tr>
                        <tr id="document-pdf-version">
                            <th>PDF version</th>
                            <td></td>
                        </tr>
                        <tr id="document-total-pages">
                            <th>Total pages</th>
                            <td></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php assets('libs/jquery/index.min.js') ?>"></script>
    <script src="<?php assets('libs/jquery/numeric.plugin.min.js') ?>"></script>
    <script src="<?php assets('libs/jquery/print.plugin.js') ?>"></script>
    <script src="<?php assets('libs/bootstrap/js/bootstrap.min.js') ?>"></script>
    <script src="<?php assets('libs/pdfjs/build/pdf.js') ?>"></script>
    <script src="<?php assets('libs/crypto/crypto-js.js') ?>"></script>
    <script src="<?php assets('js/hash.js') ?>"></script>
    <script src="<?php assets('js/pdf.js') ?>"></script>

    <?php include(ROOT_DIR . "\\views\\script.php") ?>

</body>

</html>
