<?php
/**
 * Created by PhpStorm.
 * User: helix
 * Date: 20-Jul-18
 * Time: 13:20
 */

namespace Backslash\Resolver;

use Backslash\Resolver\Exceptions\DependencyResolverException;

class DependencyResolver implements iDependencyResolver
{

    const DEFAULT_PATH = null;
    const DEFAULT_NAMESPACE = null;
    const FALLBACK_PATH = null;
    const FALLBACK_NAMESPACE = null;

    public $Cache = [];
    protected static $instance;

    public function Map()
    {
        $results = [];
        $filesFromCallbackPath = scandir(static::FALLBACK_PATH);
        $filesFromDefaultPath = scandir(static::DEFAULT_PATH);
        $files = array_merge($filesFromCallbackPath, $filesFromDefaultPath);

        if ($files != false && count($files) > 0) {
            foreach ($files as $file) {
                if(is_file($file)){
                    $file_parts = pathinfo(static::FALLBACK_PATH . '/' . $file);
                    if ($file_parts['extension'] == "php") {
                        $className = $file_parts['filename'];
                        $results[$className] = self::Resolve($className);
                    }
                }
            }
        }

        return $results;
    }

    public function Resolve($className)
    {
        $this->validate();

        $fullClassName = $this->getFullClassNameFromCache($className);

        if (empty($fullClassName)) {
            if (file_exists(static::DEFAULT_PATH . $className . ".php")) {
                $namespace = static::DEFAULT_NAMESPACE;
            } elseif (file_exists(static::FALLBACK_PATH . $className . ".php")) {
                $namespace = static::FALLBACK_NAMESPACE;
            } else {
                throw new DependencyResolverException(sprintf("Class %s does not exist.", $className));
            }

            $fullClassName = $namespace . "\\" . $className;

            $this->setFullClassNameToCache($className, $fullClassName);
        }

        return $fullClassName;
    }

    public function Validate()
    {
        $exceptionExists = false;
        $missingConstants = "";

        if (!defined('static::DEFAULT_PATH') && empty(static::DEFAULT_PATH)) {
            $exceptionExists = true;
            $missingConstants .= "DEFAULT_PATH";
        }
        if (!defined('static::FALLBACK_PATH') && empty(static::FALLBACK_PATH)) {
            $exceptionExists ? $missingConstants .= " / FALLBACK_PATH" : $missingConstants .= "FALLBACK_PATH";
            $exceptionExists = true;
        }
        if (!defined('static::DEFAULT_NAMESPACE') && empty(static::DEFAULT_NAMESPACE)) {
            $exceptionExists ? $missingConstants .= " / DEFAULT_NAMESPACE" : $missingConstants .= "DEFAULT_NAMESPACE";
            $exceptionExists = true;
        }
        if (!defined('static::FALLBACK_NAMESPACE') && empty(static::FALLBACK_NAMESPACE)) {
            $exceptionExists ? $missingConstants .= " / FALLBACK_NAMESPACE" : $missingConstants .= "FALLBACK_NAMESPACE";
            $exceptionExists = true;
        }

        if ($exceptionExists) {
            throw new DependencyResolverException($missingConstants . " not defined");
        }
    }

    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    public function setFullClassNameToCache($className, $fullClassName)
    {
        $this->Cache[$className] = $fullClassName;
    }

    public function getFullClassNameFromCache($className)
    {
        $fullClassName = null;
        if (array_key_exists($className, $this->Cache)) {
            $fullClassName = $this->Cache[$className];
        }
        return $fullClassName;
    }

}