<script>
    $(document).ready(function() {
        /** PDF.js library */
        const PDF_WORKER = "./vendors/libs/pdfjs/build/pdf.worker.js";
        const PDF_PATH_DEFAULT = "./vendors/static/pdf.pdf";
        const PDFJS = window["pdfjs-dist/build/pdf"];
        PDFJS.GlobalWorkerOptions.workerSrc = PDF_WORKER;

        /** Template string */
        const ZOOM_OPTION_PSUEDO = "zoom-option-psuedo";
        const CANVAS_ID_TEMPLATE = "pdf-viewer-:id";
        const MINI_CANVAS_ID_TEMPLATE = "mini-pdf-viewer-:id";
        const MINI_PDF_WRAPPER_CLASS = "mini-pdf-wrapper";
        const CONTAINER_ID_TEMPLATE = "pdf-container-:id";
        const CANVAS_CLASS = "canvas-layer";
        const MINI_CANVAS_CLASS = "mini-canvas-layer";
        const CANVAS_WRAPPER_CLASS = "canvas-wrapper";
        const CANVAS_MARGIN = 8;
        const CANVAS_WRAPPER_ID_TEMPLATE = "pdf-viewer-wrapper-:id";
        const MINI_CANVAS_WRAPPER_ID_TEMPLATE = "mini-pdf-viewer-wrapper-:id";

        /** CSS Unit */
        const CSS_UNIT = PDFJS.PixelsPerInch.PDF_TO_CSS_UNITS;

        /** Locale */
        const lang = window.navigator.language;
        const locales = window.navigator.languages.map((l) => l);
        const langUrlTemplate = "./vendors/libs/pdfjs/web/locale/:lang/viewer.properties";

        /** Zoom Levels */
        const ZOOM_LEVELS = {
            auto: {
                title: "Automatic Zoom",
                value: "auto",
                id: "zoom-level-auto",
            },
            actual: {
                title: "Actual Size",
                value: "actual",
                id: "zoom-level-actual",
            },
            fit: {
                title: "Page Fit",
                value: "fit",
                id: "zoom-level-fit",
            },
            width: {
                title: "Page Width",
                value: "width",
                id: "zoom-level-width",
            },
            p50: {
                title: "50%",
                value: 0.5,
            },
            p75: {
                title: "75%",
                value: 0.75,
            },
            p100: {
                title: "100%",
                value: 1,
            },
            p125: {
                title: "125%",
                value: 1.25,
            },
            p150: {
                title: "150%",
                value: 1.5,
            },
            p200: {
                title: "200%",
                value: 2,
            },
            p300: {
                title: "300%",
                value: 3,
            },
            p400: {
                title: "400%",
                value: 4,
            },
        };

        const ENABLED_MAX_WIDTH = [
            ZOOM_LEVELS.auto.value,
            ZOOM_LEVELS.fit.value,
            ZOOM_LEVELS.width.value,
        ];

        /** View modes */
        const VIEW_MODES = {
            vertical: {
                title: "Vertical Scrolling",
                value: "vertical",
            },
            horizontal: {
                title: "Horizontal Scrolling",
                value: "horizontal",
            },
            wrapped: {
                title: "Wrapped Scrolling",
                value: "wrapped",
            },
        };

        const restrictions = <?php echo $restrictions ?>;

        let pages = [];
        let miniPages = [];
        let langs = {};
        let initialState = {
            pdfDoc: null,
            currentPage: 0,
            pageCount: 0,
            zoom: CSS_UNIT,
            zoomString: ZOOM_LEVELS.auto.value,
            scaleControl: 1,
            scaleStep: 0.25,
            minScale: 0.25,
            maxScale: 5,
            viewMode: VIEW_MODES.vertical.value,
            curentRotate: 0,
            rotate: 90,
            viewport: {
                width: 0,
                height: 0
            },
            actualViewport: {
                width: 0,
                height: 0
            },
            isFullscreen: false,
            isOpenSidebar: false,
            isRenderComplete: false,
            info: {
                name: "document.pdf",
                size: 0,
                title: "",
                author: "",
                category: "",
                keyword: "",
                createdAt: "",
                updatedAt: "",
                version: "0.0.0",
                pdfViewerVersion: "0.0.0",
                pages: 0
            }
        };

        /** Navigation elements */
        const loading = $(document).find("#loading");
        const sidebarButton = $(document).find("#sidebar-controller");
        const searchButton = $(document).find("#search-controller");
        const prevButton = $(document).find("#prev-btn");
        const nextButton = $(document).find("#next-btn");
        const currentPageInput = $(document).find("#current-page");
        const totalPagesElement = $(document).find("#total-pages");
        const zoomOutButton = $(document).find("#zoom-out");
        const zoomInButton = $(document).find("#zoom-in");
        const zoomDropdown = $(document).find("#zoom-select-options");
        const pdfContainer = $(document).find("#pdf-container");
        const miniPdfContainer = $(document).find("#mini-pdf-container");
        const openFullScreenBtn = $(document).find("#open-fullscreen-btn");
        const printButton = $(document).find("#print-btn");
        const downloadButton = $(document).find("#download-btn");
        const toolButton = $(document).find("#dropdown-btn");
        const toolDrawer = $(document).find("#tools-drawer");
        const toolModal = $(document).find("#tools-modal");
        const toolFirstPage = $(document).find("#tools-firstpage");
        const toolLastPage = $(document).find("#tools-lastpage");
        const toolRotateRight = $(document).find("#tools-rotate-right");
        const toolRotateLeft = $(document).find("#tools-rotate-left");
        const toolPresenter = $(document).find("#tools-presenter");
        const toolDownload = $(document).find("#tools-download");
        const toolPrint = $(document).find("#tools-printer");
        const toolDocInfo = $(document).find("#tools-info");
        const docInfoName = $(document).find("#document-name");
        const docInfoSize = $(document).find("#document-size");
        const docInfoTitle = $(document).find("#document-title");
        const docInfoAuthor = $(document).find("#document-author");
        const docInfoCategory = $(document).find("#document-category");
        const docInfoKeywords = $(document).find("#document-keywords");
        const docInfoCreatedAt = $(document).find("#document-created-at");
        const docInfoUpdatedAt = $(document).find("#document-updated-at");
        const docInfoPdfViewerVersion = $(document).find("#document-pdf-viewer-version");
        const docInfoPdfVersion = $(document).find("#document-pdf-version");
        const docInfoTotalPages = $(document).find("#document-total-pages");
        const docInfoHeading = $(document).find("#document-info-label");
        const overlay = $(document).find("#overlay");

        $("html").attr("lang", lang);

        pdfContainer.addClass(initialState.viewMode);
        currentPageInput.numeric();
        prevButton.addClass("disabled");
        searchButton.addClass("disabled");

        /** Click prev button */
        prevButton.click(function() {
            if (initialState.currentPage > 1) {
                initialState.currentPage -= 1;
                let id = CANVAS_WRAPPER_ID_TEMPLATE.replace(":id", initialState.currentPage);
                currentPageInput.val(initialState.currentPage);
                let el = document.getElementById(id);
                if (el) {
                    pdfContainer[0].scrollTo({
                        top: el.offsetTop
                    });
                }
                let miniid = MINI_CANVAS_WRAPPER_ID_TEMPLATE.replace(":id", initialState.currentPage);
                let miniel = document.getElementById(miniid);
                if (miniel) {
                    miniPdfContainer[0].scrollTo({
                        top: miniel.offsetTop
                    });
                }
                $(`.${MINI_PDF_WRAPPER_CLASS}`).removeClass("active");
                $(`.${MINI_PDF_WRAPPER_CLASS}[data-id="${initialState.currentPage}"]`).addClass("active");
                nextButton.removeClass("disabled");
                initialState.currentPage === 1 && $(this).addClass("disabled");
            }
        });

        /** Click next button */
        nextButton.click(function() {
            if (initialState.currentPage < initialState.pageCount) {
                initialState.currentPage += 1;
                let id = CANVAS_WRAPPER_ID_TEMPLATE.replace(":id", initialState.currentPage);
                currentPageInput.val(initialState.currentPage);
                let el = document.getElementById(id);
                if (el) {
                    pdfContainer[0].scrollTo({
                        top: el.offsetTop
                    });
                }
                let miniid = MINI_CANVAS_WRAPPER_ID_TEMPLATE.replace(":id", initialState.currentPage);
                let miniel = document.getElementById(miniid);
                if (miniel) {
                    miniPdfContainer[0].scrollTo({
                        top: miniel.offsetTop
                    });
                }
                $(`.${MINI_PDF_WRAPPER_CLASS}`).removeClass("active");
                $(`.${MINI_PDF_WRAPPER_CLASS}[data-id="${initialState.currentPage}"]`).addClass("active");
                prevButton.removeClass("disabled");
                initialState.currentPage === initialState.pageCount &&
                    $(this).addClass("disabled");
            }
        });

        /** Focus page input */
        currentPageInput.focus(function() {
            $(this).select();
        });

        /** Change page input */
        currentPageInput.change(function(e) {
            let val = parseInt(e.target.value);
            if (!Number.isNaN(val)) {
                if (val <= 0) val = 1;
                if (val > initialState.pageCount) val = initialState.pageCount;
                initialState.currentPage = val;
                prevButton.removeClass("disabled");
                nextButton.removeClass("disabled");
                initialState.currentPage === 1 && prevButton.addClass("disabled");
                initialState.currentPage === initialState.pageCount &&
                    nextButton.addClass("disabled");
                let id = CANVAS_WRAPPER_ID_TEMPLATE.replace(":id", initialState.currentPage);
                let el = document.getElementById(id);
                $(this).val(val);
                pdfContainer[0].scrollTo({
                    top: el.offsetTop
                });
                let miniid = MINI_CANVAS_WRAPPER_ID_TEMPLATE.replace(":id", initialState.currentPage);
                let miniel = document.getElementById(miniid);
                miniPdfContainer[0].scrollTo({
                    top: miniel.offsetTop
                });
                $(`.${MINI_PDF_WRAPPER_CLASS}`).removeClass("active");
                $(`.${MINI_PDF_WRAPPER_CLASS}[data-id="${initialState.currentPage}"]`).addClass("active");
            }
        });

        /** Dropdown changed */
        zoomDropdown.change(function() {
            let _this = this;
            loading.css("display", "flex");
            initialState.zoom = convertZoomNumber($(this).val());
            initialState.zoomString = $(this).val();
            zoomInButton.removeClass("disabled");
            zoomOutButton.removeClass("disabled");
            initialState.scaleControl = 1;
            resize(_this);
        });

        /** Click zoom out */
        zoomOutButton.click(function() {
            if (initialState.scaleControl > initialState.minScale) {
                let _this = this;
                loading.css("display", "flex");
                zoomInButton.removeClass("disabled");
                initialState.scaleControl -= initialState.scaleStep;
                initialState.zoom = initialState.scaleControl;
                initialState.zoomString = initialState.scaleControl;
                $("#" + ZOOM_OPTION_PSUEDO).val(initialState.scaleControl);
                $("#" + ZOOM_OPTION_PSUEDO).text(initialState.scaleControl * 100 + "%");
                zoomDropdown.val(initialState.scaleControl);
                initialState.scaleControl === initialState.minScale && $(this).addClass("disabled");
                resize(_this);
            }
        });

        /** Click zoom in */
        zoomInButton.click(function() {
            if (initialState.scaleControl < initialState.maxScale) {
                let _this = this;
                loading.css("display", "flex");
                zoomOutButton.removeClass("disabled");
                initialState.scaleControl += initialState.scaleStep;
                initialState.zoom = initialState.scaleControl;
                initialState.zoomString = initialState.scaleControl;
                $("#" + ZOOM_OPTION_PSUEDO).val(initialState.scaleControl);
                $("#" + ZOOM_OPTION_PSUEDO).text(initialState.scaleControl * 100 + "%");
                zoomDropdown.val(initialState.scaleControl);
                initialState.scaleControl === initialState.maxScale && $(this).addClass("disabled");
                resize(_this);
            }
        });

        /** Open fullscreen */
        openFullScreenBtn.click(function() {
            const elem = pdfContainer[0];
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.webkitRequestFullscreen) {
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();
            }
        });

        /** Open fullscreen */
        toolPresenter.click(function() {
            const elem = pdfContainer[0];
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.webkitRequestFullscreen) {
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();
            }
        });

        /** Container fullscreen event */
        pdfContainer.on("fullscreenchange", function(e) {
            if (initialState.isFullscreen) {
                initialState.isFullscreen = false;
                pdfContainer.removeClass("fullscreen");
            } else {
                initialState.isFullscreen = true;
                pdfContainer.addClass("fullscreen");
            }
        });

        /** Print configs */
        const printConfigs = {
            beforePrintEvent: () => {
                loading.css("display", "none");
            },
            beforePrint: () => {
                loading.css("display", "flex");
            },
            afterPrint: () => {}
        }

        /** Click print */
        printButton.click(function() {
            pdfContainer.printThis(printConfigs);
        });

        /** Click print */
        toolPrint.click(function() {
            pdfContainer.printThis(printConfigs);
        });

        /** Ctrl + P */
        document.addEventListener("keydown", function(event) {
            if ((event.ctrlKey || event.metaKey) && event.keyCode === 80) {
                event.preventDefault();
                event.stopImmediatePropagation();
                pdfContainer.printThis(printConfigs);
            }
        });

        /** Toggle open sidebar */
        sidebarButton.click(function(e) {
            pdfContainer.toggleClass("fullwidth");
            miniPdfContainer.toggleClass("close");
            $(this).toggleClass("active");
            initialState.isOpenSidebar = !initialState.isOpenSidebar;
        });

        /** Click tool button */
        toolButton.click(function() {
            toolButton.toggleClass("active");
            toolDrawer.toggleClass("open");
            toolModal.toggleClass("open");
        });

        /** Go to first page */
        toolFirstPage.click(function() {
            initialState.currentPage = 1;
            currentPageInput.val(initialState.currentPage);
            let canvas = document.getElementById(CANVAS_ID_TEMPLATE.replace(":id", initialState.currentPage));
            let miniCanvas = document.getElementById(MINI_CANVAS_ID_TEMPLATE.replace(":id", initialState.currentPage));
            pdfContainer[0].scrollTo({
                top: canvas.offsetTop
            });
            miniPdfContainer[0].scrollTo({
                top: miniCanvas.offsetTop
            });
        });

        /** Go to last page */
        toolLastPage.click(function() {
            initialState.currentPage = initialState.pageCount;
            currentPageInput.val(initialState.currentPage);
            let canvas = document.getElementById(CANVAS_ID_TEMPLATE.replace(":id", initialState.currentPage));
            let miniCanvas = document.getElementById(MINI_CANVAS_ID_TEMPLATE.replace(":id", initialState.currentPage));
            pdfContainer[0].scrollTo({
                top: canvas.offsetTop
            });
            miniPdfContainer[0].scrollTo({
                top: miniCanvas.offsetTop
            });
        });

        /** Rotate right */
        toolRotateRight.click(function() {
            loading.css("display", "flex");
            initialState.curentRotate += initialState.rotate;
            miniPdfContainer.get(0).scroll({
                top: 0
            });
            rotate();
        });

        /** Rotate left */
        toolRotateLeft.click(function() {
            loading.css("display", "flex");
            initialState.curentRotate -= initialState.rotate;
            miniPdfContainer.get(0).scroll({
                top: 0
            });
            rotate();
        });

        /** Open document info modal */
        toolDocInfo.click(function() {
            docInfoName.find("td").text(initialState.info.name);
            docInfoSize.find("td").text(initialState.info.size + " byte");
            docInfoTitle.find("td").text(initialState.info.title);
            docInfoAuthor.find("td").text(initialState.info.author);
            docInfoCategory.find("td").text(initialState.info.category);
            docInfoKeywords.find("td").text(initialState.info.keyword);
            docInfoCreatedAt.find("td").text(initialState.info.createdAt);
            docInfoUpdatedAt.find("td").text(initialState.info.updatedAt);
            docInfoPdfViewerVersion.find("td").text(initialState.info.pdfViewerVersion);
            docInfoPdfVersion.find("td").text(initialState.info.version);
            docInfoTotalPages.find("td").text(initialState.info.pages);
        });

        /** Window focus */
        window.addEventListener("focus", function(e) {
            overlay.css("display", "none");
        });

        /** Window blur */
        window.addEventListener("blur", function(e) {
            overlay.css("display", "flex");
        });

        /** PDF Container scroll */
        pdfContainer.on("scroll", debounce(function(e) {
            $(`.${CANVAS_CLASS}:not(.${MINI_CANVAS_CLASS})`).each((index, elm) => {
                let visible = isVisible($("#" + elm.id));
            });
        }));

        /** Initial pdf.js */
        function render(path, ppw = "") {
            const LOADING_TASK = PDFJS.getDocument(path);

            if (ppw) {
                LOADING_TASK.onPassword = function(callback, reason) {
                    callback(d(ppw, restrictions.key));
                }
            }

            LOADING_TASK.promise
                .then((doc) => {
                    initialState.pdfDoc = doc;
                    initialState.pageCount = initialState.pdfDoc.numPages;
                    doc.getMetadata().then((metadata) => {
                        initialState.info.size = metadata.contentLength;
                        initialState.info.pages = initialState.pdfDoc.numPages;
                        initialState.info.author = metadata.info.Creator || "";
                        initialState.info.version = metadata.info.PDFFormatVersion || "";
                        initialState.info.pdfViewerVersion = PDFJS.version;
                    });
                    if (initialState.pageCount > 0) {
                        initialState.currentPage = 1;
                        currentPageInput.val(1);
                        totalPagesElement.text(initialState.pageCount);
                        initPages();
                        sidebarButton.attr("title", langs.toggle_sidebar_label);
                        searchButton.attr("title", langs["find_input.placeholder"]);
                        prevButton.attr("title", langs["previous.title"]);
                        nextButton.attr("title", langs["next.title"]);
                        zoomOutButton.attr("title", langs.zoom_out_label);
                        zoomInButton.attr("title", langs.zoom_in_label);
                        $("#" + ZOOM_LEVELS.auto.id).text(langs.page_scale_auto);
                        $("#" + ZOOM_LEVELS.actual.id).text(langs.page_scale_actual);
                        $("#" + ZOOM_LEVELS.fit.id).text(langs.page_scale_fit);
                        $("#" + ZOOM_LEVELS.width.id).text(langs.page_scale_width);
                        $("#separator").text(langs.of_pages.replace("{{pagesCount}}", ""));
                        openFullScreenBtn.attr("title", langs.presentation_mode_label);
                        downloadButton.attr("title", langs.download_label);
                        printButton.attr("title", langs.print_label);
                        toolButton.attr("title", langs.tools_label);
                        toolFirstPage.find("span").text(langs.first_page_label);
                        toolLastPage.find("span").text(langs.last_page_label);
                        toolRotateRight.find("span").text(langs.page_rotate_cw_label);
                        toolRotateLeft.find("span").text(langs.page_rotate_ccw_label);
                        toolPresenter.find("span").text(langs.presentation_mode_label);
                        toolDownload.find("span").text(langs.download_label);
                        toolPrint.find("span").text(langs.print_label);
                        toolDocInfo.find("span").text(langs.document_properties_label);
                        docInfoName.find("th").text(langs.document_properties_file_name);
                        docInfoSize.find("th").text(langs.document_properties_file_size);
                        docInfoTitle.find("th").text(langs.document_properties_title);
                        docInfoAuthor.find("th").text(langs.document_properties_author);
                        docInfoCategory.find("th").text(langs.document_properties_subject);
                        docInfoKeywords.find("th").text(langs.document_properties_keywords);
                        docInfoCreatedAt.find("th").text(langs.document_properties_creation_date);
                        docInfoUpdatedAt.find("th").text(langs.document_properties_modification_date);
                        docInfoPdfViewerVersion.find("th").text(langs.document_properties_producer);
                        docInfoPdfVersion.find("th").text(langs.document_properties_version);
                        docInfoTotalPages.find("th").text(langs.document_properties_page_count);
                        docInfoHeading.text(langs.document_properties_label);
                    }
                })
                .catch((err) => {
                    console.error(err);
                });
        }

        /** Init pdf pages */
        function initPages() {
            const lastOption = `<option style="display: none;" value="" id="${ZOOM_OPTION_PSUEDO}"></option>`;

            Object.values(ZOOM_LEVELS).forEach((level, index) => {
                const id = level.id || "zoom-level-" + index;
                const option = `<option value="${level.value}" id="${id}">${level.title}</option>`;
                zoomDropdown.html(zoomDropdown.html() + option);
            });

            zoomDropdown.html(zoomDropdown.html() + lastOption);

            for (let i = 1; i <= initialState.pageCount; i++) {
                const canvasDiv = $("<div></div>");
                const canvasWrapper = canvasDiv.get(0);
                const mainCanvas = $("<canvas></canvas>");
                const mainCanvasElement = mainCanvas.get(0);
                mainCanvas.addClass(CANVAS_CLASS);
                mainCanvasElement.height = 900;
                mainCanvasElement.width = 625;
                mainCanvasElement.style.backgroundColor = "#fff";
                mainCanvasElement.style.marginTop = CANVAS_MARGIN + "px";
                mainCanvasElement.style.marginBottom = CANVAS_MARGIN + "px";
                mainCanvasElement.id = CANVAS_ID_TEMPLATE.replace(":id", i);
                canvasWrapper.id = CANVAS_WRAPPER_ID_TEMPLATE.replace(":id", i);
                canvasWrapper.classList.add(CANVAS_WRAPPER_CLASS);
                canvasWrapper.append(mainCanvasElement);
                canvasWrapper.addEventListener("mouseover", function() {
                    initialState.currentPage = i;
                    currentPageInput.val(initialState.currentPage);
                    prevButton.removeClass("disabled");
                    nextButton.removeClass("disabled");
                    initialState.currentPage === 1 && prevButton.addClass("disabled");
                    initialState.currentPage === initialState.pageCount &&
                        nextButton.addClass("disabled");
                    $(`.${MINI_PDF_WRAPPER_CLASS}`).removeClass("active");
                    $(`.${MINI_PDF_WRAPPER_CLASS}[data-id="${initialState.currentPage}"]`).addClass("active");
                });

                const miniCanvasDiv = $("<div></div>");
                const miniCanvasWrapper = miniCanvasDiv.get(0);
                const miniCanvas = $("<canvas></canvas>");
                const miniCanvasElement = miniCanvas.get(0);
                miniCanvas.addClass(CANVAS_CLASS);
                miniCanvas.addClass(MINI_CANVAS_CLASS);
                miniCanvasElement.height = 210;
                miniCanvasElement.width = 150;
                miniCanvasElement.style.backgroundColor = "#fff";
                miniCanvasElement.style.marginTop = CANVAS_MARGIN + "px";
                miniCanvasElement.style.marginBottom = CANVAS_MARGIN + "px";
                miniCanvasElement.id = MINI_CANVAS_ID_TEMPLATE.replace(":id", i);
                miniCanvasWrapper.append(miniCanvasElement);
                miniCanvasWrapper.id = MINI_CANVAS_WRAPPER_ID_TEMPLATE.replace(":id", i);
                miniCanvasWrapper.setAttribute("data-id", i);
                miniCanvasWrapper.className = "d-flex justify-content-center align-items-center " + MINI_PDF_WRAPPER_CLASS;
                if (i === 1) miniCanvasWrapper.className += " active";
                miniCanvasWrapper.classList.add(CANVAS_WRAPPER_CLASS);
                miniCanvasWrapper.setAttribute("title", langs.page_landmark.replace("{{page}}", i));
                miniCanvasWrapper.onclick = function(e) {
                    initialState.currentPage = i;
                    $(`.${MINI_PDF_WRAPPER_CLASS}`).removeClass("active");
                    $(`.${MINI_PDF_WRAPPER_CLASS}[data-id="${initialState.currentPage}"]`).addClass("active");
                    currentPageInput.val(i);
                    let el = document.getElementById(CANVAS_ID_TEMPLATE.replace(":id", i));
                    pdfContainer.get(0).scrollTo({
                        top: el.offsetTop
                    });
                };

                pdfContainer.append(canvasWrapper);
                miniPdfContainer.append(miniCanvasWrapper);
            }

            renderCanvas();
        }

        /** Convert text to zoom number */
        function convertZoomNumber(level) {
            switch (level) {
                case ZOOM_LEVELS.auto.value:
                    return CSS_UNIT;

                case ZOOM_LEVELS.actual.value:
                    return 1;

                case ZOOM_LEVELS.width.value:
                    return (
                        pdfContainer.width() /
                        (initialState.actualViewport.width + CANVAS_MARGIN * 2)
                    );

                case ZOOM_LEVELS.fit.value:
                    const h =
                        pdfContainer.height() /
                        (initialState.actualViewport.height + CANVAS_MARGIN * 2);
                    const w =
                        pdfContainer.width() /
                        (initialState.actualViewport.width + CANVAS_MARGIN * 2);
                    return Math.min(h, w);

                default:
                    return Number.isNaN(parseFloat(level)) ? CSS_UNIT : parseFloat(level);
            }
        }

        /** Get i18n */
        function getI18n() {
            return new Promise((resolve, reject) => {
                let locale = locales.shift();
                $.ajax({
                    type: "get",
                    url: langUrlTemplate.replace(":lang", locale),
                    dataType: "html",
                    success: function(response) {
                        let output = {};
                        let array = response.split("\n");
                        let filterArray = array.filter(
                            (item) => item !== "" && item.charAt(0) !== "#"
                        );
                        filterArray.forEach((item) => {
                            let [key, value] = item.split("=");
                            output[key] = value;
                        });
                        resolve(output);
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 404) {
                            resolve(getI18n());
                        }
                    },
                });
            });
        }

        /** Request get PDF */
        function requestPDF(path, ppw = "") {
            loading.css("display", "flex");
            $.ajax({
                type: "get",
                url: path,
                data: {
                    rqt: "pdf"
                },
                xhr: function() {
                    let xhr = new XMLHttpRequest();
                    xhr.responseType = "blob";
                    return xhr;
                },
                success: function(response) {
                    loading.css("display", "none");
                    let path = PDF_PATH_DEFAULT;
                    if (response.size > 0) {
                        path = window.URL.createObjectURL(response);
                    }
                    render(path, ppw);
                    downloadButton.click(function() {
                        downloadFile(path);
                    });
                    toolDownload.click(function() {
                        downloadFile(path);
                    });
                },
                error: function(xhr, status, error) {
                    loading.css("display", "none");
                    let path = PDF_PATH_DEFAULT;
                    alert(langs.loading_error);
                    render(path, ppw);
                    downloadButton.click(function() {
                        downloadFile(path);
                    });
                    toolDownload.click(function() {
                        downloadFile(path);
                    });
                }
            });
        }

        /** Resize */
        function resize(_this, index = 0) {
            const {
                canvas,
                page
            } = pages[index];
            const elements = canvas;
            const ctx = elements.getContext("2d");
            const viewport = page.getViewport({
                scale: initialState.zoom,
                rotation: initialState.curentRotate,
            });
            const outputScale = window.devicePixelRatio || 1;
            const renderCtx = {
                canvasContext: ctx,
                viewport: viewport,
            };
            elements.height = viewport.height;
            elements.width = viewport.width;
            if (ENABLED_MAX_WIDTH.includes($(_this).val())) {
                elements.style.maxWidth = "100vw";
            } else {
                elements.style.maxWidth = "unset";
            }
            page.render(renderCtx).promise.then(() => {
                let nextIndex = index + 1;
                if (nextIndex < initialState.pageCount) {
                    resize(_this, nextIndex);
                } else {
                    loading.css("display", "none");
                }
            });
        }

        /** Rotate */
        function rotate(index = 0) {
            const {
                canvas,
                page
            } = pages[index];

            const elements = canvas;
            const ctx = elements.getContext("2d");
            const viewport = page.getViewport({
                scale: initialState.zoom,
                rotation: initialState.curentRotate,
            });

            const outputScale = window.devicePixelRatio || 1;
            const renderCtx = {
                canvasContext: ctx,
                viewport: viewport,
            };

            elements.height = viewport.height;
            elements.width = viewport.width;

            if (ENABLED_MAX_WIDTH.includes(initialState.zoomString)) {
                elements.style.maxWidth = "100vw";
            } else {
                elements.style.maxWidth = "unset";
            }

            rotatePreviewPage(index);

            page.render(renderCtx).promise.then(() => {
                let nextIndex = index + 1;
                if (nextIndex < initialState.pageCount) {
                    rotate(nextIndex);
                } else {
                    loading.css("display", "none");
                }
            });
        }

        /** Rotate preview pages */
        function rotatePreviewPage(index = 0) {
            const {
                canvas,
                page
            } = miniPages[index];

            const elements = canvas;
            const ctx = elements.getContext("2d");

            const viewport = page.getViewport({
                scale: initialState.zoom,
                rotation: initialState.curentRotate,
            });

            const outputScale = window.devicePixelRatio || 1;

            const renderCtx = {
                canvasContext: ctx,
                viewport: viewport,
            };

            elements.height = viewport.height;
            elements.width = viewport.width;

            page.render(renderCtx);
        }

        /** Download file */
        function downloadFile(path, fileName = initialState.info.name) {
            const link = document.createElement('a');
            link.href = path;
            link.download = fileName;
            document.body.append(link);
            link.click();
            link.remove();
        }

        /** r o w f e n c e d e c r y p t */
        function d(input, key, padding = "=") {
            if (typeof input === "string") {
                let textLength = input.length;
                let arrayText = input.split("");
                let collumns = Math.round(textLength / key);
                let rows = [];
                let plainRows = [];
                for (let i = 0; i < key; i++) {
                    for (let j = 0; j < collumns; j++) {
                        let pos = i * collumns + j;
                        if (!rows[i]) rows[i] = [];
                        let row = rows[i];
                        row.push(arrayText[pos]);
                    }
                }
                for (let p = 0; p < collumns; p++) {
                    plainRows[p] = rows.map(row => {
                        return row[p];
                    });
                }
                let output = plainRows.map(row => row.join("")).join("");
                return output.replace(/=/, "");
            }
        }

        /** a e s c b c d e c r y p t */
        function daes(data, iv) {
            const c = document.cookie.split(";");
            const c0 = c.reduce((obj, c1) => {
                const c2 = c1.trim();
                const c3 = c2.split("=");
                const c4 = c3[0];
                const c5 = c3[1];
                obj[c4] = c5;
                return obj;
            }, {});
            const key = c0["PHPSESSID"];
            const config = {
                iv: CryptoJS.enc.Utf8.parse(iv),
                mode: CryptoJS.mode.CBC
            };
            const utf8key = CryptoJS.enc.Utf8.parse(key);
            const raw = CryptoJS.AES.decrypt(data, utf8key, config);
            return raw.toString(CryptoJS.enc.Utf8);
        }

        /** Render canvas */
        function renderCanvas(i = 1) {
            initialState.pdfDoc.getPage(i).then((page) => {
                const wrapper = document.getElementById(CANVAS_WRAPPER_ID_TEMPLATE.replace(":id", i));
                const elements = document.getElementById(CANVAS_ID_TEMPLATE.replace(":id", i));
                const ctx = elements.getContext("2d");

                const actualViewport = page.getViewport({
                    scale: 1,
                    rotation: initialState.curentRotate,
                });

                const viewport = page.getViewport({
                    scale: initialState.zoom,
                    rotation: initialState.curentRotate,
                });

                const outputScale = window.devicePixelRatio || 1;

                const renderCtx = {
                    canvasContext: ctx,
                    viewport: viewport,
                };

                initialState.viewport = viewport;
                initialState.actualViewport = actualViewport;
                elements.height = viewport.height;
                elements.width = viewport.width;
                elements.style.backgroundColor = "transparent";

                pages.push({
                    canvas: elements,
                    page: page
                });

                renderMiniCanvas(i);
                page.render(renderCtx).promise.then(() => {
                    let nextPage = i + 1;
                    if (nextPage <= initialState.pageCount) {
                        renderCanvas(nextPage);
                    } else {
                        initialState.isRenderComplete = true;
                    }
                });
            });
        }

        /** Render mini canvas */
        function renderMiniCanvas(i = 1) {
            return new Promise((resolve, reject) => {
                initialState.pdfDoc.getPage(i).then((page) => {
                    const wrapper = document.getElementById(MINI_CANVAS_WRAPPER_ID_TEMPLATE.replace(":id", i));
                    const elements = document.getElementById(MINI_CANVAS_ID_TEMPLATE.replace(":id", i));
                    const ctx = elements.getContext("2d");

                    const viewport = page.getViewport({
                        scale: 1,
                        rotation: initialState.curentRotate,
                    });

                    const outputScale = window.devicePixelRatio || 1;

                    const renderCtx = {
                        canvasContext: ctx,
                        viewport: viewport,
                    };

                    elements.height = viewport.height;
                    elements.width = viewport.width;

                    miniPages.push({
                        canvas: elements,
                        page: page
                    });

                    page.render(renderCtx);
                });
            });
        }

        /** Check element visible in window */
        function isVisible(jqueryElement, evalType) {
            evalType = evalType || "visible";

            let vpH = $(window).height();
            let st = $(window).scrollTop();
            let y = jqueryElement.offset().top;
            let elementHeight = jqueryElement.height();

            if (evalType === "visible") return y < (vpH + st) && y > (st - elementHeight);
            if (evalType === "above") return y < (vpH + st);
            return false;
        }

        /** Debounce */
        function debounce(fn, ms = 250) {
            let timer;
            return function() {
                const args = arguments;
                const context = this;
                if (timer) clearTimeout(timer);
                timer = setTimeout(() => {
                    fn.apply(context, args);
                }, ms)
            }
        }

        /** Main flow */
        function main() {
            if (restrictions) {
                getI18n().then(languages => {
                    langs = languages;
                    requestPDF("./", restrictions.ppw);
                });
            }
        }

        main();
    });
</script>
