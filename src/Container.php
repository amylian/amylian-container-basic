<?php

/*
 * BSD 3-Clause License
 * 
 * Copyright (c) 2019, Abexto - Helicon Software Development / Amylian Project
 *  
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * 
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * 
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 */

namespace Amylian\Container\Basic;

use Amylian\Container\Basic\Exception\InvalidConfigurationException;
use Amylian\Container\Basic\Exception\NotFoundException;

/**
 * A basic PSR-11 compilant Container
 * 
 * You can imagine this container acting as a factory for the requested types
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 */
class Container implements \Psr\Container\ContainerInterface
{
    
    use \Amylian\Utils\ṔropertyTrait;

    /**
     * @var Definition\AbstractInternalDefinition All definitions
     */
    protected $_definitions = [];

    /**
     * @var array items currently resolved ($id => true). Used to detect circular references 
     */
    protected $resolving = [];

    public function __construct(array $configuration = [])
    {
        $this->configure($configuration);
    }

    public function configure(array $configuration = [])
    {
        foreach ($configuration as $p => $v) {
            $this->$p = $v;
        }
    }

    /**
     * Validates the definition by trying to get an instance of all defined id
     * 
     * During configuration very little validation is done. This has two reasons:
     * 
     * 1. We do not want to waste time with validations
     * 2. There is no mandatory order of definitions, thus references might
     *    not be yet defined when a definition is set
     * 
     * During debugging this method can be used to check definitions.
     * 
     * @return bool <code>true</code> if check passed. 
     * @throws InvalidConfigurationException if parameter <code>$throwException</code> is set to <code>true<code>
     * and getting of an id failed. If <code>$throwException</code> is set to false <code>false</false> is returned
     * in case of error
     * 
     */
    public function validate($throwException = true): bool
    {
        foreach ($this->_definitions as $k => $v) {
            try {
                $this->getDefinitionObject($k);
            } catch (\Exception $exc) {
                if (!$throwException) {
                    return false; // Error, but caller does not want an exception ===> RETURN false
                }
                // Wrap Exception in a new Exception
                throw new InvalidConfigurationException(
                        "Validation failed while preparing definition of '$k': " . $exc->getMessage(),
                        0,
                        $exc);
            }
        }

        return true;
        
        foreach ($this->_definitions as $k => $v) {
            try {
                $this->get($k);
            } catch (\Exception $exc) {
                if (!$throwException) {
                    return false; // Error, but caller does not want an exception ===> RETURN false
                }
                // Wrap Exception in a new Exception
                throw new InvalidConfigurationException(
                        "Validation failed when trying to get instance of '$k': " . $exc->getMessage(),
                        0,
                        $exc);
            }
        }

        return true;
    }

    /**
     * Creates the definition object based on the definition
     * 
     * @param \Amylian\Container\Basic\Definition\AbstractDefinition $definition
     * @return \Amylian\Container\Basic\Definition\AbstractDefinition
     * @throws InvalidConfigurationException If the definition could not be handled
     */
    protected function createDefinitionObject(array $definition): Definition\AbstractDefinition
    {
        if ($definition instanceof Definition\AbstractDefinition) {
            return $definition; // Already a Definition ===> RETRURN as is
        }

        // Do some educated guesses based on the given type in order to prevent
        // trying all definition types in most cases

        if (is_string($definition)) {
            return new Definition\AliasDefinition($definition);  // ===> RETURN AliasDefinition
        } elseif (is_callable($definition, false)) {
            return new Definition\BuildDefinition($definition); // ===> RETURN BuildDefinition
        } elseif (is_object($definition)) {
            return new Definition\InstanceDefinition($definition); // ===> RETURN InstanceDefinition
        } elseif (is_array($definition)) {
            $definitionClass = $definition['definitionClass'] ?? null;
            if ($definitionClass !== null) {
                return new $definitionClass($definition); // ===> RETURN defined Class
            }
        }

        $result = Definition\BuildDefinition::createIfPossible($definition) ??
                Definition\AliasDefinition::createIfPossible($definition) ??
                Definition\InstanceDefinition::createIfPossible($definition) ??
                Definition\BuildDefinition::createIfPossible($definition);

        if ($result === null) {
            throw new InvalidConfigurationException("Invalid Configuration");
        }

        return $result;
    }

    /**
     * Returns the definition object of a definition
     */
    protected function getDefinitionObject($id): ?Definition\AbstractDefinition
    {
        $definition = $this->_definitions[$id] ?? null;

        if ($definition !== null) {

            if ($definition instanceof Definition\AbstractDefinition) {
                return $definition; // Definition already built ===> RETURN it
            } else {
                return $this->_definitions[$id] = $this->createDefinitionObject($definition, $id);
            }
        }

        return $definition;
    }
    
    /**
     * Sets multiple definitions
     * @param array $definitions Array of definitions
     */
    public function setDefinitions(array $definitions){
        foreach ($definitions as $id => $definition) {
            $this->setDefinition($id, $definition);
        }
    }

    /**
     * Returns the definition of an id
     * @param string $id
     * @return mixed|Definition\AbstractDefinition
     */
    public function getDefinition($id, $forceBuild): ?Definition\AbstractInternalDefinition
    {
        return $this->_definitions[$id] ?? null;
    }

    /**
     * Sets the definition for an id
     * @param string|callable|array $definition
     */
    public function setDefinition(string $id, array $definition): void
    {
        $this->_definitions[$id] = $definition;
    }
    
    public function resolve($id)
    {
        $definitionObject = $this->getDefinitionObject($id);
        
        if ($definitionObject === null) {
            throw new NotFoundException("Container Item '$id' is not defined");
        }
        
        try {
            if (isset($this->resolving[$id])) {
                throw new CircularReferenceException("Circular reference detected (Referencing '$id')");
            }
            $this->resolving[$id] = true;
            return $definitionObject->resolve($this);
        } finally {
            unset($this->resolving[$id]);
        }
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws \Psr\Container\NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws \Psr\Container\ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    final public function get($id)
    {
        return $this->resolve($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        return isset($this->getInternalDefinition[$id]);
    }

}
