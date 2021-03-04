<?php

namespace App\Services\Parsers;

use Exception;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class EscoService
{
    // Started https://www.esco.ua/catalog/detail/8176/
    // Ended https://www.esco.ua/catalog/detail/17621/
    private string $m_sUrl = 'https://www.esco.ua';

    private array $m_aCategories;
    private array $m_aSubcategories;
    private array $m_aProducts;

    function __construct()
    {
        $this->clear();
    }

    public function clear()
    {
        $this->m_aCategories = $this->m_aSubcategories = $this->m_aProducts = [];
    }

    public function &getCategories()
    {
        return $this->m_aCategories;
    }

    public function &getSubcategories()
    {
        return $this->m_aSubcategories;
    }

    public function &getProducts()
    {
        return $this->m_aProducts;
    }

    public function parseAll()
    {
        $this->parseCategories();

        foreach ($this->m_aCategories as &$category) {
            $this->parseCategoryPage($category['href']);
        }

        foreach ($this->m_aSubcategories as &$subcategory) {
            $this->parseSubcategoryPage($subcategory['href']);
        }
    }

    public function &parseCategories()
    {
        $crawler = new Crawler(Http::get($this->m_sUrl)->getBody()->getContents());

        $this->clear();

        $crawler->filter('.item_category')->each(function ($node) {
            $linkElement = $node->getNode(0)->firstChild;

            $this->m_aCategories[] = [
                'name' => $linkElement->firstChild->textContent,
                'href' => $linkElement->getAttribute('href')
            ];
        });

        return $this->m_aCategories;
    }

    public function &parseCategoryPage($path)
    {
        $crawler = new Crawler(Http::get($this->m_sUrl . $path)->getBody()->getContents());

        $this->m_aSubcategories = [];

        $crawler->filter('.itemdata')->each(function ($node) {
            $linkElement = $node->getNode(0)->firstChild;

            $this->m_aSubcategories[] = [
                'name' => $linkElement->firstChild->textContent,
                'href' => $linkElement->getAttribute('href')
            ];
        });

        return $this->m_aSubcategories;
    }

    public function &parseSubcategoryPage($path)
    {
        $crawler = new Crawler(Http::get($this->m_sUrl . $path)->getBody()->getContents());

        $this->m_aProducts = [];

        $crawler->filter('.itemlink')->each(function ($node) {
            $linkElement = $node->getNode(0)->firstChild;

            $product = $this->parseProductPage($linkElement->getAttribute('href'));

            if ($product) {
                $this->m_aProducts[] = $product;
            }
        });

        return $this->m_aProducts;
    }

    public function &parseProductPage($path)
    {
        $crawler = new Crawler(Http::get($this->m_sUrl . $path)->getBody()->getContents());

        try {
            $articleText = $crawler->filter('.article')->getNode(0)->lastChild->textContent ?? '';

            $name = $crawler->filter('.product-head')->getNode(0)->firstChild->textContent ?? '';

            $priceNodes = $crawler->filter('.price');
            $priceOld = (int)str_replace(' ', '', $priceNodes->children('div.old')->getNode(0)->firstChild->textContent ?? 0);
            $price = (int)str_replace(' ', '', $priceNodes->children('div.new')->getNode(0)->firstChild->textContent ?? 0);

            $status = $crawler->filter('.have')->getNode(0)->textContent ?? '';
        } catch (Exception $e) {
            return null;
        }
        $product = [
            'article' => substr($articleText, strpos($articleText, ':')),
            'name' => $name,
            'price' => $price,
            'price_old' => $priceOld,
            'status' => $status == 'Есть в наличии'
        ];

        return $product;
    }
}
