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

namespace Tuleap\ScaledAgile\Program\PlanningConfiguration;

use Mockery;
use NullTracker;
use PHPUnit\Framework\TestCase;
use Planning;
use Tuleap\Test\Builders\UserTestBuilder;

final class PlanningAdapterTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var PlanningAdapter
     */
    private $adapter;
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|\PlanningFactory
     */
    private $planning_factory;

    protected function setUp(): void
    {
        $this->planning_factory = Mockery::mock(\PlanningFactory::class);
        $this->adapter          = new PlanningAdapter($this->planning_factory);
    }

    public function testItBuildAPlanningFromRoot(): void
    {
        $planning = new Planning(1, "test", 101, "backlog title", "plan title", []);

        $this->planning_factory->shouldReceive('getRootPlanning')->once()->andReturn($planning);

        $expected_built_planning = new PlanningData(new NullTracker(), 1, "test", []);

        $user = UserTestBuilder::aUser()->build();
        $project_id = 101;

        $this->assertEquals($expected_built_planning, $this->adapter->buildRootPlanning($user, $project_id));
    }

    public function testItBuildAPlanningFromItsId(): void
    {
        $planning = new Planning(1, "test", 101, "backlog title", "plan title", []);

        $this->planning_factory->shouldReceive('getPlanning')->once()->andReturn($planning);

        $expected_built_planning = new PlanningData(new NullTracker(), 1, "test", []);

        $this->assertEquals($expected_built_planning, $this->adapter->buildPlanningById(1));
    }
}
