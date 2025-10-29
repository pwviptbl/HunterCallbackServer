# HunterCallbackServer

O HunterCallbackServer é um serviço de backend independente, escrito em PHP e MySQL, cuja única finalidade é atuar como um "servidor de escuta" passivo. Ele registra tentativas de interação (callbacks) geradas pelos payloads OAST (Out-of-Band) do ProxyHunter e fornece uma API segura para o ProxyHunter consultar esses registros.

## Features

- **Passive Listener:** Captures HTTP/HTTPS interactions from OAST payloads without interfering with the target application.
- **Secure API:** A separate, key-protected endpoint for polling interaction data.
- **Detailed Logging:** Captures IP address, protocol, headers, request path, query string, and raw body for thorough analysis.
- **Easy Deployment:** Designed for shared hosting environments with Apache (`.htaccess` included).
- **Modern Stack:** Built with PHP 8+ and Composer for dependency management.

## Requirements

- Web Server: Apache (with `mod_rewrite`) or Nginx
- PHP: Version 8.0+
- Database: MySQL 8+ or MariaDB 10.4+
- Composer for dependency management.
- A Wildcard DNS record (e.g., `*.callback.yourdomain.com`) pointing to your server's IP address.

## Installation and Setup

1.  **Clone the Repository:**
    ```bash
    git clone https://github.com/your-repo/hunter_callback_server.git
    cd hunter_callback_server
    ```

2.  **Install Dependencies:**
    Run Composer to install the required PHP libraries.
    ```bash
    # Download composer if you don't have it
    # curl -sS https://getcomposer.org/installer | php
    php composer.phar install
    ```

3.  **Configure Environment:**
    Copy the example `.env` file and fill in your specific credentials.
    ```bash
    cp .env.example .env
    ```
    Now, edit the `.env` file with your database connection details and a secure, secret API key.
    ```ini
    DB_HOST=localhost
    DB_PORT=3306
    DB_DATABASE=your_db_name
    DB_USERNAME=your_db_user
    DB_PASSWORD=your_db_password
    API_KEY=generate_a_very_strong_secret_key_here
    ```

4.  **Set up the Database:**
    Import the `schema.sql` file into your MySQL/MariaDB database to create the `interactions` table.
    ```bash
    mysql -u your_db_user -p your_db_name < schema.sql
    ```

5.  **Configure Your Web Server:**
    -   Set the `DocumentRoot` (or web root) of your domain to the `/public` directory of this project. This is crucial for security, as it prevents direct access to the `src`, `vendor`, and `.env` files.
    -   Ensure your Wildcard DNS is correctly pointing to the server.

## Alternar entre SQLite e MySQL

O projeto suporta tanto SQLite (útil para testes locais) quanto MySQL/MariaDB (produção). Basta alterar a variável `DB_DRIVER` no seu `.env` para `sqlite` ou `mysql`.

- Para testes rápidos locais (SQLite):
    - Defina `DB_DRIVER=sqlite` e `DB_DATABASE=./data/hunter_test.sqlite` no seu `.env` (ou copie de `.env.example`).
    - O arquivo `data/hunter_test.sqlite` será criado com permissões apropriadas para testes.

- Para usar MySQL/MariaDB:
    - Defina `DB_DRIVER=mysql` no `.env` e preencha as variáveis abaixo (exemplo):

```
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hunterdb
DB_USERNAME=hunteruser
DB_PASSWORD=your_db_password_here
API_KEY=your_secret_api_key_here
```

    - Importe o schema SQL para criar a tabela `interactions`:

```
mysql -u hunteruser -p hunterdb < schema.sql
```

    - Depois ajuste `API_KEY` e reinicie seu servidor.

Observação: este repositório inclui um arquivo `.env.example` com exemplos para SQLite e MySQL para facilitar a troca.

## Usage

### Face A: The Listener (`listener.php`)

The listener is the public-facing part that receives callbacks. Any request sent to a subdomain of your configured domain (e.g., `pxh-test-123.callback.yourdomain.com`) will be silently captured by `listener.php`.

-   **Interaction ID:** The server automatically extracts the interaction ID from the first part of the subdomain (e.g., `pxh-test-123`).
-   **Response:** It always returns an empty `200 OK` response to prevent the payload from hanging or indicating a successful callback to the target.

### Face B: The API (`api.php`)

The API is the private endpoint used by ProxyHunter to check for callbacks.

-   **Endpoint:** `https://callback.yourdomain.com/api.php`
-   **Authentication:** You must provide your secret API key in the `X-ProxyHunter-Key` header with every request.
    ```
    X-ProxyHunter-Key: your_secret_api_key
    ```
-   **Querying:** To check for hits, send a GET request with the interaction ID.
    ```
    GET /api.php?id=pxh-test-123
    ```
-   **Response Format:**
    -   If no hits are found:
        ```json
        {
            "hit": false,
            "data": []
        }
        ```
    -   If hits are found, it returns an array of all interactions for that ID:
        ```json
        {
            "hit": true,
            "data": [
                {
                    "protocol": "HTTPS",
                    "source_ip": "123.45.67.89",
                    "request_data": {
                        "method": "GET",
                        "path": "/some/path",
                        "query_string": "param=value",
                        "headers": { ... },
                        "body": ""
                    },
                    "timestamp": "2024-10-27 21:30:00"
                }
            ]
        }
        ```

## Security

-   **`DocumentRoot`:** It is critical to set the web server's root to the `/public` directory.
-   **API Key:** Use a long, random, and unique string for your `API_KEY`.
-   **Timing Attacks:** The API key comparison uses `hash_equals()` to protect against timing-based attacks.
-   **HTTPS:** It is highly recommended to run the server over HTTPS (using a Let's Encrypt certificate for your wildcard domain) to protect the API key in transit.
