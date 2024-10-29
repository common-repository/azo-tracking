<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/iam/v1/policy.proto
namespace Google\Cloud\Iam\V1\AuditConfigDelta;

use UnexpectedValueException;
/**
 * The type of action performed on an audit configuration in a policy.
 *
 * Protobuf type <code>google.iam.v1.AuditConfigDelta.Action</code>
 */
class Action
{
    /**
     * Unspecified.
     *
     * Generated from protobuf enum <code>ACTION_UNSPECIFIED = 0;</code>
     */
    const ACTION_UNSPECIFIED = 0;
    /**
     * Addition of an audit configuration.
     *
     * Generated from protobuf enum <code>ADD = 1;</code>
     */
    const ADD = 1;
    /**
     * Removal of an audit configuration.
     *
     * Generated from protobuf enum <code>REMOVE = 2;</code>
     */
    const REMOVE = 2;
    private static $valueToName = [self::ACTION_UNSPECIFIED => 'ACTION_UNSPECIFIED', self::ADD => 'ADD', self::REMOVE => 'REMOVE'];
    public static function name($value)
    {
        if (!isset(self::$valueToName[$value])) {
            throw new UnexpectedValueException(\sprintf('Enum %s has no name defined for value %s', __CLASS__, $value));
        }
        return self::$valueToName[$value];
    }
    public static function value($name)
    {
        $const = __CLASS__ . '::' . \strtoupper($name);
        if (!\defined($const)) {
            throw new UnexpectedValueException(\sprintf('Enum %s has no value defined for name %s', __CLASS__, $name));
        }
        return \constant($const);
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\Google\Cloud\Iam\V1\AuditConfigDelta\Action::class, \Google\Cloud\Iam\V1\AuditConfigDelta_Action::class);