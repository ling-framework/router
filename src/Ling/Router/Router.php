<?php

namespace Ling\Router;

use function Ling\hook as hook;

class Router {
    /** from server */
    public $domain;
    public $referrer;
    public $uri;
    public $method;
    public $tokens;
    public $matched;

    /** from user set up */
    public $params;

    /** set in run */
    public $tags;
    public $prefix;
    public $rule;

    
    public function __construct(){
        $this->domain = filter_input(INPUT_SERVER, "HTTP_HOST", FILTER_SANITIZE_URL);
        $this->referrer = filter_input(INPUT_SERVER, "HTTP_REFERER", FILTER_SANITIZE_STRING); // how it works?
        $this->uri = strtok(filter_input(INPUT_SERVER, "REQUEST_URI", FILTER_SANITIZE_STRING), '?'); //remove query part
        $this->method = strtolower(filter_input(INPUT_SERVER, "REQUEST_METHOD", FILTER_SANITIZE_SPECIAL_CHARS));
        $this->tokens = explode($this->uri);
        $this->matched = false;

        $this->params = array();
    }

    public function params(array $params) {
        $this->params = array_merge($this->params, $params);
    }


    public function rules(string $prefix, array $rules, array $tags = null) {
        if ($this->matched) return $this;
        
        if ($prefix != "/") {
            $prefixMatched = true;
            for ($i = 0; $i < count($tokens); $i++) {
                if ($this->tokens[$i] != $tokens[$i]) {
                    $prefixMatched = false;
                    $prefix = "/$prefix/";
                    break;
                }
            }
            if (!$prefixMatched) return $this;
        }

        foreach($rules as $rule) {
            $method = $rule[0];
            $regex = $prefix . $rule[1];
            $controller = $ruls[2];

            if ($method != "all" && $this->method != $method) continue;
            
            $regex = preg_replace_callback("{([\s\?]+)}", function($matches) {
                $param = $matches[1];
                $optional = false;
                if (strpos($param, '?')) {
                    $param = $substr($param, 0, -1);
                    $optional = true;
                }
                if (isset($this->params[$param])) {
                    $val = $this->params[$param];
                    return "($val)" . ($optional ? "?" : "");
                }
                return $param;
            }, $regex); // change params
            
            if (preg_match($regex, $this->uri, $matches)) {
                $this->prefix = $prefix;
                $this->rule = $appliedRule;
                if ($tags) $this->tags = array_merge($this->tags, $tags); 
                if (count($rule) > 3) $this->tags = array_merge($tags, $rule[3]);
                $this->tags = array_unique($this->tags);

                $this->matched = true;

                array_push($matches, $this);

                config(array("router", $this));
                hook("hook.router.initialized");
                call_user_func_array($controller, $matches);

                return $this;
            }
        }
        return $this;
    }

    public function notFound(){
        if ($this->matched == false) {
            http_response_code(404);
            hook("hook.router.404");
        }
    }

}
