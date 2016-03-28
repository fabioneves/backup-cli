# Backup CLI

The **Backup CLI** is a command-line interface tool to backup your databases and files. The aim of this tool is to provide a simple backup process of db and files to the cloud.

## Requirements

* PHP 5.5.9 or higher, with cURL support
* MySQL support requires `mysqldump` and `mysql` command-line binaries
* PostgreSQL support requires `pg_dump` and `psql` command-line binaries
* Gzip support requires `gzip` and `gunzip` command-line binaries
* 7zip support requires `7za` command-line binaries

## Installation

Simply use this command:

    curl -sS https://fabioneves.github.io/backup-cli/installer | php

## Updating

To update to a newer version of backup-cli just run this command:

    php backup-cli.phar self-update

If you prefer, you can manually download the phar from [here](https://fabioneves.github.io/backup-cli/backup-cli.phar).

## Configuration

Backup CLI requires a `config` folder to live together with the phar file. This folder must contain the database and storage configurations (**database.yml** and **storage.yml**).

#### Configuration of database connections

Backup CLI supports MySQL and PostgreSQL.

Example of **db.yml**:
```yaml
db_mysql_profile_name:
  type: mysql
  host: localhost
  port: 3306
  user: root
  pass: password
  database: example

db_pgsql_profile_name:
  type: postgresql
  host: localhost
  port: 5432
  user: postgres
  pass: password
  database: example
```

#### Configuration of storage systems.

Backup CLI supports various cloud/storage systems, have a look at the default storage.yml provided to setup your preferred cloud system.

Example of **storage.yml**:
```yaml
s3:
  type: AwsS3
  key:
  secret:
  region: eu-west-1
  version: latest
  bucket:
  root:

local:
  type: Local
  root: /usr/local/backups
```

## Commands

#### backup:db

This command will backup a database to the specified target (filesystem). The backup file will be create on the target file system with the following structure: `database_profile/database_profile_d-m-Y_uniqid.sql.gz`.

    php backup-cli.phar backup:db <connection> <target>

#### restore:db

This command will restore a database backup from a filesystem/path and restore it to the specified database connection.

    php backup-cli.phar restore:db <filesystem> <path> <connection>

#### self-update

Pretty self-explanatory, it'll auto update backup cli whenever there's a new version.

    php backup-cli.phar self-update

## Credits

* [Symfony](http://symfony.com): for the console and other wonderful components
* [Database Backup Manager](https://github.com/backup-manager/backup-manager): the package that inspired me to do this
* [Padraic phar updater](https://github.com/padraic/phar-updater): made 'self-update' be possible!
* [Platform.sh](https://platform.sh): for the installer and [ManifestStrategy](https://github.com/pjcdawkins/platformsh-cli/blob/replace-phar-update/src/SelfUpdate/ManifestStrategy.php) for phar updater.
