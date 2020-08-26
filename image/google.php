<?php
// Google Custom Serach APIを使って画像検索する

// 次の定数が定義されていると仮定:
// - GOOGLE_CUSTOM_SEARCH_API_KEY
// - GOOGLE_CUSTOM_SEARCH_ENGINE_ID

define('GOOGLE_CUSTOM_SEARCH_URL', 'https://www.googleapis.com/customsearch/v1');

/**
 * 指定のキーワードで画像検索するURLを返す。
 */
function google_search_image_url(string $keyword)
{
    $url = GOOGLE_CUSTOM_SEARCH_URL . '?key=' . GOOGLE_CUSTOM_SEARCH_API_KEY;
    $url .= '&cx=' . GOOGLE_CUSTOM_SEARCH_ENGINE_ID;
    $url .= '&searchType=image';
    $url .= '&q=' . urlencode($keyword);
    return $url;
}

