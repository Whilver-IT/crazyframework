<?php

namespace Enum;

/**
 * Route用Enum(PHP >= 8.1)
 */
enum RouteIndex {
    case NAME;
    case URI;
    case METHOD;
    case MIDDLEWARE;
    case CALLBACK;
    case ISLOGIN;

    public function value(): int {
        return array_search($this, $this->cases());
    }

}
