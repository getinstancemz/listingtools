<?php

namespace getinstance\listingtools\github;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class GitHubConnect
{
    protected $uri;
    private $client;
    private $token;

    private $usercached;
    private $gistcached;

    public function __construct($token)
    {
        $token = rtrim($token);
        $this->uri = "https://api.github.com";
        $this->token = $token;
        $this->client = new Client(['headers' => ['Authorization' => 'Token ' . $this->token]]);
    }


    function getUser()
    {
        if (is_null($this->usercached)) {
            list($code, $body) = $this->doGet("/user");
            if ($code != 200) {
                throw new \Exception("could not get user -- error:" . print_r($body, true));
            }
            $this->usercached = $body;
        }
        return $this->usercached;
    }

    function allGists($page = 1)
    {
        list($code, $body) = $this->doGet("/gists", ["per_page" => 100, "page" => $page]);
        if ($code != 200) {
            throw new \Exception("could not get gists -- error:" . print_r($body, true));
        }
        return $body;
    }


    function createGist($project, $title, $extension, $content)
    {
        $filename = "{$title}.{$extension}";
        $params = [
            "public" => false,
            "description" => "{$project}.{$title}",
            "files" => [
                $filename => [
                    "content" => $content
                ]
            ]
        ];
        list($code, $body) = $this->doPost("/gists", $params);
        if (! $code == 201) {
            throw new \Exception("could not create gist -- error:" . print_r($body, true));
        }
        return $body;
    }

    function findGist($project, $title)
    {
        for ($page = 1; (! empty(($result = $this->allGists($page)))); $page++) {
            foreach ($result as $gist) {
                if ($gist['description'] == "{$project}.{$title}") {
                    return $gist;
                }
            }
        }
        return false;
    }

    function getGistContents($gist, $title, $extension)
    {
        $filename = "{$title}.{$extension}";
        foreach ($gist['files'] as $filekey => $fileinfo) {
            if ($filekey == $filename) {
                return file_get_contents($fileinfo['raw_url']);
            }
        }
        return null;
    }

    function updateGistFile($gist, $project, $title, $extension, $contents)
    {
        $gistid = $gist['id'];
        $filename = "{$title}.{$extension}";

        $filearray = [];
        $filearray[$filename] = [
            "content" => $contents
        ];
    
        // delete other filenames
        foreach($gist['files'] as $filekey => $contents) {
            if ($filename != $filekey) {
                $filearray[$filekey] = [
                    "content" => ""
                ];
            }
        }

        $args = [
            "description" => "{$project}.{$title}",
            "files" => $filearray 
        ];
        list($code, $body) = $this->doPatch("/gists/{$gistid}", $args);
        if (! $code == 200) {
            throw new \Exception("could not update gist -- error:" . print_r($body, true));
        }
        return $body;
    }

    function createOrUpdateGist($project, $title, $extension, $contents)
    {
        $gist = $this->findGist($project, $title);
        if (! $gist) {
            $gist = $this->createGist($project, $title, $extension, $contents);
        } else {
            $oldcontents = $this->getGistContents($gist, $title, $extension);
            if (count($gist['files']) > 1 || is_null($oldcontents) || $oldcontents != $contents) {
                $gist = $this->updateGistFile($gist, $project, $title, $extension, $contents);
            }
        }
        $user = $this->getUser();
        //<script src="https://gist.github.com/getinstancemz/3251d26237494a1539cff53f7a25c0f6.js"></script>
        $embed = "<script src=\"https://gist.github.com/{$user['login']}/{$gist['id']}.js\"></script>\n";
        return $embed;
    }

    // service utilities
    public function doPut($path, array $vals = [])
    {
        $resp = $this->client->request("PUT", $this->uri . $path, [ "json" => $vals ]);
        $bodyjson = (string)$resp->getBody();
        $body = json_decode($bodyjson, true);
        $status = $resp->getStatusCode();
        return [$status, $body];
    }

    public function doPatch($path, array $vals = [])
    {
        $resp = $this->client->request("PATCH", $this->uri . $path, [ "json" => $vals ]);
        $bodyjson = (string)$resp->getBody();
        $body = json_decode($bodyjson, true);
        $status = $resp->getStatusCode();
        return [$status, $body];
    }

    public function doPost($path, array $vals = [])
    {
        $args = [
            'exceptions' => false,
            "json" => $vals
        ];
        $resp = $this->client->request("POST", $this->uri . $path, $args);
        $bodyjson = (string)$resp->getBody();
        $body = json_decode($bodyjson, true);
        $status = $resp->getStatusCode();
        return [$status, $body];
    }

    public function doDelete($path, array $args = [])
    {
        $fullpath = $this->uri . $path;
        $resp = $this->client->request(
            "DELETE",
            $fullpath,
            [
                //'query'          => $args,
                'debug'          => false,
                "json" => $args,
                'exceptions' => false,
            ]
        );
        $bodyjson = (string)$resp->getBody();
        $body = json_decode($bodyjson, true);
        $code = $resp->getStatusCode();
        return [$code, $body];
    }

    public function doGet($path, array $args = [])
    {
        $fullpath = $this->uri . $path;
        $resp = $this->client->request(
            "GET",
            $fullpath,
            [
                'query'          => $args,
                'debug'          => false,
                'exceptions' => false,
            ]
        );
        $bodyjson = (string)$resp->getBody();
        $body = json_decode($bodyjson, true);
        $code = $resp->getStatusCode();
        return [$code, $body];
    }
}
