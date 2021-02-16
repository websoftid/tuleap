<?php
/**
 * Copyright (c) Enalean, 2021 - Present. All Rights Reserved.
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

namespace Tuleap\Timetracking\JiraImporter;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PFUser;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use SimpleXMLElement;
use Tuleap\Timetracking\JiraImporter\Worklog\Worklog;
use Tuleap\Timetracking\JiraImporter\Worklog\WorklogRetriever;
use Tuleap\Tracker\Creation\JiraImporter\Configuration\PlatformConfiguration;
use Tuleap\Tracker\Creation\JiraImporter\Import\Artifact\IssueAPIRepresentation;
use Tuleap\Tracker\Creation\JiraImporter\Import\Artifact\IssueAPIRepresentationCollection;
use Tuleap\Tracker\Creation\JiraImporter\Import\User\JiraUser;
use Tuleap\Tracker\Creation\JiraImporter\Import\User\JiraUserRetriever;
use XML_SimpleXMLCDATAFactory;

final class JiraXMLExportTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var JiraXMLExport
     */
    private $exporter;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|WorklogRetriever
     */
    private $worklog_retriever;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|JiraUserRetriever
     */
    private $jira_user_retriever;

    protected function setUp(): void
    {
        parent::setUp();

        $this->worklog_retriever   = Mockery::mock(WorklogRetriever::class);
        $this->jira_user_retriever = Mockery::mock(JiraUserRetriever::class);

        $this->exporter = new JiraXMLExport(
            $this->worklog_retriever,
            new XML_SimpleXMLCDATAFactory(),
            $this->jira_user_retriever,
            new NullLogger()
        );
    }

    public function testItEnablesTimetrackingConfigurationForJiraTrackerAndImportTimes(): void
    {
        $xml_tracker            = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><tracker/>');
        $platform_configuration = new PlatformConfiguration();
        $issue_collection       = new IssueAPIRepresentationCollection();

        $platform_configuration->addAllowedConfiguration('jira_timetracking');

        $issue_representation = new IssueAPIRepresentation(
            'ISSUE-1',
            10092,
            [],
            []
        );

        $issue_collection->addIssueRepresentationInCollection($issue_representation);

        $this->worklog_retriever->shouldReceive('getIssueWorklogsFromAPI')
            ->once()
            ->with($issue_representation)
            ->andReturn([
                new Worklog(
                    new \DateTimeImmutable('2021-02-08T19:06:41.386+0100'),
                    18000,
                    new JiraUser(
                        [
                            "accountId"    => "whatever123",
                            "emailAddress" => "whatever@example.com",
                            "displayName"  => "What Ever",
                        ]
                    )
                )
            ]);

        $time_user = Mockery::mock(PFUser::class);
        $time_user->shouldReceive('getUserName')->andReturn('user_time');

        $this->jira_user_retriever->shouldReceive('retrieveJiraAuthor')
            ->once()
            ->andReturn($time_user);

        $this->exporter->exportJiraTimetracking(
            $xml_tracker,
            $platform_configuration,
            $issue_collection
        );

        $this->assertTimetrackingConfiguration($xml_tracker);

        $this->assertTrue(isset($xml_tracker->timetracking->time));
        $this->assertSame("2021-02-08T19:06:41+01:00", (string) $xml_tracker->timetracking->time->day);
        $this->assertSame("300", (string) $xml_tracker->timetracking->time->minutes);
        $this->assertSame("user_time", (string) $xml_tracker->timetracking->time->user);
        $this->assertSame("username", (string) $xml_tracker->timetracking->time->user['format']);
    }

    public function testItOnlyEnablesTimetrackingConfigurationIfProviderIsNotKnown(): void
    {
        $xml_tracker            = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><tracker/>');
        $platform_configuration = new PlatformConfiguration();
        $issue_collection       = new IssueAPIRepresentationCollection();

        $this->worklog_retriever->shouldNotReceive('getIssueWorklogsFromAPI');

        $this->exporter->exportJiraTimetracking(
            $xml_tracker,
            $platform_configuration,
            $issue_collection
        );

        $this->assertTimetrackingConfiguration($xml_tracker);

        $this->assertFalse(isset($xml_tracker->timetracking->time));
    }

    private function assertTimetrackingConfiguration(SimpleXMLElement $xml_tracker): void
    {
        $this->assertTrue(isset($xml_tracker->timetracking));
        $this->assertSame("1", (string) $xml_tracker->timetracking['is_enabled']);

        $this->assertTrue(isset($xml_tracker->timetracking->permissions));
        $this->assertTrue(isset($xml_tracker->timetracking->permissions->write));
        $this->assertCount(1, $xml_tracker->timetracking->permissions->write->children());
        $this->assertSame("project_members", (string) $xml_tracker->timetracking->permissions->write->ugroup);

        $this->assertFalse(isset($xml_tracker->timetracking->permissions->read));
    }
}
