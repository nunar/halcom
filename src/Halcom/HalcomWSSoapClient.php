<?php

/**
 * @package Halcom
 */
namespace Halcom;

/**
 * Extend SoapClient.
 *
 * @package HalcomWS
 * @author Anze Nunar <anze@nunar.si>
 */
class HalcomWSSoapClient extends \SoapClient
{
    /**
     * Certificate.
     *
     * @var string
     */
    public $cert;

    /**
     * Tax Number.
     *
     * @var int
     */
    public $taxNumber;

    /**
     * Company Tax Number.
     *
     * @var int
     */
    public $companyTaxNumber;

    /**
     * Fix SAOP request.
     *
     * @param  string  $request
     * @param  string  $location
     * @param  string  $action
     * @param  int  $version
     * @param  int|null  $oneWay
     * @return string
     */
    public function __doRequest($request, $location, $action, $version, $oneWay = 0)
    {
        // certificateInfo and certificateStatus
        $request = str_replace('<certificate xsi:type="xsd:base64Binary">QXJyYXk=</certificate>', '<certificate xsi:type="xsd:base64Binary">'.$this->cert.'</certificate>', $request);

        // certificateTaxNumbers
        $request = str_replace('<taxNumber xsi:nil="true"/>', '<taxNumber xsi:type="xsd:string">'.$this->taxNumber.'</taxNumber>', $request);
        $request = str_replace('<companyTaxNumber xsi:nil="true"/>', '<companyTaxNumber xsi:type="xsd:string">'.$this->companyTaxNumber.'</companyTaxNumber>', $request);
        $request = str_replace('<certificate xsi:nil="true"/>', '<certificate xsi:type="xsd:base64Binary">'.$this->cert.'</certificate>', $request);

        $request = str_replace("\n", "", $request);
        return parent::__doRequest($request, $location, $action, $version, $oneWay);
    }
}
