# Play Amtgard!

A simple web tool to help users find Amtgard parks and events near them or a specific location. It utilizes the ORK (Online Record Keeper) API to fetch and display data.

## Features

-   **Find Parks**: Search for Amtgard parks within a 100-mile radius of a location or your current position.
-   **Find Events**: (Currently commented out in code) Search for events within a 500-mile radius.
-   **Geolocation**: Uses browser geolocation or manual address entry ("Look Near").
-   **Responsive Design**: Built with Materialize CSS for mobile compatibility.

## Developer Guide

### Prerequisites

-   [Docker](https://docs.docker.com/get-docker/)
-   [Docker Compose](https://docs.docker.com/compose/install/)

### Getting Started

1.  **Clone the repository** (if you haven't already).
2.  **Start the development server**:

    Run the following command in the project root:

    ```bash
    docker compose up
    ```

    To run in detached mode (background):

    ```bash
    docker compose up -d
    ```

3.  **Access the application**:

    Open your browser and navigate to:

    [http://localhost:48080](http://localhost:48080)

4.  **Stopping the server**:

    Press `Ctrl+C` if running in the foreground, or run:

    ```bash
    docker compose down
    ```

### Project Structure

-   `index.php`: The main application logic and view.
-   `content.html`: (Appears to be a static content file, usage to be confirmed).
-   `docker-compose.yml`: Docker services configuration (Nginx + PHP-FPM).
-   `nginx.conf`: Nginx server configuration.
