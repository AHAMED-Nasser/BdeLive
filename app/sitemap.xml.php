<?php
class SitemapGenerator {
    private SimpleXMLElement $xml;
    private string $baseUrl;

    public function __construct(string $baseUrl) {
        $this->baseUrl = $baseUrl;
        $this->xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
    }

    public function addStaticPages(array $staticPages): void {
        foreach ($staticPages as $pageName => $data) {
            $url = $this->xml->addChild('url');
            $url->addChild('loc', htmlspecialchars($this->baseUrl . '/index.php?page=' . $pageName));
            $url->addChild('lastmod', $data['lastmod'] ?? date('Y-m-d'));
            $url->addChild('changefreq', $data['changefreq'] ?? 'weekly');
            $url->addChild('priority', $data['priority'] ?? '0.5');
        }
    }

    public function generate(): string {
        return $this->xml->asXML();
    }
}