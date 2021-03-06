<?php
namespace YOOtheme\GraphQL\Utils;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Error\InvariantViolation;
use YOOtheme\GraphQL\Language\AST\BooleanValueNode;
use YOOtheme\GraphQL\Language\AST\DocumentNode;
use YOOtheme\GraphQL\Language\AST\EnumValueNode;
use YOOtheme\GraphQL\Language\AST\FloatValueNode;
use YOOtheme\GraphQL\Language\AST\IntValueNode;
use YOOtheme\GraphQL\Language\AST\ListTypeNode;
use YOOtheme\GraphQL\Language\AST\ListValueNode;
use YOOtheme\GraphQL\Language\AST\Location;
use YOOtheme\GraphQL\Language\AST\NamedTypeNode;
use YOOtheme\GraphQL\Language\AST\NameNode;
use YOOtheme\GraphQL\Language\AST\Node;
use YOOtheme\GraphQL\Language\AST\NodeKind;
use YOOtheme\GraphQL\Language\AST\NodeList;
use YOOtheme\GraphQL\Language\AST\NonNullTypeNode;
use YOOtheme\GraphQL\Language\AST\NullValueNode;
use YOOtheme\GraphQL\Language\AST\ObjectFieldNode;
use YOOtheme\GraphQL\Language\AST\ObjectValueNode;
use YOOtheme\GraphQL\Language\AST\OperationDefinitionNode;
use YOOtheme\GraphQL\Language\AST\StringValueNode;
use YOOtheme\GraphQL\Language\AST\ValueNode;
use YOOtheme\GraphQL\Language\AST\VariableNode;
use YOOtheme\GraphQL\Type\Definition\EnumType;
use YOOtheme\GraphQL\Type\Definition\IDType;
use YOOtheme\GraphQL\Type\Definition\InputObjectType;
use YOOtheme\GraphQL\Type\Definition\InputType;
use YOOtheme\GraphQL\Type\Definition\ListOfType;
use YOOtheme\GraphQL\Type\Definition\NonNull;
use YOOtheme\GraphQL\Type\Definition\ScalarType;
use YOOtheme\GraphQL\Type\Definition\Type;
use YOOtheme\GraphQL\Type\Schema;

/**
 * Various utilities dealing with AST
 */
class AST
{
    /**
     * Convert representation of AST as an associative array to instance of GraphQL\Language\AST\Node.
     *
     * For example:
     *
     * ```php
     * AST::fromArray([
     *     'kind' => 'ListValue',
     *     'values' => [
     *         ['kind' => 'StringValue', 'value' => 'my str'],
     *         ['kind' => 'StringValue', 'value' => 'my other str']
     *     ],
     *     'loc' => ['start' => 21, 'end' => 25]
     * ]);
     * ```
     *
     * Will produce instance of `ListValueNode` where `values` prop is a lazily-evaluated `NodeList`
     * returning instances of `StringValueNode` on access.
     *
     * This is a reverse operation for AST::toArray($node)
     *
     * @api
     * @param array $node
     * @return Node
     */
    public static function fromArray(array $node)
    {
        if (!isset($node['kind']) || !isset(NodeKind::$classMap[$node['kind']])) {
            throw new InvariantViolation("Unexpected node structure: " . Utils::printSafeJson($node));
        }

        $kind = isset($node['kind']) ? $node['kind'] : null;
        $class = NodeKind::$classMap[$kind];
        $instance = new $class([]);

        if (isset($node['loc'], $node['loc']['start'], $node['loc']['end'])) {
            $instance->loc = Location::create($node['loc']['start'], $node['loc']['end']);
        }


        foreach ($node as $key => $value) {
            if ('loc' === $key || 'kind' === $key) {
                continue ;
            }
            if (is_array($value)) {
                if (isset($value[0]) || empty($value)) {
                    $value = new NodeList($value);
                } else {
                    $value = self::fromArray($value);
                }
            }
            $instance->{$key} = $value;
        }
        return $instance;
    }

    /**
     * Convert AST node to serializable array
     *
     * @api
     * @param Node $node
     * @return array
     */
    public static function toArray(Node $node)
    {
        return $node->toArray(true);
    }

    /**
     * Produces a GraphQL Value AST given a PHP value.
     *
     * Optionally, a GraphQL type may be provided, which will be used to
     * disambiguate between value primitives.
     *
     * | PHP Value     | GraphQL Value        |
     * | ------------- | -------------------- |
     * | Object        | Input Object         |
     * | Assoc Array   | Input Object         |
     * | Array         | List                 |
     * | Boolean       | Boolean              |
     * | String        | String / Enum Value  |
     * | Int           | Int                  |
     * | Float         | Int / Float          |
     * | Mixed         | Enum Value           |
     * | null          | NullValue            |
     *
     * @api
     * @param $value
     * @param InputType $type
     * @return ObjectValueNode|ListValueNode|BooleanValueNode|IntValueNode|FloatValueNode|EnumValueNode|StringValueNode|NullValueNode
     */
    static function astFromValue($value, InputType $type)
    {
        if ($type instanceof NonNull) {
            $astValue = self::astFromValue($value, $type->getWrappedType());
            if ($astValue instanceof NullValueNode) {
                return null;
            }
            return $astValue;
        }

        if ($value === null) {
            return new NullValueNode([]);
        }

        // Convert PHP array to GraphQL list. If the GraphQLType is a list, but
        // the value is not an array, convert the value using the list's item type.
        if ($type instanceof ListOfType) {
            $itemType = $type->getWrappedType();
            if (is_array($value) || ($value instanceof \Traversable)) {
                $valuesNodes = [];
                foreach ($value as $item) {
                    $itemNode = self::astFromValue($item, $itemType);
                    if ($itemNode) {
                        $valuesNodes[] = $itemNode;
                    }
                }
                return new ListValueNode(['values' => $valuesNodes]);
            }
            return self::astFromValue($value, $itemType);
        }

        // Populate the fields of the input object by creating ASTs from each value
        // in the PHP object according to the fields in the input type.
        if ($type instanceof InputObjectType) {
            $isArray = is_array($value);
            $isArrayLike = $isArray || $value instanceof \ArrayAccess;
            if ($value === null || (!$isArrayLike && !is_object($value))) {
                return null;
            }
            $fields = $type->getFields();
            $fieldNodes = [];
            foreach ($fields as $fieldName => $field) {
                if ($isArrayLike) {
                    $fieldValue = isset($value[$fieldName]) ? $value[$fieldName] : null;
                } else {
                    $fieldValue = isset($value->{$fieldName}) ? $value->{$fieldName} : null;
                }

                // Have to check additionally if key exists, since we differentiate between
                // "no key" and "value is null":
                if (null !== $fieldValue) {
                    $fieldExists = true;
                } else if ($isArray) {
                    $fieldExists = array_key_exists($fieldName, $value);
                } else if ($isArrayLike) {
                    /** @var \ArrayAccess $value */
                    $fieldExists = $value->offsetExists($fieldName);
                } else {
                    $fieldExists = property_exists($value, $fieldName);
                }

                if ($fieldExists) {
                    $fieldNode = self::astFromValue($fieldValue, $field->getType());

                    if ($fieldNode) {
                        $fieldNodes[] = new ObjectFieldNode([
                            'name' => new NameNode(['value' => $fieldName]),
                            'value' => $fieldNode
                        ]);
                    }
                }
            }
            return new ObjectValueNode(['fields' => $fieldNodes]);
        }

        if ($type instanceof ScalarType || $type instanceof EnumType) {
            // Since value is an internally represented value, it must be serialized
            // to an externally represented value before converting into an AST.
            try {
                $serialized = $type->serialize($value);
            } catch (\Exception $error) {
                if ($error instanceof Error && $type instanceof EnumType) {
                    return null;
                }
                throw $error;
            } catch (\Throwable $error) {
                if ($error instanceof Error && $type instanceof EnumType) {
                    return null;
                }
                throw $error;
            }

            // Others serialize based on their corresponding PHP scalar types.
            if (is_bool($serialized)) {
                return new BooleanValueNode(['value' => $serialized]);
            }
            if (is_int($serialized)) {
                return new IntValueNode(['value' => $serialized]);
            }
            if (is_float($serialized)) {
                if ((int) $serialized == $serialized) {
                    return new IntValueNode(['value' => $serialized]);
                }
                return new FloatValueNode(['value' => $serialized]);
            }
            if (is_string($serialized)) {
                // Enum types use Enum literals.
                if ($type instanceof EnumType) {
                    return new EnumValueNode(['value' => $serialized]);
                }

                // ID types can use Int literals.
                $asInt = (int) $serialized;
                if ($type instanceof IDType && (string) $asInt === $serialized) {
                    return new IntValueNode(['value' => $serialized]);
                }

                // Use json_encode, which uses the same string encoding as GraphQL,
                // then remove the quotes.
                return new StringValueNode([
                    'value' => substr(json_encode($serialized), 1, -1)
                ]);
            }

            throw new InvariantViolation('Cannot convert value to AST: ' . Utils::printSafe($serialized));
        }

        throw new Error('Unknown type: ' . Utils::printSafe($type) . '.');
    }

    /**
     * Produces a PHP value given a GraphQL Value AST.
     *
     * A GraphQL type must be provided, which will be used to interpret different
     * GraphQL Value literals.
     *
     * Returns `null` when the value could not be validly coerced according to
     * the provided type.
     *
     * | GraphQL Value        | PHP Value     |
     * | -------------------- | ------------- |
     * | Input Object         | Assoc Array   |
     * | List                 | Array         |
     * | Boolean              | Boolean       |
     * | String               | String        |
     * | Int / Float          | Int / Float   |
     * | Enum Value           | Mixed         |
     * | Null Value           | null          |
     *
     * @api
     * @param $valueNode
     * @param InputType $type
     * @param null $variables
     * @return array|null|\stdClass
     * @throws \Exception
     */
    public static function valueFromAST($valueNode, InputType $type, $variables = null)
    {
        $undefined = Utils::undefined();

        if (!$valueNode) {
            // When there is no AST, then there is also no value.
            // Importantly, this is different from returning the GraphQL null value.
            return $undefined;
        }

        if ($type instanceof NonNull) {
            if ($valueNode instanceof NullValueNode) {
                // Invalid: intentionally return no value.
                return $undefined;
            }
            return self::valueFromAST($valueNode, $type->getWrappedType(), $variables);
        }

        if ($valueNode instanceof NullValueNode) {
            // This is explicitly returning the value null.
            return null;
        }

        if ($valueNode instanceof VariableNode) {
            $variableName = $valueNode->name->value;

            if (!$variables || !array_key_exists($variableName, $variables)) {
                // No valid return value.
                return $undefined;
            }
            // Note: we're not doing any checking that this variable is correct. We're
            // assuming that this query has been validated and the variable usage here
            // is of the correct type.
            return $variables[$variableName];
        }

        if ($type instanceof ListOfType) {
            $itemType = $type->getWrappedType();

            if ($valueNode instanceof ListValueNode) {
                $coercedValues = [];
                $itemNodes = $valueNode->values;
                foreach ($itemNodes as $itemNode) {
                    if (self::isMissingVariable($itemNode, $variables)) {
                        // If an array contains a missing variable, it is either coerced to
                        // null or if the item type is non-null, it considered invalid.
                        if ($itemType instanceof NonNull) {
                            // Invalid: intentionally return no value.
                            return $undefined;
                        }
                        $coercedValues[] = null;
                    } else {
                        $itemValue = self::valueFromAST($itemNode, $itemType, $variables);
                        if ($undefined === $itemValue) {
                            // Invalid: intentionally return no value.
                            return $undefined;
                        }
                        $coercedValues[] = $itemValue;
                    }
                }
                return $coercedValues;
            }
            $coercedValue = self::valueFromAST($valueNode, $itemType, $variables);
            if ($undefined === $coercedValue) {
                // Invalid: intentionally return no value.
                return $undefined;
            }
            return [$coercedValue];
        }

        if ($type instanceof InputObjectType) {
            if (!$valueNode instanceof ObjectValueNode) {
                // Invalid: intentionally return no value.
                return $undefined;
            }

            $coercedObj = [];
            $fields = $type->getFields();
            $fieldNodes = Utils::keyMap($valueNode->fields, function($field) {return $field->name->value;});
            foreach ($fields as $field) {
                /** @var ValueNode $fieldNode */
                $fieldName = $field->name;
                $fieldNode = isset($fieldNodes[$fieldName]) ? $fieldNodes[$fieldName] : null;

                if (!$fieldNode || self::isMissingVariable($fieldNode->value, $variables)) {
                    if ($field->defaultValueExists()) {
                        $coercedObj[$fieldName] = $field->defaultValue;
                    } else if ($field->getType() instanceof NonNull) {
                        // Invalid: intentionally return no value.
                        return $undefined;
                    }
                    continue ;
                }

                $fieldValue = self::valueFromAST($fieldNode ? $fieldNode->value : null, $field->getType(), $variables);

                if ($undefined === $fieldValue) {
                    // Invalid: intentionally return no value.
                    return $undefined;
                }
                $coercedObj[$fieldName] = $fieldValue;
            }
            return $coercedObj;
        }

        if ($type instanceof EnumType) {
            if (!$valueNode instanceof EnumValueNode) {
                return $undefined;
            }
            $enumValue = $type->getValue($valueNode->value);
            if (!$enumValue) {
                return $undefined;
            }

            return $enumValue->value;
        }

        if ($type instanceof ScalarType) {
            // Scalars fulfill parsing a literal value via parseLiteral().
            // Invalid values represent a failure to parse correctly, in which case
            // no value is returned.
            try {
                return $type->parseLiteral($valueNode, $variables);
            } catch (\Exception $error) {
                return $undefined;
            } catch (\Throwable $error) {
                return $undefined;
            }
        }

        throw new Error('Unknown type: ' . Utils::printSafe($type) . '.');
    }

    /**
     * Produces a PHP value given a GraphQL Value AST.
     *
     * Unlike `valueFromAST()`, no type is provided. The resulting PHP value
     * will reflect the provided GraphQL value AST.
     *
     * | GraphQL Value        | PHP Value     |
     * | -------------------- | ------------- |
     * | Input Object         | Assoc Array   |
     * | List                 | Array         |
     * | Boolean              | Boolean       |
     * | String               | String        |
     * | Int / Float          | Int / Float   |
     * | Enum                 | Mixed         |
     * | Null                 | null          |
     *
     * @api
     * @param Node $valueNode
     * @param array|null $variables
     * @return mixed
     * @throws \Exception
     */
    public static function valueFromASTUntyped($valueNode, array $variables = null) {
        switch (true) {
            case $valueNode instanceof NullValueNode:
                return null;
            case $valueNode instanceof IntValueNode:
                return intval($valueNode->value, 10);
            case $valueNode instanceof FloatValueNode:
                return floatval($valueNode->value);
            case $valueNode instanceof StringValueNode:
            case $valueNode instanceof EnumValueNode:
            case $valueNode instanceof BooleanValueNode:
                return $valueNode->value;
            case $valueNode instanceof ListValueNode:
                return array_map(
                    function($node) use ($variables) {
                        return self::valueFromASTUntyped($node, $variables);
                    },
                    iterator_to_array($valueNode->values)
                );
            case $valueNode instanceof ObjectValueNode:
              return array_combine(
                array_map(
                    function($field) { return $field->name->value; },
                    iterator_to_array($valueNode->fields)
                ),
                array_map(
                    function($field) use ($variables) { return self::valueFromASTUntyped($field->value, $variables); },
                    iterator_to_array($valueNode->fields)
                )
              );
            case $valueNode instanceof VariableNode:
                $variableName = $valueNode->name->value;
                return ($variables && isset($variables[$variableName]))
                    ? $variables[$variableName]
                    : null;
        }

        throw new Error('Unexpected value kind: ' . $valueNode->kind . '.');
    }

    /**
     * Returns type definition for given AST Type node
     *
     * @api
     * @param Schema $schema
     * @param NamedTypeNode|ListTypeNode|NonNullTypeNode $inputTypeNode
     * @return Type
     * @throws \Exception
     */
    public static function typeFromAST(Schema $schema, $inputTypeNode)
    {
        if ($inputTypeNode instanceof ListTypeNode) {
            $innerType = self::typeFromAST($schema, $inputTypeNode->type);
            return $innerType ? new ListOfType($innerType) : null;
        }
        if ($inputTypeNode instanceof NonNullTypeNode) {
            $innerType = self::typeFromAST($schema, $inputTypeNode->type);
            return $innerType ? new NonNull($innerType) : null;
        }
        if ($inputTypeNode instanceof NamedTypeNode) {
            return $schema->getType($inputTypeNode->name->value);
        }

        throw new Error('Unexpected type kind: ' . $inputTypeNode->kind . '.');
    }

    /**
     * Returns true if the provided valueNode is a variable which is not defined
     * in the set of variables.
     * @param $valueNode
     * @param $variables
     * @return bool
     */
    private static function isMissingVariable($valueNode, $variables)
    {
        return $valueNode instanceof VariableNode &&
        (!$variables || !array_key_exists($valueNode->name->value, $variables));
    }

    /**
     * Returns operation type ("query", "mutation" or "subscription") given a document and operation name
     *
     * @api
     * @param DocumentNode $document
     * @param string $operationName
     * @return bool
     */
    public static function getOperation(DocumentNode $document, $operationName = null)
    {
        if ($document->definitions) {
            foreach ($document->definitions as $def) {
                if ($def instanceof OperationDefinitionNode) {
                    if (!$operationName || (isset($def->name->value) && $def->name->value === $operationName)) {
                        return $def->operation;
                    }
                }
            }
        }
        return false;
    }
}
