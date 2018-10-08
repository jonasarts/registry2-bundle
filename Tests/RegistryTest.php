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

use jonasarts\Bundle\RegistryBundle\Registry\DoctrineRegistry;
use jonasarts\Bundle\RegistryBundle\Registry\RedisRegistry;

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
    private static $em;

    /**
     * @var Registry
     */
    private static $registry;

    const _user = 2;
    const _bln = true;
    const _int = 10;
    const _str = 'test string';
    const _flt = 0.5;
    const _dat = '2013-10-16';

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass()
    {
        if (true) {
            // remove all test keys so no key remains in storage
            $r = static::$registry->registryDelete(self::_user, 'key', 'name_bln', 'bln');
            $r = static::$registry->registryDelete(0, 'key', 'name_bln', 'bln');
            $r = static::$registry->registryDelete(self::_user, 'key', 'name_int', 'int');
            $r = static::$registry->registryDelete(0, 'key', 'name_int', 'int');
            $r = static::$registry->registryDelete(self::_user, 'key', 'name_str', 'str');
            $r = static::$registry->registryDelete(0, 'key', 'name_str', 'str');
            $r = static::$registry->registryDelete(self::_user, 'key', 'name_flt', 'flt');
            $r = static::$registry->registryDelete(0, 'key', 'name_flt', 'flt');
            $r = static::$registry->registryDelete(self::_user, 'key', 'name_dat', 'dat');
            $r = static::$registry->registryDelete(0, 'key', 'name_dat', 'dat');

            $r = static::$registry->systemDelete('key', 'name_bln', 'bln');
            $r = static::$registry->systemDelete('key', 'name_int', 'int');
            $r = static::$registry->systemDelete('key', 'name_str', 'str');
            $r = static::$registry->systemDelete('key', 'name_flt', 'flt');
            $r = static::$registry->systemDelete('key', 'name_dat', 'dat');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        self::bootKernel();

        // Symfony 4.1

        // returns the real and unchanged service container
        $container = self::$kernel->getContainer();

        // gets the special container that allows fetching private services
        $container = self::$container;

        //static::$em = $container->get('doctrine')->getManager();
        //static::$registry = $container->get('registry');

        // phpredis
        $redis = $container->get('snc_redis.phpredis.registry');
        static::$registry = new RedisRegistry($container, $redis);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {

        if (!empty(static::$em)) {
            static::$em->close();
        }

        parent::tearDown();
    }

    /**
     * registry tests.
     */
    public function testRegistryReadDefaultBln()
    {
        $r = static::$registry->registryReadDefault(0, 'key', 'name_bln', 'bln', true);

        $this->assertEquals($r, true);
    }

    public function testRegistryReadDefaultInt()
    {
        $r = static::$registry->registryReadDefault(0, 'key', 'name_int', 'int', 5);

        $this->assertEquals(5, $r);
    }

    public function testRegistryReadDefaultStr()
    {
        $r = static::$registry->registryReadDefault(0, 'key', 'name_str', 'str', 'test');

        $this->assertEquals('test', $r);
    }

    public function testRegistryReadDefaultFlt()
    {
        $r = static::$registry->registryReadDefault(0, 'key', 'name_flt', 'flt', 5.5);

        $this->assertEquals(5.5, $r);
    }

    public function testRegistryReadDefaultDat()
    {
        $r = static::$registry->registryReadDefault(0, 'key', 'name_dat', 'dat', strtotime('2013-10-16'));

        $this->assertEquals(strtotime('2013-10-16'), $r);
    }

    public function testRegistryReadDefaultNull()
    {
        $r = static::$registry->registryReadDefault(0, 'key', 'name_null', 'int', null);

        $this->assertEquals(null, $r);
    }

    public function testRegistryReadOnce()
    {
        // read once must remove the key after reading once

        $r = static::$registry->registryWrite(0, 'once_key', 'name_bln', 'bln', true);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = static::$registry->registryReadOnce(0, 'once_key', 'name_bln', 'bln');

        $this->assertTrue($r, 'registryrReadOnce not successful');

        $r = static::$registry->registryExists(0, 'once_key', 'name_bln', 'bln');

        $this->assertFalse($r, 'registryExists not successful');
    }

    public function testRegistryWriteBln()
    {
        $r = static::$registry->registryWrite(0, 'key', 'name_bln', 'bln', self::_bln);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = static::$registry->registryRead(0, 'key', 'name_bln', 'bln');

        $this->assertEquals($r, self::_bln);
    }

    public function testRegistryWriteUserBln()
    {
        $r = static::$registry->registryWrite(self::_user, 'key', 'name_bln', 'bln', !self::_bln);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = static::$registry->registryRead(self::_user, 'key', 'name_bln', 'bln');

        $this->assertEquals($r, !self::_bln);
    }

    /*
    public function testRegistryReadBln()
    {
        $r = static::$registry->registryRead(0, 'key', 'name_bln', 'bln');

        $this->assertEquals(true, $r);
    }
    */

    /**
     * @depends testRegistryWriteUserBln
     */
    public function testRegistryDeleteUserBln()
    {
        $r = static::$registry->registryDelete(self::_user, 'key', 'name_bln', 'bln');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = static::$registry->registryReadDefault(self::_user, 'key', 'name_bln', 'bln', !self::_bln); // this must read WriteBln value

        $this->assertEquals($r, self::_bln);
    }

    /**
     * @depends testRegistryWriteBln
     */
    public function testRegistryDeleteBln()
    {
        $r = static::$registry->registryDelete(0, 'key', 'name_bln', 'bln');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = static::$registry->registryReadDefault(0, 'key', 'name_bln', 'bln', !self::_bln);

        $this->assertEquals($r, !self::_bln);
    }

    public function testRegistryWriteInt()
    {
        $r = static::$registry->registryWrite(0, 'key', 'name_int', 'int', self::_int);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = static::$registry->registryRead(0, 'key', 'name_int', 'int');

        $this->assertEquals($r, self::_int);
    }

    public function testRegistryWriteUserInt()
    {
        $r = static::$registry->registryWrite(self::_user, 'key', 'name_int', 'int', self::_int - 1);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = static::$registry->registryRead(self::_user, 'key', 'name_int', 'int');

        $this->assertEquals($r, self::_int - 1);
    }

    /*
    public function testRegistryReadInt()
    {
        $r = static::$registry->registryRead(0, 'key', 'name_int', 'int');

        $this->assertEquals($r, 10);
    }
    */

    /**
     * @depends testRegistryWriteUserInt
     */
    public function testRegistryDeleteUserInt()
    {
        $r = static::$registry->registryDelete(self::_user, 'key', 'name_int', 'int');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = static::$registry->registryReadDefault(self::_user, 'key', 'name_int', 'int', self::_int - 1); // this must read WriteInt value

        $this->assertEquals($r, self::_int);
    }

    /**
     * @depends testRegistryWriteInt
     */
    public function testRegistryDeleteInt()
    {
        $r = static::$registry->registryDelete(0, 'key', 'name_int', 'int');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = static::$registry->registryReadDefault(0, 'key', 'name_int', 'int', self::_int + 1);

        $this->assertEquals($r, self::_int + 1);
    }

    public function testRegistryWriteStr()
    {
        $r = static::$registry->registryWrite(0, 'key', 'name_str', 'str', self::_str);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = static::$registry->registryRead(0, 'key', 'name_str', 'str');

        $this->assertEquals($r, self::_str);
    }

    public function testRegistryWriteUserStr()
    {
        $r = static::$registry->registryWrite(self::_user, 'key', 'name_str', 'str', self::_str.self::_str);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = static::$registry->registryRead(self::_user, 'key', 'name_str', 'str');

        $this->assertEquals($r, self::_str.self::_str);
    }

    /*
    public function testRegistryReadStr()
    {
        $r = static::$registry->registryRead(0, 'key', 'name_str', 'str');

        $this->assertEquals($r, 'test');
    }
    */

    /**
     * @depends testRegistryWriteUserStr
     */
    public function testRegistryDeleteUserStr()
    {
        $r = static::$registry->registryDelete(self::_user, 'key', 'name_str', 'str');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = static::$registry->registryReadDefault(self::_user, 'key', 'name_str', 'str', self::_str.'default'); // this must read WriteStr value

        $this->assertEquals($r, self::_str);
    }

    /**
     * @depends testRegistryWriteStr
     */
    public function testRegistryDeleteStr()
    {
        $r = static::$registry->registryDelete(0, 'key', 'name_str', 'str');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = static::$registry->registryReadDefault(0, 'key', 'name_str', 'str', self::_str.'default');

        $this->assertEquals($r, self::_str.'default');
    }

    public function testRegistryWriteFlt()
    {
        $r = static::$registry->registryWrite(0, 'key', 'name_flt', 'flt', self::_flt);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = static::$registry->registryRead(0, 'key', 'name_flt', 'flt');

        $this->assertEquals($r, self::_flt);
    }

    public function testRegistryWriteUserFlt()
    {
        $r = static::$registry->registryWrite(self::_user, 'key', 'name_flt', 'flt', self::_flt + 0.1);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = static::$registry->registryRead(self::_user, 'key', 'name_flt', 'flt');

        $this->assertEquals($r, self::_flt + 0.1);
    }

    /*
    public function testRegistryReadFlt()
    {
        $r = static::$registry->registryRead(0, 'key', 'name_flt', 'flt');

        $this->assertEquals($r, 0.5);
    }
    */

    /**
     * @depends testRegistryWriteUserFlt
     */
    public function testRegistryDeleteUserFlt()
    {
        $r = static::$registry->registryDelete(self::_user, 'key', 'name_flt', 'flt');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = static::$registry->registryReadDefault(self::_user, 'key', 'name_flt', 'flt', self::_flt + 0.25); // this must read WriteFlt value

        $this->assertEquals($r, self::_flt);
    }

    /**
     * @depends testRegistryWriteFlt
     */
    public function testRegistryDeleteFlt()
    {
        $r = static::$registry->registryDelete(0, 'key', 'name_flt', 'flt');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = static::$registry->registryReadDefault(0, 'key', 'name_flt', 'flt', self::_flt + 0.25);

        $this->assertEquals($r, self::_flt + 0.25);
    }

    public function testRegistryWriteDat()
    {
        $r = static::$registry->registryWrite(0, 'key', 'name_dat', 'dat', strtotime(self::_dat));

        $this->assertTrue($r, 'registryWrite not successful');

        $r = static::$registry->registryRead(0, 'key', 'name_dat', 'dat');

        $this->assertEquals($r, strtotime(self::_dat));
    }

    public function testRegistryWriteUserDat()
    {
        $r = static::$registry->registryWrite(self::_user, 'key', 'name_dat', 'dat', strtotime('1980-01-01'));

        $this->assertTrue($r, 'registryWrite not successful');

        $r = static::$registry->registryRead(self::_user, 'key', 'name_dat', 'dat');

        $this->assertEquals($r, strtotime('1980-01-01'));
    }

    /*
    public function testRegistryReadDat()
    {
        $r = static::$registry->registryRead(0, 'key', 'name_dat', 'dat');

        $this->assertEquals($r, strtotime('2013-10-16'));
    }
    */

    /**
     * @depends testRegistryWriteUserDat
     */
    public function testRegistryDeleteUserDat()
    {
        $r = static::$registry->registryDelete(self::_user, 'key', 'name_dat', 'dat');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = static::$registry->registryReadDefault(self::_user, 'key', 'name_dat', 'dat', strtotime('now')); // this must read WriteDat value

        $this->assertEquals($r, strtotime(self::_dat));
    }

    /**
     * @depends testRegistryWriteDat
     */
    public function testRegistryDeleteDat()
    {
        $r = static::$registry->registryDelete(0, 'key', 'name_dat', 'dat');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = static::$registry->registryReadDefault(0, 'key', 'name_dat', 'dat', strtotime('now'));

        $this->assertEquals($r, strtotime('now'));
    }

    public function testRegistryWriteUser0MatchingVale()
    {
        // if user-key-value equals user-0-value, the user-key-value must be deleted on write

        static::$registry->registryWrite(0, 'key', 'name_int', 'int', self::_int); // user-0-value
        static::$registry->registryWrite(1, 'key', 'name_int', 'int', self::_int + 1); // user-key-value

        $r = static::$registry->registryRead(1, 'key', 'name_int', 'int');

        $this->assertEquals($r, self::_int + 1);

        static::$registry->registryWrite(1, 'key', 'name_int', 'int', self::_int); // this must delete the user-key-value

        //$r = static::$registry->registryRead(1, 'key', 'name_int', 'int');
        $r = static::$registry->registryExists(1, 'key', 'name_int', 'int');

        //$this->assertEquals($r, self::_int);
        $this->assertEquals($r, false);

        static::$registry->registryDelete(0, 'key', 'name_int', 'int');
    }

    /**
     * system tests.
     */
    public function testSystemReadDefaultBln()
    {
        $r = static::$registry->systemReadDefault('key', 'name_bln', 'bln', true);

        $this->assertEquals(true, $r);
    }

    public function testSystemReadDefaultInt()
    {
        $r = static::$registry->systemReadDefault('key', 'name_int', 'int', 5);

        $this->assertEquals(5, $r);
    }

    public function testSystemReadDefaultStr()
    {
        $r = static::$registry->systemReadDefault('key', 'name_str', 'str', 'test');

        $this->assertEquals('test', $r);
    }

    public function testSystemReadDefaultFlt()
    {
        $r = static::$registry->systemReadDefault('key', 'name_flt', 'flt', 5.5);

        $this->assertEquals(5.5, $r);
    }

    public function testSystemReadDefaultDat()
    {
        $r = static::$registry->systemReadDefault('key', 'name_dat', 'dat', strtotime('2013-10-16'));

        $this->assertEquals(strtotime('2013-10-16'), $r);
    }

    public function testSystemReadDefaultNull()
    {
        $r = static::$registry->systemReadDefault('key', 'name_null', 'int', null);

        $this->assertEquals(null, $r);
    }

    public function testSystemReadOnce()
    {
        $r = static::$registry->systemWrite('once_key', 'name_bln', 'bln', true);

        $this->assertTrue($r, 'systemWrite not successful');

        $r = static::$registry->systemReadOnce('once_key', 'name_bln', 'bln');

        $this->assertTrue($r, 'systemReadOnce not successful');

        $r = static::$registry->systemExists('once_key', 'name_bln', 'bln');

        $this->assertFalse($r, 'systemExists not successful');
    }

    public function testSystemWriteBln()
    {
        $r = static::$registry->systemWrite('key', 'name_bln', 'bln', self::_bln);

        $this->assertTrue($r, 'systemWrite not successful');

        $r = static::$registry->systemRead('key', 'name_bln', 'bln');

        $this->assertEquals(self::_bln, $r);
    }

    public function testSystemDeleteBln()
    {
        $r = static::$registry->systemDelete('key', 'name_bln', 'bln');

        $this->assertTrue($r, 'systemDelete not successful');

        $r = static::$registry->systemReadDefault('key', 'name_bln', 'bln', !self::_bln);

        $this->assertEquals(!self::_bln, $r);
    }

    public function testSystemWriteInt()
    {
        $r = static::$registry->systemWrite('key', 'name_int', 'int', self::_int);

        $this->assertTrue($r, 'systemWrite not successful');

        $r = static::$registry->systemRead('key', 'name_int', 'int');

        $this->assertEquals(self::_int, $r);
    }

    public function testSystemDeleteInt()
    {
        $r = static::$registry->systemDelete('key', 'name_int', 'int');

        $this->assertTrue($r, 'systemDelete not successful');

        $r = static::$registry->systemReadDefault('key', 'name_int', 'int', self::_int + 1);

        $this->assertEquals(self::_int + 1, $r);
    }

    public function testSystemWriteStr()
    {
        $r = static::$registry->systemWrite('key', 'name_str', 'str', self::_str);

        $this->assertTrue($r, 'systemWrite not successful');

        $r = static::$registry->systemRead('key', 'name_str', 'str');

        $this->assertEquals(self::_str, $r);
    }

    public function testSystemDeleteStr()
    {
        $r = static::$registry->systemDelete('key', 'name_str', 'str');

        $this->assertTrue($r, 'systemDelete not successful');

        $r = static::$registry->systemReadDefault('key', 'name_str', 'str', self::_str.'default');

        $this->assertEquals(self::_str.'default', $r);
    }

    public function testSystemWriteFlt()
    {
        $r = static::$registry->systemWrite('key', 'name_flt', 'flt', self::_flt);

        $this->assertTrue($r, 'systemWrite not successful');

        $r = static::$registry->systemRead('key', 'name_flt', 'flt');

        $this->assertEquals(self::_flt, $r);
    }

    public function testSystemDeleteFlt()
    {
        $r = static::$registry->systemDelete('key', 'name_flt', 'flt');

        $this->assertTrue($r, 'systemDelete not successful');

        $r = static::$registry->systemReadDefault('key', 'name_flt', 'flt', self::_flt - 0.1);

        $this->assertEquals(self::_flt - 0.1, $r);
    }

    public function testSystemWriteDat()
    {
        $r = static::$registry->systemWrite('key', 'name_dat', 'dat', strtotime(self::_dat));

        $this->assertTrue($r, 'systemWrite not successful');

        $r = static::$registry->systemRead('key', 'name_dat', 'dat');

        $this->assertEquals(strtotime(self::_dat), $r);
    }

    public function testSystemDeleteDat()
    {
        $r = static::$registry->systemDelete('key', 'name_dat', 'dat');

        $this->assertTrue($r, 'systemDelete not successful');

        $r = static::$registry->systemReadDefault('key', 'name_dat', 'dat', strtotime('1990-01-01'));

        $this->assertEquals(strtotime('1990-01-01'), $r);
    }

}
