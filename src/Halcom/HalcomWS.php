<?php

/**
 * @package Halcom
 */
namespace Halcom;

use Halcom\HalcomWSSoapClient;

/**
 * HalcomWS represents the service in the WSDL.
 *
 * @package HalcomWS
 * @author Anze Nunar <anze@nunar.si>
 */
class HalcomWS
{
    /**
     * Endpoint.
     *
     * @var string
     */
    private $endpoint = 'https://ws.halcom.si/';

    /**
     * Stream Contexts.
     *
     * @var array
     */
    private $streamContext = [];

    /**
     * Create a new instance.
     *
     * @param  string  $localCert
     * @param  string  $localPk
     * @param  bool  $verifyPeer
     * @param  bool  $allowSelfSigned
     * @return void
     */
    public function __construct($localCert = null, $localPk = null, $allowSelfSigned = false, $verifyPeer = true)
    {
        if (empty($localCert) && empty($localPk) && function_exists('env')) {
            $pairs = [
                'HALCOM_LOCAL_CERT' => 'localCert',
                'HALCOM_LOCAL_PK' => 'localPk',
                'HALCOM_ALLOW_SELF_SIGNED' => 'allowSelfSigned',
                'HALCOM_VERIFY_PEER' => 'verifyPeer',
            ];

            foreach ($pairs as $value => $var) {
                $$var = env($value);
            }
        }

        $this->streamContext = [
            'ssl' => [
                'allow_self_signed' => $allowSelfSigned,
                'local_cert' => $localCert,
                'local_pk' => $localPk,
                'verify_peer' => $verifyPeer,
            ]
        ];
    }

    /**
     * Initialize SOAP client.
     *
     * @param  string  $endpoint
     * @param  string  $cert
     * @param  int|null  $taxNumber
     * @param  int|null  $companyTaxNumber
     * @return \HalcomWSSoapClient
     */
    private function soapClient($endpoint, $cert, $taxNumber = null, $companyTaxNumber = null)
    {
        $soapClient = new HalcomWSSoapClient($endpoint, [
            'stream_context' => stream_context_create($this->streamContext),
            'trace' => 0,
            'exceptions' => 0,
            'cache_wsdl' => WSDL_CACHE_NONE,
        ]);
        $soapClient->cert = str_replace(['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----'], '', $cert);
        $soapClient->taxNumber = $taxNumber;
        $soapClient->companyTaxNumber = $companyTaxNumber;
        return $soapClient;
    }

    /**
     * Web service for verifying the validity of digital certificates:
     *  - time validity,
     *  - correctness of certificate validity,
     *  - certificate status in the list of revoked certificates.
     *
     * @param  string  $cert
     * @return array
     */
    public function certificateStatus($cert)
    {
        $soapClient = $this->soapClient($this->endpoint.ucfirst(__FUNCTION__).'/'.ucfirst(__FUNCTION__).'.wsdl', $cert);
        return $soapClient->getCertificateStatus();
    }

    /**
     *  Web service for returning information about the digital certificate and its holder.
     *
     * @param  string  $cert
     * @return array
     */
    public function certificateInfo($cert)
    {
        $soapClient = $this->soapClient($this->endpoint.ucfirst(__FUNCTION__).'/'.ucfirst(__FUNCTION__).'.wsdl', $cert);
        return $soapClient->getCertificateInfo();
    }

    /**
     *  Web service for verifying tax numbers of digital certificates (holder tax number, company tax number). 
     *
     * @param  string  $cert
     * @param  int|null  $taxNumber
     * @param  int|null  $companyTaxNumber
     * @return array
     */
    public function certificateTaxNumbers($cert, $taxNumber = null, $companyTaxNumber = null)
    {
        $soapClient = $this->soapClient($this->endpoint.ucfirst(__FUNCTION__).'/'.ucfirst(__FUNCTION__).'.wsdl', $cert, $taxNumber, $companyTaxNumber);
        return $soapClient->verifyCertificateTaxNumbers();
    }
}
