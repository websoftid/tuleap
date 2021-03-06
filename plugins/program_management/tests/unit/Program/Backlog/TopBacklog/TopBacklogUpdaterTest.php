<?php
/**
 * Copyright (c) Enalean, 2021-Present. All Rights Reserved.
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

namespace Tuleap\ProgramManagement\Program\Backlog\TopBacklog;

use PHPUnit\Framework\TestCase;
use Tuleap\ProgramManagement\Program\Plan\BuildProgram;
use Tuleap\ProgramManagement\Program\Program;
use Tuleap\ProgramManagement\Program\ProgramForManagement;
use Tuleap\ProgramManagement\Program\ToBeCreatedProgram;
use Tuleap\Test\Builders\UserTestBuilder;

final class TopBacklogUpdaterTest extends TestCase
{
    public function testTopBacklogCanBeUpdated(): void
    {
        $build_program = new class implements BuildProgram
        {
            public function buildExistingProgramProject(int $id, \PFUser $user): Program
            {
                return new Program(102);
                throw new \LogicException("Not needed");
            }

            public function buildNewProgramProject(int $id, \PFUser $user): ToBeCreatedProgram
            {
                throw new \LogicException("Not needed");
            }

            public function buildExistingProgramProjectForManagement(int $id, \PFUser $user): ProgramForManagement
            {
                throw new \LogicException("Not needed");
            }
        };

        $top_backlog_change_processor = new class implements TopBacklogChangeProcessor {
            public $has_been_called = false;

            public function processTopBacklogChangeForAProgram(
                Program $program,
                TopBacklogChange $top_backlog_change,
                \PFUser $user
            ): void {
                $this->has_been_called = true;
            }
        };

        $update = new TopBacklogUpdater($build_program, $top_backlog_change_processor);
        $update->updateTopBacklog(102, new TopBacklogChange([], [10000]), UserTestBuilder::aUser()->build());

        self::assertTrue($top_backlog_change_processor->has_been_called);
    }
}
