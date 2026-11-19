<?php

namespace TorqNorth\PimcoreHelpersBundle\Dto;

/**
 * Value object representing an entry in a Pimcore asset's metadata.
 */
readonly class AssetMetadata
{
    public function __construct(
        public string $name,
        public string $type,
        public mixed $data,
        public ?string $language = null,
    ) {}
}
