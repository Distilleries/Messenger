<?php
/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 01/08/2016
 * Time: 17:34
 */

namespace Distilleries\Messenger\Helpers;

class ImageWatermark
{

    public $source;
    public $messageId;
    public $logo;

    /**
     * ImageBBSConvertor constructor.
     * @param $source
     * @param $logo
     */
    public function __construct($source, $messageId, $logo = "")
    {
        $this->source    = $source;
        $this->messageId = $messageId;
        $this->logo      = empty($logo) ? base_path('public/assets/images/small-logo.png') : $logo;

        $this->loadAndSave();
    }

    public function loadAndSave()
    {
        $content = file_get_contents($this->source);

        $path = storage_path('app/images/' . date('Y') . '/' . date('m') . '/' . date('d'));

        $rebuild = '';
        foreach (explode('/', $path) as $item) {
            $rebuild .= '/' . $item;

            if (!is_dir($rebuild)) {
                mkdir($rebuild);
            }
        }

        $extension    = explode('.', $this->source);
        $extension    = last($extension);
        $extension    = explode('?', $extension);
        $extension    = $extension[0];
        $this->source = $path . '/' . str_slug($this->messageId) . '.' . $extension;

        file_put_contents($this->source, $content);
    }


    public function convert($size = 300)
    {
        $imageMaker = app('image');

        $image  = $imageMaker->make($this->source);
        $ratio  = $size / $image->width();
        $height = $image->height() * $ratio;

        $img = $image->resize($size, $height);
        $img->insert($this->logo, 'bottom-right', 10, 10);
        $img->save($this->source);

        return env('APP_URL') . str_replace_first(storage_path(), '', $this->source);
    }

}