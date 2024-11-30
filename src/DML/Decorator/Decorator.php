<?php
namespace Concept\DBAL\DML\Decorator;

abstract class Decorator extends \Concept\Expression\Decorator\Decorator implements DecoratorInterface
{

    /**
     * Condition Identifier
     * 
     * @return callable
     */
    // public static function conditionIdentifier(): callable
    // {
    //     return fn($input) => $input instanceof SqlExpressionInterface
    //         ? $input
    //         : (is_string($input)
    //         ? preg_replace_callback(
    //             '/(?!\")\b(?!\d|\'[^\']*\'|"[^"]*")(\w+)(\.\w+)?\b/', 
    //             fn ($matches) => count($matches) > 3 
    //                 ? $matches[0] 
    //                 : (
    //                 isset($matches[2])
    //                     ? static::identifier()(sprintf('%s.%s', $matches[1], substr($matches[2], 1)))
    //                     : static::identifier()($matches[1])
    //                 ),
    //             $input
    //             )
    //         : $input
    //         );
    // }
}