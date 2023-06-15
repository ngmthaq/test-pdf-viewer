<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?php assets('img/favicon.ico') ?>" type="image/x-icon">
    <title></title>
    <style>
        * {
            padding: 0;
            margin: 0;
        }

        .error-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100vw;
            height: 100vh;
            flex-direction: column;
        }

        p.text {
            font-size: 16px;
            text-align: center;
        }

        img.logo {
            width: 120px;
            height: auto;
            margin-bottom: 24px;
        }
    </style>
</head>

<body>
    <div class="error-container">
        <img class="logo" src="<?php assets('img/pdf.png') ?>" alt="Logo">
        <p class="text" id="text"></p>
    </div>

    <script src="<?php assets('libs/jquery/index.min.js') ?>"></script>
    <script>
        /** Get i18n */
        const locales = window.navigator.languages.map((l) => l);

        getI18n().then(languages => {
            if (languages.rendering_error) {
                let text = document.getElementById("text");
                text.innerText = languages.rendering_error;
                document.title = languages.rendering_error;
            }
        });

        function getI18n() {
            return new Promise((resolve, reject) => {
                let locale = locales.shift();
                let i18nUrlTemplate =
                    "./vendors/libs/pdfjs/web/locale/:lang/viewer.properties";
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
    </script>
</body>

</html>
