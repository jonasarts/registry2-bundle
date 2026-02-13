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
use jonasarts\Bundle\RegistryBundle\Registry\RegistryInterface;

/**
 * These tests are executed on a real database!
 * Therefore, they need a proper database setup.
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
     * @var RegistryInterface
     */
    private static RegistryInterface $registry;

    const _user = 2;
    const _bln = true;
    const _int = 10;
    const _str = 'test string';
    const _flt = 0.5;
    const _dat = '2013-10-16';

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        //echo "setUpBeforeClass()";
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass(): void
    {
        //echo "tearDownAfterClass()";

        // remove all test keys so no key remains in storage
        self::$registry->registryDelete(self::_user, 'key', 'name_bln', 'bln');
        self::$registry->registryDelete(0, 'key', 'name_bln', 'bln');
        self::$registry->registryDelete(self::_user, 'key', 'name_int', 'int');
        self::$registry->registryDelete(0, 'key', 'name_int', 'int');
        self::$registry->registryDelete(self::_user, 'key', 'name_str', 'str');
        self::$registry->registryDelete(0, 'key', 'name_str', 'str');
        self::$registry->registryDelete(self::_user, 'key', 'name_flt', 'flt');
        self::$registry->registryDelete(0, 'key', 'name_flt', 'flt');
        self::$registry->registryDelete(self::_user, 'key', 'name_dat', 'dat');
        self::$registry->registryDelete(0, 'key', 'name_dat', 'dat');

        self::$registry->systemDelete('key', 'name_bln', 'bln');
        self::$registry->systemDelete('key', 'name_int', 'int');
        self::$registry->systemDelete('key', 'name_str', 'str');
        self::$registry->systemDelete('key', 'name_flt', 'flt');
        self::$registry->systemDelete('key', 'name_dat', 'dat');
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        //echo "setUp()";

        parent::setUp();

        // (1) boot the Symfony kernel
        self::bootKernel();

        // (2) use static::getContainer() to access the service container
        $container = static::getContainer();

        // (3) run some service & test the result ...

        // phpredis
        $redis = $container->get('snc_redis.registry');
        $prefix = "bundle-dev";
        $delimiter = "/";
        self::$registry = new RedisRegistry($redis, $prefix, $delimiter, null);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        //echo "tearDown()";

        parent::tearDown();
    }

    /**
     * registry tests.
     */
    public function testRegistryReadDefaultBln(): void
    {
        $r = self::$registry->registryReadDefault(0, 'key', 'name_bln', 'bln', true);

        $this->assertEquals($r, true);
    }

    public function testRegistryReadDefaultInt(): void
    {
        $r = self::$registry->registryReadDefault(0, 'key', 'name_int', 'int', 5);

        $this->assertEquals(5, $r);
    }

    public function testRegistryReadDefaultStr(): void
    {
        $r = self::$registry->registryReadDefault(0, 'key', 'name_str', 'str', 'test');

        $this->assertEquals('test', $r);
    }

    public function testRegistryReadDefaultFlt(): void
    {
        $r = self::$registry->registryReadDefault(0, 'key', 'name_flt', 'flt', 5.5);

        $this->assertEquals(5.5, $r);
    }

    public function testRegistryReadDefaultDat(): void
    {
        $r = self::$registry->registryReadDefault(0, 'key', 'name_dat', 'dat', strtotime('2013-10-16'));

        $this->assertEquals(strtotime('2013-10-16'), $r);
    }

    public function testRegistryReadDefaultNull(): void
    {
        $r = self::$registry->registryReadDefault(0, 'key', 'name_null', 'int', null);

        $this->assertEquals(null, $r);
    }

    public function testRegistryReadOnce(): void
    {
        // read once must remove the key after reading once

        $r = self::$registry->registryWrite(0, 'once_key', 'name_bln', 'bln', true);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = self::$registry->registryReadOnce(0, 'once_key', 'name_bln', 'bln');

        $this->assertTrue($r, 'registryrReadOnce not successful');

        $r = self::$registry->registryExists(0, 'once_key', 'name_bln', 'bln');

        $this->assertFalse($r, 'registryExists not successful');
    }

    public function testRegistryWriteBln(): void
    {
        $r = self::$registry->registryWrite(0, 'key', 'name_bln', 'bln', self::_bln);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = self::$registry->registryRead(0, 'key', 'name_bln', 'bln');

        $this->assertEquals($r, self::_bln);
    }

    public function testRegistryWriteUserBln(): void
    {
        $r = self::$registry->registryWrite(self::_user, 'key', 'name_bln', 'bln', false);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = self::$registry->registryRead(self::_user, 'key', 'name_bln', 'bln');

        $this->assertEquals($r, false);
    }

    /*
    public function testRegistryReadBln(): void
    {
        $r = self::$registry->registryRead(0, 'key', 'name_bln', 'bln');

        $this->assertEquals(true, $r);
    }
    */

    /**
     * @depends testRegistryWriteUserBln
     */
    public function testRegistryDeleteUserBln(): void
    {
        $r = self::$registry->registryDelete(self::_user, 'key', 'name_bln', 'bln');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = self::$registry->registryReadDefault(self::_user, 'key', 'name_bln', 'bln', false); // this must read WriteBln value

        $this->assertEquals($r, self::_bln);
    }

    /**
     * @depends testRegistryWriteBln
     */
    public function testRegistryDeleteBln(): void
    {
        $r = self::$registry->registryDelete(0, 'key', 'name_bln', 'bln');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = self::$registry->registryReadDefault(0, 'key', 'name_bln', 'bln', false);

        $this->assertEquals($r, false);
    }

    public function testRegistryWriteInt(): void
    {
        $r = self::$registry->registryWrite(0, 'key', 'name_int', 'int', self::_int);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = self::$registry->registryRead(0, 'key', 'name_int', 'int');

        $this->assertEquals($r, self::_int);
    }

    public function testRegistryWriteUserInt(): void
    {
        $r = self::$registry->registryWrite(self::_user, 'key', 'name_int', 'int', self::_int - 1);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = self::$registry->registryRead(self::_user, 'key', 'name_int', 'int');

        $this->assertEquals($r, self::_int - 1);
    }

    /*
    public function testRegistryReadInt(): void
    {
        $r = self::$registry->registryRead(0, 'key', 'name_int', 'int');

        $this->assertEquals($r, 10);
    }
    */

    /**
     * @depends testRegistryWriteUserInt
     */
    public function testRegistryDeleteUserInt(): void
    {
        $r = self::$registry->registryDelete(self::_user, 'key', 'name_int', 'int');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = self::$registry->registryReadDefault(self::_user, 'key', 'name_int', 'int', self::_int - 1); // this must read WriteInt value

        $this->assertEquals($r, self::_int);
    }

    /**
     * @depends testRegistryWriteInt
     */
    public function testRegistryDeleteInt(): void
    {
        $r = self::$registry->registryDelete(0, 'key', 'name_int', 'int');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = self::$registry->registryReadDefault(0, 'key', 'name_int', 'int', self::_int + 1);

        $this->assertEquals($r, self::_int + 1);
    }

    public function testRegistryWriteStr(): void
    {
        $r = self::$registry->registryWrite(0, 'key', 'name_str', 'str', self::_str);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = self::$registry->registryRead(0, 'key', 'name_str', 'str');

        $this->assertEquals($r, self::_str);
    }

    public function testRegistryWriteUserStr(): void
    {
        $r = self::$registry->registryWrite(self::_user, 'key', 'name_str', 'str', self::_str.self::_str);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = self::$registry->registryRead(self::_user, 'key', 'name_str', 'str');

        $this->assertEquals($r, self::_str.self::_str);
    }

    /*
    public function testRegistryReadStr(): void
    {
        $r = self::$registry->registryRead(0, 'key', 'name_str', 'str');

        $this->assertEquals($r, 'test');
    }
    */

    /**
     * @depends testRegistryWriteUserStr
     */
    public function testRegistryDeleteUserStr(): void
    {
        $r = self::$registry->registryDelete(self::_user, 'key', 'name_str', 'str');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = self::$registry->registryReadDefault(self::_user, 'key', 'name_str', 'str', self::_str.'default'); // this must read WriteStr value

        $this->assertEquals($r, self::_str);
    }

    /**
     * @depends testRegistryWriteStr
     */
    public function testRegistryDeleteStr(): void
    {
        $r = self::$registry->registryDelete(0, 'key', 'name_str', 'str');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = self::$registry->registryReadDefault(0, 'key', 'name_str', 'str', self::_str.'default');

        $this->assertEquals($r, self::_str.'default');
    }

    public function testRegistryWriteFlt(): void
    {
        $r = self::$registry->registryWrite(0, 'key', 'name_flt', 'flt', self::_flt);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = self::$registry->registryRead(0, 'key', 'name_flt', 'flt');

        $this->assertEquals($r, self::_flt);
    }

    public function testRegistryWriteUserFlt(): void
    {
        $r = self::$registry->registryWrite(self::_user, 'key', 'name_flt', 'flt', self::_flt + 0.1);

        $this->assertTrue($r, 'registryWrite not successful');

        $r = self::$registry->registryRead(self::_user, 'key', 'name_flt', 'flt');

        $this->assertEquals($r, self::_flt + 0.1);
    }

    /*
    public function testRegistryReadFlt(): void
    {
        $r = self::$registry->registryRead(0, 'key', 'name_flt', 'flt');

        $this->assertEquals($r, 0.5);
    }
    */

    /**
     * @depends testRegistryWriteUserFlt
     */
    public function testRegistryDeleteUserFlt(): void
    {
        $r = self::$registry->registryDelete(self::_user, 'key', 'name_flt', 'flt');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = self::$registry->registryReadDefault(self::_user, 'key', 'name_flt', 'flt', self::_flt + 0.25); // this must read WriteFlt value

        $this->assertEquals($r, self::_flt);
    }

    /**
     * @depends testRegistryWriteFlt
     */
    public function testRegistryDeleteFlt(): void
    {
        $r = self::$registry->registryDelete(0, 'key', 'name_flt', 'flt');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = self::$registry->registryReadDefault(0, 'key', 'name_flt', 'flt', self::_flt + 0.25);

        $this->assertEquals($r, self::_flt + 0.25);
    }

    public function testRegistryWriteDat(): void
    {
        $r = self::$registry->registryWrite(0, 'key', 'name_dat', 'dat', strtotime(self::_dat));

        $this->assertTrue($r, 'registryWrite not successful');

        $r = self::$registry->registryRead(0, 'key', 'name_dat', 'dat');

        $this->assertEquals($r, strtotime(self::_dat));
    }

    public function testRegistryWriteUserDat(): void
    {
        $r = self::$registry->registryWrite(self::_user, 'key', 'name_dat', 'dat', strtotime('1980-01-01'));

        $this->assertTrue($r, 'registryWrite not successful');

        $r = self::$registry->registryRead(self::_user, 'key', 'name_dat', 'dat');

        $this->assertEquals($r, strtotime('1980-01-01'));
    }

    /*
    public function testRegistryReadDat(): void
    {
        $r = self::$registry->registryRead(0, 'key', 'name_dat', 'dat');

        $this->assertEquals($r, strtotime('2013-10-16'));
    }
    */

    /**
     * @depends testRegistryWriteUserDat
     */
    public function testRegistryDeleteUserDat(): void
    {
        $r = self::$registry->registryDelete(self::_user, 'key', 'name_dat', 'dat');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = self::$registry->registryReadDefault(self::_user, 'key', 'name_dat', 'dat', strtotime('now')); // this must read WriteDat value

        $this->assertEquals($r, strtotime(self::_dat));
    }

    /**
     * @depends testRegistryWriteDat
     */
    public function testRegistryDeleteDat(): void
    {
        $r = self::$registry->registryDelete(0, 'key', 'name_dat', 'dat');

        $this->assertTrue($r, 'registryDelete not successful');

        $r = self::$registry->registryReadDefault(0, 'key', 'name_dat', 'dat', strtotime('now'));

        $this->assertEquals($r, strtotime('now'));
    }

    public function testRegistryWriteUser0MatchingVale(): void
    {
        // if user-key-value equals user-0-value, the user-key-value must be deleted on write

        self::$registry->registryWrite(0, 'key', 'name_int', 'int', self::_int); // user-0-value
        self::$registry->registryWrite(1, 'key', 'name_int', 'int', self::_int + 1); // user-key-value

        $r = self::$registry->registryRead(1, 'key', 'name_int', 'int');

        $this->assertEquals($r, self::_int + 1);

        self::$registry->registryWrite(1, 'key', 'name_int', 'int', self::_int); // this must delete the user-key-value

        //$r = self::$registry->registryRead(1, 'key', 'name_int', 'int');
        $r = self::$registry->registryExists(1, 'key', 'name_int', 'int');

        //$this->assertEquals($r, self::_int);
        $this->assertEquals($r, false);

        self::$registry->registryDelete(0, 'key', 'name_int', 'int');
    }

    /**
     * system tests.
     */
    public function testSystemReadDefaultBln(): void
    {
        $r = self::$registry->systemReadDefault('key', 'name_bln', 'bln', true);

        $this->assertEquals(true, $r);
    }

    public function testSystemReadDefaultInt(): void
    {
        $r = self::$registry->systemReadDefault('key', 'name_int', 'int', 5);

        $this->assertEquals(5, $r);
    }

    public function testSystemReadDefaultStr(): void
    {
        $r = self::$registry->systemReadDefault('key', 'name_str', 'str', 'test');

        $this->assertEquals('test', $r);
    }

    public function testSystemReadDefaultFlt(): void
    {
        $r = self::$registry->systemReadDefault('key', 'name_flt', 'flt', 5.5);

        $this->assertEquals(5.5, $r);
    }

    public function testSystemReadDefaultDat(): void
    {
        $r = self::$registry->systemReadDefault('key', 'name_dat', 'dat', strtotime('2013-10-16'));

        $this->assertEquals(strtotime('2013-10-16'), $r);
    }

    public function testSystemReadDefaultNull(): void
    {
        $r = self::$registry->systemReadDefault('key', 'name_null', 'int', null);

        $this->assertEquals(null, $r);
    }

    public function testSystemReadOnce(): void
    {
        $r = self::$registry->systemWrite('once_key', 'name_bln', 'bln', true);

        $this->assertTrue($r, 'systemWrite not successful');

        $r = self::$registry->systemReadOnce('once_key', 'name_bln', 'bln');

        $this->assertTrue($r, 'systemReadOnce not successful');

        $r = self::$registry->systemExists('once_key', 'name_bln', 'bln');

        $this->assertFalse($r, 'systemExists not successful');
    }

    public function testSystemWriteBln(): void
    {
        $r = self::$registry->systemWrite('key', 'name_bln', 'bln', self::_bln);

        $this->assertTrue($r, 'systemWrite not successful');

        $r = self::$registry->systemRead('key', 'name_bln', 'bln');

        $this->assertEquals(self::_bln, $r);
    }

    public function testSystemDeleteBln(): void
    {
        $r = self::$registry->systemDelete('key', 'name_bln', 'bln');

        $this->assertTrue($r, 'systemDelete not successful');

        $r = self::$registry->systemReadDefault('key', 'name_bln', 'bln', false);

        $this->assertEquals(false, $r);
    }

    public function testSystemWriteInt(): void
    {
        $r = self::$registry->systemWrite('key', 'name_int', 'int', self::_int);

        $this->assertTrue($r, 'systemWrite not successful');

        $r = self::$registry->systemRead('key', 'name_int', 'int');

        $this->assertEquals(self::_int, $r);
    }

    public function testSystemDeleteInt(): void
    {
        $r = self::$registry->systemDelete('key', 'name_int', 'int');

        $this->assertTrue($r, 'systemDelete not successful');

        $r = self::$registry->systemReadDefault('key', 'name_int', 'int', self::_int + 1);

        $this->assertEquals(self::_int + 1, $r);
    }

    public function testSystemWriteStr(): void
    {
        $r = self::$registry->systemWrite('key', 'name_str', 'str', self::_str);

        $this->assertTrue($r, 'systemWrite not successful');

        $r = self::$registry->systemRead('key', 'name_str', 'str');

        $this->assertEquals(self::_str, $r);
    }

    public function testSystemDeleteStr(): void
    {
        $r = self::$registry->systemDelete('key', 'name_str', 'str');

        $this->assertTrue($r, 'systemDelete not successful');

        $r = self::$registry->systemReadDefault('key', 'name_str', 'str', self::_str.'default');

        $this->assertEquals(self::_str.'default', $r);
    }

    public function testSystemWriteFlt(): void
    {
        $r = self::$registry->systemWrite('key', 'name_flt', 'flt', self::_flt);

        $this->assertTrue($r, 'systemWrite not successful');

        $r = self::$registry->systemRead('key', 'name_flt', 'flt');

        $this->assertEquals(self::_flt, $r);
    }

    public function testSystemDeleteFlt(): void
    {
        $r = self::$registry->systemDelete('key', 'name_flt', 'flt');

        $this->assertTrue($r, 'systemDelete not successful');

        $r = self::$registry->systemReadDefault('key', 'name_flt', 'flt', self::_flt - 0.1);

        $this->assertEquals(self::_flt - 0.1, $r);
    }

    public function testSystemWriteDat(): void
    {
        $r = self::$registry->systemWrite('key', 'name_dat', 'dat', strtotime(self::_dat));

        $this->assertTrue($r, 'systemWrite not successful');

        $r = self::$registry->systemRead('key', 'name_dat', 'dat');

        $this->assertEquals(strtotime(self::_dat), $r);
    }

    public function testSystemDeleteDat(): void
    {
        $r = self::$registry->systemDelete('key', 'name_dat', 'dat');

        $this->assertTrue($r, 'systemDelete not successful');

        $r = self::$registry->systemReadDefault('key', 'name_dat', 'dat', strtotime('1990-01-01'));

        $this->assertEquals(strtotime('1990-01-01'), $r);
    }

}
