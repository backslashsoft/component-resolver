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
        $files = scandir(self::FALLBACK_PATH);

        if ($files != false && count($files) > 0) {
            foreach ($files as $file) {
                $file_parts = pathinfo(self::FALLBACK_PATH . '/' . $file);
                if ($file_parts['extension'] == "php") {
                    $className = $file_parts['filename'];
                    $results[$className] = self::Resolve($className);
                }
            }
        }

        return $results;
    }

    public function Resolve($className)
    {

        $this->validate();

        $path = $this->getPathFromCache($className);

        if (empty($path)) {
            if (file_exists(self::DEFAULT_PATH . $className)) {
                $path = self::DEFAULT_PATH;
            } else {
                if (file_exists(self::FALLBACK_PATH . $className)) {
                    $path = self::FALLBACK_PATH;
                }
            }
            $this->setPathToCache($className, $path);
        }

        return $path;
    }

    public function Validate()
    {
        $exceptionExists = false;
        $missingConstants = "";

        if (!defined('self::DEFAULT_PATH') && empty(self::DEFAULT_PATH)) {
            $exceptionExists = true;
            $missingConstants .= "DEFAULT_PATH";
        }
        if (!defined('self::FALLBACK_PATH') && empty(self::FALLBACK_PATH)) {
            $exceptionExists ? $missingConstants .= " / FALLBACK_PATH" : $missingConstants .= "FALLBACK_PATH";
            $exceptionExists = true;
        }
        if (!defined('self::DEFAULT_NAMESPACE') && empty(self::DEFAULT_NAMESPACE)) {
            $exceptionExists ? $missingConstants .= " / DEFAULT_NAMESPACE" : $missingConstants .= "DEFAULT_NAMESPACE";
            $exceptionExists = true;
        }
        if (!defined('self::FALLBACK_NAMESPACE') && empty(self::FALLBACK_NAMESPACE)) {
            $exceptionExists ? $missingConstants .= " / FALLBACK_NAMESPACE" : $missingConstants .= "FALLBACK_NAMESPACE";
            $exceptionExists = true;
        }

        if($exceptionExists){
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

    public function setPathToCache($className, $path)
    {
        $this->Cache[$className] = $path;
    }

    public function getPathFromCache($className)
    {
        $path = null;
        if (array_key_exists($className, $this->Cache)) {
            $path = $this->Cache[$className];
        }
        return $path;
    }

}