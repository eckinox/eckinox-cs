# Eckinox CS: linting and coding standards

## Getting started
To add the coding standards checkers and linters to your PHP project, follow the instructions below:

1. Make sure you don't have any uncommited files in your project.  
   This package will add files to your project, so you'll likely want to commit all of these all at once.
1. Add the package to your project with Composer using the command below:
   ```bash
   composer require --dev eckinox/eckinox-cs
   ```
   The package will automatically add all of the configuration files and utility scripts you'll need to your project.
1. Follow any additional instructions provided in the <abbr title="Command Line Interface">CLI</abbr>.
1. Run the following command to install Javascript dependencies:
   ```bash
   npm install
   ```
1. Ensure scripts are executable:
   ```bash
   chmod +x DEV/**/*
   ```
1. Commit the files to your project:
   ```bash
   git add --a && git commit -m "Adds coding standards and linting checks via eckinox/eckinox-cs"
   ```
1. Enjoy!

## What's included
This package is a like a metapackage, but with a little sugar on top.  

Not only does it add other PHP dependencies via Composer, but it also:
- Adds JS dependencies.
- Creates configuration files.
- Adds shell scripts to facilitate usage of the tools.
- Adds a Git pre-commit hook.
- Adds a Github actions workflow.

Here's a bit of information about all of that.

### Tools and packages
Here are the tools that are included and configured in this package:

| Tool                                                                                                                                  | Config                           | Git-aware |
|---------------------------------------------------------------------------------------------------------------------------------------|----------------------------------|-----------|
| [EditorConfig](https://editorconfig.org/)<br>Helps maintain consistent coding styles across various editors and IDEs.                 | `.editorconfig`                  |    N/A    |
| [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)<br>A tool to automatically fix PHP Coding Standards issues               | `.php_cs.dist`                   |     ✅     |
| [PHPStan](https://phpstan.org/)<br>PHP Static Analysis Tool - discover bugs in your code without running it!                          | `phpstan.neon`                   |     ✅     |
| [PHPMD](https://phpmd.org/)<br>PHP Mess Detector                                                                                      | `.phpmd.xml`                     |     ✅     |
| [Twigcs](https://github.com/friendsoftwig/twigcs)<br>The missing checkstyle for twig!                                                 | N/A                              |     ❌     |
| [ESLint](https://eslint.org/)<br>Find and fix problems in your JavaScript code                                                        | `.eslintrc.json` `.eslintignore` |     ❌     |
| [CSS stylelint](https://stylelint.io/)<br>A mighty, modern linter that helps you avoid errors and enforce conventions in your styles. | `.stylelintrc.json`              |     ❌     |

The _Git-aware_ column indicates tools whose provided execution script (located in `DEV/cs/`) will only take into account staged files, instead of running on every file in your project every time.

### Configuration files
The packge creates configuration files for every tool it adds.

These configuration files match Eckinox's coding standards, and should not be changed manually.  
Changed files could be overwritten in later updates of `eckinox/eckinox-cs`.

### Pre-commit hook for Git
The package includes a pre-commit script that will execute all of the provided tools to check for potential errors and non-standard code.

If you don't have a pre-commit script already, the package will automatically set up this one (as a symbolic link) when you install the package.  
If you already have a pre-commit, you will have to merge the two manually.

### Github actions workflow
To ensure your project is always respecting the standards, this package adds a workflow for Github Actions that runs every included tool on your codebase.

This allows project members and maintainers to view the status of every branch and pull request, right in Github.

#### Authentication for private repositories
If you are using private Github repositories via Composer, set up the secrets for HTTP authentication as described in [php-actions/composer's documentation](https://github.com/php-actions/composer#http-basic-authentication). 

The workflow included by eckinox-cs already includes the steps necessary to retrieve and use that authentication token, if it is present.
