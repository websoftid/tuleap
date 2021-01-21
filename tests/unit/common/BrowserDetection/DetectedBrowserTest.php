<?php
/**
 * Copyright (c) Enalean, 2020-Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Tuleap\BrowserDetection;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

final class DetectedBrowserTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public const IE11_USER_AGENT_STRING             = 'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko';
    public const OLD_IE_USER_AGENT_STRING           = 'Mozilla/4.0 (compatible; MSIE 4.01; Mac_PowerPC)';
    public const EDGE_LEGACY_USER_AGENT_STRING      = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36 Edge/18.17763';
    public const VERY_OLD_FIREFOX_USER_AGENT_STRING = 'Mozilla/5.0 (Windows NT 6.1; rv:68.7) Gecko/20100101 Firefox/68.7';

    /**
     * @dataProvider dataProviderBrowserUA
     */
    public function testDetectsBrowser(
        string $user_agent,
        ?string $expected_browser_name,
        bool $expected_is_ie,
        bool $expected_edge_legacy,
        bool $expected_browser_is_outdated
    ): void {
        $detected_browser = self::buildDetectedBrowserFromSpecificUserAgentString($user_agent);
        self::assertEquals($expected_browser_name, $detected_browser->getName());
        self::assertEquals($expected_is_ie, $detected_browser->isIE());
        self::assertEquals($expected_edge_legacy, $detected_browser->isEdgeLegacy());
        self::assertEquals($expected_browser_is_outdated, $detected_browser->isAnOutdatedBrowser());
    }

    public function dataProviderBrowserUA(): array
    {
        return [
            'IE11' => [
                self::IE11_USER_AGENT_STRING,
                'Internet Explorer',
                true,
                false,
                true,
            ],
            'Old IE' => [
                self::OLD_IE_USER_AGENT_STRING,
                'Internet Explorer',
                true,
                false,
                true,
            ],
            'Edge Legacy' => [
                self::EDGE_LEGACY_USER_AGENT_STRING,
                'Edge Legacy',
                false,
                true,
                true,
            ],
            'Edge' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3738.0 Safari/537.36 Edg/75.0.107.0',
                'Edge',
                false,
                false,
                false,
            ],
            'Firefox' => [
                'Mozilla/5.0 (X11; Linux x86_64; rv:78.0) Gecko/20100101 Firefox/78.0',
                'Firefox',
                false,
                false,
                false,
            ],
            'Very Old Firefox' => [
                self::VERY_OLD_FIREFOX_USER_AGENT_STRING,
                'Firefox',
                false,
                false,
                true,
            ],
            'Chrome' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.96 Safari/537.36',
                'Chrome',
                false,
                false,
                false,
            ],
            'Very Old Chrome' => [
                'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36',
                'Chrome',
                false,
                false,
                true,
            ],
            'Chromium' => [
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36',
                'Chrome',
                false,
                false,
                false,
            ],
            'curl' => [
                'curl/7.71.1',
                null,
                false,
                false,
                false,
            ],
        ];
    }

    public function testDoesNotIdentifyAnythingWhenNoUserAgentHeaderIsSet(): void
    {
        $request = \Mockery::mock(\HTTPRequest::class);
        $request->shouldReceive('getFromServer')->with('HTTP_USER_AGENT')->andReturn(false);

        $detected_browser = DetectedBrowser::detectFromTuleapHTTPRequest($request);

        self::assertNull($detected_browser->getName());
        self::assertFalse($detected_browser->isIE());
        self::assertFalse($detected_browser->isEdgeLegacy());
        self::assertFalse($detected_browser->isAnOutdatedBrowser());
    }

    private static function buildDetectedBrowserFromSpecificUserAgentString(string $user_agent): DetectedBrowser
    {
        $request = \Mockery::mock(\HTTPRequest::class);
        $request->shouldReceive('getFromServer')->with('HTTP_USER_AGENT')->andReturn($user_agent);

        return DetectedBrowser::detectFromTuleapHTTPRequest($request);
    }
}
