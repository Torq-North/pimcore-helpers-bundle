# Pimcore Helpers Bundle
A collection of several simple but commonly used, mockable service wrappers

- `AssetRepository`: Service wrapper for fetching, saving and deleting assets
  - `createAsset(UploadedFile $upload, ElementInterface $parent, bool $overwrite = false, iterable $metadata = [])`: Creates and saves an asset from an UploadedFile, optionally including typed `AssetMetadata` values in its initial save.
- `FolderRepository`: Service wrappers for either getting or getting/creating the three major folder types in pimcore
- `DataObjectRepository`: Abstract service wrapper for saving/deleting data objects
- `ArrayUtils`: A collection of helper functions for deep parsing arrays as well as finding items within an array
  - `get(string|string[] $key, array $array)`: Navigates either one or a series of nested keys within an array and returns either the target value or null if _any_ part of the chain does not exist. Several casting variations also exist which return specific types or null:
    - `getDate()`
    - `getInt()`
    - `getFloat()`
  - `findIndex(callable $callable, array $array)` and `findInArray(callable $callable, array $array)` functions, which find an item in an array based on a given function

### Creating assets with metadata

`createAsset()` accepts any iterable containing `TorqNorth\PimcoreHelpersBundle\Dto\AssetMetadata` objects (including arrays, generators, and `ArrayIterator`). Metadata is added before the asset's initial save, so callers do not need to save the returned asset again just to persist metadata.

```php
use TorqNorth\PimcoreHelpersBundle\Dto\AssetMetadata;

$asset = $assetRepository->createAsset(
    $uploadedFile,
    $folder,
    metadata: [
        new AssetMetadata('Legal', 'checkbox', true),
        new AssetMetadata('title', 'input', 'Example document', 'en'),
    ],
);
```

Existing calls without metadata continue to work. The `overwrite` argument remains the third positional argument.

# License

This bundle is licensed under the Pimcore Open Core License (POCL)
and is intended for use with Pimcore Platform 2025.1 and newer.

See LICENSE.md for full license text.

