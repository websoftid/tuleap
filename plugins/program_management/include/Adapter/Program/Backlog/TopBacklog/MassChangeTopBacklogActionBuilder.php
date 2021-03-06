<?php
/**
 * Copyright (c) Enalean, 2021-Present. All Rights Reserved.
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

namespace Tuleap\ProgramManagement\Adapter\Program\Backlog\TopBacklog;

use PFUser;
use TemplateRenderer;
use Tuleap\ProgramManagement\Adapter\Program\Plan\PrioritizeFeaturesPermissionVerifier;
use Tuleap\ProgramManagement\Adapter\Program\Plan\ProgramAccessException;
use Tuleap\ProgramManagement\Adapter\Program\Plan\ProjectIsNotAProgramException;
use Tuleap\ProgramManagement\Program\Plan\BuildProgram;
use Tuleap\ProgramManagement\Program\Plan\PlanStore;

class MassChangeTopBacklogActionBuilder
{
    /**
     * @var BuildProgram
     */
    private $build_program;
    /**
     * @var PrioritizeFeaturesPermissionVerifier
     */
    private $prioritize_features_permission_verifier;
    /**
     * @var PlanStore
     */
    private $plan_store;
    /**
     * @var TemplateRenderer
     */
    private $template_renderer;

    public function __construct(
        BuildProgram $build_program,
        PrioritizeFeaturesPermissionVerifier $prioritize_features_permission_verifier,
        PlanStore $plan_store,
        TemplateRenderer $template_renderer
    ) {
        $this->build_program                           = $build_program;
        $this->prioritize_features_permission_verifier = $prioritize_features_permission_verifier;
        $this->plan_store                              = $plan_store;
        $this->template_renderer                       = $template_renderer;
    }

    public function buildMassChangeAction(TopBacklogActionMassChangeSourceInformation $source_information, PFUser $user): ?string
    {
        try {
            $program = $this->build_program->buildExistingProgramProject($source_information->project_id, $user);
        } catch (ProgramAccessException | ProjectIsNotAProgramException $e) {
            return null;
        }

        if (! $this->prioritize_features_permission_verifier->canUserPrioritizeFeatures($program, $user)) {
            return null;
        }

        if (! $this->plan_store->isPlannable($source_information->tracker_id)) {
            return null;
        }

        return $this->template_renderer->renderToString('mass-change-top-backlog-actions', []);
    }
}
