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

class Filter extends \Google\Model
{
    /**
     * @var string
     */
    public $accountId;
    protected $advancedDetailsType = \Google\Service\Analytics\FilterAdvancedDetails::class;
    protected $advancedDetailsDataType = '';
    public $advancedDetails;
    /**
     * @var string
     */
    public $created;
    protected $excludeDetailsType = \Google\Service\Analytics\FilterExpression::class;
    protected $excludeDetailsDataType = '';
    public $excludeDetails;
    /**
     * @var string
     */
    public $id;
    protected $includeDetailsType = \Google\Service\Analytics\FilterExpression::class;
    protected $includeDetailsDataType = '';
    public $includeDetails;
    /**
     * @var string
     */
    public $kind;
    protected $lowercaseDetailsType = \Google\Service\Analytics\FilterLowercaseDetails::class;
    protected $lowercaseDetailsDataType = '';
    public $lowercaseDetails;
    /**
     * @var string
     */
    public $name;
    protected $parentLinkType = \Google\Service\Analytics\FilterParentLink::class;
    protected $parentLinkDataType = '';
    public $parentLink;
    protected $searchAndReplaceDetailsType = \Google\Service\Analytics\FilterSearchAndReplaceDetails::class;
    protected $searchAndReplaceDetailsDataType = '';
    public $searchAndReplaceDetails;
    /**
     * @var string
     */
    public $selfLink;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $updated;
    protected $uppercaseDetailsType = \Google\Service\Analytics\FilterUppercaseDetails::class;
    protected $uppercaseDetailsDataType = '';
    public $uppercaseDetails;
    /**
     * @param string
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;
    }
    /**
     * @return string
     */
    public function getAccountId()
    {
        return $this->accountId;
    }
    /**
     * @param FilterAdvancedDetails
     */
    public function setAdvancedDetails(\Google\Service\Analytics\FilterAdvancedDetails $advancedDetails)
    {
        $this->advancedDetails = $advancedDetails;
    }
    /**
     * @return FilterAdvancedDetails
     */
    public function getAdvancedDetails()
    {
        return $this->advancedDetails;
    }
    /**
     * @param string
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }
    /**
     * @return string
     */
    public function getCreated()
    {
        return $this->created;
    }
    /**
     * @param FilterExpression
     */
    public function setExcludeDetails(\Google\Service\Analytics\FilterExpression $excludeDetails)
    {
        $this->excludeDetails = $excludeDetails;
    }
    /**
     * @return FilterExpression
     */
    public function getExcludeDetails()
    {
        return $this->excludeDetails;
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
     * @param FilterExpression
     */
    public function setIncludeDetails(\Google\Service\Analytics\FilterExpression $includeDetails)
    {
        $this->includeDetails = $includeDetails;
    }
    /**
     * @return FilterExpression
     */
    public function getIncludeDetails()
    {
        return $this->includeDetails;
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
     * @param FilterLowercaseDetails
     */
    public function setLowercaseDetails(\Google\Service\Analytics\FilterLowercaseDetails $lowercaseDetails)
    {
        $this->lowercaseDetails = $lowercaseDetails;
    }
    /**
     * @return FilterLowercaseDetails
     */
    public function getLowercaseDetails()
    {
        return $this->lowercaseDetails;
    }
    /**
     * @param string
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @param FilterParentLink
     */
    public function setParentLink(\Google\Service\Analytics\FilterParentLink $parentLink)
    {
        $this->parentLink = $parentLink;
    }
    /**
     * @return FilterParentLink
     */
    public function getParentLink()
    {
        return $this->parentLink;
    }
    /**
     * @param FilterSearchAndReplaceDetails
     */
    public function setSearchAndReplaceDetails(\Google\Service\Analytics\FilterSearchAndReplaceDetails $searchAndReplaceDetails)
    {
        $this->searchAndReplaceDetails = $searchAndReplaceDetails;
    }
    /**
     * @return FilterSearchAndReplaceDetails
     */
    public function getSearchAndReplaceDetails()
    {
        return $this->searchAndReplaceDetails;
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
     * @param string
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * @param string
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }
    /**
     * @return string
     */
    public function getUpdated()
    {
        return $this->updated;
    }
    /**
     * @param FilterUppercaseDetails
     */
    public function setUppercaseDetails(\Google\Service\Analytics\FilterUppercaseDetails $uppercaseDetails)
    {
        $this->uppercaseDetails = $uppercaseDetails;
    }
    /**
     * @return FilterUppercaseDetails
     */
    public function getUppercaseDetails()
    {
        return $this->uppercaseDetails;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\Google\Service\Analytics\Filter::class, 'AZO\\Google_Service_Analytics_Filter');
