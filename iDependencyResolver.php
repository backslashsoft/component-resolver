<?php
/**
 * Created by PhpStorm.
 * User: helix
 * Date: 20-Jul-18
 * Time: 13:20
 */

namespace Backslash\Resolver;

interface iDependencyResolver {

    public function Map();

    public function Resolve($className);

    public function Validate();

    public function setPathToCache($className, $path);

    public function getPathFromCache($className);

    public function getInstance();

}