const PDF_WORKER = "/public/libs/pdfjs/build/pdf.worker.js";
const PDF_PATH_DEFAULT = "/public/static/pdf2.pdf";

// PDF.js library
const PDFJS = window["pdfjs-dist/build/pdf"];
PDFJS.GlobalWorkerOptions.workerSrc = PDF_WORKER;

// Template string
const ZOOM_OPTION_PSUEDO = "zoom-option-psuedo";
const CANVAS_ID_TEMPLATE = "pdf-viewer-:id";
const CONTAINER_ID_TEMPLATE = "pdf-container-:id";
const CANVAS_CLASS = "canvas-layer";
const CANVAS_MARGIN = 8;

// CSS Unit
const CSS_UNIT = PDFJS.PixelsPerInch.PDF_TO_CSS_UNITS;

// Locale
const [iso, lng] = window.navigator.languages;

// Zoom levels
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

const ENABLED_MAX_WIDTH = [ZOOM_LEVELS.auto.value, ZOOM_LEVELS.fit.value, ZOOM_LEVELS.width.value];

// View modes
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
