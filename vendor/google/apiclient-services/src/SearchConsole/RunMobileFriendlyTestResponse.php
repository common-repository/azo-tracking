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
namespace Google\Service\SearchConsole;

class RunMobileFriendlyTestResponse extends \Google\Collection
{
    protected $collection_key = 'resourceIssues';
    /**
     * @var string
     */
    public $mobileFriendliness;
    protected $mobileFriendlyIssuesType = \Google\Service\SearchConsole\MobileFriendlyIssue::class;
    protected $mobileFriendlyIssuesDataType = 'array';
    public $mobileFriendlyIssues = [];
    protected $resourceIssuesType = \Google\Service\SearchConsole\ResourceIssue::class;
    protected $resourceIssuesDataType = 'array';
    public $resourceIssues = [];
    protected $screenshotType = \Google\Service\SearchConsole\Image::class;
    protected $screenshotDataType = '';
    public $screenshot;
    protected $testStatusType = \Google\Service\SearchConsole\TestStatus::class;
    protected $testStatusDataType = '';
    public $testStatus;
    /**
     * @param string
     */
    public function setMobileFriendliness($mobileFriendliness)
    {
        $this->mobileFriendliness = $mobileFriendliness;
    }
    /**
     * @return string
     */
    public function getMobileFriendliness()
    {
        return $this->mobileFriendliness;
    }
    /**
     * @param MobileFriendlyIssue[]
     */
    public function setMobileFriendlyIssues($mobileFriendlyIssues)
    {
        $this->mobileFriendlyIssues = $mobileFriendlyIssues;
    }
    /**
     * @return MobileFriendlyIssue[]
     */
    public function getMobileFriendlyIssues()
    {
        return $this->mobileFriendlyIssues;
    }
    /**
     * @param ResourceIssue[]
     */
    public function setResourceIssues($resourceIssues)
    {
        $this->resourceIssues = $resourceIssues;
    }
    /**
     * @return ResourceIssue[]
     */
    public function getResourceIssues()
    {
        return $this->resourceIssues;
    }
    /**
     * @param Image
     */
    public function setScreenshot(\Google\Service\SearchConsole\Image $screenshot)
    {
        $this->screenshot = $screenshot;
    }
    /**
     * @return Image
     */
    public function getScreenshot()
    {
        return $this->screenshot;
    }
    /**
     * @param TestStatus
     */
    public function setTestStatus(\Google\Service\SearchConsole\TestStatus $testStatus)
    {
        $this->testStatus = $testStatus;
    }
    /**
     * @return TestStatus
     */
    public function getTestStatus()
    {
        return $this->testStatus;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\Google\Service\SearchConsole\RunMobileFriendlyTestResponse::class, 'AZO\\Google_Service_SearchConsole_RunMobileFriendlyTestResponse');
