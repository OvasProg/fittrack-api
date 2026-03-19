# 🚀 FitTrack API: Frontend Local Setup Guide

Welcome to the FitTrack backend repository! Follow these steps to get the local API up and running so you can start integrating the UI.

### Prerequisites

Before you begin, ensure you have the following installed on your machine:

- **PHP** (v8.1 or higher)
- **Composer** (PHP package manager)

---

### Step 1: Initial Setup

Clone this repository to your local machine and install the required backend dependencies.

```bash
git clone <your-repo-url>
cd fittrack-api
composer install
```

### Step 2: Environment Configuration

Laravel requires an environment file to store database paths and security configuration.

1. Duplicate the example environment file:

```bash
  cp .env.example .env
```

2. Generate the unique application encryption key:

```bash
  php artisan key:generate
```

### Step 3: Frontend Connection & Sanctum Cookies

To ensure authentication cookies work locally, you need to tell the backend where your frontend is running.

Open the new `.env` file in your code editor and find/update the following variables. _(Assuming your local frontend runs on port `5500`—if you use a different port like `3000` or `5173`, change these values to match!)_

```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5500

# Allow Sanctum cookies to be sent to this domain
SANCTUM_STATEFUL_DOMAINS=localhost:5500
```

### Step 4: Database Setup & Seeding

We are using a lightweight SQLite database for local development, so you do not need to install MySQL or Postgres.

1. Create the empty SQLite file:

    ```bash
    # On Mac/Linux:
    touch database/database.sqlite

    # On Windows (PowerShell):
    New-Item database\database.sqlite -ItemType File
    ```

2. Run the migrations to build the tables, and run the seeders to populate the database with fake users, exercises, and trainings so you have data to work with:
    ```bash
    php artisan migrate:fresh --seed
    ```

### Step 5: Start the Server

Start the local PHP development server:

```bash
php artisan serve
```

By default, the API will now be running at **`http://localhost:8000`**.

**Changing the Port:**
If port `8000` is already in use on your machine, you can specify a different one:

```bash
php artisan serve --port=8081
```

_(Note: If you change the backend port, you must update your frontend's global fetch/Axios base URL to match!)_

---

### 🧪 Testing the Connection

To verify the API is running correctly, you can make a simple `GET` request in your browser or frontend code to:
`http://localhost:8000/api/user`
