# One For All Web Service (by [Halcom](https://www.halcom.si/en/))
*Disclaimer: The presentation text below is taken from the [official website](http://www.halcom.si/en/products/web-service/web-service-2/).*

One For All Web Service enables the e-business applications to be independent from specific agencies, who issue digital certificates.
The web service supports the certificates of all Slovenian certificate agencies: [SIGEN-CA](https://www.sigen-ca.si), [SIGOV-CA](http://www.sigov-ca.gov.si), [ACNLB](https://www.nlb.si/ac-nlb), [POŠTA®CA](https://postarca.posta.si) and [HALCOM-CA](http://www.halcom.si/si/storitve/certifikatna-agencija/). Certain functionalities of the web service are not supported for some special certificates issued by these agencies.

More information about this web service:
- [Halcom.si](http://www.halcom.si/si/produkti/spletni-servis-2/spletni-servis/)
- [Technical documentation](http://www.halcom.si/faq/getAttach/15/AA-00222/Halcom+spletni+servisi+-+tehnicna+dokumentacija.pdf) (sorry, only in Slovenian)

## Target users
Halcom’s web services for facilitating electronic business are intended for various electronic services providers that want to enable their users access to services using digital certificates of all registered certificate authorities in Slovenia, but do not want to deal with different ways registration authorities save data into digital certificates.

## Accessing and using these web services
The use of these web services is free of charge, but not anonymous. You can request access to web services [here](http://www.halcom.si/en/products/web-service/order-2/). After your request is placed, you will receive an email from [ca@halcom.si](mailto:ca@halcom.si) and then send them your clients’ *(public)* digital certificate.

## Installation
To install nunar/halcom just require it with composer

```php
composer require "nunar/halcom"
```

## Examples of usage

More information about how the web services work, as well as example of requests and responses are available in the [technical documentation](http://www.halcom.si/faq/getAttach/15/AA-00222/Halcom+spletni+servisi+-+tehnicna+dokumentacija.pdf).

Firstly, we have to save certificate (in PEM format) to variable, e.g. `$cert`. All examples are based on [cert.pem](cert.pem).

```php
$cert = file_get_contents('cert.pem');
```

### Initialization
For its operation, the web service requires the client's digital certificate.

```php
require 'vendor/autoload.php';
use Halcom\HalcomWS;

$halcom = new HalcomWS('public.crt', 'private.key');
```

Web service WSDL file is available on the domain [https://ws.halcom.si](https://ws.halcom.si) which uses a certificate issued by [Halcom CA PO 2](http://www.halcom.si/si/pomoc/?action=showEntry&data=194&searchText=politike).
In most cases [Halcom CA PO 2](http://www.halcom.si/si/pomoc/?action=showEntry&data=194&searchText=politike) is not in CA store, so you have to add it or disable peer verification and allow self-signed certificates:

```php
$allowSelfSigned = true; // default false
$verifyPeer = false; // default true
$halcom = new HalcomWS('public.crt', 'private.key', $allowSelfSigned, $verifyPeer);
```

If you are using [Laravel](https://laravel.com) you can set this in `.env`

```
HALCOM_LOCAL_CERT=public.crt
HALCOM_LOCAL_PK=private.key
HALCOM_ALLOW_SELF_SIGNED=false
HALCOM_VERIFY_PEER=true
```

```php
$halcom = new HalcomWS();
```

### CertificateInfo Web Service
CertificateInfo Web Service is a web service for returning information about the digital certificate and its holder.

#### Request

```php
$halcom->certificateInfo($cert);
```

The CertificateInfo Web Service supports the certificates of all Slovenian certificate agencies, but the amount of information in the response depends on the certificate agency.

For all certificate agencies, the response will contain:
- issuer's common name (e.g. SIGEN-CA, SIGOV-CA, ACNLB, POŠTArCA, Halcom CA PO or Halcom CA PO 2),
- certificate serial number,
- period of certificate validity (from, to),
- the certificate holder's first and last name or common name,
- company mentioned in the certificate (for certificates held by the representatives of legal entities).

This web service will return a company tax number only when the certificate belongs to a company or a representative of a legel entity.

The holder's tax number will be returned only for certificates issued by [ACNLB](https://www.nlb.si/ac-nlb) or [POŠTA®CA](https://postarca.posta.si).

Company ID will be returned only for certificates issued by [HALCOM-CA](http://www.halcom.si/si/storitve/certifikatna-agencija/).

#### Response

```
Array
(
    [returnCode] => 0
    [returnText] => AR00000  OK
    [issuerCN] => Halcom CA PO 2
    [serialNumber] => 016B10
    [validFrom] => 2004-09-17T08:20:28.000Z
    [validTo] => 2007-09-17T08:20:28.000Z
    [firstName] => Andrej
    [lastName] => Komelj
    [commonName] => Andrej Komelj
    [company] => HALCOM D.D
    [taxNumber] => 90374312
    [companyTaxNumber] => 43353126
    [companyID] => 5556511000
    [other] => 
)
```

### CertificateStatus Web Service
CertificateStatus Web Service is a web service for verifying the validity of digital certificates:
- period of validity,
- validity of the signature on the certificate,
- certificate status on the list of revoked certificates.

#### Request

```php
$halcom->certificateStatus($cert);
```

#### Response

```
Array
(
    [returnCode] => 0
    [returnText] => AR00000  OK
    [certificateStatus] => 304
    [producedAt] => 2018-01-20T11:16:04.114Z
    [thisUpdate] => 
    [nextUpdate] => 
    [revocationReason] => 
    [revocationDate] => 1970-01-01T00:00:00.000Z
    [other] => 
)
```

### CertificateTaxNumbers Web Service
CertificateTaxNumbers Web Service is a web service for verifying tax numbers of digital certificates (holder tax number or company tax number).

The certificates held by representatives of legal entities and issued by the certificate agency [SIGEN-CA](https://www.sigen-ca.si) or [SIGOV-CA](http://www.sigov-ca.gov.si) don't include personal or employees' tax numbers, so the tax number verification is only possible for certificates from these agencies: [ACNLB](https://www.nlb.si/ac-nlb), [POŠTA®CA](https://postarca.posta.si) and [HALCOM-CA](http://www.halcom.si/si/storitve/certifikatna-agencija/).

#### Request

```php
$halcom->certificateTaxNumbers($cert, '90374312', '43353126');
```

#### Response

```
Array
(
    [returnCode] => 0
    [returnText] => AR00000  OK
    [taxNumberStatus] => 0
    [companyTaxNumberStatus] => 0
    [other] => 
)
```

It's also possible to verify only holder tax number or only company tax number:

```php
// holder tax number (only)
$halcom->certificateTaxNumbers($cert, '90374312');

// company tax number (only)
$halcom->certificateTaxNumbers($cert, null, '43353126');
```

In this case response status for `companyTaxNumberStatus` or `taxNumberStatus` will be **701**.
