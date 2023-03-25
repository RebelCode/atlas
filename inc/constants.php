<?php

/** For use in select queries, for readability purposes. */

use RebelCode\Atlas\Join;
use RebelCode\Atlas\Order;

const ALL = ['*'];
const ASC = Order::ASC;
const DESC = Order::DESC;
const INNER = Join::INNER;
const CROSS = Join::CROSS;
const STRAIGHT = Join::STRAIGHT;
const LEFT = Join::LEFT;
const RIGHT = Join::RIGHT;
const NATURAL_LEFT = Join::NATURAL_LEFT;
const NATURAL_RIGHT = Join::NATURAL_RIGHT;
