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

namespace Tuleap\ProgramManagement\Program\Backlog\ProgramIncrement;

use Tuleap\Tracker\FormElement\Field\ArtifactLink\Nature\NaturePresenter;

final class ProgramIncrementArtifactLinkType extends NaturePresenter
{
    public const ART_LINK_SHORT_NAME = '_mirrored_milestone';

    /**
     * @psalm-readonly
     */
    public $is_system = true;

    public function __construct()
    {
        parent::__construct(
            self::ART_LINK_SHORT_NAME,
            dgettext('tuleap-program_management', 'Mirror of'),
            dgettext('tuleap-program_management', 'Mirrored by'),
            false
        );
    }
}
