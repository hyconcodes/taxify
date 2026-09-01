# Taxify — Vehicle Plate Number Recognition System

A Laravel-powered law enforcement application for license plate recognition (LPR), vehicle registration management, and automated alerting. Built with Livewire 4, Flux UI, Tailwind CSS v4, and Roboflow serverless OCR.

---

## Table of Contents

1. [What This System Does](#what-this-system-does)
2. [Prerequisites (What You Need to Install)](#prerequisites-what-you-need-to-install)
3. [Step-by-Step Installation](#step-by-step-installation)
4. [Environment Configuration](#environment-configuration)
5. [Database Setup](#database-setup)
6. [Roboflow OCR Setup](#roboflow-ocr-setup)
7. [Running the Application](#running-the-application)
8. [Default Login Credentials](#default-login-credentials)
9. [How to Use the System](#how-to-use-the-system)
10. [Project Structure](#project-structure)
11. [Testing](#testing)
12. [Troubleshooting](#troubleshooting)
13. [Useful Commands](#useful-commands)

---

## What This System Does

Taxify allows law enforcement officers to:

- **Register vehicles and owners** — Store vehicle details (plate number, make, model, year, VIN, color, insurance status) linked to owner profiles (name, phone, email, address, national ID, driver licence number).
- **Capture and recognize license plates** — Upload an image or use a camera to capture a plate. The system sends the image to a cloud OCR service (Roboflow) that detects the plate text and returns an annotated image.
- **Match plates automatically** — Every captured plate is instantly checked against the registered vehicle database.
- **Generate alerts** — If a plate is recognized but does not match any registered vehicle, an alert is automatically created for investigation.
- **Look up plates** — Officers can search the registry by plate number to quickly retrieve vehicle and owner information.

---

## Prerequisites (What You Need to Install)

Before you begin, make sure the following software is installed on your computer.

### Required Software

| Software | Minimum Version | How to Check | Download |
|----------|----------------|--------------|----------|
| **PHP** | 8.3 or newer | Open a terminal and run `php -v` | [php.net/downloads](https://www.php.net/downloads/) |
| **Composer** | 2.x | Run `composer -V` | [getcomposer.org](https://getcomposer.org/) |
| **Node.js** | 18 or newer | Run `node -v` | [nodejs.org](https://nodejs.org/) |
| **npm** | (comes with Node.js) | Run `npm -v` | (installed with Node.js) |
| **Git** | Any recent version | Run `git --version` | [git-scm.com](https://git-scm.com/) |
| **A database** | MySQL 8+ or SQLite | See below | See below |

### Database Options

You have two choices:

**Option A — SQLite (Recommended for Beginners)**
SQLite requires no separate server installation. Laravel will create a file-based database for you automatically. This is the easiest option for getting started.

**Option B — MySQL**
If you prefer MySQL, make sure you have MySQL 8 installed and running. You will need to create a database before proceeding.

### How to Install PHP (Windows)

1. Go to [https://windows.php.net/download/](https://windows.php.net/download/)
2. Download the latest **VS16 x64 Non Thread Safe** ZIP file (e.g., `php-8.4.x-nts-Win32-vs16-x64.zip`)
3. Extract the ZIP to `C:\php`
4. Add `C:\php` to your system PATH:
   - Press `Win + S`, search for "Environment Variables"
   - Click "Edit the system environment variables"
   - Click "Environment Variables"
   - Under "System variables", find `Path`, click "Edit"
   - Click "New" and type `C:\php`
   - Click "OK" on all dialogs
5. Open a **new** terminal and run `php -v` to confirm

### How to Install Composer (Windows)

1. Go to [https://getcomposer.org/Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe)
2. Run the installer
3. During installation, select your PHP executable (e.g., `C:\php\php.exe`)
4. Open a **new** terminal and run `composer -V` to confirm

### How to Install Node.js (Windows)

1. Go to [https://nodejs.org/](https://nodejs.org/)
2. Download the **LTS** version
3. Run the installer (accept all defaults)
4. Open a **new** terminal and run `node -v` and `npm -v` to confirm

### How to Install Git (Windows)

1. Go to [https://git-scm.com/download/win](https://git-scm.com/download/win)
2. Download and run the installer (accept all defaults)
3. Open a **new** terminal and run `git --version` to confirm

---

## Step-by-Step Installation

Open a terminal (Command Prompt, PowerShell, or Windows Terminal) and run the following commands one by one.

### Step 1: Clone or Download the Project

```bash
git clone <your-repository-url> taxify
cd taxify
```

If you do not have Git or the repository URL, you can also download the project as a ZIP file from GitHub and extract it. Then open a terminal in the extracted `taxify` folder.

### Step 2: Install PHP Dependencies

```bash
composer install
```

This installs all the PHP packages the project needs. It may take a few minutes.

### Step 3: Install JavaScript Dependencies

```bash
npm install
```

This installs the frontend packages (Tailwind CSS, Vite, Livewire, etc.).

### Step 4: Build Frontend Assets

```bash
npm run build
```

This compiles the CSS and JavaScript files. You should see a success message.

### Step 5: Create the Environment File

```bash
cp .env.example .env
```

On Windows Command Prompt (if `cp` does not work):

```bash
copy .env.example .env
```

### Step 6: Generate Application Key

```bash
php artisan key:generate
```

This creates a unique encryption key for your application.

### Step 7: Set Up the Database

**If using SQLite (default, recommended):**

```bash
type nul > database\database.sqlite
```

On PowerShell:

```bash
New-Item -Path "database\database.sqlite" -ItemType File -Force
```

**If using MySQL:**

1. Create a database named `taxify` in MySQL
2. Open the `.env` file in a text editor
3. Change the database lines to:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=taxify
DB_USERNAME=root
DB_PASSWORD=your_password
```

Replace `your_password` with your MySQL root password.

### Step 8: Run Database Migrations

```bash
php artisan migrate
```

When asked "Would you like to run all pending database migrations?", type `yes` and press Enter.

### Step 9: Seed the Database

```bash
php artisan db:seed
```

This creates:
- An admin user account
- 15 sample vehicle owners with linked vehicles

### Step 10: Configure OCR (Roboflow)

You need a Roboflow account and API key for plate recognition to work. See [Roboflow OCR Setup](#roboflow-ocr-setup) below for detailed instructions.

Once you have your API key and endpoint, open `.env` and set:

```
ROBOFLOW_API_KEY=your_api_key_here
ROBOFLOW_ENDPOINT=https://serverless.roboflow.com/your-workspace/workflows/your-workflow
```

### Step 11: Start the Application

```bash
composer run dev
```

This starts three services simultaneously:
- **Laravel server** at http://localhost:8000
- **Queue worker** (for background jobs)
- **Vite dev server** (for frontend hot-reload)

You should see output like:

```
server  | INFO  Server running on [http://127.0.0.1:8000].
queue   | Processing jobs from [default]...
vite    | VITE v8.x.x  ready in xxx ms
```

### Step 12: Open in Browser

Go to http://localhost:8000 in your web browser. You will see the welcome page.

---

## Environment Configuration

The `.env` file controls all settings. Here are the key variables:

```env
# Application
APP_NAME=Taxify
APP_ENV=local
APP_KEY=                    # Auto-generated in Step 6
APP_DEBUG=true
APP_URL=http://localhost

# Database (SQLite by default)
DB_CONNECTION=sqlite

# OCR Settings (REQUIRED for plate recognition)
ROBOFLOW_API_KEY=           # Your Roboflow API key
ROBOFLOW_ENDPOINT=https://serverless.roboflow.com/your-workspace/workflows/your-workflow
OCR_TIMEOUT=120             # Max wait time for OCR response (seconds)
OCR_CONNECT_TIMEOUT=10      # Max time to establish connection (seconds)

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

---

## Database Setup

### Tables Created by Migrations

The system creates the following tables:

| Table | Purpose |
|-------|---------|
| `users` | Officer accounts with authentication data |
| `cache` | Application caching |
| `jobs` | Queue job tracking |
| `personal_access_tokens` | API tokens |
| `sessions` | User session storage |
| `passkeys` | WebAuthn passkey credentials |
| `vehicle_owners` | Owner profiles (name, phone, email, address, national ID, state of origin, driver licence) |
| `vehicles` | Registered vehicles (plate number, make, model, year, VIN, registration date, color, type, insurance status) |
| `plate_captures` | Every plate capture attempt (plate text, image, annotated image, confidence, match status) |
| `plate_alerts` | Alerts generated for unmatched plates |

### Running Migrations

```bash
php artisan migrate          # Run pending migrations
php artisan migrate:status   # Check migration status
php artisan migrate:reset    # Roll back all migrations (WARNING: destroys data)
```

---

## Roboflow OCR Setup

The plate recognition feature requires a Roboflow account with a serverless workflow.

### Step 1: Create a Roboflow Account

1. Go to [https://roboflow.com/](https://roboflow.com/)
2. Click "Sign Up" and create a free account
3. Verify your email address

### Step 2: Create a Workspace

1. After logging in, you will be prompted to create a workspace
2. Give it a name (e.g., `my-workspace`)
3. Select "Personal" for the plan type

### Step 3: Create a Workflow

1. In your workspace, click "Workflows" in the left sidebar
2. Click "Create Workflow"
3. Add the following blocks:
   - **Input** block (accepts an image)
   - **Roboflow Model** block (select a license plate detection model, or use a pre-trained one from the Roboflow Universe)
   - **OCR** block (to read the plate text)
   - **Output** block (returns the annotated image and plate text)
4. Save and deploy the workflow
5. Note the workflow URL — it will look like:
   ```
   https://serverless.roboflow.com/YOUR-WORKSPACE/workflows/YOUR-WORKFLOW
   ```

### Step 4: Get Your API Key

1. Go to your Roboflow account settings
2. Find "API Key" or "Private API Key"
3. Copy the key

### Step 5: Configure in `.env`

```env
ROBOFLOW_API_KEY=your_copied_api_key
ROBOFLOW_ENDPOINT=https://serverless.roboflow.com/YOUR-WORKSPACE/workflows/YOUR-WORKFLOW
```

### What Happens Without Roboflow

If you do not configure Roboflow, you can still use the system for:
- Registering vehicles and owners
- Manual plate entry and matching
- Viewing alerts

The OCR capture feature will not work until Roboflow is configured.

---

## Running the Application

### Development Mode (Recommended)

```bash
composer run dev
```

This starts all services (web server, queue worker, Vite) in one command. Access the app at http://localhost:8000.

### Manual Start (Individual Terminals)

If you prefer to run each service separately, open three terminal windows:

**Terminal 1 — Web Server:**
```bash
php artisan serve
```

**Terminal 2 — Queue Worker:**
```bash
php artisan queue:work
```

**Terminal 3 — Frontend Assets:**
```bash
npm run dev
```

### Stopping the Application

Press `Ctrl + C` in the terminal where `composer run dev` is running.

---

## Default Login Credentials

After running the database seeder, you can log in with:

| Field | Value |
|-------|-------|
| **Email** | `admin@taxify.com` |
| **Password** | `password` |

> **Important:** Change this password immediately in a production environment.

### Creating Additional Users

Registration is disabled for security (this is a law enforcement system). To create new user accounts, use Laravel Tinker:

```bash
php artisan tinker
```

Then run:

```php
App\Models\User::factory()->create([
    'name' => 'Officer Name',
    'email' => 'officer@example.com',
    'password' => bcrypt('their_password'),
    'email_verified_at' => now(),
]);
```

Type `exit` to leave Tinker.

---

## How to Use the System

### 1. Dashboard

After logging in, you land on the dashboard showing:
- Total registered vehicles
- Total plate captures
- Active alerts count
- Recent capture history

### 2. Register a Vehicle

1. Click **Vehicles** in the sidebar
2. Click **Register Vehicle**
3. Fill in the **Vehicle Information** section:
   - Plate number (e.g., `ABC123`)
   - VIN (Vehicle Identification Number)
   - Type (e.g., Sedan, SUV, Truck)
   - Make (e.g., Toyota)
   - Model (e.g., Camry)
   - Year
   - Registration date
   - Color
   - Insurance status (Valid or Expired)
4. Fill in the **Owner Information** section:
   - Full name
   - Phone number
   - Email (optional)
   - National ID (optional)
   - State of origin
   - Driver licence number
   - Address
5. Click **Register Vehicle**

### 3. Capture a Plate (OCR)

1. Click **Plate Captures** in the sidebar
2. Under **OCR Capture**, choose one of:
   - **Drag and drop** an image onto the upload zone
   - **Click** the upload zone to select a file
   - **Use Camera** to take a photo with your device camera
3. Click **Capture & Recognize**
4. Wait for the OCR processing (a spinner will appear)
5. The result shows:
   - The recognized plate number
   - Confidence score
   - Annotated image with detected regions highlighted
   - Match status (Matched or Unmatched)

### 4. Manual Plate Entry

1. On the Plate Captures page, find the **Manual Entry** card
2. Type a plate number (e.g., `ABC123`)
3. Click **Match Plate**
4. The system checks if the plate is registered and shows the result

### 5. View Capture Details

1. On the Plate Captures page, click any row in the **Capture History** table
2. A modal opens showing:
   - The annotated image
   - Plate number and confidence
   - If matched: vehicle details (make, model, year, color) and owner details
   - If unmatched: a warning that no match was found and an alert was generated

### 6. Manage Alerts

1. Click **Alerts** in the sidebar
2. View all alerts (unmatched plates)
3. Filter by status: All, Alert, or Cleared
4. Click **Clear** on an alert to mark it as investigated

### 7. Look Up a Plate

1. Click **Plate Lookup** in the sidebar
2. Enter a plate number
3. Click **Search**
4. If registered, view the vehicle and owner details
5. If not found, see a "Not Found" message

---

## Project Structure

```
taxify/
├── app/
│   ├── Actions/
│   │   └── Plate/
│   │       └── MatchPlateAction.php    # Plate matching logic
│   ├── Enums/
│   │   └── VehicleInsuranceStatus.php  # Valid/Expired enum
│   ├── Http/
│   │   └── Controllers/
│   │       └── PlateImageController.php # Serves captured images
│   ├── Models/
│   │   ├── PlateAlert.php
│   │   ├── PlateCapture.php
│   │   ├── User.php
│   │   ├── Vehicle.php
│   │   └── VehicleOwner.php
│   ├── Services/
│   │   └── OcrService.php             # Roboflow API communication
│   └── Providers/
├── config/
│   ├── fortify.php                     # Auth configuration
│   ├── services.php                    # Roboflow API key
│   └── taxify.php                      # OCR settings
├── database/
│   ├── factories/                      # Model factories for testing
│   ├── migrations/                     # Database schema
│   └── seeders/
│       └── DatabaseSeeder.php          # Creates admin + sample data
├── resources/
│   ├── css/
│   │   └── app.css                     # Tailwind CSS + theme config
│   ├── views/
│   │   ├── components/                 # Reusable UI components
│   │   ├── flux/                       # Flux UI overrides (icons, navlist)
│   │   ├── layouts/
│   │   │   ├── app/sidebar.blade.php   # Main app layout with sidebar
│   │   │   └── auth/simple.blade.php   # Auth page layout
│   │   ├── pages/
│   │   │   ├── auth/                   # Login, register, password pages
│   │   │   ├── captures/               # Plate capture page
│   │   │   ├── vehicles/               # Vehicle list and registration
│   │   │   ├── alerts/                 # Alert management
│   │   │   ├── settings/               # Profile, security, appearance
│   │   │   └── plate-lookup.blade.php  # Plate search page
│   │   └── partials/                   # Head, settings heading
│   └── js/
│       ├── app.js
│       └── passkeys.js
├── routes/
│   ├── web.php                         # All web routes
│   └── settings.php                    # Settings routes
├── tests/
│   ├── Feature/                        # Feature tests
│   └── Unit/                           # Unit tests
├── .env.example                        # Environment template
├── composer.json                       # PHP dependencies
├── package.json                        # JavaScript dependencies
├── phpunit.xml                         # Test configuration
├── pint.json                           # Code style config
└── vite.config.js                      # Build configuration
```

---

## Testing

### Run All Tests

```bash
php artisan test --compact
```

### Run Specific Tests

```bash
# OCR-related tests
php artisan test --compact --filter=OcrCapture
php artisan test --compact --filter=OcrService

# Plate matching tests
php artisan test --compact --filter=MatchPlate

# Vehicle registration tests
php artisan test --compact --filter=VehicleRegistration

# Captures page tests
php artisan test --compact --filter=CapturesPage

# Run a specific test file
php artisan test --compact tests/Feature/OcrCaptureTest.php
```

### Code Formatting

Format PHP code with Laravel Pint:

```bash
vendor/bin/pint --dirty --format agent
```

Check formatting without changing files:

```bash
vendor/bin/pint --test --format agent
```

### Static Analysis

Run PHPStan for type checking:

```bash
vendor/bin/phpstan analyse
```

---

## Troubleshooting

### "ViteManifestNotFoundException" or Missing CSS

If the page looks unstyled or you see a Vite error:

```bash
npm run build
```

Or start the dev server:

```bash
npm run dev
```

### "No such table: users" Error in Tests

This is a known issue. The test suite has `RefreshDatabase` commented out in `tests/Pest.php`. The Taxify-specific tests (OcrCapture, VehicleRegistration, etc.) work correctly. The 25 pre-existing failures are in the default Fortify/Auth/Settings tests and are not caused by Taxify code.

### "Class 'App\Services\OcrService' not found"

Run:

```bash
composer dump-autoload
```

### Port 8000 Already in Use

Start the server on a different port:

```bash
php artisan serve --port=8080
```

Then visit http://localhost:8080 instead.

### npm Install Fails

Try clearing the cache:

```bash
rm -rf node_modules
npm cache clean --force
npm install
```

### Database Migration Errors

Reset and re-run migrations:

```bash
php artisan migrate:fresh --seed
```

**Warning:** This destroys all data and re-creates the database.

### Roboflow OCR Timeout

If OCR takes too long, check your internet connection. The default timeout is 120 seconds. You can increase it in `.env`:

```
OCR_TIMEOUT=180
```

### Images Not Displaying

Make sure the storage symlink exists:

```bash
php artisan storage:link
```

---

## Useful Commands

| Command | What It Does |
|---------|-------------|
| `composer run dev` | Start all development services |
| `php artisan serve` | Start the web server only |
| `php artisan migrate` | Run database migrations |
| `php artisan migrate:fresh --seed` | Reset database and re-seed |
| `php artisan db:seed` | Run database seeders |
| `php artisan tinker` | Open interactive PHP shell |
| `php artisan route:list` | Show all registered routes |
| `php artisan test --compact` | Run all tests |
| `php artisan config:clear` | Clear configuration cache |
| `php artisan cache:clear` | Clear application cache |
| `php artisan view:clear` | Clear compiled views |
| `npm run build` | Build frontend assets for production |
| `npm run dev` | Start Vite dev server with hot-reload |
| `vendor/bin/pint --dirty --format agent` | Format PHP code |
| `vendor/bin/phpstan analyse` | Run static analysis |

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend Framework | Laravel 13 |
| Frontend | Livewire 4 (Single File Components), Flux UI 2, Tailwind CSS v4, Alpine.js |
| OCR Engine | Roboflow Serverless Workflow API |
| Database | MySQL 8 (production) / SQLite (development) |
| Authentication | Laravel Fortify 1 (login, email verification, 2FA, passkeys) |
| Testing | Pest 4 / PHPUnit 12 |
| Code Style | Laravel Pint |
| Static Analysis | Larastan (PHPStan) |
| Build Tool | Vite 8 |
| PHP Version | 8.3 or newer |

---

## License

This project is proprietary software intended for law enforcement use.
