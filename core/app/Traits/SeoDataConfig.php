<?php

namespace App\Traits;

use App\Facades\GlobalLanguage;

trait SeoDataConfig
{
    public function setMetaDataInfo($page = null, $data = [])
    {
        $dataSet = !empty($data) ? $data : [];

        if (!is_null($page)) {
            $dataSet['title'] = optional($page->metainfo)->title;
            $dataSet['description'] = optional($page->metainfo)->description;
            $dataSet['image'] = get_attachment_image_by_id(optional($page->metainfo)->image)['img_url'] ?? '';

            $dataSet['tw_image'] = get_attachment_image_by_id(optional($page->metainfo)->tw_image)['img_url'] ?? '';
            $dataSet['tw_title'] = optional($page->metainfo)->tw_title;
            $dataSet['tw_description'] = optional($page->metainfo)->tw_description;

            $dataSet['fb_image'] = get_attachment_image_by_id(optional($page->metainfo)->fb_image)['img_url'] ?? '';
            $dataSet['fb_title'] = optional($page->metainfo)->fb_title;
            $dataSet['fb_description'] = optional($page->metainfo)->fb_description;
        }

        if (request()->routeIs('landlord.homepage')) {
            $this->seo()->setTitle(empty($dataSet['title']) ? get_static_option('site_title').' - '.get_static_option('site_tag_line') : $dataSet['title']);
        }
        elseif (request()->routeIs('landlord.dynamic.page')) {
            $this->seo()->setTitle(empty($dataSet['title']) ? $page->title : $dataSet['title']);
        }
        else {
            $this->seo()->setTitle(empty($dataSet['title']) ? $page->title : $dataSet['title']);
        }

        $this->seo()->setCanonical(canonical_url());
        $this->seo()->setDescription($dataSet['description'] ?? '');
        $this->seo()->addImages($dataSet['image'] ?? '');

        $this->seo()->twitter()->setImage($dataSet['tw_image'] ?? '');
        $this->seo()->twitter()->setSite($dataSet['tw_title'] ?? '');
        $this->seo()->twitter()->setDescription($dataSet['tw_description'] ?? '');

        $this->seo()->opengraph()->addImage($dataSet['fb_image'] ?? '');
        $this->seo()->opengraph()->setTitle($dataSet['fb_title'] ?? '');
        $this->seo()->opengraph()->setDescription($dataSet['fb_description'] ?? '');
        $this->seo()->jsonLd()->addImage($dataSet['image'] ?? '');
    }

    public static function staticSetMetaDataInfo($page_post)
    {
        (new self())->setMetaDataInfo($page_post);
    }
}
