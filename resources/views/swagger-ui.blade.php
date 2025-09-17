<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Levels Derm Clinic API Documentation</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui.css" />
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            margin:0;
            background: #fafafa;
        }
        .swagger-ui .topbar {
            background-color: #2c3e50;
        }
        .swagger-ui .topbar .download-url-wrapper {
            display: none;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: '/swagger.json',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                tryItOutEnabled: true,
                requestInterceptor: function(request) {
                    // Add authorization header if token exists
                    const token = localStorage.getItem('api_token');
                    if (token) {
                        request.headers.Authorization = 'Bearer ' + token;
                    }
                    return request;
                },
                onComplete: function() {
                    // Add custom CSS for better Arabic support
                    const style = document.createElement('style');
                    style.textContent = `
                        .swagger-ui .opblock-summary-description {
                            direction: rtl;
                            text-align: right;
                        }
                        .swagger-ui .opblock-description-wrapper p {
                            direction: rtl;
                            text-align: right;
                        }
                        .swagger-ui .response-col_description__inner p {
                            direction: rtl;
                            text-align: right;
                        }
                    `;
                    document.head.appendChild(style);
                }
            });
            
            // Add token input
            const tokenInput = document.createElement('div');
            tokenInput.innerHTML = `
                <div style="position: fixed; top: 10px; right: 10px; z-index: 1000; background: white; padding: 10px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <label for="token-input" style="display: block; margin-bottom: 5px; font-weight: bold;">API Token:</label>
                    <input type="text" id="token-input" placeholder="Enter your Bearer token" style="width: 200px; padding: 5px; border: 1px solid #ccc; border-radius: 3px;">
                    <button onclick="setToken()" style="margin-left: 5px; padding: 5px 10px; background: #2c3e50; color: white; border: none; border-radius: 3px; cursor: pointer;">Set Token</button>
                </div>
            `;
            document.body.appendChild(tokenInput);
            
            window.setToken = function() {
                const token = document.getElementById('token-input').value;
                localStorage.setItem('api_token', token);
                alert('Token set successfully! Refresh the page to apply.');
            };
        };
    </script>
</body>
</html>
