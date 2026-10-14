# My Recipe Book

A self-hosted recipe management web application built with PHP and MySQL. Collect, organize, search, and filter your recipes in one place.

> This is a personal side project. AI tools were used as assistance during development.

## Features

- Browse recipes from the database
- Search recipes by name and recipe content
- Filter recipes by available criteria, such as preparation time, difficulty, and dietary preferences
- Mark and filter favorites (if enabled in your local version)
- Add your own recipes, including an image, ingredients, instructions, servings, and preparation details
- Keep cooking and baking recipes organized

*The exact features depend on the version of the source code and database schema you use.*

## Tech stack

- PHP
- MySQL / MariaDB
- HTML, CSS, and JavaScript
- A web server with PHP support (for example, Apache)

## Requirements

- PHP with the MySQL PDO extension enabled
- MySQL or MariaDB
- A local web server or hosting environment that supports PHP
- A modern web browser

## Installation

1. **Clone or download this repository**

   ```bash
   git clone <REPOSITORY-URL>
   cd my-recipe-book
   ```

2. **Create a database**

   Create a new MySQL/MariaDB database for the application.

3. **Import the database schema**

   If the repository includes an SQL schema or migration file, import it into your database using phpMyAdmin or the MySQL command line. If no schema file is included, create the tables expected by the PHP code (`recepies` and `backing`) and add the columns used by the application.

4. **Configure database access**

   Open the database configuration used by the PHP files and enter your local database host, database name, username, and password.

   **Do not commit real passwords, access codes, or other secrets to GitHub.** Prefer a local configuration file that is excluded from version control.

5. **Configure the web server**

   Place the project in your web server's document root, or configure a virtual host to point to the project directory.

6. **Open the application**

   Visit the local address configured for your web server in a browser.

## Adding recipes

Use the application's recipe creation form to add your own recipes. You can include your own photos and adapt recipes to your preferences.

The repository may include a sample recipe. If a recipe was inspired by an external source, that source should be credited where appropriate. Only add images and content that you have the right to publish.

## Configuration and security

- Keep database credentials and private access codes out of the repository.
- Configure upload limits and permissions appropriately for your hosting environment.
- Validate uploaded files and use secure database queries.
- Before exposing the application to the internet, review authentication, authorization, file uploads, and error handling.
- Back up your database regularly.

## License

This project is distributed under the terms described in the [`LICENSE`](LICENSE) file. The license is intended to allow non-commercial use, modification, self-hosting, and redistribution, while requiring permission for commercial use.

## Contributing

Suggestions and improvements are welcome. Before submitting changes, please check that they work with your PHP version and database schema.

## Disclaimer

This project is provided as-is, without a guarantee that it is suitable for a particular purpose. Review and test the code before deploying it, especially on a public server.
