<?php
/**
 * Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
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

namespace Tuleap\ProgramManagement\Adapter\Program\Backlog\CreationCheck;

use Tracker;
use Tracker_FormElement_Field_List;
use Tracker_Semantic_Status;
use Tracker_Semantic_StatusDao;
use Tracker_Semantic_StatusFactory;
use Tuleap\ProgramManagement\Domain\Program\Admin\Configuration\ConfigurationErrorsCollector;
use Tuleap\ProgramManagement\Domain\Program\Backlog\ProgramIncrement\Team\TeamProjectsCollection;
use Tuleap\ProgramManagement\Domain\Program\Backlog\Source\SourceTrackerCollection;
use Tuleap\ProgramManagement\Domain\Program\Backlog\TrackerCollection;
use Tuleap\ProgramManagement\Domain\TrackerReference;
use Tuleap\ProgramManagement\Tests\Builder\ProgramIdentifierBuilder;
use Tuleap\ProgramManagement\Tests\Stub\RetrieveProjectReferenceStub;
use Tuleap\ProgramManagement\Tests\Stub\TrackerReferenceStub;
use Tuleap\ProgramManagement\Tests\Stub\SearchTeamsOfProgramStub;
use Tuleap\ProgramManagement\Tests\Stub\RetrieveMirroredProgramIncrementTrackerStub;
use Tuleap\ProgramManagement\Tests\Stub\RetrieveVisibleProgramIncrementTrackerStub;
use Tuleap\ProgramManagement\Tests\Stub\UserIdentifierStub;
use Tuleap\Tracker\Test\Builders\TrackerTestBuilder;

final class StatusSemanticCheckerTest extends \Tuleap\Test\PHPUnit\TestCase
{
    private StatusSemanticChecker $checker;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&Tracker_Semantic_StatusDao
     */
    private $semantic_status_dao;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&Tracker_Semantic_StatusFactory
     */
    private $semantic_status_factory;
    private TrackerCollection $collection;
    private Tracker $tracker_team_01;
    private Tracker $tracker_team_02;
    private SourceTrackerCollection $source_trackers;
    private Tracker $timebox_tracker;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&Tracker_Semantic_Status
     */
    private $timebox_tracker_semantic_status;
    private Tracker $program_increment;
    private TrackerReference $program_increment_tracker;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\TrackerFactory
     */
    private $tracker_factory;
    private TrackerReference $timebox_program_tracker;

    protected function setUp(): void
    {
        $this->semantic_status_dao     = $this->createMock(Tracker_Semantic_StatusDao::class);
        $this->semantic_status_factory = $this->createMock(Tracker_Semantic_StatusFactory::class);
        $this->tracker_factory         = $this->createMock(\TrackerFactory::class);

        $this->checker = new StatusSemanticChecker(
            $this->semantic_status_dao,
            $this->semantic_status_factory,
            $this->tracker_factory
        );

        $this->tracker_team_01 = TrackerTestBuilder::aTracker()->withId(123)->build();
        $this->tracker_team_02 = TrackerTestBuilder::aTracker()->withId(124)->build();

        $this->timebox_program_tracker         = TrackerReferenceStub::withDefaults();
        $this->timebox_tracker                 = TrackerTestBuilder::aTracker()->withId(1)->build();
        $this->timebox_tracker_semantic_status = $this->createMock(Tracker_Semantic_Status::class);
        $this->timebox_tracker_semantic_status->method('getOpenLabels')->willReturn(['open', 'review']);

        $this->program_increment         = TrackerTestBuilder::aTracker()->withId(104)->build();
        $this->program_increment_tracker = TrackerReferenceStub::withId(104);

        $user_identifier = UserIdentifierStub::buildGenericUser();
        $teams           = TeamProjectsCollection::fromProgramIdentifier(
            SearchTeamsOfProgramStub::buildTeams(101, 102),
            new RetrieveProjectReferenceStub(),
            ProgramIdentifierBuilder::build()
        );
        $retriever       = RetrieveMirroredProgramIncrementTrackerStub::withValidTrackers(
            TrackerReferenceStub::fromTracker($this->tracker_team_01),
            TrackerReferenceStub::fromTracker($this->tracker_team_02),
        );

        $this->collection      = TrackerCollection::buildRootPlanningMilestoneTrackers($retriever, $teams, $user_identifier, new ConfigurationErrorsCollector(false));
        $this->source_trackers = SourceTrackerCollection::fromProgramAndTeamTrackers(
            RetrieveVisibleProgramIncrementTrackerStub::withValidTracker($this->timebox_program_tracker),
            ProgramIdentifierBuilder::build(),
            $this->collection,
            $user_identifier
        );
    }

    public function testItReturnsTrueIfAllStatusSemanticAreWellConfigured(): void
    {
        $list_field = $this->createMock(Tracker_FormElement_Field_List::class);

        $top_planning_tracker_semantic_status = $this->createMock(Tracker_Semantic_Status::class);
        $top_planning_tracker_semantic_status->method('getField')
            ->willReturn($list_field);

        $this->semantic_status_dao->method('getTrackerIdsWithoutSemanticStatusDefined')
            ->with([1, 123, 124])
            ->willReturn([]);

        $top_planning_tracker_semantic_status->expects(self::once())
            ->method('getOpenLabels')
            ->willReturn(['open', 'review']);

        $tracker_01_semantic_status = $this->createMock(Tracker_Semantic_Status::class);
        $tracker_01_semantic_status->expects(self::once())
            ->method('getOpenLabels')
            ->willReturn(['open', 'review']);

        $tracker_02_semantic_status = $this->createMock(Tracker_Semantic_Status::class);
        $tracker_02_semantic_status->expects(self::once())
            ->method('getOpenLabels')
            ->willReturn(['open', 'in progress', 'review']);

        $this->semantic_status_factory->method('getByTracker')
            ->withConsecutive(
                [$this->program_increment],
                [$this->timebox_tracker],
                [$this->tracker_team_01],
                [$this->tracker_team_02]
            )
            ->willReturnOnConsecutiveCalls(
                $top_planning_tracker_semantic_status,
                $this->timebox_tracker_semantic_status,
                $tracker_01_semantic_status,
                $tracker_02_semantic_status
            );

        $this->tracker_factory->method('getTrackerById')
            ->willReturnOnConsecutiveCalls(
                $this->program_increment,
                $this->timebox_tracker,
                $this->tracker_team_01,
                $this->tracker_team_02
            );

        $configuration_errors = new ConfigurationErrorsCollector(true);
        self::assertTrue(
            $this->checker->isStatusWellConfigured(
                $this->program_increment_tracker,
                $this->source_trackers,
                $configuration_errors
            )
        );

        self::assertCount(0, $configuration_errors->getSemanticStatusMissingValues());
        self::assertCount(0, $configuration_errors->getSemanticStatusNoField());
        self::assertCount(0, $configuration_errors->getStatusMissingInTeams());
    }

    public function testItReturnsFalseIfProgramTrackerDoesNotHaveStatusSemantic(): void
    {
        $top_planning_tracker_semantic_status = $this->createMock(Tracker_Semantic_Status::class);
        $this->semantic_status_factory->method('getByTracker')
            ->with($this->program_increment)
            ->willReturn($top_planning_tracker_semantic_status);

        $top_planning_tracker_semantic_status->method('getField')
            ->willReturn(null);

        $this->tracker_factory->method('getTrackerById')->willReturn($this->program_increment);

        $configuration_errors = new ConfigurationErrorsCollector(true);
        self::assertFalse(
            $this->checker->isStatusWellConfigured(
                $this->program_increment_tracker,
                $this->source_trackers,
                $configuration_errors
            )
        );
        self::assertSame(104, $configuration_errors->getSemanticStatusNoField()[0]->tracker_id);
    }

    public function testItReturnsFalseIfSomeTeamTrackersDoNotHaveSemanticStatusDefined(): void
    {
        $top_planning_tracker_semantic_status = $this->createMock(Tracker_Semantic_Status::class);
        $this->semantic_status_factory->method('getByTracker')
            ->with($this->program_increment)
            ->willReturn($top_planning_tracker_semantic_status);

        $list_field = $this->createMock(Tracker_FormElement_Field_List::class);
        $top_planning_tracker_semantic_status->method('getField')
            ->willReturn($list_field);

        $this->semantic_status_dao->method('getTrackerIdsWithoutSemanticStatusDefined')
            ->with([1, 123, 124])
            ->willReturn([1]);

        $this->tracker_factory->method('getTrackerById')->willReturn($this->program_increment);

        $configuration_errors = new ConfigurationErrorsCollector(true);
        self::assertFalse(
            $this->checker->isStatusWellConfigured(
                $this->program_increment_tracker,
                $this->source_trackers,
                $configuration_errors
            )
        );
        self::assertSame(1, $configuration_errors->getStatusMissingInTeams()[0]->getId());
    }

    public function testItReturnsFalseIfSomeTeamStatusSemanticDoesNotContainTheProgramOpenValue(): void
    {
        $list_field = $this->createMock(Tracker_FormElement_Field_List::class);

        $top_planning_tracker_semantic_status = $this->createMock(Tracker_Semantic_Status::class);
        $top_planning_tracker_semantic_status->method('getField')
            ->willReturn($list_field);

        $this->semantic_status_dao->method('getTrackerIdsWithoutSemanticStatusDefined')
            ->with([1, 123, 124])
            ->willReturn([]);

        $top_planning_tracker_semantic_status->expects(self::once())
            ->method('getOpenLabels')
            ->willReturn(['open', 'review']);

        $tracker_01_semantic_status = $this->createMock(Tracker_Semantic_Status::class);
        $this->semantic_status_factory->method('getByTracker')
            ->withConsecutive([$this->program_increment], [$this->timebox_tracker], [$this->tracker_team_01])
            ->willReturnOnConsecutiveCalls(
                $top_planning_tracker_semantic_status,
                $this->timebox_tracker_semantic_status,
                $tracker_01_semantic_status
            );

        $tracker_01_semantic_status->expects(self::once())
            ->method('getOpenLabels')
            ->willReturn(['open']);

        $this->tracker_factory->method('getTrackerById')->willReturn(
            $this->program_increment,
            $this->timebox_tracker,
            $this->tracker_team_01
        );

        $configuration_errors = new ConfigurationErrorsCollector(true);
        self::assertFalse(
            $this->checker->isStatusWellConfigured(
                $this->program_increment_tracker,
                $this->source_trackers,
                $configuration_errors
            )
        );
        self::assertSame('review', $configuration_errors->getSemanticStatusMissingValues()[0]->missing_values);
    }
}
