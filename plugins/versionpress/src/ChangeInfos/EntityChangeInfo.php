<?php

namespace VersionPress\ChangeInfos;

use VersionPress\Database\DbSchemaInfo;
use VersionPress\Database\EntityInfo;
use VersionPress\Git\ActionsInfo;
use VersionPress\Git\CommitMessage;

/**
 * Base class for entity change infos like PostChangeInfo, CommentChangeInfo etc.
 * An entity is a database-tracked object that usually has a VPID (but not alwasy, see e.g. options).
 *
 * Derived ChangeInfos have these things in common:
 *
 * - The VP-Action tag value has the form of "entityName/action/entityId",
 *   e.g. "post/create/8F805A77ABC9485BA3F114E3E251E5FD" or "option/edit/blogname".
 *   Most commonly, the entityId is VPID.
 *
 * - Subclasses usually provide a set of VP tags to store additional info to commits, usually
 *   in the form of "VP-EntityType-Something: value", e.g. "VP-Post-Title: Hello world". These
 *   tags are used when the commit is read later and human-friendly message is rendered in the UI.
 *
 */
class EntityChangeInfo extends TrackedChangeInfo
{

    /** @var EntityInfo */
    private $entityInfo;
    /** @var ActionsInfo */
    private $actionsInfo;

    /**
     * @param EntityInfo $entityInfo
     * @param ActionsInfo $actionsInfo
     * @param string $action
     * @param string $entityId
     * @param array $customTags
     * @param array $customFiles
     */
    public function __construct($entityInfo, $actionsInfo, $action, $entityId, $customTags = [], $customFiles = [])
    {
        parent::__construct($entityInfo->entityName, $actionsInfo, $action, $entityId, $customTags, $customFiles);
        $this->entityInfo = $entityInfo;
        $this->actionsInfo = $actionsInfo;
    }

    public function getEntityName()
    {
        return $this->entityInfo->entityName;
    }

    public function getChangedFiles()
    {
        $change = [
            "type" => "storage-file",
            "entity" => $this->getEntityName(),
            "id" => $this->getEntityId(),
            "parent-id" => $this->getParentId()
        ];

        return array_merge([$change], parent::getChangedFiles());
    }

    /**
     * Used by meta-entity storages as performance optimalization.
     * For example PostMetaChangeInfo returns VPID of related post.
     *
     * @return string|null
     */
    public function getParentId()
    {
        if ($this->entityInfo->parentReference) {
            $entityName = $this->entityInfo->entityName;
            $tagContainingParentId = $this->actionsInfo->getTagContainingParentId($entityName);

            return $this->getCustomTags()[$tagContainingParentId];
        }

        return null;
    }
}
