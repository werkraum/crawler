<?php
namespace AOE\Crawler\Tests\Functional\Hooks;


/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 AOE GmbH <dev@aoe.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use AOE\Crawler\Hooks\TsfeHook;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use PHPUnit\Framework\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class TsfeHookTest
 */
class TsfeHookTest extends FunctionalTestCase
{
    /**
     * @var TsfeHook
     */
    protected $subject;

    /**
     * @var array
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/crawler'];

    public function setUp()
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/../Fixtures/tx_crawler_queue.xml');

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $objectManager->get(TsfeHook::class);
    }

    /**
     * @test
     */
    public function fe_initNoOutPutExpected()
    {
        $params = [];
        $ref = null;

        $this->expectOutputString('');
        $this->subject->fe_init($params, $ref);
    }

    /**
     * @test
     */
    public function fe_unitExpectAdjustedParams()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = sha1('this-is-a-encryption-key-for-testing-purpose-only');
        $_SERVER['HTTP_X_T3CRAWLER'] = '20:cbed25b36eebbcbea6fd178897397414';
        $params = [];
        $ref = null;

        $expectedArray = [];
        $expectedArray['pObj']->applicationData['tx_crawler']['running'] = true;
        $expectedArray['pObj']->applicationData['tx_crawler']['parameters'] = 'test-params';
        $expectedArray['pObj']->applicationData['tx_crawler']['log'] = [];

        $this->subject->fe_init($params, $ref);

        $this->assertEquals(
            $expectedArray,
            $params
        );

    }

    /**
     * @test
     */
    public function FeEofeHookExpectsErrorLogInformation()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['pollSuccess'][] = 'tx_staticpub';

        $params = [];
        $params['pObj']->applicationData['tx_crawler']['running'] = true;
        $params['pObj']->id = 1234;
        $params['pObj']->gr_list = 4321;
        $params['pObj']->no_cache = 0;
        $params['pObj']->applicationData['tx_crawler']['parameters']['procInstructions'] = ['tx_staticpub'];

        $this->subject->fe_eofe($params, null);
        $this->assertContains(
            'Error: Pollable extension',
            $params['pObj']->applicationData['tx_crawler']['errorlog']
        );
    }
}
