<?php
/**
 * Copyright (c) Enalean, 2019 - present. All Rights Reserved.
 *
 *  This file is a part of Tuleap.
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
 *
 */

declare(strict_types=1);

namespace Tuleap\AgileDashboard\REST\v1\Milestone;

use MilestoneParentLinker;
use PHPUnit\Framework\MockObject\MockObject;
use Planning_ArtifactMilestone;
use Planning_Milestone;
use Tracker_ArtifactFactory;
use Tracker_FormElement_Field_ArtifactLink;
use Tuleap\AgileDashboard\ExplicitBacklog\ArtifactsInExplicitBacklogDao;
use Tuleap\AgileDashboard\REST\v1\MilestoneResourceValidator;
use Tuleap\AgileDashboard\REST\v1\ResourcesPatcher;
use Tuleap\AgileDashboard\Test\Builders\PlanningBuilder;
use Tuleap\Test\Builders\ProjectTestBuilder;
use Tuleap\Test\Builders\UserTestBuilder;
use Tuleap\Test\DB\DBTransactionExecutorPassthrough;
use Tuleap\Test\PHPUnit\TestCase;
use Tuleap\Tracker\FormElement\Field\ArtifactLink\ArtifactLinkUpdater;
use Tuleap\Tracker\Test\Builders\ArtifactTestBuilder;
use Tuleap\Tracker\Test\Builders\TrackerTestBuilder;

final class MilestoneElementMoverTest extends TestCase
{
    private Tracker_ArtifactFactory&MockObject $tracker_artifact_factory;
    private MilestoneParentLinker&MockObject $milestone_parent_linker;
    private ArtifactsInExplicitBacklogDao&MockObject $explicit_backlog_dao;
    private MilestoneElementMover $mover;
    private ArtifactLinkUpdater&MockObject $artifact_link_updater;
    private MilestoneResourceValidator&MockObject $milestone_validator;
    private ResourcesPatcher&MockObject $resources_patcher;

    protected function setUp(): void
    {
        $this->resources_patcher        = $this->createMock(ResourcesPatcher::class);
        $this->milestone_validator      = $this->createMock(MilestoneResourceValidator::class);
        $this->artifact_link_updater    = $this->createMock(ArtifactLinkUpdater::class);
        $this->tracker_artifact_factory = $this->createMock(Tracker_ArtifactFactory::class);
        $this->milestone_parent_linker  = $this->createMock(MilestoneParentLinker::class);
        $this->explicit_backlog_dao     = $this->createMock(ArtifactsInExplicitBacklogDao::class);

        $this->mover = new MilestoneElementMover(
            $this->resources_patcher,
            $this->milestone_validator,
            $this->artifact_link_updater,
            new DBTransactionExecutorPassthrough(),
            $this->tracker_artifact_factory,
            $this->milestone_parent_linker,
            $this->explicit_backlog_dao
        );
    }

    public function testItMovesElementToMilestone(): void
    {
        $user         = UserTestBuilder::buildWithDefaults();
        $artifact     = ArtifactTestBuilder::anArtifact(1)->build();
        $milestone    = $this->createMock(Planning_Milestone::class);
        $add          = ['id' => 112];
        $valid_to_add = [112];

        $expected_result = $valid_to_add;


        $this->resources_patcher->expects(self::once())->method('startTransaction');
        $this->resources_patcher->expects(self::once())->method('removeArtifactFromSource')
            ->with($user, $add)
            ->willReturn($valid_to_add);

        $this->milestone_validator->expects(self::once())->method('validateArtifactIdsCanBeAddedToBacklog')->willReturn($valid_to_add);

        $milestone->expects(self::exactly(2))->method('getArtifact')->willReturn($artifact);

        $this->artifact_link_updater->expects(self::once())->method('updateArtifactLinks')
            ->with(
                $user,
                $milestone->getArtifact(),
                $valid_to_add,
                [],
                Tracker_FormElement_Field_ArtifactLink::NO_TYPE,
            );
        $this->resources_patcher->expects(self::once())->method('commit');

        $result = $this->mover->moveElement($user, $add, $milestone);

        self::assertEquals($expected_result, $result);
    }

    public function testItMovesElementFromTopBacklogToARelease(): void
    {
        $user      = UserTestBuilder::buildWithDefaults();
        $tracker   = TrackerTestBuilder::aTracker()->withId(10)->build();
        $artifact  = ArtifactTestBuilder::anArtifact(112)->inTracker($tracker)->build();
        $milestone = new Planning_ArtifactMilestone(
            ProjectTestBuilder::aProject()->withId(101)->build(),
            PlanningBuilder::aPlanning(101)->withBacklogTrackers(
                $tracker,
                TrackerTestBuilder::aTracker()->withId(11)->build(),
            )->build(),
            $artifact
        );
        $add       = ['id' => 112];

        $this->resources_patcher->expects(self::once())->method('removeArtifactFromSource')->willReturn([$add]);

        $this->tracker_artifact_factory->expects(self::exactly(2))->method('getArtifactById')->with($add)->willReturn($artifact);

        $this->artifact_link_updater->expects(self::once())->method('updateArtifactLinks');
        $this->milestone_parent_linker->expects(self::once())->method('linkToMilestoneParent');
        $this->explicit_backlog_dao->expects(self::once())->method('removeItemsFromExplicitBacklogOfProject');

        $this->mover->moveElementToMilestoneContent($milestone, $user, $add);
    }
}
