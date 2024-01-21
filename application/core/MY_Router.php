<?php
/**
 * Created by PhpStorm.
 * User: GABRIEL
 * Date: 23/05/2018
 * Time: 13:24
 */

class MY_Router extends CI_Router
{
    protected function _parse_routes()
    {
        // Turn the segment array into a URI string
        $uri = implode('/', $this->uri->segments);

        // Get HTTP verb
        $http_verb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';

        // Loop through the route array looking for wildcards
        foreach ($this->routes as $key => $val)
        {
            // Check if route format is using HTTP verbs
            if (is_array($val))
            {
                $val = array_change_key_case($val, CASE_LOWER);
                if (isset($val[$http_verb]))
                {
                    $val = $val[$http_verb];
                }
                else
                {
                    continue;
                }
            }

            // Convert wildcards to RegEx
            $key = str_replace(array(':any', ':num'), array('[^/]+', '[0-9]+'), $key);
            // Does the RegEx match?

            if (preg_match('#^'.$key.'$#', $uri, $matches))
            {
                // Are we using callbacks to process back-references?
                if ( ! is_string($val) && is_callable($val))
                {
                    // Remove the original string from the matches array.
                    array_shift($matches);

                    // Execute the callback using the values in matches as its parameters.
                    $val = call_user_func_array($val, $matches);
                }
                // Are we using the default routing method for back-references?
                elseif (strpos($val, '$') !== FALSE && strpos($key, '(') !== FALSE)
                {
                    $val = preg_replace('#^'.$key.'$#', $val, $uri);
                }

                $this->_set_request(explode('/', $val));
                return;
            }
        }

        return;
    }
}