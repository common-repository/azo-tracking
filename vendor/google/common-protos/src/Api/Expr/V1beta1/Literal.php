<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/api/expr/v1beta1/expr.proto
namespace Google\Api\Expr\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;
/**
 * Represents a primitive literal.
 * This is similar to the primitives supported in the well-known type
 * `google.protobuf.Value`, but richer so it can represent CEL's full range of
 * primitives.
 * Lists and structs are not included as constants as these aggregate types may
 * contain [Expr][google.api.expr.v1beta1.Expr] elements which require evaluation and are thus not constant.
 * Examples of literals include: `"hello"`, `b'bytes'`, `1u`, `4.2`, `-2`,
 * `true`, `null`.
 *
 * Generated from protobuf message <code>google.api.expr.v1beta1.Literal</code>
 */
class Literal extends \Google\Protobuf\Internal\Message
{
    protected $constant_kind;
    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int $null_value
     *           null value.
     *     @type bool $bool_value
     *           boolean value.
     *     @type int|string $int64_value
     *           int64 value.
     *     @type int|string $uint64_value
     *           uint64 value.
     *     @type float $double_value
     *           double value.
     *     @type string $string_value
     *           string value.
     *     @type string $bytes_value
     *           bytes value.
     * }
     */
    public function __construct($data = NULL)
    {
        \GPBMetadata\Google\Api\Expr\V1Beta1\Expr::initOnce();
        parent::__construct($data);
    }
    /**
     * null value.
     *
     * Generated from protobuf field <code>.google.protobuf.NullValue null_value = 1;</code>
     * @return int
     */
    public function getNullValue()
    {
        return $this->readOneof(1);
    }
    /**
     * null value.
     *
     * Generated from protobuf field <code>.google.protobuf.NullValue null_value = 1;</code>
     * @param int $var
     * @return $this
     */
    public function setNullValue($var)
    {
        GPBUtil::checkEnum($var, \Google\Protobuf\NullValue::class);
        $this->writeOneof(1, $var);
        return $this;
    }
    /**
     * boolean value.
     *
     * Generated from protobuf field <code>bool bool_value = 2;</code>
     * @return bool
     */
    public function getBoolValue()
    {
        return $this->readOneof(2);
    }
    /**
     * boolean value.
     *
     * Generated from protobuf field <code>bool bool_value = 2;</code>
     * @param bool $var
     * @return $this
     */
    public function setBoolValue($var)
    {
        GPBUtil::checkBool($var);
        $this->writeOneof(2, $var);
        return $this;
    }
    /**
     * int64 value.
     *
     * Generated from protobuf field <code>int64 int64_value = 3;</code>
     * @return int|string
     */
    public function getInt64Value()
    {
        return $this->readOneof(3);
    }
    /**
     * int64 value.
     *
     * Generated from protobuf field <code>int64 int64_value = 3;</code>
     * @param int|string $var
     * @return $this
     */
    public function setInt64Value($var)
    {
        GPBUtil::checkInt64($var);
        $this->writeOneof(3, $var);
        return $this;
    }
    /**
     * uint64 value.
     *
     * Generated from protobuf field <code>uint64 uint64_value = 4;</code>
     * @return int|string
     */
    public function getUint64Value()
    {
        return $this->readOneof(4);
    }
    /**
     * uint64 value.
     *
     * Generated from protobuf field <code>uint64 uint64_value = 4;</code>
     * @param int|string $var
     * @return $this
     */
    public function setUint64Value($var)
    {
        GPBUtil::checkUint64($var);
        $this->writeOneof(4, $var);
        return $this;
    }
    /**
     * double value.
     *
     * Generated from protobuf field <code>double double_value = 5;</code>
     * @return float
     */
    public function getDoubleValue()
    {
        return $this->readOneof(5);
    }
    /**
     * double value.
     *
     * Generated from protobuf field <code>double double_value = 5;</code>
     * @param float $var
     * @return $this
     */
    public function setDoubleValue($var)
    {
        GPBUtil::checkDouble($var);
        $this->writeOneof(5, $var);
        return $this;
    }
    /**
     * string value.
     *
     * Generated from protobuf field <code>string string_value = 6;</code>
     * @return string
     */
    public function getStringValue()
    {
        return $this->readOneof(6);
    }
    /**
     * string value.
     *
     * Generated from protobuf field <code>string string_value = 6;</code>
     * @param string $var
     * @return $this
     */
    public function setStringValue($var)
    {
        GPBUtil::checkString($var, True);
        $this->writeOneof(6, $var);
        return $this;
    }
    /**
     * bytes value.
     *
     * Generated from protobuf field <code>bytes bytes_value = 7;</code>
     * @return string
     */
    public function getBytesValue()
    {
        return $this->readOneof(7);
    }
    /**
     * bytes value.
     *
     * Generated from protobuf field <code>bytes bytes_value = 7;</code>
     * @param string $var
     * @return $this
     */
    public function setBytesValue($var)
    {
        GPBUtil::checkString($var, False);
        $this->writeOneof(7, $var);
        return $this;
    }
    /**
     * @return string
     */
    public function getConstantKind()
    {
        return $this->whichOneof("constant_kind");
    }
}
