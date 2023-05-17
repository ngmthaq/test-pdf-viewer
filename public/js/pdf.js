const PDF_WORKER = "/public/libs/pdfjs/build/pdf.worker.js";
const PDF_PATH = "/public/static/pdf.pdf";
const pdfjsLib = window["pdfjs-dist/build/pdf"];
const CANVAS_ID_TEMPLATE = "pdf-viewer-:id";
const TEXT_LAYER_ID_TEMPLATE = "text-layer-:id";
const CANVAS_CLASS = "canvas-layer";
const TEXT_LAYER_CLASS = "text-layer-layer";

const zoomLevels = {
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

$(async function () {
  const [iso, lng] = window.navigator.languages;
  const match = /^(\d+)\.(\d+)\.(\d+)$/.exec(pdfjsLib.version);

  $("html").attr("lang", lng);
  $("html").attr("data-lang", iso);

  if (match && (match[1] | 0) >= 3 && (match[2] | 0) >= 2) {
    // Get DOM elements
    const prevBtn = $("#prev-btn");
    const nextBtn = $("#next-btn");
    const currentPageEl = $("#current-page");
    const totalPagesEl = $("#total-pages");
    const pdfContainer = $("#pdf-container");
    const zoomSelect = $("#zoom-select-options");

    // Init PDF.js worker
    pdfjsLib.GlobalWorkerOptions.workerSrc = PDF_WORKER;

    // Create pdf document
    let doc = await pdfjsLib.getDocument(PDF_PATH).promise;
    let scale = pdfjsLib.PixelsPerInch.PDF_TO_CSS_UNITS;
    let firstPage = 1;
    let lastPage = doc._pdfInfo.numPages;
    let currentPage = firstPage;
    let zoomLevel = zoomLevels.auto.value;
    let canvasWidth = 0;
    let canvasHeight = 0;

    // Render zoom levels
    Object.values(zoomLevels).forEach((level) => {
      const option = `<option value="${level.value}">${level.title}</option>`;
      zoomSelect.html(zoomSelect.html() + option);
    });

    // Render all pages
    await init();

    // Handle window resize
    $(window).on("resize", async function () {});

    // Handle click prev page button
    prevBtn.click(async function (e) {
      e.preventDefault();
      if (currentPage > firstPage) {
        currentPage = currentPage - 1;
        await changePage();
      }
    });

    // Handle click next page button
    nextBtn.click(async function (e) {
      e.preventDefault();
      if (currentPage < lastPage) {
        currentPage = currentPage + 1;
        await changePage();
      }
    });

    // Accept number only
    currentPageEl.numeric();

    // Handle change page input
    currentPageEl.change(async function (e) {
      e.preventDefault();
      let val = $(this).val();
      if (val <= 0) val = 1;
      if (val > lastPage) val = lastPage;
      currentPage = val;
      await changePage();
    });

    // Handle render specific page
    async function render(number) {
      if (number && number >= 1 && number <= lastPage) {
        const container = document.createElement("div");
        const textWrapper = document.createElement("div");
        const canvas = document.createElement("canvas");
        const canvasContext = canvas.getContext("2d");
        const page = await doc.getPage(number);
        const initViewport = await page.getViewport({ scale: scale });
        const viewScale =
          window.innerWidth > initViewport.width
            ? scale
            : window.innerWidth / initViewport.width;
        const viewport = await page.getViewport({ scale: viewScale });
        container.style.position = "relative";
        container.style.marginBottom = "16px";
        canvasHeight = viewport.height;
        canvasWidth = viewport.width;
        canvas.id = CANVAS_ID_TEMPLATE.replace(":id", number);
        canvas.classList.add(CANVAS_CLASS);
        canvas.height = viewport.height;
        canvas.width = viewport.width;
        canvas.style.display = "block";
        canvas.style.margin = "0 auto";
        canvas.style.transformOrigin = "top left";
        textWrapper.id = TEXT_LAYER_ID_TEMPLATE.replace(":id", number);
        textWrapper.classList.add(TEXT_LAYER_CLASS);
        textWrapper.style.height = viewport.height + "px";
        textWrapper.style.width = viewport.width + "px";
        textWrapper.style.top = 0 + "px";
        textWrapper.style.left = "50%";
        textWrapper.style.transform = "translateX(-50%)";
        textWrapper.style.position = "absolute";
        textWrapper.style.transformOrigin = "top left";
        textWrapper.style.zIndex = "1";
        await page.render({ canvasContext, viewport }).promise;
        const { items, styles } = await page.getTextContent();
        Object.entries(styles).forEach(([className, value]) => {
          addStyle({ ["." + className]: { fontFamily: value.fontFamily } });
        });
        items.forEach((item, index) => {
          // console.log(item);
          // let span = document.createElement("span");
          // span.innerText = item.str;
          // span.setAttribute("role", "presentation");
          // span.setAttribute("dir", item.dir);
          // span.classList.add(item.fontName);
          // textWrapper.appendChild(span);
        });
        container.append(canvas, textWrapper);
        pdfContainer.append(container);
      } else {
        console.error("Please specify a valid page number");
      }
    }

    // Handle change page
    async function changePage() {
      const id = CANVAS_ID_TEMPLATE.replace(":id", currentPage);
      const page = document.getElementById(id);
      page.scrollIntoView({ behavior: "smooth" });
      currentPageEl.val(currentPage);
      prevBtn.removeClass("disabled");
      nextBtn.removeClass("disabled");
      if (currentPage === 1) {
        prevBtn.addClass("disabled");
      } else if (currentPage === lastPage) {
        nextBtn.addClass("disabled");
      }
    }

    // Init all pages
    async function init() {
      currentPageEl.val(currentPage);
      prevBtn.addClass("disabled");
      totalPagesEl.text(lastPage);
      for (let i = firstPage; i <= lastPage; i++) {
        await render(i);
      }
    }

    // Add stylesheet
    function addStyle(style) {
      let allStyles = [];

      $.each(style, function (selector, rules) {
        let arr = $.map(rules, function (value, prop) {
          return " " + camelToKebabCase(prop) + ": " + value + ";";
        });
        allStyles.push(selector + " {\n" + arr.join("\n") + "\n}");
      });

      $("<style>")
        .prop("type", "text/css")
        .html(allStyles.join("\n"))
        .appendTo("head");
    }

    // Convert camel case to kebab case
    function camelToKebabCase(str) {
      return str.replace(/[A-Z]/g, (letter) => `-${letter.toLowerCase()}`);
    }

    // Convert zoom level
    function convertZoomLevel(level) {
      let zoom = 1;
      switch (level) {
        case zoomLevels.auto.value:
          zoom = pdfjsLib.PixelsPerInch.PDF_TO_CSS_UNITS;
          break;

        case zoomLevels.actual.value:
          zoom = 1;
          break;

        case zoomLevels.width.value:
          zoom = 1;
          break;

        case zoomLevels.fit.value:
          zoom = 1;
          break;

        default:
          zoom = level;
          break;
      }
      return zoom;
    }

    // Log pdfjs library
    // console.log(window);
    // console.log(pdfjsLib);
    // console.log(doc);
  }
});
