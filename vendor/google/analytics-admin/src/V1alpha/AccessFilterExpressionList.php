<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/analytics/admin/v1alpha/access_report.proto
namespace Google\Analytics\Admin\V1alpha;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;
/**
 * A list of filter expressions.
 *
 * Generated from protobuf message <code>google.analytics.admin.v1alpha.AccessFilterExpressionList</code>
 */
class AccessFilterExpressionList extends \Google\Protobuf\Internal\Message
{
    /**
     * A list of filter expressions.
     *
     * Generated from protobuf field <code>repeated .google.analytics.admin.v1alpha.AccessFilterExpression expressions = 1;</code>
     */
    private $expressions;
    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type array<\Google\Analytics\Admin\V1alpha\AccessFilterExpression>|\Google\Protobuf\Internal\RepeatedField $expressions
     *           A list of filter expressions.
     * }
     */
    public function __construct($data = NULL)
    {
        \GPBMetadata\Google\Analytics\Admin\V1Alpha\AccessReport::initOnce();
        parent::__construct($data);
    }
    /**
     * A list of filter expressions.
     *
     * Generated from protobuf field <code>repeated .google.analytics.admin.v1alpha.AccessFilterExpression expressions = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getExpressions()
    {
        return $this->expressions;
    }
    /**
     * A list of filter expressions.
     *
     * Generated from protobuf field <code>repeated .google.analytics.admin.v1alpha.AccessFilterExpression expressions = 1;</code>
     * @param array<\Google\Analytics\Admin\V1alpha\AccessFilterExpression>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setExpressions($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Analytics\Admin\V1alpha\AccessFilterExpression::class);
        $this->expressions = $arr;
        return $this;
    }
}
