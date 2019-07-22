<?php
namespace AOE\Crawler\Tests\Unit\Hooks;

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
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;

class TsfeHookTest extends UnitTestCase
{

    /**
     * @var TsfeHook
     */
    protected $subject;

    public function setUp()
    {
        /** @var TsfeHook subject */
        $this->subject = GeneralUtility::makeInstance(TsfeHook::class);
    }

    /**
     * @test
     *
     * @param $groupList
     * @param $expected
     *
     * @dataProvider fe_feuserInitDataProvider
     */
    public function fe_feuserInit($groupList, $expected)
    {
        $params = [];
        $params['pObj']->applicationData['tx_crawler']['running'] = true;
        $params['pObj']->applicationData['tx_crawler']['parameters']['feUserGroupList'] = $groupList;
        $params['pObj']->fe_user = GeneralUtility::makeInstance(FrontendUser::class);
        //$params['pObj']->fe_user->user = new \stdClass();

        $this->subject->fe_feuserInit($params, null);

        $this->assertSame(
            $params['pObj']->fe_user->user['usergroup'],
            $expected
        );

        // Only check log if groupList, otherwise the log empty.
        // I know it's not a nice implementation, but will try to improve it
        if($groupList) {
            $this->assertSame(
                ['User Groups: ' . $groupList],
                $params['pObj']->applicationData['tx_crawler']['log']
            );
        }
    }

    /**
     * @return array
     */
    public function fe_feuserInitDataProvider()
    {
        return [
            'Only one group' => [
                'groupList' => '12',
                'expected' => '12'
            ],
            'Two groups assigned' => [
                'groupList' => '12,34',
                'expected' => '12,34'
            ],
            'No group' => [
                'groupList' => '',
                'expected' => null
            ]
        ];
    }

    /**
     * @test
     */
    public function fe_feuserInitCrawlerNotRunningExpectingParamsToBeUntouched()
    {
        $params = [];
        $params['pObj']->applicationData['tx_crawler']['running'] = false;
        $params['pObj']->applicationData['tx_crawler']['parameters']['feUserGroupList'] = '12,45';
        $params['pObj']->fe_user = GeneralUtility::makeInstance(FrontendUser::class);

        $actualParams = $params;

        $this->subject->fe_feuserInit($params, null);

        $this->assertSame(
            $actualParams,
            $params
        );
    }

    /**
     * @test
     */
    public function fe_isOutputtingIfCrawlerRunningExpectFalse()
    {
        $params = [];
        $params['pObj']->applicationData['tx_crawler']['running'] = true;
        $params['enableOutput'] = true;

        $this->subject->fe_isOutputting($params, null);
        $this->assertFalse($params['enableOutput']);
    }

    /**
     * @test
     */
    public function fe_isOutputtingIfCrawlerIsNotRunningExpectTrue()
    {
        $params = [];
        $params['pObj']->applicationData['tx_crawler']['running'] = false;
        $params['enableOutput'] = true;

        $this->subject->fe_isOutputting($params, null);
        $this->assertTrue($params['enableOutput']);
    }

    /**
     * @test
     */
    public function fe_eofeCrawlerNotRunningNoOutputExpected()
    {
        $params = [];
        $params['pObj']->applicationData['tx_crawler']['running'] = false;

        $this->assertNull(
            $this->subject->fe_eofe($params, null)
        );
    }


}
