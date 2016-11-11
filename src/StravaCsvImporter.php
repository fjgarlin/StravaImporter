<?php

namespace fjgarlin;

use Iamstuartwilson\StravaApi;

class StravaCsvImporter {

    private $config;
    private $data;
    private $api;
    private $code;
    private $accessToken;
    private $athlete;

    public function __construct($config)
    {
        $this->config = (object)$config;
        $this->api = new StravaApi(
            $this->config->id,
            $this->config->secret
        );

        $this->code = null;
        $this->data = null;
        $this->accessToken = null;
        $this->athlete = null;
    }

    public function authorized()
    {
        return !is_null($this->accessToken);
    }

    public function getAuthorizeUrl()
    {
        return $this->api->authenticationUrl($this->config->redirect_url, 'auto', 'write', null);
    }

    public function getAthlete()
    {
        return $this->athlete;
    }

    public function authorize($code)
    {
        //set code and exchange tokens
        $this->code = $code;
        $accessToken = $this->api->tokenExchange($this->code);

        if ($accessToken) {
            if (isset($accessToken->athlete)) {
                $this->athlete = $accessToken->athlete;
            }
            else {
                //echo "<pre>" . print_r($accessToken, true). "</pre>"; exit;
            }

            //and finally set the accessToken
            $this->accessToken = $accessToken->access_token;
            $this->api->setAccessToken($this->accessToken);
        }
    }

    public function upload($data)
    {
        if (!$this->authorized()) {
            return (object)[
                'status' => false,
                'message' => 'Not authorized.'
            ];
        }

        if (!$data or !is_array($data)) {
            return (object)[
                'status' => false,
                'message' => 'Data is not valid.'
            ];
        }

        $this->data = $data;
        foreach ($this->data as $activity) {
            $activity = (object)$activity;

            //http://strava.github.io/api/v3/activities/#create
            $res = $this->api->post('activities', [
                'name'             => $activity->name,
                'type'             => $activity->type,
                'start_date_local' => $activity->date,                  //ISO 8601: 2016-11-11T11:07:59Z
                'elapsed_time'     => $activity->time * 60,             //minutes to seconds
                'distance'         => $activity->distance * 1609.34,    //miles to meters
                'private'          => 0
            ]);

            //TODO: check if any activity was not uploaded
            //echo "<pre>" . print_r($res, true). "</pre>"; exit;
        }

        return (object)[
            'status' => true,
            'message' => 'Activity uploaded'
        ];
    }
}