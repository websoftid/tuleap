<?php
/**
 * Copyright (c) Enalean, 2014. All Rights Reserved.
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

namespace Tuleap\Testing\REST\v1;

use Tuleap\REST\ProjectAuthorization;
use Tuleap\REST\Header;
use Luracast\Restler\RestException;
use Tracker_ArtifactFactory;
use Tracker_Artifact;
use UserManager;
use PFUser;
use Tracker_FormElementFactory;
use Tracker_REST_Artifact_ArtifactUpdater;
use Tracker_REST_Artifact_ArtifactValidator;
use Tracker_FormElement_InvalidFieldException;
use Tracker_Exception;
use Tracker_NoChangeException;
use TrackerFactory;
use Tracker_URLVerification;
use Tracker_Artifact_ChangesetValue_Text;

class ExecutionsResource {
    const FIELD_RESULTS      = 'results';
    const FIELD_STATUS       = 'status';

    /** @var Tracker_ArtifactFactory */
    private $artifact_factory;

    /** @var Tracker_FormElementFactory */
    private $formelement_factory;

    /** @var TrackerFactory */
    private $tracker_factory;

    public function __construct() {
        $this->tracker_factory     = TrackerFactory::instance();
        $this->formelement_factory = Tracker_FormElementFactory::instance();
        $this->artifact_factory    = Tracker_ArtifactFactory::instance();
    }

    /**
     * @url OPTIONS
     */
    public function options() {
        Header::allowOptions();
    }

    /**
     * @url OPTIONS {id}
     */
    public function optionsId($id) {
        Header::allowOptionsPut();
    }

    /**
     * Update a test exception
     *
     * @url PUT {id}
     *
     * @param string $id     Id of the artifact
     * @param string $status Status of the execution {@from body} {@choice notrun,passed,failed,blocked}
     * @param string $results Result of the execution {@from body}
     */
    protected function putId($id, $status, $results = '') {
        try {
            $user     = UserManager::instance()->getCurrentUser();
            $artifact = $this->getArtifactById($user, $id);
            $changes  = $this->getChanges($status, $results, $artifact, $user);

            $updater = new Tracker_REST_Artifact_ArtifactUpdater(
                new Tracker_REST_Artifact_ArtifactValidator(
                    $this->formelement_factory
                )
            );
            $updater->update($user, $artifact, $changes);
        } catch (Tracker_FormElement_InvalidFieldException $exception) {
            throw new RestException(400, $exception->getMessage());
        } catch (Tracker_NoChangeException $exception) {
            // Do nothing
        } catch (Tracker_Exception $exception) {
            if ($GLOBALS['Response']->feedbackHasErrors()) {
                throw new RestException(500, $GLOBALS['Response']->getRawFeedback());
            }
            throw new RestException(500, $exception->getMessage());
        }
        $this->sendAllowHeadersForExecution($artifact);
    }

    /** @return array */
    private function getChanges(
        $status,
        $results,
        Tracker_Artifact $artifact,
        PFUser $user
    ) {
        $changes = array();

        $status_value = $this->getFormattedChangesetValueForFieldList(
            self::FIELD_STATUS,
            $status,
            $artifact,
            $user
        );
        if ($status_value) {
            $changes[] = $status_value;
        }

        if (get_magic_quotes_gpc()) {
            $results = stripslashes($results);
        }
        $result_value = $this->getFormattedChangesetValueForFieldText(
            self::FIELD_RESULTS,
            $results,
            $artifact,
            $user
        );
        if ($result_value) {
            $changes[] = $result_value;
        }

        return $changes;
    }

    private function getFormattedChangesetValueForFieldList(
        $field_name,
        $value,
        Tracker_Artifact $artifact,
        PFUser $user
    ) {
        $field = $this->getFieldByName($field_name, $artifact, $user);
        if (! $field) {
            return null;
        }

        $binds = $field->getBind()->getValuesByKeyword($value);
        $bind = array_pop($binds);
        if (! $bind) {
            throw new RestException(400, 'Invalid status value');
        }

        return array(
            "field_id"      => (int) $field->getId(),
            "bind_value_ids" => array(
                (int) $bind->getId()
            )
        );
    }

    private function getFormattedChangesetValueForFieldText(
        $field_name,
        $value,
        $artifact,
        $user
    ) {
        $field = $this->getFieldByName($field_name, $artifact, $user);
        if (! $field) {
            return null;
        }

        return array(
            "field_id" => (int) $field->getId(),
            "value"    => array(
                'format'  => Tracker_Artifact_ChangesetValue_Text::TEXT_CONTENT,
                'content' => $value
            )
        );
    }

    private function getFieldByName($field_name, $artifact, $user) {
        $tracker_id = $artifact->getTrackerId();

        return  $this->formelement_factory->getUsedFieldByNameForUser(
            $tracker_id,
            $field_name,
            $user
        );
    }

    /**
     * @param int $id
     *
     * @return Tracker_Artifact
     */
    private function getArtifactById(PFUser $user, $id) {
        $artifact = $this->artifact_factory->getArtifactByIdUserCanView($user, $id);
        if ($artifact) {
            ProjectAuthorization::userCanAccessProject(
                $user,
                $artifact->getTracker()->getProject(),
                new Tracker_URLVerification()
            );
            return $artifact;
        }
        throw new RestException(404);
    }

    private function sendAllowHeadersForExecution(Tracker_Artifact $artifact) {
        $date = $artifact->getLastUpdateDate();
        Header::allowOptionsPut();
        Header::lastModified($date);
    }
}
