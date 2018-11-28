<?php
/**
 * Copyright (c) Enalean, 2018. All Rights Reserved.
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

namespace Tuleap\GitLFS\Transfer\Basic;

use Tuleap\GitLFS\StreamFilter\FilterInterface;

final class SHA256ComputeOnReadFilter implements FilterInterface
{
    /**
     * @var resource
     */
    private $hash_context;

    public function __construct()
    {
        $this->hash_context = \hash_init('sha256');
    }

    /**
     * @return string
     */
    public function process($data_chunk)
    {
        \hash_update($this->hash_context, $data_chunk);
        return $data_chunk;
    }

    public function getFilteredChainIdentifier()
    {
        return STREAM_FILTER_READ;
    }

    /**
     * @return string
     */
    public function getHashValue()
    {
        return \hash_final(\hash_copy($this->hash_context));
    }
}
