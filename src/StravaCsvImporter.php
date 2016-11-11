<?php

namespace fjgarlin;

use Iamstuartwilson\StravaApi;

class StravaCsvImporter {

    private $config;
    private $data;

    public function __construct($config, $data = null)
    {
        $this->config = (object)$config;
        $this->data = $data;
    }

    public function upload($data = null)
    {
        if (!is_null($data)) {
            $this->data = $data;
        }

        if (!is_null($data)) {
            $api = new StravaApi(
                $this->config->id,
                $this->config->secret
            );

            //$api->authenticationUrl($this->config->redirect_url);
            //$api->tokenExchange($code);
            //$api->setAccessToken($accessToken);
//            $api->post('activities', [
//                'name'             => 'API Test',
//                'type'             => 'Ride',
//                'start_date_local' => date( 'Y-m-d\TH:i:s\Z'),
//                'elapsed_time'     => 3600
//            ]);
        }

        return (object)[
            'status' => false,
            'message' => 'Unknown API error.'
        ];
    }
}