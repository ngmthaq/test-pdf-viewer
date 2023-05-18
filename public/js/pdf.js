// PDF.js library
const PDFJS = window["pdfjs-dist/build/pdf"];

// Static paths
const PDF_WORKER = "/public/libs/pdfjs/build/pdf.worker.js";
const PDF_PATH = "/public/static/pdf.pdf";

// Template string
const ZOOM_OPTION_PSUEDO = "zoom-option-psuedo";
const CANVAS_ID_TEMPLATE = "pdf-viewer-:id";
const CONTAINER_ID_TEMPLATE = "pdf-container-:id";
const CANVAS_CLASS = "canvas-layer";
const CANVAS_MARGIN = 8;

// CSS Unit
const CSS_UNIT = PDFJS.PixelsPerInch.PDF_TO_CSS_UNITS;

// Zoom levels
const ZOOM_LEVELS = {
  auto: { title: "Automatic Zoom", value: "auto" },
  actual: { title: "Actual Size", value: "actual" },
  fit: { title: "Page Fit", value: "fit" },
  width: { title: "Page Width", value: "width" },
  p50: { title: "50%", value: 0.5 },
  p75: { title: "75%", value: 0.75 },
  p100: { title: "100%", value: 1 },
  p125: { title: "125%", value: 1.25 },
  p150: { title: "150%", value: 1.5 },
  p200: { title: "200%", value: 2 },
  p300: { title: "300%", value: 3 },
  p400: { title: "400%", value: 4 },
};

const ENABLED_MAX_WIDTH = [
  ZOOM_LEVELS.auto.value,
  ZOOM_LEVELS.fit.value,
  ZOOM_LEVELS.width.value,
];

// View modes
const VIEW_MODES = {
  vertical: { title: "Vertical Scrolling", value: "vertical" },
  horizontal: { title: "Horizontal Scrolling", value: "horizontal" },
  wrapped: { title: "Wrapped Scrolling", value: "wrapped" },
};

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
  viewport: { width: 0, height: 0 },
  actualViewport: { width: 0, height: 0 },
  isFullscreen: false,
};

let pages = [];

console.log(PDFJS);

$(document).ready(function () {
  // Navigation elements
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
  const openFullScreenBtn = $(document).find("#open-fullscreen-btn");

  pdfContainer.addClass(initialState.viewMode);
  currentPageInput.numeric();
  prevButton.addClass("disabled");

  // Init pdf.js Worker
  PDFJS.GlobalWorkerOptions.workerSrc = PDF_WORKER;

  // Click prev button
  prevButton.click(function () {
    if (initialState.currentPage > 1) {
      initialState.currentPage -= 1;
      let id = CANVAS_ID_TEMPLATE.replace(":id", initialState.currentPage);
      currentPageInput.val(initialState.currentPage);
      let el = document.getElementById(id);
      if (el) el.scrollIntoView({ behavior: "smooth", block: "start" });
      nextButton.removeClass("disabled");
      initialState.currentPage === 1 && $(this).addClass("disabled");
    }
  });

  // Click next button
  nextButton.click(function () {
    if (initialState.currentPage < initialState.pageCount) {
      initialState.currentPage += 1;
      let id = CANVAS_ID_TEMPLATE.replace(":id", initialState.currentPage);
      currentPageInput.val(initialState.currentPage);
      let el = document.getElementById(id);
      if (el) el.scrollIntoView({ behavior: "smooth", block: "start" });
      prevButton.removeClass("disabled");
      initialState.currentPage === initialState.pageCount &&
        $(this).addClass("disabled");
    }
  });

  // Focus page input
  currentPageInput.focus(function () {
    $(this).select();
  });

  // Change page input
  currentPageInput.change(function (e) {
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
      setTimeout(() => {
        if (el) el.scrollIntoView({ behavior: "smooth", block: "start" });
      }, 1);
    }
  });

  // Dropdown changed
  zoomDropdown.change(function () {
    initialState.zoom = convertZoomNumber($(this).val());
    zoomInButton.removeClass("disabled");
    zoomOutButton.removeClass("disabled");
    initialState.scaleControl = 1;
    pages.forEach(({ page, canvas }) => {
      const elements = canvas[0];
      const ctx = elements.getContext("2d");
      const viewport = page.getViewport({ scale: initialState.zoom });
      const renderCtx = { canvasContext: ctx, viewport: viewport };
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
  zoomOutButton.click(function () {
    if (initialState.scaleControl > initialState.minScale) {
      zoomInButton.removeClass("disabled");
      initialState.scaleControl -= initialState.scaleStep;
      initialState.zoom = initialState.scaleControl;
      $("#" + ZOOM_OPTION_PSUEDO).val(initialState.scaleControl);
      $("#" + ZOOM_OPTION_PSUEDO).text(initialState.scaleControl * 100 + "%");
      zoomDropdown.val(initialState.scaleControl);
      initialState.scaleControl === initialState.minScale &&
        $(this).addClass("disabled");
      pages.forEach(({ page, canvas }) => {
        const elements = canvas[0];
        const ctx = elements.getContext("2d");
        const viewport = page.getViewport({ scale: initialState.zoom });
        const renderCtx = { canvasContext: ctx, viewport: viewport };
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
  zoomInButton.click(function () {
    if (initialState.scaleControl < initialState.maxScale) {
      zoomOutButton.removeClass("disabled");
      initialState.scaleControl += initialState.scaleStep;
      initialState.zoom = initialState.scaleControl;
      $("#" + ZOOM_OPTION_PSUEDO).val(initialState.scaleControl);
      $("#" + ZOOM_OPTION_PSUEDO).text(initialState.scaleControl * 100 + "%");
      zoomDropdown.val(initialState.scaleControl);
      initialState.scaleControl === initialState.maxScale &&
        $(this).addClass("disabled");
      pages.forEach(({ page, canvas }) => {
        const elements = canvas[0];
        const ctx = elements.getContext("2d");
        const viewport = page.getViewport({ scale: initialState.zoom });
        const renderCtx = { canvasContext: ctx, viewport: viewport };
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
  openFullScreenBtn.click(function () {
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
  pdfContainer.on("fullscreenchange", function (e) {
    if (initialState.isFullscreen) {
      initialState.isFullscreen = false;
      pdfContainer.removeClass("fullscreen");
    } else {
      initialState.isFullscreen = true;
      pdfContainer.addClass("fullscreen");
    }
  });

  // Initial pdf.js
  PDFJS.getDocument(PDF_PATH)
    .promise.then((doc) => {
      initialState.pdfDoc = doc;
      initialState.pageCount = initialState.pdfDoc.numPages;
      if (initialState.pageCount > 0) {
        initialState.currentPage = 1;
        currentPageInput.val(1);
        totalPagesElement.text(initialState.pageCount);
        initPages();
      }
    })
    .catch((err) => {
      console.error(err);
    });

  // Render pages
  function initPages() {
    const lastOption = `<option style="display: none;" value="" id="${ZOOM_OPTION_PSUEDO}"></option>`;
    Object.values(ZOOM_LEVELS).forEach((level) => {
      const option = `<option value="${level.value}">${level.title}</option>`;
      zoomDropdown.html(zoomDropdown.html() + option);
    });
    zoomDropdown.html(zoomDropdown.html() + lastOption);

    for (let i = initialState.currentPage; i <= initialState.pageCount; i++) {
      initialState.pdfDoc.getPage(i).then((page) => {
        const div = $("<div></div>");
        const wrapper = div[0];
        const canvas = $("<canvas></canvas>");
        const elements = canvas[0];
        const ctx = elements.getContext("2d");
        const actualViewport = page.getViewport({ scale: 1 });
        const viewport = page.getViewport({ scale: initialState.zoom });
        const renderCtx = { canvasContext: ctx, viewport: viewport };
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
        wrapper.addEventListener("mouseover", function () {
          initialState.currentPage = i;
          currentPageInput.val(initialState.currentPage);
          prevButton.removeClass("disabled");
          nextButton.removeClass("disabled");
          initialState.currentPage === 1 && prevButton.addClass("disabled");
          initialState.currentPage === initialState.pageCount &&
            nextButton.addClass("disabled");
        });
        pages.push({ canvas, page });
        page.render(renderCtx);
        pdfContainer.append(wrapper);
      });
    }
  }

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
});
