<?php

namespace fjgarlin;

use Iamstuartwilson\StravaApi;

/**
 * Class StravaImporter
 * @package fjgarlin
 */
class StravaImporter {

    /**
     * @var object
     */
    private $config;

    /**
     * @var null
     */
    private $data;

    /**
     * @var StravaApi
     */
    private $api;

    /**
     * @var null
     */
    private $code;

    /**
     * @var null
     */
    private $accessToken;

    /**
     * @var null
     */
    private $athlete;

    /**
     * StravaImporter constructor.
     * @param $config
     */
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

    /**
     * Returns if we're already authorized
     * @return bool
     */
    public function authorized()
    {
        return !is_null($this->accessToken);
    }

    /**
     * Returns the URL to authorize this app
     * @return string
     */
    public function getAuthorizeUrl()
    {
        return $this->api->authenticationUrl($this->config->redirect_url, 'auto', 'write', null);
    }

    /**
     * Get athlete's data
     * @return null
     */
    public function getAthlete()
    {
        return $this->athlete;
    }

    /**
     * Set an athlete
     * @param $athlete
     */
    public function setAthlete($athlete)
    {
        $this->athlete = $athlete;
    }

    /**
     * Perform authorization from a given code
     * @param $code
     */
    public function authorize($code)
    {
        //set code and exchange tokens
        $this->code = $code;
        $accessToken = $this->api->tokenExchange($this->code);

        if ($accessToken) {
            if (isset($accessToken->athlete)) {
                $this->setAthlete($accessToken->athlete);
            }
            else {
                //echo "<pre>" . print_r($accessToken, true). "</pre>"; exit;
            }

            //and finally set the accessToken
            $this->accessToken = $accessToken->access_token;
            $this->api->setAccessToken($this->accessToken);
        }
    }

    /**
     * Upload the given data to Strava
     * @param $data
     * @return object
     */
    public function upload($data)
    {
        if (!$this->authorized()) {
            return (object)[
                'status' => false,
                'message' => 'Not authorized.'
            ];
        }

        if (!$data) {
            return (object)[
                'status' => false,
                'message' => 'Data is not valid.'
            ];
        }

        //if the file is being passed instead of an array
        if (!is_array($data)) {
            $data = $this->_csvToArray($data);
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

    // http://php.net/manual/en/function.str-getcsv.php
    protected function _csvToArray($file_path) {
        $csv = array_map('str_getcsv', file($file_path));
        array_walk($csv, function(&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv); # remove column header

        return $csv;
    }
}