<?php
/*
* # Scrape product details from square space
* @author Brian Gaeddert.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /webservices/products.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
include '../conf/loadconfig.inc.php';

function getAll($urls){
    $data = [];
    $data['products'] = [];

    foreach ($urls as $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0');
        $res = curl_exec($ch);
        curl_close($ch);
        $raw = getData($res);
        $raw['productUrl'] = $url;
        if(!empty($raw['title'])){
            $data['products'][] = $raw;
        }
    }

    return $data;
}

function getData($html){
    return [
      'title' => getTextContent($html, "ProductItem-details-title"),
      'price' => getTextContent($html, "product-price"),
      'excerpt' => getTextContent($html, "ProductItem-details-excerpt"),
      'imageUrl' => getImage($html, "ProductItem-gallery-slides"),
    ];
}

function getTextContent($html, $classname, $nodeIndex = 0){
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    $finder = new DomXPath($dom);
    $nodes = $finder->query("//*[contains(@class, '$classname')]");

    try {
        return trim($nodes->item($nodeIndex)->textContent ?? '');
    }catch (\Throwable $e){
        return '';
    }
}

function getImage($html, $classname, $nodeIndex = 0){
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    $finder = new DomXPath($dom);
    $nodes = $finder->query("//*[contains(@class, '$classname')]//img");

    try {
        return $nodes->item($nodeIndex)->attributes->getNamedItem('data-src')->textContent;
    }catch (\Throwable $e){
        return '';
    }
}

$urls = [];

setting_get('product_link_one', $product_link_one);
setting_get('product_link_two', $product_link_two);
setting_get('product_link_three', $product_link_three);
setting_get('product_promo_code', $product_promo_code);
setting_get('product_promo_text', $product_promo_text);

if(!empty($product_link_one)){
    $urls[] = $product_link_one;
}
if(!empty($product_link_two)){
    $urls[] = $product_link_two;
}
if(!empty($product_link_three)){
    $urls[] = $product_link_three;
}

header('Content-type: application/json');

if(empty($urls)){
    echo json_encode([]);
    return;
}

$data = getAll($urls);
$data['promo_code'] = $product_promo_code;
$data['promo_text'] = $product_promo_text;

echo json_encode($data);

?>

