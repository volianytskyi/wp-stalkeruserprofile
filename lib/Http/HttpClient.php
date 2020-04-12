<?php
/**
 * User: volyanytsky
 * Date: 24.12.2017
 * Time: 23:11
 */

namespace Http;


class HttpClient
{
    private $url;
    private $login;
    private $password;


    /**
     * HttpClient constructor.
     * @param $url
     * @param string $login
     * @param string $password
     */
    public function __construct($url, $login = '', $password = '')
    {
        $this->setUrl($url);
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * @param $url
     * @throws \HttpUrlException
     */
    public function setUrl($url)
    {
        if(!filter_var($url, FILTER_VALIDATE_URL))
        {
            throw new \HttpUrlException("Incorrect URL: $url", 1);
        }
        ($url[strlen($url) - 1] === '/') ? $this->url = $url : $this->url = $url.'/'; //url must ends with /
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }


    /**
     * @param $resource
     * @param string $id
     * @return bool|string
     */
    public function get($resource, $id = '')
    {
        $opts = [
            'http' => [
                'method'  => "GET",
                'header'  => "Authorization: Basic " . base64_encode($this->login.":".$this->password)
            ]
        ];
        return $this->getAnswer($opts, "$resource/$id");
    }


    /**
     * @param $resource
     * @param array $data
     * @return bool|string
     */
    public function post($resource, array $data)
    {
        if($resource[strlen($resource) - 1] !== '/')
        {
            $resource .= '/';
        }
        $data=http_build_query($data);
        $opts = [
            'http' => [
                'method'=> "POST",
                'header' => "Authorization: Basic " . base64_encode($this->login.":".$this->password) . "\r\n"
                    . "Content-type: application/x-www-form-urlencoded\r\n"
                    . "Content-Length: " . strlen($data) . "\r\n",
                'content' => $data
            ]
        ];
        return $this->getAnswer($opts, $resource);
    }


    /**
     * @param $resource
     * @param array $data
     * @return bool|string
     */
    public function put($resource, array $data)
    {
        $data=http_build_query($data);
        $opts = [
            'http' => [
                'method'  => "PUT",
                'header'  => "Authorization: Basic " . base64_encode($this->login.":".$this->password) ."\r\n"
                    . "Content-type: application/x-www-form-urlencoded\r\n"
                    . "Content-Length: " . strlen($data) . "\r\n",
                'content' => $data
            ]
        ];
        return $this->getAnswer($opts, $resource);
    }


    /**
     * @param $resource
     * @param $id
     * @return bool|string
     */
    public function delete($resource, $id)
    {
        $opts = [
            'http' => [
                'method'  => "DELETE",
                'header'  => "Authorization: Basic " . base64_encode($this->login.":".$this->password)
            ]
        ];
        return $this->getAnswer($opts, "$resource/$id");
    }


    /**
     * @param array $opts
     * @param $resource
     * @return bool|string
     */
    private function getAnswer(array $opts, $resource)
    {
        return file_get_contents($this->url.$resource, false, stream_context_create($opts));
    }
}
