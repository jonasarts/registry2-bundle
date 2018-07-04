<?php

/*
 * This file is part of the jonasarts Registry bundle package.
 *
 * (c) Jonas Hauser <symfony@jonasarts.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jonasarts\Bundle\RegistryBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * This tests are executed on a real database!
 * Therefore they need a proper database setup.
 * Best practice is to use config_test.yml.
 * 
 * DO NOT TEST ON A PRODUCTION SYSTEM!
 * 
 * Important assumption:
 * The tests below must be executed in order
 * (to maintain write before delete operations).
 */
class RegistryTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var Registry
     */
    private $registry;

    const _user = 2;
    const _bln = true;
    const _int = 10;
    const _str = 'test string';
    const _flt = 0.5;
    const _dat = '2013-10-16';

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        
        self::bootKernel();

        /*
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
        ;
        $this->registry = static::$kernel->getContainer()
            ->get('registry')
        ;
        */

        // Symfony 4.1

        // returns the real and unchanged service container
        //$container = self::$kernel->getContainer();

        // gets the special container that allows fetching private services
        $container = self::$container;

        $this->em = $container->get('doctrine')->getManager();
        $this->registry = $container->get('registry');

    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {

        // remove all test keys so no key remains in storage
        $r = $this->registry->registryDelete(self::_user, 'key', 'name_bln', 'bln');
        $r = $this->registry->registryDelete(0, 'key', 'name_bln', 'bln');
        $r = $this->registry->registryDelete(self::_user, 'key', 'name_int', 'int');
        $r = $this->registry->registryDelete(0, 'key', 'name_int', 'int');
        $r = $this->registry->registryDelete(self::_user, 'key', 'name_str', 'str');
        $r = $this->registry->registryDelete(0, 'key', 'name_str', 'str');
        $r = $this->registry->registryDelete(self::_user, 'key', 'name_flt', 'flt');
        $r = $this->registry->registryDelete(0, 'key', 'name_flt', 'flt');
        $r = $this->registry->registryDelete(self::_user, 'key', 'name_dat', 'dat');
        $r = $this->registry->registryDelete(0, 'key', 'name_dat', 'dat');

        $r = $this->registry->systemDelete('key', 'name_bln', 'bln');
        $r = $this->registry->systemDelete('key', 'name_int', 'int');
        $r = $this->registry->systemDelete('key', 'name_str', 'str');
        $r = $this->registry->systemDelete('key', 'name_flt', 'flt');
        $r = $this->registry->systemDelete('key', 'name_dat', 'dat');

        $this->em->close();

        parent::tearDown();
    }

    /**
     * registry tests.
     */
    public function testRegistryReadDefaultBln()
    {
        $r = $this->registry->registryReadDefault(0, 'key', 'name_bln', 'bln', true);

        $this->assertEquals($r, true);
    }

    public function testRegistryReadDefaultInt()
    {
        $r = $this->registry->registryReadDefault(0, 'key', 'name_int', 'int', 5);

        $this->assertEquals(5, $r);
    }

    public function testRegistryReadDefaultStr()
    {
        $r = $this->registry->registryReadDefault(0, 'key', 'name_str', 'str', 'test');

        $this->assertEquals('test', $r);
    }

    public function testRegistryReadDefaultFlt()
    {
        $r = $this->registry->registryReadDefault(0, 'key', 'name_flt', 'flt', 5.5);

        $this->assertEquals(5.5, $r);
    }

    public function testRegistryReadDefaultDat()
    {
        $r = $this->registry->registryReadDefault(0, 'key', 'name_dat', 'dat', strtotime('2013-10-16'));

        $this->assertEquals(strtotime('2013-10-16'), $r);
    }

    public function testRegistryReadDefaultNull()
    {
        $r = $this->registry->registryReadDefault(0, 'key', 'name_null', 'int', null);

        $this->assertEquals(null, $r);
    }

    public function testRegistryReadOnce()
    {
        // read once must remove the key after reading once

        $r = $this->registry->registryWrite(0, 'once_key', 'name_bln', 'bln', true);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = $this->registry->registryReadOnce(0, 'once_key', 'name_bln', 'bln');

        $this->assertTrue($r, 'registryrReadOnce not successful');

        $r = $this->registry->registryExists(0, 'once_key', 'name_bln', 'bln');

        $this->assertFalse($r, 'registryExists not successful');
    }

    public function testRegistryWriteBln()
    {
        $r = $this->registry->registryWrite(0, 'key', 'name_bln', 'bln', self::_bln);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = $this->registry->registryRead(0, 'key', 'name_bln', 'bln');

        $this->assertEquals($r, self::_bln);
    }

    public function testRegistryWriteUserBln()
    {
        $r = $this->registry->registryWrite(self::_user, 'key', 'name_bln', 'bln', !self::_bln);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = $this->registry->registryRead(self::_user, 'key', 'name_bln', 'bln');

        $this->assertEquals($r, !self::_bln);
    }

    /*
    public function testRegistryReadBln()
    {
        $r = $this->registry->registryRead(0, 'key', 'name_bln', 'bln');

        $this->assertEquals(true, $r);
    }
    */

    /**
     * @depends testRegistryWriteUserBln
     */
    public function testRegistryDeleteUserBln()
    {
        $r = $this->registry->registryDelete(self::_user, 'key', 'name_bln', 'bln');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = $this->registry->registryReadDefault(self::_user, 'key', 'name_bln', 'bln', !self::_bln); // this must read WriteBln value

        $this->assertEquals($r, self::_bln);
    }

    /**
     * @depends testRegistryWriteBln
     */
    public function testRegistryDeleteBln()
    {
        $r = $this->registry->registryDelete(0, 'key', 'name_bln', 'bln');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = $this->registry->registryReadDefault(0, 'key', 'name_bln', 'bln', !self::_bln);

        $this->assertEquals($r, !self::_bln);
    }

    public function testRegistryWriteInt()
    {
        $r = $this->registry->registryWrite(0, 'key', 'name_int', 'int', self::_int);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = $this->registry->registryRead(0, 'key', 'name_int', 'int');

        $this->assertEquals($r, self::_int);
    }

    public function testRegistryWriteUserInt()
    {
        $r = $this->registry->registryWrite(self::_user, 'key', 'name_int', 'int', self::_int - 1);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = $this->registry->registryRead(self::_user, 'key', 'name_int', 'int');

        $this->assertEquals($r, self::_int - 1);
    }

    /*
    public function testRegistryReadInt()
    {
        $r = $this->registry->registryRead(0, 'key', 'name_int', 'int');

        $this->assertEquals($r, 10);
    }
    */

    /**
     * @depends testRegistryWriteUserInt
     */
    public function testRegistryDeleteUserInt()
    {
        $r = $this->registry->registryDelete(self::_user, 'key', 'name_int', 'int');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = $this->registry->registryReadDefault(self::_user, 'key', 'name_int', 'int', self::_int - 1); // this must read WriteInt value

        $this->assertEquals($r, self::_int);
    }

    /**
     * @depends testRegistryWriteInt
     */
    public function testRegistryDeleteInt()
    {
        $r = $this->registry->registryDelete(0, 'key', 'name_int', 'int');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = $this->registry->registryReadDefault(0, 'key', 'name_int', 'int', self::_int + 1);

        $this->assertEquals($r, self::_int + 1);
    }

    public function testRegistryWriteStr()
    {
        $r = $this->registry->registryWrite(0, 'key', 'name_str', 'str', self::_str);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = $this->registry->registryRead(0, 'key', 'name_str', 'str');

        $this->assertEquals($r, self::_str);
    }

    public function testRegistryWriteUserStr()
    {
        $r = $this->registry->registryWrite(self::_user, 'key', 'name_str', 'str', self::_str.self::_str);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = $this->registry->registryRead(self::_user, 'key', 'name_str', 'str');

        $this->assertEquals($r, self::_str.self::_str);
    }

    /*
    public function testRegistryReadStr()
    {
        $r = $this->registry->registryRead(0, 'key', 'name_str', 'str');

        $this->assertEquals($r, 'test');
    }
    */

    /**
     * @depends testRegistryWriteUserStr
     */
    public function testRegistryDeleteUserStr()
    {
        $r = $this->registry->registryDelete(self::_user, 'key', 'name_str', 'str');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = $this->registry->registryReadDefault(self::_user, 'key', 'name_str', 'str', self::_str.'default'); // this must read WriteStr value

        $this->assertEquals($r, self::_str);
    }

    /**
     * @depends testRegistryWriteStr
     */
    public function testRegistryDeleteStr()
    {
        $r = $this->registry->registryDelete(0, 'key', 'name_str', 'str');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = $this->registry->registryReadDefault(0, 'key', 'name_str', 'str', self::_str.'default');

        $this->assertEquals($r, self::_str.'default');
    }

    public function testRegistryWriteFlt()
    {
        $r = $this->registry->registryWrite(0, 'key', 'name_flt', 'flt', self::_flt);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = $this->registry->registryRead(0, 'key', 'name_flt', 'flt');

        $this->assertEquals($r, self::_flt);
    }

    public function testRegistryWriteUserFlt()
    {
        $r = $this->registry->registryWrite(self::_user, 'key', 'name_flt', 'flt', self::_flt + 0.1);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = $this->registry->registryRead(self::_user, 'key', 'name_flt', 'flt');

        $this->assertEquals($r, self::_flt + 0.1);
    }

    /*
    public function testRegistryReadFlt()
    {
        $r = $this->registry->registryRead(0, 'key', 'name_flt', 'flt');

        $this->assertEquals($r, 0.5);
    }
    */

    /**
     * @depends testRegistryWriteUserFlt
     */
    public function testRegistryDeleteUserFlt()
    {
        $r = $this->registry->registryDelete(self::_user, 'key', 'name_flt', 'flt');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = $this->registry->registryReadDefault(self::_user, 'key', 'name_flt', 'flt', self::_flt + 0.25); // this must read WriteFlt value

        $this->assertEquals($r, self::_flt);
    }

    /**
     * @depends testRegistryWriteFlt
     */
    public function testRegistryDeleteFlt()
    {
        $r = $this->registry->registryDelete(0, 'key', 'name_flt', 'flt');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = $this->registry->registryReadDefault(0, 'key', 'name_flt', 'flt', self::_flt + 0.25);

        $this->assertEquals($r, self::_flt + 0.25);
    }

    public function testRegistryWriteDat()
    {
        $r = $this->registry->registryWrite(0, 'key', 'name_dat', 'dat', strtotime(self::_dat));

        $this->assertTrue($r, 'registryWrite not successful');

        $r = $this->registry->registryRead(0, 'key', 'name_dat', 'dat');

        $this->assertEquals($r, strtotime(self::_dat));
    }

    public function testRegistryWriteUserDat()
    {
        $r = $this->registry->registryWrite(self::_user, 'key', 'name_dat', 'dat', strtotime('1980-01-01'));

        $this->assertTrue($r, 'registryWrite not successful');

        $r = $this->registry->registryRead(self::_user, 'key', 'name_dat', 'dat');

        $this->assertEquals($r, strtotime('1980-01-01'));
    }

    /*
    public function testRegistryReadDat()
    {
        $r = $this->registry->registryRead(0, 'key', 'name_dat', 'dat');

        $this->assertEquals($r, strtotime('2013-10-16'));
    }
    */

    /**
     * @depends testRegistryWriteUserDat
     */
    public function testRegistryDeleteUserDat()
    {
        $r = $this->registry->registryDelete(self::_user, 'key', 'name_dat', 'dat');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = $this->registry->registryReadDefault(self::_user, 'key', 'name_dat', 'dat', strtotime('now')); // this must read WriteDat value

        $this->assertEquals($r, strtotime(self::_dat));
    }

    /**
     * @depends testRegistryWriteDat
     */
    public function testRegistryDeleteDat()
    {
        $r = $this->registry->registryDelete(0, 'key', 'name_dat', 'dat');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = $this->registry->registryReadDefault(0, 'key', 'name_dat', 'dat', strtotime('now'));

        $this->assertEquals($r, strtotime('now'));
    }

    public function testRegistryWriteUser0MatchingVale()
    {
        // if user-key-value equals user-0-value, the user-key-value must be deleted on write

        $this->registry->registryWrite(0, 'key', 'name_int', 'int', self::_int); // user-0-value
        $this->registry->registryWrite(1, 'key', 'name_int', 'int', self::_int + 1); // user-key-value

        $r = $this->registry->registryRead(1, 'key', 'name_int', 'int');

        $this->assertEquals($r, self::_int + 1);

        $this->registry->registryWrite(1, 'key', 'name_int', 'int', self::_int); // this must delete the user-key-value

        //$r = $this->registry->registryRead(1, 'key', 'name_int', 'int');
        $r = $this->registry->registryExists(1, 'key', 'name_int', 'int');

        //$this->assertEquals($r, self::_int);
        $this->assertEquals($r, false);

        $this->registry->registryDelete(0, 'key', 'name_int', 'int');
    }

    /**
     * system tests.
     */
    public function testSystemReadDefaultBln()
    {
        $r = $this->registry->systemReadDefault('key', 'name_bln', 'bln', true);

        $this->assertEquals(true, $r);
    }

    public function testSystemReadDefaultInt()
    {
        $r = $this->registry->systemReadDefault('key', 'name_int', 'int', 5);

        $this->assertEquals(5, $r);
    }

    public function testSystemReadDefaultStr()
    {
        $r = $this->registry->systemReadDefault('key', 'name_str', 'str', 'test');

        $this->assertEquals('test', $r);
    }

    public function testSystemReadDefaultFlt()
    {
        $r = $this->registry->systemReadDefault('key', 'name_flt', 'flt', 5.5);

        $this->assertEquals(5.5, $r);
    }

    public function testSystemReadDefaultDat()
    {
        $r = $this->registry->systemReadDefault('key', 'name_dat', 'dat', strtotime('2013-10-16'));

        $this->assertEquals(strtotime('2013-10-16'), $r);
    }

    public function testSystemReadDefaultNull()
    {
        $r = $this->registry->systemReadDefault('key', 'name_null', 'int', null);

        $this->assertEquals(null, $r);
    }

    public function testSystemReadOnce()
    {
        $r = $this->registry->systemWrite('once_key', 'name_bln', 'bln', true);

        $this->assertTrue($r, 'systemWrite not successful');

        $r = $this->registry->systemReadOnce('once_key', 'name_bln', 'bln');

        $this->assertTrue($r, 'systemReadOnce not successful');

        $r = $this->registry->systemExists('once_key', 'name_bln', 'bln');

        $this->assertFalse($r, 'systemExists not successful');
    }

    public function testSystemWriteBln()
    {
        $r = $this->registry->systemWrite('key', 'name_bln', 'bln', self::_bln);

        $this->assertTrue($r, 'systemWrite not successful');

        $r = $this->registry->systemRead('key', 'name_bln', 'bln');

        $this->assertEquals(self::_bln, $r);
    }

    public function testSystemDeleteBln()
    {
        $r = $this->registry->systemDelete('key', 'name_bln', 'bln');

        $this->assertTrue($r, 'systemDelete not successful');

        $r = $this->registry->systemReadDefault('key', 'name_bln', 'bln', !self::_bln);

        $this->assertEquals(!self::_bln, $r);
    }

    public function testSystemWriteInt()
    {
        $r = $this->registry->systemWrite('key', 'name_int', 'int', self::_int);

        $this->assertTrue($r, 'systemWrite not successful');

        $r = $this->registry->systemRead('key', 'name_int', 'int');

        $this->assertEquals(self::_int, $r);
    }

    public function testSystemDeleteInt()
    {
        $r = $this->registry->systemDelete('key', 'name_int', 'int');

        $this->assertTrue($r, 'systemDelete not successful');

        $r = $this->registry->systemReadDefault('key', 'name_int', 'int', self::_int + 1);

        $this->assertEquals(self::_int + 1, $r);
    }

    public function testSystemWriteStr()
    {
        $r = $this->registry->systemWrite('key', 'name_str', 'str', self::_str);

        $this->assertTrue($r, 'systemWrite not successful');

        $r = $this->registry->systemRead('key', 'name_str', 'str');

        $this->assertEquals(self::_str, $r);
    }

    public function testSystemDeleteStr()
    {
        $r = $this->registry->systemDelete('key', 'name_str', 'str');

        $this->assertTrue($r, 'systemDelete not successful');

        $r = $this->registry->systemReadDefault('key', 'name_str', 'str', self::_str.'default');

        $this->assertEquals(self::_str.'default', $r);
    }

    public function testSystemWriteFlt()
    {
        $r = $this->registry->systemWrite('key', 'name_flt', 'flt', self::_flt);

        $this->assertTrue($r, 'systemWrite not successful');

        $r = $this->registry->systemRead('key', 'name_flt', 'flt');

        $this->assertEquals(self::_flt, $r);
    }

    public function testSystemDeleteFlt()
    {
        $r = $this->registry->systemDelete('key', 'name_flt', 'flt');

        $this->assertTrue($r, 'systemDelete not successful');

        $r = $this->registry->systemReadDefault('key', 'name_flt', 'flt', self::_flt - 0.1);

        $this->assertEquals(self::_flt - 0.1, $r);
    }

    public function testSystemWriteDat()
    {
        $r = $this->registry->systemWrite('key', 'name_dat', 'dat', strtotime(self::_dat));

        $this->assertTrue($r, 'systemWrite not successful');

        $r = $this->registry->systemRead('key', 'name_dat', 'dat');

        $this->assertEquals(strtotime(self::_dat), $r);
    }

    public function testSystemDeleteDat()
    {
        $r = $this->registry->systemDelete('key', 'name_dat', 'dat');

        $this->assertTrue($r, 'systemDelete not successful');

        $r = $this->registry->systemReadDefault('key', 'name_dat', 'dat', strtotime('1990-01-01'));

        $this->assertEquals(strtotime('1990-01-01'), $r);
    }

}
