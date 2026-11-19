<?php

namespace TorqNorth\PimcoreHelpersBundle\Service\Repository;

use Pimcore\Model\Asset;
use Pimcore\Model\Asset\Service;
use Pimcore\Model\Element\ElementInterface;
use TorqNorth\PimcoreHelpersBundle\Dto\AssetMetadata;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AssetRepository
{
    public function getById(int $id): ?Asset
    {
        return Asset::getById($id);
    }

    public function getByPath(string $path)
    {
        return Asset::getByPath($path);
    }

    public function save(Asset $asset): Asset
    {
        return $asset->save();
    }

    public function delete(Asset $asset)
    {
        $asset->delete();
    }

    public function deleteIfExists(string $path)
    {
        $target = Asset::getByPath($path);

        if ($target) {
            $target->delete();
        }
    }

    /**
     * @param iterable<AssetMetadata> $metadata
     */
    public function createAsset(
        UploadedFile $upload,
        ElementInterface $parent,
        bool $overwrite = false,
        iterable $metadata = [],
    ): Asset
    {
        $asset = new Asset();
        $asset->setKey($upload->getClientOriginalName());
        $asset->setParent($parent);
        $asset->setData(file_get_contents($upload->getPathname()));

        // Apply all metadata before overwriting an existing asset or saving the new one.
        foreach ($metadata as $item) {
            if (!$item instanceof AssetMetadata) {
                throw new \InvalidArgumentException(sprintf(
                    'Asset metadata must contain %s objects; received %s.',
                    AssetMetadata::class,
                    get_debug_type($item),
                ));
            }

            $asset->addMetadata($item->name, $item->type, $item->data, $item->language);
        }

        if ($overwrite) {
            $this->deleteIfExists($parent->getFullPath() . "/" . $asset->getKey());
        } else {
            $asset->setKey(Service::getUniqueKey($asset));
        }

        $asset->save();

        return $asset;
    }
}
