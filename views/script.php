<script>
    $(document).ready(function() {
        const restrictions = <?php echo $restrictions ?>;

        let pages = [];
        let initialState = {
            pdfDoc: null,
            currentPage: 0,
            pageCount: 0,
            zoom: CSS_UNIT,
            scaleControl: 1,
            scaleStep: 0.25,
            minScale: 0.25,
            maxScale: 5,
            viewMode: VIEW_MODES.vertical.value,
            viewport: {
                width: 0,
                height: 0
            },
            actualViewport: {
                width: 0,
                height: 0
            },
            isFullscreen: false,
            isRequestingPassword: false,
            isOpenSidebar: false,
        };

        // Navigation elements
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

        $("html").attr("lang", lang);

        pdfContainer.addClass(initialState.viewMode);
        currentPageInput.numeric();
        prevButton.addClass("disabled");
        searchButton.addClass("disabled");

        // Click prev button
        prevButton.click(function() {
            if (initialState.currentPage > 1) {
                initialState.currentPage -= 1;
                let id = CANVAS_ID_TEMPLATE.replace(":id", initialState.currentPage);
                currentPageInput.val(initialState.currentPage);
                let el = document.getElementById(id);
                if (el) {
                    pdfContainer[0].scrollTo({
                        top: el.offsetTop
                    });
                }
                let miniid = MINI_CANVAS_ID_TEMPLATE.replace(":id", initialState.currentPage);
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

        // Click next button
        nextButton.click(function() {
            if (initialState.currentPage < initialState.pageCount) {
                initialState.currentPage += 1;
                let id = CANVAS_ID_TEMPLATE.replace(":id", initialState.currentPage);
                currentPageInput.val(initialState.currentPage);
                let el = document.getElementById(id);
                if (el) {
                    pdfContainer[0].scrollTo({
                        top: el.offsetTop
                    });
                }
                let miniid = MINI_CANVAS_ID_TEMPLATE.replace(":id", initialState.currentPage);
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

        // Focus page input
        currentPageInput.focus(function() {
            $(this).select();
        });

        // Change page input
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
                let id = CANVAS_ID_TEMPLATE.replace(":id", initialState.currentPage);
                let el = document.getElementById(id);
                $(this).val(val);
                pdfContainer[0].scrollTo({
                    top: el.offsetTop
                });
                let miniid = MINI_CANVAS_ID_TEMPLATE.replace(":id", initialState.currentPage);
                let miniel = document.getElementById(miniid);
                miniPdfContainer[0].scrollTo({
                    top: miniel.offsetTop
                });
                $(`.${MINI_PDF_WRAPPER_CLASS}`).removeClass("active");
                $(`.${MINI_PDF_WRAPPER_CLASS}[data-id="${initialState.currentPage}"]`).addClass("active");
            }
        });

        // Dropdown changed
        zoomDropdown.change(function() {
            initialState.zoom = convertZoomNumber($(this).val());
            zoomInButton.removeClass("disabled");
            zoomOutButton.removeClass("disabled");
            initialState.scaleControl = 1;
            pages.forEach(({
                page,
                canvas
            }) => {
                const elements = canvas[0];
                const ctx = elements.getContext("2d");
                const viewport = page.getViewport({
                    scale: initialState.zoom
                });
                const renderCtx = {
                    canvasContext: ctx,
                    viewport: viewport
                };
                elements.height = viewport.height;
                elements.width = viewport.width;
                if (ENABLED_MAX_WIDTH.includes($(this).val())) {
                    canvas.css("max-width", "100vw");
                } else {
                    canvas.css("max-width", "unset");
                }
                page.render(renderCtx);
            });
        });

        // Click zoom out
        zoomOutButton.click(function() {
            if (initialState.scaleControl > initialState.minScale) {
                zoomInButton.removeClass("disabled");
                initialState.scaleControl -= initialState.scaleStep;
                initialState.zoom = initialState.scaleControl;
                $("#" + ZOOM_OPTION_PSUEDO).val(initialState.scaleControl);
                $("#" + ZOOM_OPTION_PSUEDO).text(initialState.scaleControl * 100 + "%");
                zoomDropdown.val(initialState.scaleControl);
                initialState.scaleControl === initialState.minScale &&
                    $(this).addClass("disabled");
                pages.forEach(({
                    page,
                    canvas
                }) => {
                    const elements = canvas[0];
                    const ctx = elements.getContext("2d");
                    const viewport = page.getViewport({
                        scale: initialState.zoom
                    });
                    const renderCtx = {
                        canvasContext: ctx,
                        viewport: viewport
                    };
                    elements.height = viewport.height;
                    elements.width = viewport.width;
                    if (ENABLED_MAX_WIDTH.includes($(this).val())) {
                        canvas.css("max-width", "100vw");
                    } else {
                        canvas.css("max-width", "unset");
                    }
                    page.render(renderCtx);
                });
            }
        });

        // Click zoom in
        zoomInButton.click(function() {
            if (initialState.scaleControl < initialState.maxScale) {
                zoomOutButton.removeClass("disabled");
                initialState.scaleControl += initialState.scaleStep;
                initialState.zoom = initialState.scaleControl;
                $("#" + ZOOM_OPTION_PSUEDO).val(initialState.scaleControl);
                $("#" + ZOOM_OPTION_PSUEDO).text(initialState.scaleControl * 100 + "%");
                zoomDropdown.val(initialState.scaleControl);
                initialState.scaleControl === initialState.maxScale &&
                    $(this).addClass("disabled");
                pages.forEach(({
                    page,
                    canvas
                }) => {
                    const elements = canvas[0];
                    const ctx = elements.getContext("2d");
                    const viewport = page.getViewport({
                        scale: initialState.zoom
                    });
                    const renderCtx = {
                        canvasContext: ctx,
                        viewport: viewport
                    };
                    elements.height = viewport.height;
                    elements.width = viewport.width;
                    if (ENABLED_MAX_WIDTH.includes($(this).val())) {
                        canvas.css("max-width", "100vw");
                    } else {
                        canvas.css("max-width", "unset");
                    }
                    page.render(renderCtx);
                });
            }
        });

        // Open fullscreen
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

        // Container fullscreen event
        pdfContainer.on("fullscreenchange", function(e) {
            if (initialState.isFullscreen) {
                initialState.isFullscreen = false;
                pdfContainer.removeClass("fullscreen");
            } else {
                initialState.isFullscreen = true;
                pdfContainer.addClass("fullscreen");
            }
        });

        // Click print
        printButton.click(function() {
            pdfContainer.printThis();
        });

        // Ctrl + P
        document.addEventListener("keydown", function(event) {
            if ((event.ctrlKey || event.metaKey) && event.keyCode === 80) {
                event.preventDefault();
                event.stopImmediatePropagation();
                pdfContainer.printThis();
            }
        });

        // Open request password prompt when winfow re-focus
        window.addEventListener("focus", function(e) {
            if (initialState.isRequestingPassword) {
                requestPassword(restrictions);
            }
        })

        // Toggle open sidebar
        sidebarButton.click(function(e) {
            pdfContainer.toggleClass("fullwidth");
            miniPdfContainer.toggleClass("close");
            $(this).toggleClass("active");
            initialState.isOpenSidebar = !initialState.isOpenSidebar;
        })

        // Initial pdf.js
        function render(path, password = "") {
            const LOADING_TASK = PDFJS.getDocument(path);
            LOADING_TASK.onPassword = function(callback, reason) {
                callback(password);
            }
            LOADING_TASK.promise
                .then((doc) => {
                    initialState.pdfDoc = doc;
                    initialState.pageCount = initialState.pdfDoc.numPages;
                    if (initialState.pageCount > 0) {
                        initialState.currentPage = 1;
                        currentPageInput.val(1);
                        totalPagesElement.text(initialState.pageCount);
                        getI18n().then((langs) => {
                            initPages(langs);
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
                        });
                    }
                })
                .catch((err) => {
                    console.error(err);
                });
        }

        // Init pdf pages
        function initPages(langs) {
            const lastOption = `<option style="display: none;" value="" id="${ZOOM_OPTION_PSUEDO}"></option>`;
            Object.values(ZOOM_LEVELS).forEach((level, index) => {
                const id = level.id || "zoom-level-" + index;
                const option = `<option value="${level.value}" id="${id}">${level.title}</option>`;
                zoomDropdown.html(zoomDropdown.html() + option);
            });
            zoomDropdown.html(zoomDropdown.html() + lastOption);

            for (let i = initialState.currentPage; i <= initialState.pageCount; i++) {
                initialState.pdfDoc.getPage(i).then((page) => {
                    // Main pdf page
                    const div = $("<div></div>");
                    const wrapper = div[0];
                    const canvas = $("<canvas></canvas>");
                    const elements = canvas[0];
                    const ctx = elements.getContext("2d");
                    const actualViewport = page.getViewport({
                        scale: 1
                    });
                    const viewport = page.getViewport({
                        scale: initialState.zoom
                    });
                    const renderCtx = {
                        canvasContext: ctx,
                        viewport: viewport
                    };
                    initialState.viewport = viewport;
                    initialState.actualViewport = actualViewport;
                    canvas.addClass(CANVAS_CLASS);
                    elements.height = viewport.height;
                    elements.width = viewport.width;
                    elements.style.margin = "0 auto";
                    elements.style.marginTop = CANVAS_MARGIN + "px";
                    elements.style.marginBottom = CANVAS_MARGIN + "px";
                    elements.id = CANVAS_ID_TEMPLATE.replace(":id", i);
                    wrapper.style.width = "100%";
                    wrapper.append(elements);
                    wrapper.addEventListener("mouseover", function() {
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
                    pages.push({
                        canvas,
                        page
                    });
                    page.render(renderCtx);
                    pdfContainer.append(wrapper);
                    return page;
                }).then((page) => {
                    // Mini pdf page
                    const div = $("<div></div>");
                    const wrapper = div[0];
                    const canvas = $("<canvas></canvas>");
                    const elements = canvas[0];
                    const ctx = elements.getContext("2d");
                    const viewport = page.getViewport({
                        scale: 1
                    });
                    const renderCtx = {
                        canvasContext: ctx,
                        viewport: viewport
                    };
                    canvas.addClass(CANVAS_CLASS);
                    elements.height = viewport.height;
                    elements.width = viewport.width;
                    elements.style.margin = "0 auto";
                    elements.style.marginTop = CANVAS_MARGIN + "px";
                    elements.style.marginBottom = CANVAS_MARGIN + "px";
                    elements.id = MINI_CANVAS_ID_TEMPLATE.replace(":id", i);
                    wrapper.setAttribute("data-id", i);
                    wrapper.className = "d-flex justify-content-center align-items-center " + MINI_PDF_WRAPPER_CLASS;
                    if (i === 1) wrapper.className += " active";
                    wrapper.style.width = "100%";
                    wrapper.setAttribute("title", langs.page_landmark.replace("{{page}}", i));
                    wrapper.append(elements);
                    wrapper.onclick = function(e) {
                        initialState.currentPage = i
                        $(`.${MINI_PDF_WRAPPER_CLASS}`).removeClass("active");
                        $(`.${MINI_PDF_WRAPPER_CLASS}[data-id="${initialState.currentPage}"]`).addClass("active");
                        currentPageInput.val(i);
                        let el = document.getElementById(CANVAS_ID_TEMPLATE.replace(":id", i));
                        pdfContainer[0].scrollTo({
                            top: el.offsetTop
                        });
                    }
                    page.render(renderCtx);
                    miniPdfContainer.append(wrapper);
                });
            }
        }

        // Convert text to zoom number
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

        // Get i18n
        function getI18n() {
            return new Promise((resolve, reject) => {
                let locale = locales.shift();
                let i18nUrlTemplate =
                    "/public/libs/pdfjs/web/locale/:lang/viewer.properties";
                $.ajax({
                    type: "get",
                    url: i18nUrlTemplate.replace(":lang", locale),
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

        // Request get PDF
        function requestPDF(path, password = "") {
            loading.css("display", "flex");
            $.ajax({
                type: "get",
                url: path,
                data: {
                    pdf: true
                },
                xhr: function() {
                    let xhr = new XMLHttpRequest();
                    xhr.responseType = 'blob'
                    return xhr;
                },
                success: function(response) {
                    let path = PDF_PATH_DEFAULT;
                    if (response.size > 0) {
                        path = window.URL.createObjectURL(response);
                    }
                    render(path, password)
                    loading.css("display", "none");
                    downloadButton.click(function() {
                        downloadFile(path);
                    });
                },
                error: function(xhr, status, error) {
                    alert("Something wrong, please try again later");
                }
            });
        }

        // Request enter pdf password
        function requestPassword(restrictions, retry = 0) {
            let value = window.prompt(retry === 0 ? "Enter the password to open PDF file" : "Wrong password, enter the password to open PDF file");
            if (value && value === restrictions.ppw) {
                requestPDF("/", restrictions.ppw);
                initialState.isRequestingPassword = false;
            } else {
                if (value !== null) {
                    retry = retry + 1;
                    requestPassword(restrictions, retry);
                }
            }
        }

        // Download file
        function downloadFile(path, fileName = "documentation.pdf") {
            const link = document.createElement('a');
            link.href = path;
            link.download = fileName;
            document.body.append(link);
            link.click();
            link.remove();
        }

        // Main flow
        if (restrictions) {
            if (restrictions.ppw === "") {
                requestPDF("/");
            } else {
                initialState.isRequestingPassword = true;
                requestPassword(restrictions);
            }
        }
    });
</script>
