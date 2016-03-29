<?php
namespace BackupCli\Services;

use BackupCli\Config;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Yaml\Yaml;

class Profile extends Service
{

    public static function newDatabase($database, $username, $password, $profile_name = null, $type = 'mysql', $server = 'localhost', $port = 3306)
    {
        // Profile data.
        $db_profile = [
          $profile_name => [
            'type'     => $type,
            'host'     => $server,
            'port'     => $port,
            'user'     => $username,
            'pass'     => $password,
            'database' => $database,
          ],
        ];

        try {

            // Check if the profile name is unique.
            $databases = Config::getFile('database');
            if (!empty($databases[$profile_name])) {
                throw new \Exception("Profile $profile_name already exists.");
            }

            // Check if database type is valid.
            if (($type != 'mysql') && ($type != 'postgresql')) {
                throw new \Exception("Invalid database type: $type");
            }

            // Add profile to the database file.
            file_put_contents('config/database.yml', "\n" . Yaml::dump($db_profile), FILE_APPEND);

        } catch (\Exception $e) {
            return parent::consoleFormattedError($e->getMessage());
        }

        return "  Database profile created <fg=green>successfully</>!\n";
    }

}