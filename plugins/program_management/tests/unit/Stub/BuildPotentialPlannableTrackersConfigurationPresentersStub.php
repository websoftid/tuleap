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

namespace Tuleap\ProgramManagement\Stub;

use Tuleap\ProgramManagement\Domain\Program\Admin\PlannableTrackersConfiguration\BuildPotentialPlannableTrackersConfigurationPresenters;
use Tuleap\ProgramManagement\Domain\Program\Admin\ProgramSelectOptionConfigurationPresenter;

final class BuildPotentialPlannableTrackersConfigurationPresentersStub implements BuildPotentialPlannableTrackersConfigurationPresenters
{
    /**
     * @var int[]
     */
    private array $ids;

    /**
     * @param int[] $ids
     */
    private function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    /**
     * @return ProgramSelectOptionConfigurationPresenter[]
     */
    public function buildPotentialPlannableTrackerPresenters(int $program_id): array
    {
        $presenters = [];
        foreach ($this->ids as $id) {
            $presenters[] = new ProgramSelectOptionConfigurationPresenter($id, 'tracker', false);
        }
        return $presenters;
    }

    public static function buildPresentersFromIds(int ...$ids): self
    {
        return new self($ids);
    }
}
