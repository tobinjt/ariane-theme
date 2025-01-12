<?php

declare(strict_types=1);

/* Wrap wp_get_attachment_image_src() to return an object. */
final class WPImageInfo
{
    private int $image_id = 0;
    private string $url = '';
    private int $height_int = 0;
    private string $height_str = '';
    private int $width_int = 0;
    private string $width_str = '';

    public function __construct(int $attachment_id, string $size)
    {
        // $image_info is an array of [url (str), width (int), height (int)].
        $image_info = wp_get_attachment_image_src($attachment_id, $size);
        $this->image_id = $attachment_id;
        $this->url = strval($image_info[0]);
        $this->width_int = $image_info[1];
        $this->height_int = $image_info[2];
        $this->width_str = strval($this->width_int);
        $this->height_str = strval($this->height_int);
    }

    /**
     * @return array{'src': string, 'width': int, 'height': int}
     */
    public function imageToData(): array
    {
        # This needs to stay compatible with slider.js.
        return [
            'src' => $this->url,
            'width' => $this->width_int,
            'height' => $this->height_int,
        ];
    }

    // Getter methods
    public function getImageId(): int
    {
        return $this->image_id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getHeightInt(): int
    {
        return $this->height_int;
    }

    public function getHeightStr(): string
    {
        return $this->height_str;
    }

    public function getWidthInt(): int
    {
        return $this->width_int;
    }

    public function getWidthStr(): string
    {
        return $this->width_str;
    }
}
