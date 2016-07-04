# Kunstmaan Deployer Bundle

## Installation

`composer require webtown-php/deployer-bundle`

## Usage

`bin/console webtown:deployer:init`

Requests information about the project repository and deployment server(s) then generates a `deploy.php` file. Optionally generates a `servers.yml` as well with the entered server details.

`bin/console webtown:deployer:rollback-db`

Used primarily by the `deploy.php` to rollback db migrations to the previously deployed state.

