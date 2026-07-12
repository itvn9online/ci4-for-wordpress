<?php

namespace App\Controllers\Sadmin\Traits;

//
trait PostsThumbnailTrait
{
    private function processThumbnailAndContent(array &$postData): void
    {
        // Extract and validate thumbnail URL
        $thumbnailUrl = $this->extractThumbnailUrl($postData);

        if (empty($thumbnailUrl)) {
            $postData['thumbnail_url'] = '';
            return;
        }

        // Set full thumbnail URL
        $postData['thumbnail_url'] = $this->buildFullImageUrl($thumbnailUrl);

        // Auto-insert image into content if conditions are met
        $this->autoInsertImageToContent($postData);
    }

    /**
     * Extract thumbnail URL from post meta data
     * 
     * @param array $postData Post data array
     * @return string Thumbnail URL or empty string
     */
    private function extractThumbnailUrl(array $postData): string
    {
        return $postData['post_meta']['image'] ?? '';
    }

    /**
     * Build full image URL with dynamic base URL
     * 
     * @param string $imageUrl Relative image URL
     * @return string Full image URL
     */
    private function buildFullImageUrl(string $imageUrl): string
    {
        // Ensure the URL doesn't already have the base URL
        if (strpos($imageUrl, 'http') === 0) {
            return $imageUrl;
        }

        return DYNAMIC_BASE_URL . ltrim($imageUrl, '/');
    }

    /**
     * Automatically insert image into post content if conditions are met
     * 
     * @param array &$postData Post data array (passed by reference)
     * @return void
     */
    private function autoInsertImageToContent(array &$postData): void
    {
        // Check if content exists and doesn't already contain images
        if (empty($postData['post_content']) || $this->contentHasImages($postData['post_content'])) {
            return;
        }

        // Generate image HTML
        $imageHtml = $this->generateImageHtml(
            $postData['thumbnail_url'],
            $postData['post_title'] ?? 'Image'
        );

        // Prepend image to content
        $postData['post_content'] = $imageHtml . $postData['post_content'];
    }

    /**
     * Check if content already contains images
     * 
     * @param string $content Post content
     * @return bool True if content has images, false otherwise
     */
    private function contentHasImages(string $content): bool
    {
        // More comprehensive image detection
        $imagePatterns = [
            '/<img[^>]+src=/', // Standard img tags
            '/<figure[^>]*>.*?<img/', // Images in figure tags
            '/\[img[^\]]*\]/', // Custom image shortcodes
            '/!\[.*?\]\(.*?\)/' // Markdown images
        ];

        foreach ($imagePatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate proper image HTML with security considerations
     * 
     * @param string $imageUrl Image URL
     * @param string $altText Alt text for accessibility
     * @return string Generated image HTML
     */
    private function generateImageHtml(string $imageUrl, string $altText): string
    {
        // Sanitize inputs
        $imageUrl = htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8');
        // xóa bỏ `-medium.` nếu có trong $imageUrl
        $imageUrl = str_replace('-medium.', '.', $imageUrl);
        // Ensure alt text is safe and meaningful
        $altText = htmlspecialchars(strip_tags($altText), ENT_QUOTES, 'UTF-8');

        // Generate responsive image HTML
        return sprintf(
            '<p class="post-featured-image"><img src="%s" alt="%s" fetchpriority="high" decoding="async" /></p>',
            $imageUrl,
            $altText
        );
    }
}
