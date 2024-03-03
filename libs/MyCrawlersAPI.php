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
    protected $apiUrl = "http://cms.local/api";

    public function profile()
    {
        $response = wp_remote_post("{$this->apiUrl}/auth/profile", [
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
        $response = wp_remote_post("{$this->apiUrl}/crawl/contents/{$contentId}/translate", [
            'headers' => [
                'Accept' => 'application/json'
            ],
            'body' => [
                'locale' => $toLangCode
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
