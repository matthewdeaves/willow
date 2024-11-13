<?php
namespace App\Model\Entity;

use App\Utility\SettingsManager;

trait ImageUrlTrait
{
    /**
     * Retrieves the URL for an image by removing the 'webroot/' prefix from the directory path.
     *
     * This method constructs the image URL by concatenating the directory and image name,
     * and then removes the 'webroot/' part from the path to generate a relative URL.
     *
     * @return string The relative URL of the image.
     */
    protected function _getImageUrl(): string
    {
        return str_replace('webroot/', '', $this->dir . $this->image);
    }

    /**
     * Magic method to dynamically retrieve image URLs based on field names.
     *
     * This method checks if the requested field name matches the pattern for image URLs
     * (e.g., "thumbnailImageUrl") and returns the corresponding image URL if available.
     * If the field does not match the pattern, it delegates to the parent::__get() method.
     *
     * @param string $field The name of the field being accessed.
     * @return mixed The URL of the image if the field matches the pattern, otherwise the result of parent::__get().
     */
    public function &__get(string $field): mixed
    {
        if (preg_match('/^(.+)ImageUrl$/', $field, $matches)) {
            $size = lcfirst($matches[1]);
            $imageSizes = SettingsManager::read('ImageSizes');
            if (isset($imageSizes[$size])) {
                $url = $this->getImageUrlBySize($size);
                return $url;
            }
        }
    
        return parent::__get($field);
    }
    
    /**
     * Retrieves the URL for an image of a specified size.
     *
     * This method constructs the URL for an image based on the provided size by
     * removing the 'webroot/' prefix and appending the directory, size, and image name.
     *
     * @param string $size The size of the image (e.g., 'thumbnail', 'medium').
     * @return string The constructed URL for the image.
     */
    protected function getImageUrlBySize(string $size): string
    {
        $imageSizes = SettingsManager::read('ImageSizes');

        return str_replace('webroot/', '', $this->dir . $imageSizes[$size] . DS . $this->image);
    }
}