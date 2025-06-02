<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Modules\Attributes\Entities\Category;
use Modules\Attributes\Entities\ChildCategory;
use Modules\Attributes\Entities\SubCategory;
use Modules\Blog\Entities\Blog;
use Modules\Blog\Entities\BlogCategory;
use Modules\Blog\Entities\BlogTag;
use Modules\DigitalProduct\Entities\DigitalAuthor;
use Modules\DigitalProduct\Entities\DigitalCategories;
use Modules\DigitalProduct\Entities\DigitalChildCategories;
use Modules\DigitalProduct\Entities\DigitalLanguage;
use Modules\DigitalProduct\Entities\DigitalProduct;
use Modules\DigitalProduct\Entities\DigitalProductTags;
use Modules\DigitalProduct\Entities\DigitalProductType;
use Modules\DigitalProduct\Entities\DigitalSubCategories;
use Modules\Product\Entities\Product;

class SlugModifierSeeder extends Seeder
{
    const slugModifiedKey = 'slug-modified';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pageFlag = false;
        $blogFlag = false;
        $blogCategoryFlag = false;
        $blogTagFlag = false;
        $categoryFlag = false;
        $subCategoryFlag = false;
        $childCategoryFlag = false;
        $productFlag = false;
        $digitalCategoriesFlag = false;
        $digitalSubCategoriesFlag = false;
        $digitalChildCategoriesFlag = false;
        $digitalAuthorFlag = false;
        $digitalProductTypeFlag = false;
        $digitalProductFlag = false;
        $digitalProductTagsFlag = false;
        $digitalLanguageFlag = false;

        if (tenant() && empty(get_static_option(self::slugModifiedKey))) {

            Page::all()->each(function (Page $page) use (&$pageFlag) {
                try {
                    $page->slug()->updateOrCreate(['slug' => $page->slug]);
                } catch (\Exception $exception) {
                    $page->slug()->updateOrCreate(['slug' => create_slug($page->slug, 'Slug')]);
                }

                $pageFlag = true;
            });


            Blog::all()->each(function (Blog $blog) use (&$blogFlag) {
                try {
                    $blog->slug()->updateOrCreate(['slug' => $blog->slug]);
                } catch (\Exception $exception) {
                    $blog->slug()->updateOrCreate(['slug' => create_slug($blog->slug, 'Slug')]);
                }

                $blogFlag = true;
            });

            BlogCategory::all()->each(function (BlogCategory $blogCategory) use (&$blogCategoryFlag) {
                try {
                    $blogCategory->slug()->updateOrCreate(['slug' => $blogCategory->slug]);
                } catch (\Exception $e) {
                    $blogCategory->slug()->updateOrCreate(['slug' => create_slug($blogCategory->slug, 'Slug')]);
                }

                $blogCategoryFlag = true;
            });

            BlogTag::all()->each(function (BlogTag $blogCategory) use (&$blogTagFlag) {
                try {
                    $blogCategory->slug()->updateOrCreate(['slug' => $blogCategory->slug]);
                } catch (\Exception $e) {
                    $blogCategory->slug()->updateOrCreate(['slug' => create_slug($blogCategory->slug, 'Slug')]);
                }

                $blogTagFlag = true;
            });

            Category::all()->each(function (Category $category) use (&$categoryFlag) {
                try {
                    $category->slug()->updateOrCreate(['slug' => $category->slug]);
                } catch (\Exception $exception) {
                    $category->slug()->updateOrCreate(['slug' => create_slug($category->slug, 'Slug')]);
                }

                $categoryFlag = true;
            });

            SubCategory::all()->each(function (SubCategory $category) use (&$subCategoryFlag) {
                try {
                    $category->slug()->updateOrCreate(['slug' => $category->slug]);
                } catch (\Exception $exception) {
                    $category->slug()->updateOrCreate(['slug' => create_slug($category->slug, 'Slug')]);
                }

                $subCategoryFlag = true;
            });

            ChildCategory::all()->each(function (ChildCategory $category) use (&$childCategoryFlag) {
                try {
                    $category->slug()->updateOrCreate(['slug' => $category->slug]);
                } catch (\Exception $exception) {
                    $category->slug()->updateOrCreate(['slug' => create_slug($category->slug, 'Slug')]);
                }

                $childCategoryFlag = true;
            });

            Product::all()->each(function (Product $category) use (&$productFlag) {
                try {
                    $category->slug()->updateOrCreate(['slug' => $category->slug]);
                } catch (\Exception $exception) {
                    $category->slug()->updateOrCreate(['slug' => create_slug($category->slug, 'Slug')]);
                }

                $productFlag = true;
            });

            DigitalCategories::all()->each(function (DigitalCategories $category) use (&$digitalCategoriesFlag) {
                try {
                    $category->slug()->updateOrCreate(['slug' => $category->slug]);
                } catch (\Exception $exception) {
                    $category->slug()->updateOrCreate(['slug' => create_slug($category->slug, 'Slug')]);
                }

                $digitalCategoriesFlag = true;
            });

            DigitalSubCategories::all()->each(function (DigitalSubCategories $category) use (&$digitalSubCategoriesFlag) {
                try {
                    $category->slug()->updateOrCreate(['slug' => $category->slug]);
                } catch (\Exception $exception) {
                    $category->slug()->updateOrCreate(['slug' => create_slug($category->slug, 'Slug')]);
                }

                $digitalSubCategoriesFlag = true;
            });

            DigitalChildCategories::all()->each(function (DigitalChildCategories $category) use (&$digitalChildCategoriesFlag) {
                try {
                    $category->slug()->updateOrCreate(['slug' => $category->slug]);
                } catch (\Exception $exception) {
                    $category->slug()->updateOrCreate(['slug' => create_slug($category->slug, 'Slug')]);
                }

                $digitalChildCategoriesFlag = true;
            });

            DigitalAuthor::all()->each(function (DigitalAuthor $category) use (&$digitalAuthorFlag) {
                try {
                    $category->slug()->updateOrCreate(['slug' => $category->slug]);
                } catch (\Exception $exception) {
                    $category->slug()->updateOrCreate(['slug' => create_slug($category->slug, 'Slug')]);
                }

                $digitalAuthorFlag = true;
            });

            DigitalProductType::all()->each(function (DigitalProductType $category) use (&$digitalProductTypeFlag) {
                try {
                    $category->slug()->updateOrCreate(['slug' => $category->slug]);
                } catch (\Exception $exception) {
                    $category->slug()->updateOrCreate(['slug' => create_slug($category->slug, 'Slug')]);
                }

                $digitalProductTypeFlag = true;
            });

            DigitalProduct::all()->each(function (DigitalProduct $category) use (&$digitalProductFlag) {
                try {
                    $category->slug()->updateOrCreate(['slug' => $category->slug]);
                } catch (\Exception $exception) {
                    $category->slug()->updateOrCreate(['slug' => create_slug($category->slug, 'Slug')]);
                }

                $digitalProductFlag = true;
            });

            DigitalProductTags::all()->each(function (DigitalProductTags $category) use (&$digitalProductTagsFlag) {
                try {
                    $category->slug()->updateOrCreate(['slug' => $category->tag_name]);
                } catch (\Exception $exception) {
                    $category->slug()->updateOrCreate(['slug' => create_slug($category->tag_name, 'Slug')]);
                }

                $digitalProductTagsFlag = true;
            });

            DigitalLanguage::all()->each(function (DigitalLanguage $category) use (&$digitalLanguageFlag) {
                try {
                    $category->slug()->updateOrCreate(['slug' => $category->slug]);
                } catch (\Exception $exception) {
                    $category->slug()->updateOrCreate(['slug' => create_slug($category->slug, 'Slug')]);
                }

                $digitalLanguageFlag = true;
            });
        }

        if (
            $pageFlag &&
            $blogFlag &&
            $blogCategoryFlag &&
            $blogTagFlag &&
            $categoryFlag &&
            $subCategoryFlag &&
            $childCategoryFlag &&
            $productFlag &&
            $digitalCategoriesFlag &&
            $digitalSubCategoriesFlag &&
            $digitalChildCategoriesFlag &&
            $digitalAuthorFlag &&
            $digitalProductTypeFlag &&
            $digitalProductFlag &&
            $digitalProductTagsFlag &&
            $digitalLanguageFlag
        )
        {
            update_static_option(self::slugModifiedKey, 'yes');
        }
    }
}
