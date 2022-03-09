<?php
/**
 * Copyright (c) Enalean, 2015 - Present. All Rights Reserved.
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

use Tracker\Artifact\XMLArtifactSourcePlatformExtractor;
use Tuleap\Project\XML\Import\ExternalFieldsExtractor;
use Tuleap\Tracker\Artifact\Changeset\AfterNewChangesetHandler;
use Tuleap\Tracker\Artifact\Changeset\ArtifactChangesetSaver;
use Tuleap\Tracker\Artifact\Changeset\Comment\PrivateComment\TrackerPrivateCommentUGroupEnabledDao;
use Tuleap\Tracker\Artifact\Changeset\Comment\PrivateComment\TrackerPrivateCommentUGroupPermissionDao;
use Tuleap\Tracker\Artifact\Changeset\Comment\PrivateComment\TrackerPrivateCommentUGroupPermissionInserter;
use Tuleap\Tracker\Artifact\Changeset\Comment\PrivateComment\XMLImport\TrackerPrivateCommentUGroupExtractor;
use Tuleap\Tracker\Artifact\Changeset\FieldsToBeSavedInSpecificOrderRetriever;
use Tuleap\Tracker\Artifact\Creation\TrackerArtifactCreator;
use Tuleap\Tracker\Artifact\ExistingArtifactSourceIdFromTrackerExtractor;
use Tuleap\Tracker\DAO\TrackerArtifactSourceIdDao;
use Tuleap\Tracker\FormElement\Field\ArtifactLink\Type\TypeDao;
use Tuleap\Tracker\FormElement\Field\ListFields\Bind\BindStaticValueDao;

class Tracker_Artifact_XMLImportBuilder // phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace,Squiz.Classes.ValidClassName.NotCamelCaps
{
    public function build(
        User\XML\Import\IFindUserFromXMLReference $user_finder,
        \Psr\Log\LoggerInterface $logger,
    ): Tracker_Artifact_XMLImport {
        $artifact_factory        = Tracker_ArtifactFactory::instance();
        $formelement_factory     = Tracker_FormElementFactory::instance();
        $artifact_link_usage_dao = new \Tuleap\Tracker\Admin\ArtifactLinksUsageDao();
        $type_dao                = new TypeDao();
        $artifact_link_validator = new \Tuleap\Tracker\FormElement\ArtifactLinkValidator(
            $artifact_factory,
            new \Tuleap\Tracker\FormElement\Field\ArtifactLink\Type\TypePresenterFactory(
                $type_dao,
                $artifact_link_usage_dao,
            ),
            $artifact_link_usage_dao
        );

        $fields_validator            = new Tracker_Artifact_Changeset_AtGivenDateFieldsValidator(
            $formelement_factory,
            $artifact_link_validator
        );
        $changeset_comment_dao       = new Tracker_Artifact_Changeset_CommentDao();
        $fields_retriever            = new FieldsToBeSavedInSpecificOrderRetriever($formelement_factory);
        $event_manager               = EventManager::instance();
        $after_new_changeset_handler = new AfterNewChangesetHandler(
            $artifact_factory,
            $fields_retriever,
            \WorkflowFactory::instance()
        );
        $field_initializator         = new Tracker_Artifact_Changeset_ChangesetDataInitializator($formelement_factory);
        $artifact_changeset_saver    = ArtifactChangesetSaver::build();

        $send_notifications = false;

        $artifact_creator = TrackerArtifactCreator::build(
            new Tracker_Artifact_Changeset_InitialChangesetAtGivenDateCreator(
                $fields_validator,
                $fields_retriever,
                $event_manager,
                $field_initializator,
                $logger,
                $artifact_changeset_saver,
                $after_new_changeset_handler
            ),
            $fields_validator,
            $logger
        );

        $new_changeset_creator = new Tracker_Artifact_Changeset_NewChangesetAtGivenDateCreator(
            $fields_validator,
            $fields_retriever,
            $changeset_comment_dao,
            $event_manager,
            ReferenceManager::instance(),
            $field_initializator,
            new \Tuleap\DB\DBTransactionExecutorWithConnection(\Tuleap\DB\DBFactory::getMainTuleapDBConnection()),
            $artifact_changeset_saver,
            new \Tuleap\Tracker\FormElement\Field\ArtifactLink\ParentLinkAction(
                $artifact_factory
            ),
            new TrackerPrivateCommentUGroupPermissionInserter(new TrackerPrivateCommentUGroupPermissionDao()),
            $after_new_changeset_handler
        );

        $artifact_source_id_dao = new TrackerArtifactSourceIdDao();

        return new Tracker_Artifact_XMLImport(
            new XML_RNGValidator(),
            $artifact_creator,
            $new_changeset_creator,
            $formelement_factory,
            $user_finder,
            new BindStaticValueDao(),
            $logger,
            $send_notifications,
            $artifact_factory,
            $type_dao,
            new XMLArtifactSourcePlatformExtractor(new Valid_HTTPURI(), $logger),
            new ExistingArtifactSourceIdFromTrackerExtractor($artifact_source_id_dao),
            $artifact_source_id_dao,
            new ExternalFieldsExtractor($event_manager),
            new TrackerPrivateCommentUGroupExtractor(new TrackerPrivateCommentUGroupEnabledDao(), new UGroupManager())
        );
    }
}
