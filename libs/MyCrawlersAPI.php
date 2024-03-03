<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

class MyCrawlersAPI
{
    protected $apiUrl = "http://crawler.local/api";

    public function profile()
    {
        $response = wp_remote_get("{$this->apiUrl}/auth/profile", [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => $this->getAuthorizationToken(),
            ],
        ]);

        $body = wp_remote_retrieve_body($response);

        return json_decode($body, true);
    }

    public function postContent($title, $content, $locale)
    {
        $response = wp_remote_post("{$this->apiUrl}/crawl/contents", [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => $this->getAuthorizationToken(),
            ],
            'body' => [
                'title' => $title,
                'content' => $content,
                'locale' => $locale,
            ],
        ]);

        $body = wp_remote_retrieve_body($response);

        return json_decode($body, true);
    }

    public function translate($contentId, $toLangCode)
    {
        $response = wp_remote_post("{$this->apiUrl}/crawl/contents/translate", [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => $this->getAuthorizationToken(),
            ],
            'body' => [
                'locales' => [$toLangCode],
                'ids' => [$contentId],
            ],
        ]);

        $body = wp_remote_retrieve_body($response);

        return json_decode($body, true);
    }

    public function postAutoPost($options = [])
    {
        $response = wp_remote_post("{$this->apiUrl}/tools/auto-post/websites", [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => $this->getAuthorizationToken(),
            ],
            'body' => [
                'type' => $options['type'] ?? 'custom',
                'limit_per_day' => $options['limit_per_day'] ?? 0,
                'configs' => $options['configs'] ?? [],
                'filters' => [
                    'website_id' => ['apply_all' => 1],
                    'lang' => ['apply_all' => 1],
                    'category_ids' => ['apply_all' => 1],
                ],
                'filter_is_source' => $options['filter_is_source'] ?? 0,
            ],
        ]);

        $body = wp_remote_retrieve_body($response);

        return json_decode($body, true);
    }

    protected function getAuthorizationToken()
    {
        $options = get_option( 'wtc_options' );

        return "Bearer {$options['wtc_api_key']}";
    }
}
