<?php
include('File/CMS.php');

$privKey = new Crypt_RSA();
extract($privKey->createKey());
$privKey->loadKey($privatekey);

$pubKey = new Crypt_RSA();
$pubKey->loadKey($publickey);
$pubKey->setPublicKey();

$subject = new File_X509();
$subject->setDNProp('id-at-organizationName', 'demo cert');
$subject->setPublicKey($pubKey);

$issuer = new File_X509();
$issuer->setPrivateKey($privKey);
$issuer->setDN($subject->getDN());

$x509 = new File_X509();
//$x509->setSerialNumber(chr(0));

$result = $x509->sign($issuer, $subject);
$privkey = $privKey->getPrivateKey();
$rsa = new Crypt_RSA();
$rsa->loadKey($privkey);
$x509 = $x509->saveX509($result);

$cms = new File_CMS();
$cms->setData('hello, world!');
$cms->addSigner($x509, $rsa);
echo $cms->save();