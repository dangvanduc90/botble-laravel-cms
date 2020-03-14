<?php

namespace Botble\Media\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface MediaFileInterface extends RepositoryInterface
{
    /**
     * @return int
     */
    public function getSpaceUsed();

    /**
     * @return int
     */
    public function getSpaceLeft();

    /**
     * @return int
     */
    public function getQuota();

    /**
     * @return int
     */
    public function getPercentageUsed();

    /**
     * @param string $name
     * @param string $folder
     */
    public function createName($name, $folder);

    /**
     * @param string $name
     * @param string $extension
     * @param string $folderPath
     */
    public function createSlug($name, $extension, $folderPath);

    /**
     * @param int $folderId
     * @param array $params
     * @param bool $withFolders
     * @param array $folderParams
     * @return mixed
     */
    public function getFilesByFolderId($folderId, array $params = [], $withFolders = true, $folderParams = []);

    /**
     * @param int $folderId
     * @param array $params
     * @param bool $withFolders
     * @param array $folderParams
     * @return mixed
     */
    public function getTrashed($folderId, array $params = [], $withFolders = true, $folderParams = []);

    /**
     * @return bool
     */
    public function emptyTrash();
}
