The **Backup CLI** is a command-line interface to help you backup databases and files and send them to the cloud easily. This tool is being actively developed and more features will be added in the future. Currently we *only* have database support.

## Requirements

* PHP 5.5.9 or higher, with cURL support

## Installation

Simply use this command:

    curl -sS https://fabioneves.github.io/backup-cli/installer | php

## Updating

Whenever there is a new version of backup-cli just run this command:

    backup-cli.phar self-update

If you prefer, you can manually download the phar from [here](https://fabioneves.github.io/backup-cli/backup-cli.phar).

## Configuration

Backup CLI requires a `config` folder to live together with the phar file. This folder must contain the database (**db.yml**) and filesystem (**filesystem.yml**) configuration YAML files.

### Configuration of database connections

Backup CLI supports MySQL and PostgreSQL.

Example of **db.yml**:
```
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

### Configuration of filesystems

Currently Backup CLI supports **local** and **AWS S3** file systems. Here's an example of the configuration:

```
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

### backup:db

This command will backup a database to the specified target (filesystem). The backup file will be create on the target file system with the following structure: `database_profile/database_profile_d-m-Y_uniqid.sql.gz`.

    backup-cli.phar backup:db <connection> <target>

### restore:db

This command will restore a database backup from a filesystem/path and restore it to the specified database connection.

    backup-cli.phar restore:db <filesystem> <path> <connection>

### self-update

Pretty self-explanatory, it'll auto update backup cli whenever there's a new version.

    backup-cli.phar self-update

## Credits

* [Symfony](http://symfony.com): for the console and other wonderful components
* [Database Backup Manager](https://github.com/backup-manager/backup-manager): the package that started this and backup cli relies heavily on it
* [Padraic phar updater](https://github.com/padraic/phar-updater): made 'self-update' be possible!
* [Platform.sh](https://platform.sh): for the installer and [ManifestStrategy](https://github.com/pjcdawkins/platformsh-cli/blob/replace-phar-update/src/SelfUpdate/ManifestStrategy.php) for phar updater.
