<?php
/**
 * Copyright (c) Enalean, 2023 - Present. All Rights Reserved.
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

namespace Tuleap\AgileDashboard\Stub\Milestone\Sidebar;

use Tuleap\AgileDashboard\Milestone\Sidebar\CheckMilestonesInSidebar;

final class CheckMilestonesInSidebarStub implements CheckMilestonesInSidebar
{
    private function __construct(private readonly bool $should_sidebar_display_last_milestones)
    {
    }

    public static function withMilestonesInSidebar(): self
    {
        return new self(true);
    }

    public static function withoutMilestonesInSidebar(): self
    {
        return new self(false);
    }

    public function shouldSidebarDisplayLastMilestones(int $project_id): bool
    {
        return $this->should_sidebar_display_last_milestones;
    }

    public function isMilestonesInSidebarAvailable(): bool
    {
        return true;
    }
}
