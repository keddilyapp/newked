<?php

namespace App\Enums;

enum SlugMorphableTypeEnum: string
{
    case PAGE = 'App\Models\Page';
    case BLOG = 'Modules\Blog\Entities\Blog';
    case BLOG_CATEGORY = 'Modules\Blog\Entities\BlogCategory';
    case BLOG_TAG = 'Modules\Blog\Entities\BlogTag';
    case PRODUCT_CATEGORY = 'Modules\Attributes\Entities\Category';
    case PRODUCT_SUBCATEGORY = 'Modules\Attributes\Entities\SubCategory';
    case PRODUCT_CHILDCATEGORY = 'Modules\Attributes\Entities\ChildCategory';
    case PRODUCT_BRAND = 'Modules\Attributes\Entities\Brand';
    case PRODUCT_COLOR = 'Modules\Attributes\Entities\Color';
    case PRODUCT_SIZE = 'Modules\Attributes\Entities\Size';
    case PRODUCT = 'Modules\Product\Entities\Product';
    case PRODUCT_DIGITAL_CATEGORY = 'Modules\DigitalProduct\Entities\DigitalCategories';
    case PRODUCT_DIGITAL_SUBCATEGORY = 'Modules\DigitalProduct\Entities\DigitalSubCategories';
    case PRODUCT_DIGITAL_CHILDCATEGORY = 'Modules\DigitalProduct\Entities\DigitalChildCategories';
    case PRODUCT_DIGITAL_AUTHOR = 'Modules\DigitalProduct\Entities\DigitalAuthor';
    case PRODUCT_DIGITAL_TAG = 'Modules\DigitalProduct\Entities\DigitalProductTags';
    case PRODUCT_DIGITAL_LANGUAGE = 'Modules\DigitalProduct\Entities\DigitalLanguage';
    case PRODUCT_DIGITAL_TYPE = 'Modules\DigitalProduct\Entities\DigitalProductType';
    case PRODUCT_DIGITAL_PRODUCT = 'Modules\DigitalProduct\Entities\DigitalProduct';
}
