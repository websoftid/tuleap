<?php
/**
 * Copyright Enalean (c) 2017 - Present. All rights reserved.
 *
 * Tuleap and Enalean names and logos are registrated trademarks owned by
 * Enalean SAS. All other trademarks or names are properties of their respective
 * owners.
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

namespace Tuleap\Tracker\FormElement\Field\ListFields\Bind;

use Tracker_FormElement_Field;
use Tuleap\Event\Dispatchable;

class CanValueBeHiddenStatementsCollection implements Dispatchable
{
    public const NAME = 'canValueBeHiddenStatementsCollection';

    /**
     * @var array
     */
    private $statements = [];

    /**
     * @var Tracker_FormElement_Field
     */
    private $field;

    public function __construct(Tracker_FormElement_Field $field)
    {
        $this->field = $field;
    }

    public function add($statement)
    {
        $this->statements[] = $statement;
    }

    public function asUnion()
    {
        if (! $this->statements) {
            return '';
        }

        return ' UNION ' . implode(' UNION ', $this->statements);
    }

    /**
     * @return Tracker_FormElement_Field
     */
    public function getField()
    {
        return $this->field;
    }
}
