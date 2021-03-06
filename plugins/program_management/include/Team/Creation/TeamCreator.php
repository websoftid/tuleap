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

namespace Tuleap\ProgramManagement\Team\Creation;

use Tuleap\ProgramManagement\Adapter\Program\Plan\ProgramAccessException;
use Tuleap\ProgramManagement\Adapter\Program\Plan\ProjectIsNotAProgramException;
use Tuleap\ProgramManagement\Adapter\Team\AtLeastOneTeamShouldBeDefinedException;
use Tuleap\ProgramManagement\Adapter\Team\ProjectIsAProgramException;
use Tuleap\ProgramManagement\Adapter\Team\TeamAccessException;
use Tuleap\ProgramManagement\Program\Plan\BuildProgram;

final class TeamCreator implements CreateTeam
{
    /**
     * @var BuildProgram
     */
    private $program_build;
    /**
     * @var BuildTeam
     */
    private $build_team;
    /**
     * @var TeamStore
     */
    private $team_store;

    public function __construct(BuildProgram $program_build, BuildTeam $build_team, TeamStore $team_store)
    {
        $this->program_build = $program_build;
        $this->build_team    = $build_team;
        $this->team_store    = $team_store;
    }

    /**
     * @throws ProgramAccessException
     * @throws ProjectIsNotAProgramException
     * @throws AtLeastOneTeamShouldBeDefinedException
     * @throws ProjectIsAProgramException
     * @throws TeamAccessException
     */
    public function create(\PFUser $user, int $project_id, array $team_ids): void
    {
        $program_project = $this->program_build->buildNewProgramProject($project_id, $user);

        $team_collection = $this->build_team->buildTeamProject(
            $team_ids,
            $program_project,
            $user
        );

        $this->team_store->save($team_collection);
    }
}
