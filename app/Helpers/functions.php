<?php

// for $absolute = false
if (! function_exists('route')) {
    function route($name, $parameters = [], $absolute = false) {
        return app('url')->route($name, $parameters, $absolute);
    }
}
