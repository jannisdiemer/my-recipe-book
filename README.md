# My Recipe Book

A self-hosted recipe management web application built with PHP,
MySQL/MariaDB, HTML, CSS and JavaScript. It lets you collect cooking and
baking recipes, search and filter them, mark favorites, and add recipes
with photos.

> Personal side project. AI tools were used as assistance during
> development.

## Features

-   Separate overview pages for cooking recipes and baking recipes
-   Search by recipe name, ingredients and description
-   Filter by preparation time, difficulty and dietary properties
-   Sort by newest, preparation time or name
-   Mark recipes as favorites and filter to show favorites
-   Create recipes with an image, ingredients, instructions, time,
    difficulty and servings
-   Responsive layout for desktop and mobile screens
-   Recipe detail pages

Favorites are stored in the browser's `localStorage`. They are therefore
specific to the browser/device and are not shared between users or
synchronized with the database.

## Technology

-   PHP
-   MySQL or MariaDB
-   PDO for database access
-   HTML, CSS and JavaScript
-   A web server that supports PHP, such as Apache

## Requirements

-   PHP with the PDO MySQL extension enabled
-   MySQL or MariaDB
-   A PHP-capable web server (for example, Apache through XAMPP, MAMP or
    a hosting provider)
-   PHP file uploads enabled
-   A writable `images/` directory for uploaded recipe pictures
-   A modern browser

The application is **not a static website**. Opening the PHP files
directly from the filesystem or hosting them on GitHub Pages will not
run the PHP/database functionality. Use a PHP web server.

## Project structure

The main files should use these names:

``` text
my-recipe-book/
├── index.php            # Recipe overview (cooking/baking)
├── create_recipe.php    # Add a recipe and upload its image
├── view.php             # Recipe details
├── mysql.php            # Database connection settings
├── script.js            # Search, filters, sorting and favorites
├── style.css            # Page styling
├── images/
│   ├── logo.png         # Optional site icon (if referenced by the pages)
│   └── ...              # Uploaded recipe images
├── README.md
├── LICENSE
└── .gitignore           # Recommended: keep local secrets out of Git
```

Your web server may also need rewrite rules so the `/cooking` and
`/backing` paths point to `index.php`. The exact configuration depends
on the hosting provider.

**Important:** Use one canonical copy of each file. During development,
files downloaded from a chat or browser may have names such as
`index(2).php` or `script(2).js`. Rename the chosen versions to
`index.php` and `script.js` in your project. Check that the script
reference in `index.php` matches the actual filename. For example,
`<script src="/script.js"></script>` requires a file named `script.js`
at the web root.

## Installation

### 1. Clone the repository

``` bash
git clone https://github.com/jannisdiemer/my-recipe-book.git
cd my-recipe-book
```

Copy the project into the document root of your PHP web server, or
configure a virtual host to serve this directory.

### 2. Create the database

Create a MySQL/MariaDB database and a database user with only the
permissions the application needs. The current repository does not
include a database schema/migration file, so the tables must be
created/imported separately.

The PHP code expects these table names:

-   `recepies` --- cooking recipes (the spelling is intentionally
    retained to match the current code)
-   `backing` --- baking recipes

Both tables need the columns used by the application:

  --------------------------------------------------------------------------
  Column                  Suggested type          Purpose
  ----------------------- ----------------------- --------------------------
  `id`                    `INT` primary key,      Recipe ID
                          auto-increment          

  `Name`                  `VARCHAR(255)`          Recipe name

  `image`                 `VARCHAR(255)`,         Stored image filename
                          nullable                

  `duration`              `INT`, nullable         Total time in minutes

  `prep_time`             `INT`, nullable         Active preparation time in
                                                  minutes

  `ingredients`           `TEXT`, nullable        Ingredients

  `description`           `TEXT`, nullable        Instructions/description

  `difficulty`            `VARCHAR(20)`, nullable `Einfach`, `Mittel` or
                                                  `Schwer`

  `servings`              `INT`, nullable         Number of servings

  `vegetarian`            `TINYINT(1)`, default   Vegetarian flag
                          `0`                     

  `vegan`                 `TINYINT(1)`, default   Vegan flag
                          `0`                     

  `high_protein`          `TINYINT(1)`, default   High-protein flag
                          `0`                     

  `gluten_free`           `TINYINT(1)`, default   Gluten-free flag
                          `0`                     

  `lactose_free`          `TINYINT(1)`, default   Lactose-free flag
                          `0`                     
  --------------------------------------------------------------------------

Create the same schema for both tables. Match column names and
capitalization to the existing code. Back up your database before
changing an existing schema.

### 3. Configure database credentials

Open **`mysql.php`**. The current connection settings are defined near
the top:

``` php
$host = "HOST-ADRESS";
$name = "DATABASE-NAME";
$user = "USERNAME";
$passwort = "PASSWORD";
```

Replace the placeholders with your database server address, database
name, database username and database password. These values are
examples/placeholders, not working credentials.

The connection uses PDO and the `utf8mb4` character set. Keep real
credentials out of GitHub. For a public or production deployment, load
secrets from environment variables or a configuration file stored
outside the public web directory, and exclude local secret files from
Git with `.gitignore`.

### 4. Configure the access code

The current application uses a shared access code rather than a full
user-account system. The variable **`$validCode`** is currently defined
separately in these files:

-   **`index.php`** --- protects the recipe overview
-   **`create_recipe.php`** --- protects the recipe creation form
-   **`view.php`** --- protects recipe detail pages

In each file, find this line:

``` php
$validCode = "YOUR-CODE";
```

Replace `YOUR-CODE` with the same long, unique access code in all three
files. If the values differ, links between pages may stop working.

**Security warning:** This is a basic shared-code gate, not robust
authentication. The code is passed in the URL as a `code` query
parameter, so it may be stored in browser history, server logs and
copied links. Do not use this mechanism to protect sensitive data. For a
public-facing deployment, move to session-based authentication and store
secrets outside the repository. Do not commit a real access code to
GitHub.

### 5. Check image storage and PHP upload settings

The application saves uploaded images in **`images/`**. Make sure that
directory exists and is writable by the PHP/web-server user.

The form currently permits JPG, PNG and WebP images up to 15 MB, with
maximum dimensions of 6000 × 6000 pixels. PHP/server upload limits such
as `upload_max_filesize` and `post_max_size` must also allow the chosen
file size. Set permissions as narrowly as possible; do not make the
entire project directory world-writable.

### 6. Configure routes and file paths

The page code uses root-relative paths such as `/style.css`,
`/script.js` and `/images/...`, and overview links use `/cooking` and
`/backing`. This assumes the application is served from the domain root
and that the web server routes those paths correctly.

If the project is installed in a subdirectory, adjust these paths.
Configure Apache rewrite rules or equivalent routing so `/cooking` and
`/backing` reach `index.php`. Ensure the file is named exactly
`script.js` and that `index.php` references the same name.

File and directory names can be case-sensitive on Linux hosting. The
code references both `/images/` and `/Images/logo.png` in different
places; standardize the directory capitalization (recommended:
`images/`) and update the logo paths if necessary.

### 7. Open the application

Start the web server and database, then visit the local or hosted URL
configured for the project. For example, the overview routes may be:

-   `/cooking?code=YOUR-CODE`
-   `/backing?code=YOUR-CODE`

Replace the example code in the URL with your configured access code. Do
not share links containing the real code.

## Adding recipes

Open the recipe overview and select **"Rezept hinzufügen"**. Choose
whether you are adding a cooking or baking recipe, complete the form and
upload an image. The application stores recipe details in the
corresponding database table and the uploaded image in `images/`.

Only upload photos and recipe content that you have permission to use or
publish. Credit external recipe sources where appropriate.

## Known limitations and items to review

-   **No database schema is included yet.** Add a versioned SQL schema
    or migration file so new installations can create the tables
    consistently.
-   **Shared URL access code:** replace this with session-based
    authentication before exposing the application publicly.
-   **Database error handling:** the current `mysql.php` catches
    connection errors but does not stop execution cleanly. Production
    code should log the detailed exception server-side and show a
    generic error page.
-   **Duplicate filenames:** make sure only one active `index.php` and
    one active `script.js` are deployed; old variants can easily cause
    the wrong JavaScript file to load.
-   **Case-sensitive paths:** standardize `images/` versus `Images/`.
-   **Routing:** `/cooking` and `/backing` need server rewrite
    configuration.
-   **Favorites:** favorites are browser-local and can be cleared when
    browser storage is cleared.
-   **Upload security:** keep PHP and the server updated, verify
    uploaded files, limit upload sizes and permissions, and prevent
    uploaded files from being executed as scripts.
-   **Validation:** server-side validation should enforce the same upper
    and lower bounds as the HTML form, including sensible limits for
    time and servings.
-   **No user roles or ownership:** anyone who knows the shared code can
    use the same recipe-creation access.

## Security checklist before deployment

-   [ ] Replace database placeholders in `mysql.php` with working
    credentials stored securely.
-   [ ] Replace the shared access-code approach with proper
    authentication if the site is public.
-   [ ] Keep passwords, access codes and environment files out of Git.
-   [ ] Configure HTTPS.
-   [ ] Configure restrictive file permissions for the project and
    `images/`.
-   [ ] Verify upload limits and prevent script execution in the upload
    directory.
-   [ ] Turn off public PHP error display and log errors privately.
-   [ ] Back up the database and uploaded images regularly.
-   [ ] Test recipe creation, filtering, image uploads and navigation on
    the actual hosting environment.

## License

See [`LICENSE`](LICENSE). The project license permits non-commercial
use, modification, self-hosting and redistribution under its stated
conditions. Commercial use requires prior written permission from the
copyright holder. Third-party images, recipe content and other assets
may have separate rights and licenses.

## Disclaimer

The software is provided "as is", without warranty. Review and test it
before deployment, especially on a public server. The current
shared-code access mechanism should not be considered a substitute for
secure authentication.
