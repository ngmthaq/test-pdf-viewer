<style>
    @import url("/vendors/fonts/noto_san_jp/NotoSansJP-VariableFont_wght.ttf");

    :root {
        /* Element Height */
        --controller-height: 35px;

        /* Element Width */
        --mini-pdf-container-width: 200px;

        /* Color */
        --header-bg-color: #38383d;
        --btn-main-color: rgba(249, 249, 250, 1);
        --btn-disabled-color: rgb(192, 192, 192);
        --button-hover-bg-color: rgba(102, 102, 103, 1);
        --button-click-bg-color: rgb(93, 93, 94);
        --dropdown-bg-color: rgba(74, 74, 79, 1);

        /* z-index */
        --z-index-loading: 1;
        --z-index-overlay: 10000;
    }

    * {
        padding: 0;
        margin: 0;
        scroll-behavior: smooth;
        font-family: 'Noto Sans JP', sans-serif;
    }

    body {
        background-color: #2a2a2e;
    }

    body::-webkit-scrollbar {
        display: none;
    }

    #pdf-app {
        width: 100%;
    }

    #pdf-app #controller {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 16px;
        height: var(--controller-height);
        width: 100%;
        background-color: var(--header-bg-color);
        border-bottom: 1px solid black;
    }

    /* Left controller */

    #pdf-app #controller .left-controller {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        height: 100%;
        flex: 1;
    }

    #sidebar-controller {
        margin-right: 40px;
    }

    #search-controller {
        margin-right: 8px;
    }

    #current-page {
        width: 60px;
        background-color: transparent;
        border: 1px solid var(--button-hover-bg-color);
        outline: none;
        text-align: right;
        padding: 4px 8px;
        border-radius: 2px;
        font-size: 14px;
        line-height: 16px;
        color: var(--btn-main-color);
        margin-left: 4px;
    }

    #separator {
        font-size: 14px;
        line-height: 16px;
        color: var(--btn-main-color);
    }

    #total-pages {
        font-size: 14px;
        line-height: 16px;
        color: var(--btn-main-color);
    }

    /* End left controller */

    /* Mid controller */

    #pdf-app #controller .mid-controller {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        flex: 1;
    }

    #zoom-in,
    #zoom-out {
        font-size: 18px;
        line-height: 20px;
        margin: 0 4px;
    }

    #zoom-separator {
        height: 20px;
        border-right: 1px solid grey;
    }

    #zoom-select-options {
        background-color: var(--dropdown-bg-color);
        color: var(--btn-main-color);
        padding: 2px 8px;
        border: none;
        outline: none;
        font-size: 15px;
        line-height: 20px;
        cursor: pointer;
        border-radius: 2px;
    }

    /* End mid controller */

    /* Right controller */

    #pdf-app #controller .right-controller {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        height: 100%;
        flex: 1;
    }

    #dropdown-separator {
        height: 20px;
        border-right: 1px solid grey;
    }

    #open-fullscreen-btn {
        margin: 0 4px;
    }

    #dropdown-btn {
        margin: 0 4px;
    }

    #print-btn {
        margin: 0 4px;
    }

    /* End reight controller */

    /* PDF Container */

    #section-container {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #mini-pdf-container {
        width: var(--mini-pdf-container-width);
        height: calc(100vh - var(--controller-height));
        background-color: var(--header-bg-color);
        transition: all .25s ease-in-out;
        overflow-y: scroll;
        transform: translateX(0);
        position: absolute;
        top: 0;
        left: 0;
    }

    #mini-pdf-container.close {
        transform: translateX(-250px);
    }

    #mini-pdf-container .canvas-layer {
        width: 150px;
        height: auto;
    }

    #mini-pdf-container .mini-pdf-wrapper:hover {
        background-color: rgba(255, 255, 255, 0.1);
        cursor: pointer;
    }

    #mini-pdf-container .mini-pdf-wrapper.active {
        background-color: rgba(255, 255, 255, 0.2);
    }

    #pdf-app #pdf-container {
        width: 100vw;
        height: calc(100vh - var(--controller-height));
        overflow-y: scroll;
        transition: all .25s ease-in-out;
        flex-shrink: 0;
    }

    #pdf-app #pdf-container.fullscreen::-webkit-scrollbar {
        display: none;
    }

    #pdf-app #pdf-container.vertical {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: flex-start;
    }

    #pdf-app #pdf-container .canvas-layer {
        max-width: 100vw;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

    .canvas-wrapper {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: flex-start;
    }

    /* End PDF Container */

    .focus-btn,
    .hover-btn {
        width: 28px;
        height: 28px;
        border-radius: 2px;
        background-color: transparent;
        color: var(--btn-main-color);
        cursor: pointer;
        font-size: 14px;
        line-height: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .2s ease-in-out;
    }

    .focus-btn:hover,
    .hover-btn:hover {
        background-color: hsla(0, 0%, 0%, .1);
        box-shadow: 0 1px 0 hsla(0, 0%, 100%, .05) inset, 0 0 1px hsla(0, 0%, 100%, .15) inset, 0 0 1px hsla(0, 0%, 0%, .05);
        border-color: hsla(0, 0%, 0%, .32) hsla(0, 0%, 0%, .38) hsla(0, 0%, 0%, .42);
        border-left-color: transparent;
    }

    .focus-btn.active {
        background-color: hsla(0, 0%, 0%, .2);
        box-shadow: 0 1px 0 hsla(0, 0%, 100%, .05) inset, 0 0 1px hsla(0, 0%, 100%, .15) inset, 0 0 1px hsla(0, 0%, 0%, .05);
        border-color: hsla(0, 0%, 0%, .32) hsla(0, 0%, 0%, .38) hsla(0, 0%, 0%, .42);
        border-left-color: transparent;
    }

    .focus-btn.disabled,
    .hover-btn.disabled {
        cursor: default !important;
        color: var(--btn-disabled-color) !important;
        background-color: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }


    /* LOADING */

    #loading {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(102, 102, 103, 0.2);
        z-index: var(--z-index-loading);
    }

    /* Tools drawer */

    #tools-drawer {
        position: fixed;
        top: var(--controller-height);
        right: -500px;
        width: 300px;
        height: calc(100vh - var(--controller-height));
        background-color: var(--header-bg-color);
        overflow-y: scroll;
        transition: all .2s linear;
    }

    #tools-drawer.open {
        right: 0;
    }

    #tools-drawer::-webkit-scrollbar {
        width: 0;
    }

    #tools-drawer .tool {
        padding: 16px;
        background-color: #38383d;
        color: aliceblue;
        cursor: pointer;
        user-select: none;
        transition: all .1s ease;
        display: flex;
        align-items: center;
        justify-content: flex-start;
    }

    #tools-drawer .tool:hover {
        background-color: #1e1e1e;
    }

    #tools-drawer .tool span {
        display: inline-block;
        margin-left: 16px;
    }

    @media only screen and (max-width: 600px) {
        #tools-drawer {
            width: 100vw;
        }
    }

    /* Modal */

    .custom-modal-content {
        border-radius: 4px;
    }

    .custom-modal-content .modal-header {
        padding: 8px 16px;
    }

    #overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.8);
        z-index: var(--z-index-overlay);
        display: none !important;
    }
</style>
