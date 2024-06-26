<?php

namespace RebelCode\Atlas\Query\Traits;

use RebelCode\Atlas\Order;

trait HasOrderTrait
{
    /** @var Order[] */
    protected array $order = [];

    /**
     * Creates a copy with new ordering.
     *
     * @param Order[] $order A list of {@link Order} instances.
     * @return static The new instance.
     */
    public function orderBy(array $order): self
    {
        $new = clone $this;
        $new->order = $order;
        return $new;
    }

    /**
     * Compiles the ORDER BY fragment of an SQL query.
     *
     * @return string
     */
    protected function compileOrder(): string
    {
        if (empty($this->order)) {
            return '';
        }

        $orderParts = [];
        foreach ($this->order as $order) {
            $orderParts[] = $order->getColumn() . ' ' . $order->getSort();
        }

        return 'ORDER BY ' . implode(', ', $orderParts);
    }
}
