<?php

namespace TorqNorth\PimcoreHelpersBundle\Tests\Service\Repository;

use InvalidArgumentException;
use Pimcore\Model\Asset;
use Pimcore\Model\Asset\Service;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use TorqNorth\PimcoreHelpersBundle\Dto\AssetMetadata;
use TorqNorth\PimcoreHelpersBundle\Service\Repository\AssetRepository;
use TorqNorth\PimcoreHelpersBundle\Tests\Base\PimcoreTestBootstrapped;

class AssetRepositoryTest extends PimcoreTestBootstrapped
{
    private AssetRepository $repository;
    private Asset\Folder $folder;
    private array $temporaryFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new AssetRepository();
        $this->folder = Service::createFolderByPath(
            '/pimcore-helpers-tests/asset-repository-' . bin2hex(random_bytes(8))
        );
    }

    protected function tearDown(): void
    {
        try {
            $this->folder->delete();
            foreach ($this->temporaryFiles as $path) {
                @unlink($path);
            }
        } finally {
            parent::tearDown();
        }
    }

    public function testCreatesAssetWithoutMetadata(): void
    {
        $asset = $this->repository->createAsset($this->createUpload(), $this->folder);

        $persisted = Asset::getById($asset->getId());
        self::assertNotNull($persisted);
        self::assertSame([], $persisted->getMetadata());
    }

    public function testPersistsCheckboxAndLocalizedMetadata(): void
    {
        $asset = $this->repository->createAsset(
            $this->createUpload(),
            $this->folder,
            metadata: [
                new AssetMetadata('Legal', 'checkbox', true),
                new AssetMetadata('Finance', 'checkbox', false),
                new AssetMetadata('title', 'input', 'Example document', 'en'),
            ],
        );

        $persisted = Asset::getById($asset->getId());
        self::assertNotNull($persisted);
        self::assertSame(true, $persisted->getMetadata('Legal'));
        self::assertSame(false, $persisted->getMetadata('Finance'));
        self::assertSame('Example document', $persisted->getMetadata('title', 'en', true));
    }

    public function testAcceptsGeneratorOfMetadata(): void
    {
        $metadata = (static function (): \Generator {
            yield new AssetMetadata('Legal', 'checkbox', true);
        })();

        $asset = $this->repository->createAsset(
            $this->createUpload(),
            $this->folder,
            metadata: $metadata,
        );

        self::assertSame(true, Asset::getById($asset->getId())->getMetadata('Legal'));
    }

    public function testInvalidMetadataDoesNotDeleteExistingAssetDuringOverwrite(): void
    {
        $original = $this->repository->createAsset($this->createUpload(), $this->folder);
        $originalId = $original->getId();
        $path = $original->getFullPath();

        try {
            $this->repository->createAsset(
                $this->createUpload(),
                $this->folder,
                overwrite: true,
                metadata: [new AssetMetadata('Legal', 'checkbox', true), new \stdClass()],
            );
            self::fail('Expected invalid asset metadata to be rejected.');
        } catch (InvalidArgumentException) {
            self::assertSame($originalId, Asset::getByPath($path)?->getId());
        }
    }

    private function createUpload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'pimcore-asset-test-');
        file_put_contents($path, 'example asset content');
        $this->temporaryFiles[] = $path;

        return new UploadedFile($path, 'example.txt', 'text/plain', null, true);
    }
}
