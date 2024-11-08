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

class RemarketingAudienceAudienceDefinition extends \Google\Model
{
    protected $includeConditionsType = \Google\Service\Analytics\IncludeConditions::class;
    protected $includeConditionsDataType = '';
    public $includeConditions;
    /**
     * @param IncludeConditions
     */
    public function setIncludeConditions(\Google\Service\Analytics\IncludeConditions $includeConditions)
    {
        $this->includeConditions = $includeConditions;
    }
    /**
     * @return IncludeConditions
     */
    public function getIncludeConditions()
    {
        return $this->includeConditions;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\Google\Service\Analytics\RemarketingAudienceAudienceDefinition::class, 'AZO\\Google_Service_Analytics_RemarketingAudienceAudienceDefinition');
