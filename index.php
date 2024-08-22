<?php

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;

require_once __DIR__ . '/vendor/autoload.php';

$serverUrl = 'http://localhost:4444';

// $driver = RemoteWebDriver::create($serverUrl, DesiredCapabilities::firefox());

// $urlSite = 'http://smarta.ru';

// $urlSiteCatalog = 'http://smarta.ru/catalog/';

// $pagesHref = [
//     'pnevmoavtomatika/',
//     'zapornaya_armatura/',
//     'elektromekhanika_i_upravlenie/',
//     'kontrolno_izmeritelnye_pribory/',
// ];

// $driver->get($urlSite);

// $driver->navigate()->to('http://smarta.ru/catalog/');

// $blockUl = $driver->findElements(WebDriverBy::cssSelector('ul.catalog__sublist'));

// $uris = [];

// foreach ($blockUl as $link) {

//     $links = $link->findElements(WebDriverBy::cssSelector('a.catalog__item-link'));

//     foreach ($links as $el) {
//         $uris[] = $el->getAttribute('href');
//     }
// }

// $linksCatalog = [];
// foreach ($uris as $uri) {
   
//     $driver->navigate()->to($urlSite . $uri);

//     $catalogGropLinks = $driver->findElements(WebDriverBy::cssSelector('a.product-cards__image'));

//     foreach ($catalogGropLinks as $key => $catalogGropLink) {
//         if(checkUrl($catalogGropLink->getAttribute('href'))) {
//            $linksCatalog[] = $catalogGropLink->getAttribute('href');
//         }
//     }
// }

// $linksCatalogGroup = [];
// foreach ($linksCatalog as $url) {
    
//     $driver->navigate()->to($urlSite . $url);

//     $catalogGropLinks = $driver->findElements(WebDriverBy::cssSelector('a.product-cards__image'));

//     foreach ($catalogGropLinks as $key => $catalogGropLink) {
//         if(checkUrl($catalogGropLink->getAttribute('href'))) {
//            $linksCatalogGroup[] = $catalogGropLink->getAttribute('href');
//         }
//     }

// }

$stream = fopen('data.csv', 'w');

$stream2 = fopen('urls.txt', 'r');

$currUrl = '';
while ($row = fgets($stream2)) {

    if($row != $currUrl) {
        $currUrl = $row;
        $offset = 1;
    }

    var_dump(trim($row));
    clicker(RemoteWebDriver::create($serverUrl, DesiredCapabilities::firefox()), trim($row), $stream, $offset);
}




// foreach ($linksCatalogGroup as $page) {

//     echo $page . "\n";
//     // clicker($driver, $page, $stream);

// }




//*****************************************************************************************************
$driver->quit();






function checkUrl($str): bool
{
    $base = basename($str);

    if(strpos($base, '.pdf') !== false) {
        return false;
    }

    return true;
}


function clicker($driver, $url, $stream, $page)
{
    $driver->navigate()->to($url . "?PAGEN_1=$page");
    var_dump($url . "?PAGEN_1=$page");
    $chekBtn = false;
    try {
        $btn = $driver->findElement(WebDriverBy::cssSelector('.table__pagination-btn'));
        var_dump('Пагинация найдена');

    } catch (NoSuchElementException $e) {
        var_dump('Пагинация не найдена: true');
        $chekBtn = true;
    }

    if(!$chekBtn) {
        $newUrl = $driver->getCurrentURL();


        $newUrl = str_replace("&", "?", $newUrl);
        $dataUrl = parse_url($newUrl);
        $queryPage = $dataUrl['query'];
        $equal_pos = strpos($queryPage, "=");
        $currentPage = (int)substr($queryPage, $equal_pos + 1);

        if(($currentPage + 1) < $page - 1) {
            $driver->quit();
            var_dump('Последняя страница каталога');
            return;
        }
    }

    
    try {

        // $orderTable = $driver->findElement(WebDriverBy::cssSelector('.catalog-list.catalog-list--margin'));
        $orderblocks = $driver->findElements(WebDriverBy::xpath('//*[@data-entity="item"]'));
        var_dump('Таблица найдена');
        foreach ($orderblocks as $orderblock) {       
            $orderCoding = $orderblock->findElement(WebDriverBy::cssSelector('h3.catalog-list__item-title'));
            $orderCode = $orderCoding->findElement(WebDriverBy::cssSelector('a.catalog-list__item-title'))->getText();
            $orderNum = $orderCoding->findElement(WebDriverBy::cssSelector('span.bar_code_product'))->getText();
    
            $orderCountBlock = $orderblock->findElement(WebDriverBy::cssSelector('.catalog-list__item-available'));
            $orderCount = $orderCountBlock->findElements(WebDriverBy::cssSelector('span'))[1]->getText();

            fputcsv($stream, [$orderCode, $orderNum, $orderCount]);
        }

    } catch (NoSuchElementException $e) {
        $driver->quit();
        var_dump('Таблица не найдена');
        var_dump($e->getMessage());
        return;
    }


    if($chekBtn) {
        $driver->quit();
        var_dump('Пагинация не найдена');
        return;
    }

    $newPage = $page + 1;
    $driver->quit();
    clicker(RemoteWebDriver::create('http://localhost:4444', DesiredCapabilities::firefox()), $url, $stream, $newPage);
}
