<?php
# PULL REQUEST START — SafeUrlGenerator: handle '#' pseudo-route gracefully
namespace App\Support;

use Illuminate\Routing\UrlGenerator;

class SafeUrlGenerator extends UrlGenerator
{
    /**
     * @param  string  $name
     * @param  mixed  $parameters
     * @param  bool  $absolute
     * @return string
     */
    public function route($name, $parameters = [], $absolute = true)
    {
        if ($name === '#') {
            return '#';
        }
        return parent::route($name, $parameters, $absolute);
    }
}
# PULL REQUEST END — SafeUrlGenerator
