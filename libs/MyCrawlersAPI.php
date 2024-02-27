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

    public function login($email, $password)
    {
        $response = wp_remote_post("{$this->apiUrl}/auth/login", [
            'headers' => [
                'Accept' => 'application/json'
            ],
            'body' => [
                'email' => $email,
                'password' => $password
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
        return "Bearer {$params['token']}";
    }
}
