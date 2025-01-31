<?php

namespace Concept\DBAL\DML\Builder\Contract\Traits;

use Concept\DBAL\DML\Expression\KeywordEnum;
use Concept\DBAL\DML\Expression\SqlExpressionInterface;
use Stringable;

trait OrderByTrait
{
    /**
     * {@inheritDoc}
     */
    public function orderBy(...$columns): static
    {
        $orderList = $this->expression()->join(', ')->type(SqlExpressionInterface::TYPE_LIST);

        foreach ($columns as $key => $column) {
            if (is_string($column) || $column instanceof Stringable) {
                $orderList->push($this->expression()->identifier($column));
            } elseif (is_array($column)) {
                foreach ($column as $col => $order) {
                    $orderList->push(
                        $this->expression(
                            $this->expression()->identifier($col),
                            $this->expression()->keyword($this->validateOrderType($order))
                        )->type(SqlExpressionInterface::TYPE_GROUP)
                    );
                }
            } else {
                throw new \InvalidArgumentException("Invalid column format in ORDER BY clause.");
            }
        }
        
        if (!$orderList->isEmpty()) {
            $this->getSection(KeywordEnum::ORDER_BY)->push($orderList);
        }

        return $this;
    }

    /**
     * Validate the ORDER BY type
     *
     * @param string $order
     * @return string
     */
    protected function validateOrderType(string $order): string
    {
        $validOrders = [
            KeywordEnum::ASC, 
            KeywordEnum::DESC, 
            KeywordEnum::NULLS_FIRST, 
            KeywordEnum::NULLS_LAST
        ];

        $upperOrder = strtoupper(trim($order));

        if (!in_array($upperOrder, $validOrders, true)) {
            throw new \InvalidArgumentException("Invalid ORDER BY type: {$order}. Allowed: " . implode(', ', $validOrders));
        }

        return $upperOrder;
    }
}
