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

namespace Tuleap\ProgramManagement\Adapter;

use Project;
use Tuleap\ProgramManagement\BuildProject;
use Tuleap\ProgramManagement\Project as ProgramManagementProject;

final class ProjectAdapter implements BuildProject
{
    /**
     * @var \ProjectManager
     */
    private $project_manager;

    public function __construct(\ProjectManager $project_manager)
    {
        $this->project_manager = $project_manager;
    }

    public static function build(Project $project): ProgramManagementProject
    {
        return new ProgramManagementProject((int) $project->getID(), (string) $project->getUnixName(), (string) $project->getPublicName());
    }

    public function buildFromId(int $id): ProgramManagementProject
    {
        $team_project = $this->project_manager->getProject($id);

        return self::build($team_project);
    }
}
