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

class SitemapsListResponse extends \Google\Collection
{
    protected $collection_key = 'sitemap';
    protected $sitemapType = \Google\Service\SearchConsole\WmxSitemap::class;
    protected $sitemapDataType = 'array';
    public $sitemap = [];
    /**
     * @param WmxSitemap[]
     */
    public function setSitemap($sitemap)
    {
        $this->sitemap = $sitemap;
    }
    /**
     * @return WmxSitemap[]
     */
    public function getSitemap()
    {
        return $this->sitemap;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\Google\Service\SearchConsole\SitemapsListResponse::class, 'AZO\\Google_Service_SearchConsole_SitemapsListResponse');
