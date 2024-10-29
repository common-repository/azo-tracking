<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/api/expr/v1alpha1/syntax.proto
namespace Google\Api\Expr\V1alpha1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;
/**
 * Source information collected at parse time.
 *
 * Generated from protobuf message <code>google.api.expr.v1alpha1.SourceInfo</code>
 */
class SourceInfo extends \Google\Protobuf\Internal\Message
{
    /**
     * The syntax version of the source, e.g. `cel1`.
     *
     * Generated from protobuf field <code>string syntax_version = 1;</code>
     */
    private $syntax_version = '';
    /**
     * The location name. All position information attached to an expression is
     * relative to this location.
     * The location could be a file, UI element, or similar. For example,
     * `acme/app/AnvilPolicy.cel`.
     *
     * Generated from protobuf field <code>string location = 2;</code>
     */
    private $location = '';
    /**
     * Monotonically increasing list of character offsets where newlines appear.
     * The line number of a given position is the index `i` where for a given
     * `id` the `line_offsets[i] < id_positions[id] < line_offsets[i+1]`. The
     * column may be derivd from `id_positions[id] - line_offsets[i]`.
     *
     * Generated from protobuf field <code>repeated int32 line_offsets = 3;</code>
     */
    private $line_offsets;
    /**
     * A map from the parse node id (e.g. `Expr.id`) to the character offset
     * within source.
     *
     * Generated from protobuf field <code>map<int64, int32> positions = 4;</code>
     */
    private $positions;
    /**
     * A map from the parse node id where a macro replacement was made to the
     * call `Expr` that resulted in a macro expansion.
     * For example, `has(value.field)` is a function call that is replaced by a
     * `test_only` field selection in the AST. Likewise, the call
     * `list.exists(e, e > 10)` translates to a comprehension expression. The key
     * in the map corresponds to the expression id of the expanded macro, and the
     * value is the call `Expr` that was replaced.
     *
     * Generated from protobuf field <code>map<int64, .google.api.expr.v1alpha1.Expr> macro_calls = 5;</code>
     */
    private $macro_calls;
    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $syntax_version
     *           The syntax version of the source, e.g. `cel1`.
     *     @type string $location
     *           The location name. All position information attached to an expression is
     *           relative to this location.
     *           The location could be a file, UI element, or similar. For example,
     *           `acme/app/AnvilPolicy.cel`.
     *     @type int[]|\Google\Protobuf\Internal\RepeatedField $line_offsets
     *           Monotonically increasing list of character offsets where newlines appear.
     *           The line number of a given position is the index `i` where for a given
     *           `id` the `line_offsets[i] < id_positions[id] < line_offsets[i+1]`. The
     *           column may be derivd from `id_positions[id] - line_offsets[i]`.
     *     @type array|\Google\Protobuf\Internal\MapField $positions
     *           A map from the parse node id (e.g. `Expr.id`) to the character offset
     *           within source.
     *     @type array|\Google\Protobuf\Internal\MapField $macro_calls
     *           A map from the parse node id where a macro replacement was made to the
     *           call `Expr` that resulted in a macro expansion.
     *           For example, `has(value.field)` is a function call that is replaced by a
     *           `test_only` field selection in the AST. Likewise, the call
     *           `list.exists(e, e > 10)` translates to a comprehension expression. The key
     *           in the map corresponds to the expression id of the expanded macro, and the
     *           value is the call `Expr` that was replaced.
     * }
     */
    public function __construct($data = NULL)
    {
        \GPBMetadata\Google\Api\Expr\V1Alpha1\Syntax::initOnce();
        parent::__construct($data);
    }
    /**
     * The syntax version of the source, e.g. `cel1`.
     *
     * Generated from protobuf field <code>string syntax_version = 1;</code>
     * @return string
     */
    public function getSyntaxVersion()
    {
        return $this->syntax_version;
    }
    /**
     * The syntax version of the source, e.g. `cel1`.
     *
     * Generated from protobuf field <code>string syntax_version = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setSyntaxVersion($var)
    {
        GPBUtil::checkString($var, True);
        $this->syntax_version = $var;
        return $this;
    }
    /**
     * The location name. All position information attached to an expression is
     * relative to this location.
     * The location could be a file, UI element, or similar. For example,
     * `acme/app/AnvilPolicy.cel`.
     *
     * Generated from protobuf field <code>string location = 2;</code>
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }
    /**
     * The location name. All position information attached to an expression is
     * relative to this location.
     * The location could be a file, UI element, or similar. For example,
     * `acme/app/AnvilPolicy.cel`.
     *
     * Generated from protobuf field <code>string location = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setLocation($var)
    {
        GPBUtil::checkString($var, True);
        $this->location = $var;
        return $this;
    }
    /**
     * Monotonically increasing list of character offsets where newlines appear.
     * The line number of a given position is the index `i` where for a given
     * `id` the `line_offsets[i] < id_positions[id] < line_offsets[i+1]`. The
     * column may be derivd from `id_positions[id] - line_offsets[i]`.
     *
     * Generated from protobuf field <code>repeated int32 line_offsets = 3;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getLineOffsets()
    {
        return $this->line_offsets;
    }
    /**
     * Monotonically increasing list of character offsets where newlines appear.
     * The line number of a given position is the index `i` where for a given
     * `id` the `line_offsets[i] < id_positions[id] < line_offsets[i+1]`. The
     * column may be derivd from `id_positions[id] - line_offsets[i]`.
     *
     * Generated from protobuf field <code>repeated int32 line_offsets = 3;</code>
     * @param int[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setLineOffsets($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::INT32);
        $this->line_offsets = $arr;
        return $this;
    }
    /**
     * A map from the parse node id (e.g. `Expr.id`) to the character offset
     * within source.
     *
     * Generated from protobuf field <code>map<int64, int32> positions = 4;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getPositions()
    {
        return $this->positions;
    }
    /**
     * A map from the parse node id (e.g. `Expr.id`) to the character offset
     * within source.
     *
     * Generated from protobuf field <code>map<int64, int32> positions = 4;</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setPositions($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::INT64, \Google\Protobuf\Internal\GPBType::INT32);
        $this->positions = $arr;
        return $this;
    }
    /**
     * A map from the parse node id where a macro replacement was made to the
     * call `Expr` that resulted in a macro expansion.
     * For example, `has(value.field)` is a function call that is replaced by a
     * `test_only` field selection in the AST. Likewise, the call
     * `list.exists(e, e > 10)` translates to a comprehension expression. The key
     * in the map corresponds to the expression id of the expanded macro, and the
     * value is the call `Expr` that was replaced.
     *
     * Generated from protobuf field <code>map<int64, .google.api.expr.v1alpha1.Expr> macro_calls = 5;</code>
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getMacroCalls()
    {
        return $this->macro_calls;
    }
    /**
     * A map from the parse node id where a macro replacement was made to the
     * call `Expr` that resulted in a macro expansion.
     * For example, `has(value.field)` is a function call that is replaced by a
     * `test_only` field selection in the AST. Likewise, the call
     * `list.exists(e, e > 10)` translates to a comprehension expression. The key
     * in the map corresponds to the expression id of the expanded macro, and the
     * value is the call `Expr` that was replaced.
     *
     * Generated from protobuf field <code>map<int64, .google.api.expr.v1alpha1.Expr> macro_calls = 5;</code>
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setMacroCalls($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::INT64, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Api\Expr\V1alpha1\Expr::class);
        $this->macro_calls = $arr;
        return $this;
    }
}