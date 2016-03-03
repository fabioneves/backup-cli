<?php namespace BackupCli\Filesystems;

use BackupManager\Filesystems\Filesystem;
use Techyah\Flysystem\OVH\OVHAdapter;
use Techyah\Flysystem\OVH\OVHClient;
use League\Flysystem\Filesystem as Flysystem;

/**
 * Class DropboxFilesystem
 * @package BackupManager\Filesystems
 */
class OVH implements Filesystem {

    /**
     * Test fitness of visitor.
     * @param $type
     * @return bool
     */
    public function handles($type) {
        return strtolower($type) == 'ovh';
    }

    /**
     * @param array $config
     * @return Flysystem
     */
    public function get(array $config) {
        $client = new OVHClient([
          'username'  => $config['username'],
          'password'  => $config['password'],
          'tenantId'  => $config['tenant_id'],
          'container' => $config['container'],
          'region'    => $config['region']
        ]);
        return new Flysystem(new OVHAdapter($client->getContainer()));
    }
}
