<?php

declare(strict_types=1);

namespace GoodMaven\BlurredImage\Concerns;

use Exception;
use Spatie\MediaLibrary\HasMedia;

/**
 * @mixin HasMedia
 */
trait HasBlurredImages
{
    public function addBlurredThumbnailConversion(): void
    {
        $conversion = $this->addMediaConversion($this->thumbnailConversionName());

        $conversion->width(config('blurred-image.thumbnail_resolution.width'));
        $conversion->height(config('blurred-image.thumbnail_resolution.height'));
        $conversion->sharpen(10);
        $conversion->nonQueued();
    }

    public function getFirstBlurredImageThumbnailUrl(string $collection = 'default', int $mediaIndex = 0): string
    {
        $this->assertBlurredImagePreconditions($collection, $mediaIndex);

        return $this->getMedia($collection)
            ->slice($mediaIndex, 1)
            ->first()
            ->getUrl($this->thumbnailConversionName());
    }

    protected function thumbnailConversionName(): string
    {
        return config('blurred-image.conversion_name');
    }

    protected function assertBlurredImagePreconditions(string $collection, int $mediaIndex): void
    {
        $media = $this->getMedia($collection)
            ->slice($mediaIndex, 1)
            ->first();

        if (! $media) {
            throw new Exception("Blurred Image exception: No media found for the \"{$collection}\" collection. Double check your collection and media, please.");
        }

        if ($media->responsive_images) {
            throw new Exception("Blurred Image exception: The found media has responsive images. There's no point of using the BlurredImage package then!");
        }

        if (! $media->hasGeneratedConversion($this->thumbnailConversionName())) {
            throw new Exception('Blurred Image exception: The found media does not have a generated blur-thumbnail. Please generate one for it using the Artisan command or the BlurredImage::generate() facade method.');
        }
    }
}
