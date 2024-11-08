<?php

/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */
namespace Google\Service\Analytics;

class EntityUserLink extends \Google\Model
{
    protected $entityType = \Google\Service\Analytics\EntityUserLinkEntity::class;
    protected $entityDataType = '';
    public $entity;
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $kind;
    protected $permissionsType = \Google\Service\Analytics\EntityUserLinkPermissions::class;
    protected $permissionsDataType = '';
    public $permissions;
    /**
     * @var string
     */
    public $selfLink;
    protected $userRefType = \Google\Service\Analytics\UserRef::class;
    protected $userRefDataType = '';
    public $userRef;
    /**
     * @param EntityUserLinkEntity
     */
    public function setEntity(\Google\Service\Analytics\EntityUserLinkEntity $entity)
    {
        $this->entity = $entity;
    }
    /**
     * @return EntityUserLinkEntity
     */
    public function getEntity()
    {
        return $this->entity;
    }
    /**
     * @param string
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @param string
     */
    public function setKind($kind)
    {
        $this->kind = $kind;
    }
    /**
     * @return string
     */
    public function getKind()
    {
        return $this->kind;
    }
    /**
     * @param EntityUserLinkPermissions
     */
    public function setPermissions(\Google\Service\Analytics\EntityUserLinkPermissions $permissions)
    {
        $this->permissions = $permissions;
    }
    /**
     * @return EntityUserLinkPermissions
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
    /**
     * @param string
     */
    public function setSelfLink($selfLink)
    {
        $this->selfLink = $selfLink;
    }
    /**
     * @return string
     */
    public function getSelfLink()
    {
        return $this->selfLink;
    }
    /**
     * @param UserRef
     */
    public function setUserRef(\Google\Service\Analytics\UserRef $userRef)
    {
        $this->userRef = $userRef;
    }
    /**
     * @return UserRef
     */
    public function getUserRef()
    {
        return $this->userRef;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\Google\Service\Analytics\EntityUserLink::class, 'AZO\\Google_Service_Analytics_EntityUserLink');
