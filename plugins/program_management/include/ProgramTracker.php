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

namespace Tuleap\ProgramManagement;

use Tracker;
use Tuleap\Tracker\TrackerColor;

final class ProgramTracker
{
    /**
     * @var Tracker
     * @psalm-readonly
     */
    private $tracker;

    public function __construct(Tracker $tracker)
    {
        $this->tracker = $tracker;
    }

    /**
     * @psalm-mutation-free
     */
    public function getTrackerId(): int
    {
        return $this->tracker->getId();
    }

    /**
     * @psalm-mutation-free
     */
    public function getFullTracker(): Tracker
    {
        return $this->tracker;
    }

    public function userCanSubmitArtifact(\PFUser $user): bool
    {
        return $this->tracker->userCanSubmitArtifact($user);
    }

    /**
     * @psalm-mutation-free
     */
    public function getName(): string
    {
        return $this->tracker->getName();
    }

    /**
     * @psalm-mutation-free
     */
    public function getColor(): TrackerColor
    {
        return $this->tracker->getColor();
    }
}
