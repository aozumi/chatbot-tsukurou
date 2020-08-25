<?php
// 楽天の商品検索APIで検索する。

// アプリIDが RAKUTEN_APP_ID という定数で定義されていることを仮定する。

// 商品検索APIのリクエストURL
define('SEARCH_URL', 'https://app.rakuten.co.jp/services/api/IchibaItem/Search/20170706');

function rakuten_query_url(string $keyword): string
{
    $url = SEARCH_URL . '?applicationId=' . urlencode(RAKUTEN_APP_ID);
    $url .= '&keyword=' . urlencode($keyword);
    $url .= '&sort=' . urlencode('+itemPrice'); // 価格順
    $url .= '&hits=3'; // 取得件数
    $url .= '&elements=itemName,itemPrice,itemUrl'; // 取得情報要素
    return $url;
}
