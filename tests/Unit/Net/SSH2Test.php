<?php

/**
 * @author    Marc Scholten <marc@pedigital.de>
 * @copyright 2013 Marc Scholten
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

class Unit_Net_SSH2Test extends PhpseclibTestCase
{
    public function formatLogDataProvider()
    {
        return array(
            array(
                array('hello world'),
                array('<--'),
                "<--\r\n00000000  68:65:6c:6c:6f:20:77:6f:72:6c:64                 hello world\r\n\r\n"
            ),
            array(
                array('hello', 'world'),
                array('<--', '<--'),
                "<--\r\n00000000  68:65:6c:6c:6f                                   hello\r\n\r\n" .
                "<--\r\n00000000  77:6f:72:6c:64                                   world\r\n\r\n"
            ),
        );
    }

    /**
     * @dataProvider formatLogDataProvider
     */
    public function testFormatLog(array $message_log, array $message_number_log, $expected)
    {
        $ssh = $this->createSSHMock();

        $result = $ssh->_format_log($message_log, $message_number_log);
        $this->assertEquals($expected, $result);
    }

    public function testGenerateIdentifier()
    {
        $identifier = $this->createSSHMock()->_generate_identifier();
        $this->assertStringStartsWith('SSH-2.0-phpseclib_2.0', $identifier);

        if (extension_loaded('libsodium')) {
            $this->assertContains('libsodium', $identifier);
        }

        if (extension_loaded('openssl')) {
            $this->assertContains('openssl', $identifier);
            $this->assertNotContains('mcrypt', $identifier);
        } elseif (extension_loaded('mcrypt')) {
            $this->assertNotContains('openssl', $identifier);
            $this->assertContains('mcrypt', $identifier);
        } else {
            $this->assertNotContains('openssl', $identifier);
            $this->assertNotContains('mcrypt', $identifier);
        }

        if (extension_loaded('gmp')) {
            $this->assertContains('gmp', $identifier);
            $this->assertNotContains('bcmath', $identifier);
        } elseif (extension_loaded('bcmath')) {
            $this->assertNotContains('gmp', $identifier);
            $this->assertContains('bcmath', $identifier);
        } else {
            $this->assertNotContains('gmp', $identifier);
            $this->assertNotContains('bcmath', $identifier);
        }
    }

    public function testGetExitStatusIfNotConnected()
    {
        $ssh = $this->createSSHMock();

        $this->assertFalse($ssh->getExitStatus());
    }

    public function testPTYIDefaultValue()
    {
        $ssh = $this->createSSHMock();
        $this->assertFalse($ssh->isPTYEnabled());
    }

    public function testEnablePTY()
    {
        $ssh = $this->createSSHMock();

        $ssh->enablePTY();
        $this->assertTrue($ssh->isPTYEnabled());

        $ssh->disablePTY();
        $this->assertFalse($ssh->isPTYEnabled());
    }

    public function testQuietModeDefaultValue()
    {
        $ssh = $this->createSSHMock();

        $this->assertFalse($ssh->isQuietModeEnabled());
    }

    public function testEnableQuietMode()
    {
        $ssh = $this->createSSHMock();

        $ssh->enableQuietMode();
        $this->assertTrue($ssh->isQuietModeEnabled());

        $ssh->disableQuietMode();
        $this->assertFalse($ssh->isQuietModeEnabled());
    }

    public function testDefaultEncryptionAlgorithms()
    {
        $ssh = $this->createSSHMock();

        $this->assertSame(
            array (
                'arcfour256',
                'arcfour128',
                'aes128-ctr',
                'aes192-ctr',
                'aes256-ctr',
                'twofish128-ctr',
                'twofish192-ctr',
                'twofish256-ctr',
                'aes128-cbc',
                'aes192-cbc',
                'aes256-cbc',
                'twofish128-cbc',
                'twofish192-cbc',
                'twofish256-cbc',
                'twofish-cbc',
                'blowfish-ctr',
                'blowfish-cbc',
                '3des-ctr',
                '3des-cbc',
            ),
            $ssh->getEncryptionAlgorithms()
        );
    }

    public function testOverwrittenEncryptionAlgorithms()
    {
        $ssh = $this->createSSHMock();
        $ssh->setEncryptionAlgorithms(array(
            'aes128-ctr',
            'aes192-ctr',
            'aes256-ctr',
        ));

        $this->assertSame(
            array (
                'aes128-ctr',
                'aes192-ctr',
                'aes256-ctr',
            ),
            $ssh->getEncryptionAlgorithms()
        );
    }

    public function testUnsupportedEncryptionAlgorithms()
    {
        $this->setExpectedException('PHPUnit_Framework_Error_Notice', 'Encryption algorithms are not supported: unsupported-algorithm');
        $ssh = $this->createSSHMock();
        $ssh->setEncryptionAlgorithms(array(
            'aes128-ctr',
            'unsupported-algorithm'
        ));
    }

    /**
     * @return \phpseclib\Net\SSH2
     */
    protected function createSSHMock()
    {
        return $this->getMockBuilder('phpseclib\Net\SSH2')
            ->disableOriginalConstructor()
            ->setMethods(array('__destruct'))
            ->getMock();
    }
}
