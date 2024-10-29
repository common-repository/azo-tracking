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

class FilterUppercaseDetails extends \Google\Model
{
    /**
     * @var string
     */
    public $field;
    /**
     * @var int
     */
    public $fieldIndex;
    /**
     * @param string
     */
    public function setField($field)
    {
        $this->field = $field;
    }
    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }
    /**
     * @param int
     */
    public function setFieldIndex($fieldIndex)
    {
        $this->fieldIndex = $fieldIndex;
    }
    /**
     * @return int
     */
    public function getFieldIndex()
    {
        return $this->fieldIndex;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\Google\Service\Analytics\FilterUppercaseDetails::class, 'AZO\\Google_Service_Analytics_FilterUppercaseDetails');
