<?php

namespace Ebs\Gateways;

use phpseclib\Crypt\RSA;

class Cashaman
{
    /**
     * Cashaman Endpoint
     * @var string
     */
    private $API = "https://www.cashaman.net/DSPaymentAPI/APIV1/";

    /**
     * Cashaman API Version
     * @var string
     */
    private $APP_VERSION = "1.4";

    /**
     * EBS Public Key
     * @var string
     */
    private $PUB_KEY = "MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBANx4gKYSMv3CrWWsxdPfxDxFvl+Is/0kc1dvMI1yNWDXI3AgdI4127KMUOv7gmwZ6SnRsHX/KAM0IPRe0+Sa0vMCAwEAAQ==";

    /**
     * Account Info
     * @var null
     */
    private $ACCOUNT_INFO = null;

    /**
     * Options
     * @var array
     */
    private $VARS = array(
        'username' => '',
        'password' => '',
    );


    /**
     * Cashaman constructor.
     * @param $username
     * @param $password
     */
    public function __construct($username, $password)
    {
        $this->VARS['username'] = $username;
        $this->VARS['password'] = $password;
        $this->ACCOUNT_INFO = $this->login();
    }

    /**
     * @param $from
     * @param $to
     * @param $exp
     * @param $ipin
     * @param $amount
     * @return array
     */
    public function transfer($from, $to, $exp, $ipin, $amount)
    {

        $uuid = $this->uuid();
        $response = $this->client('moneyTransfer', array(
            'token' => $this->ACCOUNT_INFO['token'],
            'userid' => $this->ACCOUNT_INFO['userid'],
            'uuid' => $uuid,
            'panfrom' => $from,
            'panto' => $to,
            'ipin' => $this->encryption($uuid . $ipin),
            'expdate' => $exp,
            'amount' => $amount,
            'app_version' => $this->APP_VERSION
        ));
        if ($response) {
            $data = $response;
            if ($data['statuscode'] != 0) {
                $msgReturn = json_decode($data['message'], true);
                if (isset($msgReturn['output_responsemessage']))
                    return array('status' => 'error', 'message' => $msgReturn['output_responsemessage']);
                else
                    return array('status' => 'error', 'message' => $data['message']);
            } else {
                return array(
                    'status' => 'success',
                    'transaction' => array(
                        'params' => array(
                            'token' => $this->ACCOUNT_INFO['token'],
                            'userid' => $this->ACCOUNT_INFO['userid'],
                            'uuid' => $uuid,
                            'panfrom' => $from,
                            'panto' => $to,
                            'expdate' => $exp,
                            'amount' => $amount,
                            'app_version' => $this->APP_VERSION
                        ),
                        'response' => json_decode($data['message'], true)
                    )
                );
            }
        } else {
            return array('status' => 'error', 'message' => 'Internal server error');
        }
    }

    /**
     * @param $uuid
     * @return array
     */
    public function status($uuid)
    {

        $_uuid = $this->uuid();
        $response = $this->client('moneyTransfer', array(
            'token' => $this->ACCOUNT_INFO['token'],
            'userid' => $this->ACCOUNT_INFO['userid'],
            'uuid' => $_uuid,
            'originalUUID ' => $uuid,
            'app_version' => $this->APP_VERSION
        ));
        if ($response) {
            $data = $response;
            if ($data['statuscode'] != 0) {
                $msgReturn = json_decode($data['message'], true);
                if (isset($msgReturn['output_responsemessage']))
                    return array('status' => 'error', 'message' => $msgReturn['output_responsemessage']);
                else
                    return array('status' => 'error', 'message' => $data['message']);
            } else {
                return array(
                    'status' => 'success'
                );
            }
        } else {
            return array('status' => 'error', 'message' => 'Internal server error');
        }
    }

    /**
     * @return array
     */
    private function login()
    {
        $response = $this->client('login', array(
            'username' => $this->VARS['username'],
            'password' => $this->VARS['password'],
            'app_version' => $this->APP_VERSION,
        ));

        if ($response) {
            $data = $response;
            if (isset($data['token']))
                return array(
                    "status" => 'success',
                    "token" => base64_decode($data['token']),
                    "userid" => base64_decode($data['id'])
                );
            else
                return array(
                    "status" => "error",
                    "message" => (isset($data['message'])) ? $data['message'] : "Internal Payment Error !, try again later"
                );
        } else {
            return array(
                "status" => "error",
                "message" => "Internal Payment Error !, try again later"
            );
        }
    }

    /**
     * @return string
     */
    private function uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }

    /**
     * @param $buffer
     * @return string
     */
    private function encryption($buffer)
    {
        $rsa = new RSA();
        $rsa->loadKey($this->PUB_KEY);
        $rsa->setEncryptionMode(2); //
        return base64_encode($rsa->encrypt($buffer));
    }

    /**
     * @param $path
     * @param $data
     * @return mixed
     */
    private function client($path, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->API . $path);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }
}