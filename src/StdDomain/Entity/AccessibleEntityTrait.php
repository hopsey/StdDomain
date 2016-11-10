<?php
/**
 * Created by PhpStorm.
 * User: tomaszchmielewski
 * Date: 09/11/16
 * Time: 11:55
 */

namespace StdDomain\Entity;


trait AccessibleEntityTrait
{
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        return null;
    }
}