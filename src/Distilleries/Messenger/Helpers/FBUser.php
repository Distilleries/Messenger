<?php
/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 02/08/2016
 * Time: 10:39
 */

namespace Distilleries\Messenger\Helpers;


use Distilleries\Messenger\Exceptions\ConfigException;
use Distilleries\Messenger\Exceptions\MessengerException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class FBUser
{

    protected $config = [];
    protected $client = null;

    /**
     * Message constructor.
     * @param array $config
     */
    public function __construct(array $config, Client $client)
    {

        $this->client = $client;

        if ($this->checkConfig($config) && !empty($client)) {
            $this->config = $config;
        } else {
            throw new ConfigException(trans('messenger::errors.config_not_valid'));
        }

    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return Client|null
     */
    public function getClient()
    {
        return $this->client;
    }


    protected function checkConfig(array $config)
    {
        return (empty($config['page_access_token']) || empty($config['uri_open_graph'])) ? false : true;
    }


    public function getProfile($uid, $fields = ['fields' => 'first_name,last_name,profile_pic,locale,timezone,gender'])
    {

        return $this->callSendAPI($uid, $fields);
    }


    public function callSendAPI($uid, $data)
    {

        $data = $data + ['access_token' => $this->config['page_access_token']];

        try {
            $res = $this->client->request('GET', $this->config['uri_open_graph'] . $uid, [
                'query' => $data
            ]);

            return \GuzzleHttp\json_decode($res->getBody()->getContents());

        } catch (ClientException $e) {
            throw new MessengerException(trans('messenger::errors.unable_to_load_user_profile'), 0, $e);
        }

    }

}