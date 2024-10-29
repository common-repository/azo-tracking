<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/analytics/admin/v1alpha/access_report.proto
namespace Google\Analytics\Admin\V1alpha;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;
/**
 * Expresses dimension or metric filters. The fields in the same expression need
 * to be either all dimensions or all metrics.
 *
 * Generated from protobuf message <code>google.analytics.admin.v1alpha.AccessFilterExpression</code>
 */
class AccessFilterExpression extends \Google\Protobuf\Internal\Message
{
    protected $one_expression;
    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Analytics\Admin\V1alpha\AccessFilterExpressionList $and_group
     *           Each of the FilterExpressions in the and_group has an AND relationship.
     *     @type \Google\Analytics\Admin\V1alpha\AccessFilterExpressionList $or_group
     *           Each of the FilterExpressions in the or_group has an OR relationship.
     *     @type \Google\Analytics\Admin\V1alpha\AccessFilterExpression $not_expression
     *           The FilterExpression is NOT of not_expression.
     *     @type \Google\Analytics\Admin\V1alpha\AccessFilter $access_filter
     *           A primitive filter. In the same FilterExpression, all of the filter's
     *           field names need to be either all dimensions or all metrics.
     * }
     */
    public function __construct($data = NULL)
    {
        \GPBMetadata\Google\Analytics\Admin\V1Alpha\AccessReport::initOnce();
        parent::__construct($data);
    }
    /**
     * Each of the FilterExpressions in the and_group has an AND relationship.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1alpha.AccessFilterExpressionList and_group = 1;</code>
     * @return \Google\Analytics\Admin\V1alpha\AccessFilterExpressionList|null
     */
    public function getAndGroup()
    {
        return $this->readOneof(1);
    }
    public function hasAndGroup()
    {
        return $this->hasOneof(1);
    }
    /**
     * Each of the FilterExpressions in the and_group has an AND relationship.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1alpha.AccessFilterExpressionList and_group = 1;</code>
     * @param \Google\Analytics\Admin\V1alpha\AccessFilterExpressionList $var
     * @return $this
     */
    public function setAndGroup($var)
    {
        GPBUtil::checkMessage($var, \Google\Analytics\Admin\V1alpha\AccessFilterExpressionList::class);
        $this->writeOneof(1, $var);
        return $this;
    }
    /**
     * Each of the FilterExpressions in the or_group has an OR relationship.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1alpha.AccessFilterExpressionList or_group = 2;</code>
     * @return \Google\Analytics\Admin\V1alpha\AccessFilterExpressionList|null
     */
    public function getOrGroup()
    {
        return $this->readOneof(2);
    }
    public function hasOrGroup()
    {
        return $this->hasOneof(2);
    }
    /**
     * Each of the FilterExpressions in the or_group has an OR relationship.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1alpha.AccessFilterExpressionList or_group = 2;</code>
     * @param \Google\Analytics\Admin\V1alpha\AccessFilterExpressionList $var
     * @return $this
     */
    public function setOrGroup($var)
    {
        GPBUtil::checkMessage($var, \Google\Analytics\Admin\V1alpha\AccessFilterExpressionList::class);
        $this->writeOneof(2, $var);
        return $this;
    }
    /**
     * The FilterExpression is NOT of not_expression.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1alpha.AccessFilterExpression not_expression = 3;</code>
     * @return \Google\Analytics\Admin\V1alpha\AccessFilterExpression|null
     */
    public function getNotExpression()
    {
        return $this->readOneof(3);
    }
    public function hasNotExpression()
    {
        return $this->hasOneof(3);
    }
    /**
     * The FilterExpression is NOT of not_expression.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1alpha.AccessFilterExpression not_expression = 3;</code>
     * @param \Google\Analytics\Admin\V1alpha\AccessFilterExpression $var
     * @return $this
     */
    public function setNotExpression($var)
    {
        GPBUtil::checkMessage($var, \Google\Analytics\Admin\V1alpha\AccessFilterExpression::class);
        $this->writeOneof(3, $var);
        return $this;
    }
    /**
     * A primitive filter. In the same FilterExpression, all of the filter's
     * field names need to be either all dimensions or all metrics.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1alpha.AccessFilter access_filter = 4;</code>
     * @return \Google\Analytics\Admin\V1alpha\AccessFilter|null
     */
    public function getAccessFilter()
    {
        return $this->readOneof(4);
    }
    public function hasAccessFilter()
    {
        return $this->hasOneof(4);
    }
    /**
     * A primitive filter. In the same FilterExpression, all of the filter's
     * field names need to be either all dimensions or all metrics.
     *
     * Generated from protobuf field <code>.google.analytics.admin.v1alpha.AccessFilter access_filter = 4;</code>
     * @param \Google\Analytics\Admin\V1alpha\AccessFilter $var
     * @return $this
     */
    public function setAccessFilter($var)
    {
        GPBUtil::checkMessage($var, \Google\Analytics\Admin\V1alpha\AccessFilter::class);
        $this->writeOneof(4, $var);
        return $this;
    }
    /**
     * @return string
     */
    public function getOneExpression()
    {
        return $this->whichOneof("one_expression");
    }
}
