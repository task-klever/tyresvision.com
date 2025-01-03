<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | https://github.com/hybridauth/hybridauth
*  (c) 2009-2015 HybridAuth authors | hybridauth.sourceforge.net/licenses.html
*/

namespace Mageplaza\SocialLogin\Model\Providers;

use Exception;
use Hybrid_Auth;
use Hybrid_Provider_Model_OAuth2;
use Hybrid_User_Contact;
use Hybrid_User_Profile;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use OAuth2Client;
use RuntimeException;

/**
 * Class Vkontakte
 *
 * @package Mageplaza\SocialLogin\Model\Providers
 */
class Vkontakte extends Hybrid_Provider_Model_OAuth2
{
    public $scope = 'email';

    public $version = '5.107';

    public $fields = [
        'identifier'  => 'id',
        'firstName'   => 'first_name',
        'lastName'    => 'last_name',
        'displayName' => 'screen_name',
        'gender'      => 'sex',
        'photoURL'    => 'photo_big',
        'home_town'   => 'home_town',
        'profileURL'  => 'domain',
        'nickname'    => 'nickname',
        'bdate'       => 'bdate',
        'timezone'    => 'timezone',
        'photo_rec'   => 'photo_rec',
        'domain'      => 'domain',
        'photo_max'   => 'photo_max',
        'home_phone'  => 'home_phone',
        'city'        => 'city',
        'country'     => 'country',
    ];

    /**
     * @throws Exception
     */
    function initialize()
    {
        parent::initialize();

        $this->api->api_base_url  = 'https://api.vk.com/method/';
        $this->api->authorize_url = 'https://oauth.vk.com/authorize';
        $this->api->token_url     = 'https://oauth.vk.com/token';
        if (!empty($this->config['fields'])) {
            $this->fields = $this->config['fields'];
        }
        if (array_key_exists('version', $this->config)) {
            $this->version = $this->config['version'];
        }
        if (array_key_exists('v', $this->config)) {
            $this->version = $this->config['v'];
        }
    }

    /**
     * @param $class
     *
     * @return mixed
     */
    public function getDataObject($class)
    {
        $objectManager = ObjectManager::getInstance();

        return $objectManager->create($class);
    }

    /**
     * @throws Exception
     */
    function loginFinish()
    {
        /**
         * @var RequestInterface $request
         */
        $request = $this->getDataObject(RequestInterface::class);
        $params  = $request->getParams();
        $error   = array_key_exists('error', $params) ? $params['error'] : '';

        if ($error) {
            throw new Exception("Authentication failed! {$this->providerId} returned an error: $error", 5);
        }

        $code = array_key_exists('code', $params) ? $params['code'] : '';

        try {
            $response = $this->api->authenticate($code);
        } catch (Exception $e) {
            throw new Exception("User profile request failed! {$this->providerId} returned an error: $e", 6);
        }

        if (empty($response->user_id) || !$this->api->access_token) {
            throw new Exception("Authentication failed! {$this->providerId} returned an invalid access token.", 5);
        }

        $this->token('access_token', $this->api->access_token);
        $this->token('refresh_token', $this->api->refresh_token);
        $this->token('expires_in', $this->api->access_token_expires_in);
        $this->token('expires_at', $this->api->access_token_expires_at);

        Hybrid_Auth::storage()->set("hauth_session.{$this->providerId}.user_id", $response->user_id);
        Hybrid_Auth::storage()->set(
            "hauth_session.{$this->providerId}.user_email",
            !empty($response->email) ? $response->email : null
        );

        // set user connected locally
        $this->setUserConnected();
    }

    /**
     * @return Hybrid_User_Profile
     * @throws Exception
     */
    function getUserProfile()
    {
        $this->refreshToken();

        $params['user_ids'] = Hybrid_Auth::storage()->get("hauth_session.{$this->providerId}.user_id");
        $params['fields']   = implode(',', $this->fields);
        $params['v']        = $this->version;

        $response = $this->api->api('users.get', 'GET', $params);

        if (isset($response->error)) {
            throw new RuntimeException(
                "User profile request failed! {$this->providerId} returned an error #{$response->error->error_code}: {$response->error->error_msg}",
                6
            );
        }

        if (!isset($response->response[0], $response->response[0]->id)) {
            throw new Exception("User profile request failed! {$this->providerId} returned an invalid response.", 6);
        }

        $response = reset($response->response);
        foreach ($this->getUserByResponse($response, true) as $k => $v) {
            $this->user->profile->$k = $v;
        }

        $this->user->profile->email = Hybrid_Auth::storage()->get("hauth_session.{$this->providerId}.user_email");

        return $this->user->profile;
    }

    /**
     * @return array|Hybrid_User_Contact[]
     */
    function getUserContacts()
    {
        $params = [
            'fields' => implode(',', $this->fields),
        ];

        $response = $this->api->api('friends.get', 'GET', $params);

        if (empty($response) || empty($response->response)) {
            return [];
        }

        $contacts = [];
        foreach ($response->response as $item) {
            $contacts[] = $this->getUserByResponse($item);
        }

        return $contacts;
    }

    /**
     * @param $response
     * @param bool $withAdditionalRequests
     *
     * @return Hybrid_User_Contact
     */
    function getUserByResponse($response, $withAdditionalRequests = false)
    {
        $user = new Hybrid_User_Contact();

        foreach ($this->fields as $field => $map) {
            $user->$field = isset($response->$map) ? $response->$map : null;
        }

        if (!empty($user->profileURL)) {
            $user->profileURL = 'http://vk.com/' . $user->profileURL;
        }

        if (isset($user->gender)) {
            switch ($user->gender) {
                case 1:
                    $user->gender = 'female';
                    break;

                case 2:
                    $user->gender = 'male';
                    break;

                default:
                    $user->gender = null;
                    break;
            }
        }

        if (!empty($user->bdate)) {
            $birthday = explode('.', $user->bdate);
            switch (count($birthday)) {
                case 3:
                    $user->birthDay   = (int) $birthday[0];
                    $user->birthMonth = (int) $birthday[1];
                    $user->birthYear  = (int) $birthday[2];
                    break;

                case 2:
                    $user->birthDay   = (int) $birthday[0];
                    $user->birthMonth = (int) $birthday[1];
                    break;
            }
        }

        if (!empty($user->city) && $withAdditionalRequests) {
            $params = ['city_ids' => $user->city];
            $cities = (array) $this->api->api('database.getCitiesById', 'GET', $params);
            $city   = reset($cities);

            if (is_array($city)) {
                $city = reset($city);
            }

            if (is_object($city) || is_string($city)) {
                $user->city = isset($city->name) ? $city->name : null;
            }
        }

        if (!empty($user->country) && $withAdditionalRequests) {
            $params    = ['country_ids' => $user->country];
            $countries = (array) $this->api->api('database.getCountriesById', 'GET', $params);
            $country   = reset($countries);

            if (is_array($country)) {
                $country = reset($country);
            }

            if (is_object($country) || is_string($country)) {
                $user->country = isset($country->name) ? $country->name : null;
            }
        }

        return $user;
    }
}
