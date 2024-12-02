<?php
namespace App\Http\Controllers;

use App\Models\keyword;
use App\Models\keyword_related;
use App\Models\Pages;
use App\Models\page_related;
use App\Models\keyword_page;
use App\Models\Domain;

use Illuminate\Support\Str;

class Controller // تغییر نام کلاس
{
    public function save()
    {
        // کلید اول
        $firstKey = 'key 6';
        $firstSlug = Str::slug($firstKey);

        // ذخیره‌سازی کلید اول
        $firstKeySave = Keyword::firstOrCreate(
            ['keyword' => $firstKey],
            [
                'slug' => $firstSlug,
                'category_id' => 1,
                'value' => 2,
                'search_value' => 1.1,
                'degree_difficulty' => 2.1,
            ]
        );

        $firstSavedId = $firstKeySave->id;

        $keywords = ['key 2', 'key 3'];


        // ذخیره‌سازی کلیدواژه‌های دیگر
        foreach ($keywords as $keyword) {
            $slug = Str::slug($keyword);

            $keySaved = Keyword::firstOrCreate(
                ['keyword' => $keyword],
                [
                    'slug' => $slug,
                    'category_id' => 1,
                    'value' => 2,
                    'search_value' => 1.1,
                    'degree_difficulty' => 2.1,
                ]
            );

            $keySavedId = $keySaved->id;

            keyword_related::firstOrCreate(
                [
                    'keyword_id' => $firstSavedId,
                    'related_id' => $keySavedId
                ]
            );
        }

        //ذخیره سایت ها و دامنه ها
        $urls = ['http://test.com','http://test.com/page','http://test2.com','http://test3.com'];

        foreach ($urls as $url) {

            // ذخیره دامنه ها
            $domain = parse_url($url, PHP_URL_HOST);
            Domain::firstOrCreate(['url' => $domain],
                [
                    'category_id' => 1,
                    'value' => 2
                ]
            );

            //ذخیره سایت ها
            $page = pages::firstOrCreate(['url' => $url],
                [
                    'category_id' => 1,
                    'value' => 2
                ]
            );

            //ذخیره رابطه سایت و کلمه
            $pageId = $page->id;
            keyword_page::firstOrCreate(
                [
                    'page_id' => $pageId,
                    'keyword_id' => $firstSavedId
                ]
            );

            //ذخیره رابطه سایت ها
            $relatedUrls = array_filter($urls, fn($relatedUrl) => $relatedUrl !== $url);
            foreach ($relatedUrls as $relatedUrl) {
                $relatedPage = Pages::where('url', $relatedUrl)->first();
                if ($relatedPage) {
                    $relatedPageId = $relatedPage->id;

                    $exists = page_related::where(function($query) use ($relatedPageId, $pageId) {
                        $query->where('page_id', $relatedPageId)
                              ->where('related_id', $pageId);
                    })->orWhere(function($query) use ($relatedPageId, $pageId) {
                        $query->where('page_id', $pageId)
                              ->where('related_id', $relatedPageId);
                    })->exists();
                    
                    if (!$exists) {
                        page_related::create([
                            'page_id' => $relatedPageId,
                            'related_id' => $pageId,
                            'keyword_id' => $firstSavedId
                        ]);
                    }
                    else{
                        // اگر رکورد وجود دارد، بررسی کنید که آیا keyword_id متفاوت است
                        $existingRelation = page_related::where('page_id', $relatedPageId)
                            ->where('related_id', $pageId)
                            ->first();
            
                        if ($existingRelation && $existingRelation->keyword_id !== $firstSavedId) {
                            // رکورد جدید با keyword_id متفاوت ایجاد کنید
                            page_related::create([
                                'page_id' => $relatedPageId,
                                'related_id' => $pageId,
                                'keyword_id' => $firstSavedId
                            ]);
                        }
                    }
                }
            }
            
        }
        
    }


    public function show()
    {

        echo 'سرچ بر اساس id کلمه کلیدی<br>';
        $key_id = 6;

        $search_related_key = keyword_related::where('keyword_id', $key_id)->get();
        foreach ($search_related_key as $related) {

            $search_keys = keyword::where('id', $related->related_id)->first();
            echo 'کلمه مرتبط: ' . $search_keys->keyword . '<br>';
        }

        $search_related_page = keyword_page::where('keyword_id', $key_id)->get();
        foreach ($search_related_page as $page) {

            $search_page = Pages::where('id', $page->page_id)->first();
            echo 'سایت مرتبط: ' . $search_page->url . '<br>';
        }


        echo '-------------------------------- <br>';
        echo 'سرچ بر اساس id صفحه<br>';
        $page_id = 2;

        $search_related_key = keyword_page::where('page_id', $page_id)->get();
        foreach ($search_related_key as $related) {

            $search_keys = keyword::where('id', $related->keyword_id)->first();
            echo 'کلمه مرتبط: ' . $search_keys->keyword . '<br>';
        }

        $search_related_page_1 = page_related::where('page_id', $page_id)->get();
        $printed_page = [];

        foreach ($search_related_page_1 as $page) {
            $search_page = Pages::where('id', $page->related_id)->first();
            
            if ($search_page && !in_array($search_page->url, $printed_page)) {
                echo 'سایت مرتبط: ' . $search_page->url . '<br>';

                $printed_page[] = $search_page->url;
            }
        }

        $search_related_page_2 = page_related::where('related_id', $page_id)->get();
        foreach ($search_related_page_2 as $page) {
            $search_page = Pages::where('id', $page->page_id)->first();
            
            if ($search_page && !in_array($search_page->url, $printed_page)) {
                echo 'سایت مرتبط: ' . $search_page->url . '<br>';

                $printed_page[] = $search_page->url;
            }
        }

        echo '-------------------------------- <br>';
        echo 'سرچ بر اساس id دامنه<br>';

        $domain_id = 1;
        $domain = domain::where('id', $domain_id)->first();
        $domain = $domain->url;
        $search_related_domain = pages::where('url', 'LIKE', '%' . $domain . '%')->get();

        foreach ($search_related_domain as $domain) {
            $page_id = $domain->id;

            $search_related_key = keyword_page::where('page_id', $page_id)->get();
            foreach ($search_related_key as $related) {

                $search_keys = keyword::where('id', $related->keyword_id)->first();
                echo 'کلمه مرتبط: ' . $search_keys->keyword . '<br>';
            }

            $search_related_page_1 = page_related::where('page_id', $page_id)->get();
            $printed_page = [];

            foreach ($search_related_page_1 as $page) {
                $search_page = Pages::where('id', $page->related_id)->first();
                
                if ($search_page && !in_array($search_page->url, $printed_page)) {
                    echo 'سایت مرتبط: ' . $search_page->url . '<br>';

                    $printed_page[] = $search_page->url;
                }
            }

            $search_related_page_2 = page_related::where('related_id', $page_id)->get();
            foreach ($search_related_page_2 as $page) {
                $search_page = Pages::where('id', $page->page_id)->first();
                
                if ($search_page && !in_array($search_page->url, $printed_page)) {
                    echo 'سایت مرتبط: ' . $search_page->url . '<br>';

                    $printed_page[] = $search_page->url;
                }
            }

            echo'<br>';
        }

    }
}
