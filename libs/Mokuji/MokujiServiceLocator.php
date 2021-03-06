<?php
class MokujiServiceLocator extends Object
{
    /**
     * Adds the specified service to the service container.
     * @param  string service name
     * @param  mixed  object, class name or factory callback
     * @param  bool   is singleton?
     * @param  array  factory options
     * @return void
     */
    public static function addService($name, $service, $singleton = TRUE, array $options = NULL)
    {
        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException("Service name must be a non-empty string, " . gettype($name) . " given.");
        }
        $session = Environment::getSession('MokujiServiceLocator');
        
        $lower = strtolower($name);
        
        if ($session->$lower == $service) { // only for instantiated services?
            //throw new AmbiguousServiceException("Service named '$name' has been already registered.");
        }

        if (is_object($service)) {
            if (!$singleton || $options) {
                throw new InvalidArgumentException("Service named '$name' is an instantiated object and must therefore be singleton without options.");
            }
            $session->$lower = $service;

        } else {
            if (!$service) {
                throw new InvalidArgumentException("Service named '$name' is empty.");
            }
            $session->factories[$lower] = array($service, $singleton, $options);
        }
    }

    /**
     * Removes the specified service type from the service container.
     * @return void
     */
    public function removeService($name)
    {
        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException("Service name must be a non-empty string, " . gettype($name) . " given.");
        }

        $lower = strtolower($name);
        unset($this->registry[$lower], $this->factories[$lower]);
    }



    /**
     * Gets the service object of the specified type.
     * @param  string service name
     * @param  array  options in case service is not singleton
     * @return mixed
     */
    public static function getService($name, array $options = NULL)
    {
        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException("Service name must be a non-empty string, " . gettype($name) . " given.");
        }
        $session = Environment::getSession('MokujiServiceLocator');
        $lower = strtolower($name);

        if (isset($session->$lower)) { // instantiated singleton
            if ($options) {
                throw new InvalidArgumentException("Service named '$name' is singleton and therefore can not have options.");
            }
            return $session->$lower;

        } elseif (isset($session->factories[$lower])) {
            list($factory, $singleton, $defOptions) = $session->factories[$lower];

            if ($singleton && $options) {
                throw new InvalidArgumentException("Service named '$name' is singleton and therefore can not have options.");

            } elseif ($defOptions) {
                $options = $options ? $options + $defOptions : $defOptions;
            }

            if (is_string($factory) && strpos($factory, ':') === FALSE) { // class name
                Framework::fixNamespace($factory);
                if (!class_exists($factory)) {
                    throw new AmbiguousServiceException("Cannot instantiate service '$name', class '$factory' not found.");
                }
                $service = new $factory;
                if ($options && method_exists($service, 'setOptions')) {
                    $service->setOptions($options); // TODO: better!
                }

            } else { // factory callback
                $factory = callback($factory);
                if (!$factory->isCallable()) {
                    throw new InvalidStateException("Cannot instantiate service '$name', handler '$factory' is not callable.");
                }
                $service = $factory->invoke($options);
                if (!is_object($service)) {
                    throw new AmbiguousServiceException("Cannot instantiate service '$name', value returned by '$factory' is not object.");
                }
            }

            if ($singleton) {
                $session->$lower = $service;
                unset($session->factories[$lower]);
            }
            return $service;
        }

        if ($this->parent !== NULL) {
            return $this->parent->getService($name, $options);

        } else {
            throw new InvalidStateException("Service '$name' not found.");
        }
    }



    /**
     * Exists the service?
     * @param  string service name
     * @param  bool   must be created yet?
     * @return bool
     */
    public function hasService($name, $created = FALSE)
    {
        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException("Service name must be a non-empty string, " . gettype($name) . " given.");
        }

        $lower = strtolower($name);
        return isset($this->registry[$lower]) || (!$created && isset($this->factories[$lower])) || ($this->parent !== NULL && $this->parent->hasService($name, $created));
    }
}
