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

namespace Amylian\Container\Basic\Definition;

/**
 * Description of AbstractDefinition
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 */
abstract class AbstractDefinition implements \Amylian\Container\Basic\Contract\ResolvableInterface
{
    use \Amylian\Utils\ṔropertyTrait;
    
    protected $_shared = true;
    
    /**
     * @var object The shared instance (irrelevant if not shared)
     */
    protected $_instance = null;
    
    public function __construct(array $definition)
    {
        $this->configure($definition);
    }
    
    public function configure(array $definition)
    {
        if (is_array($definition)) {
            foreach ($definition as $p => $v) {
                $this->$p = $v;
            }
        }
    }
    
    /**
     * Returns true if the passed definition is supported
     */
    static function supportsDefinition($definition): bool
    {
        return is_array($definition) && 
            isset($definition['definitionClass']) && 
            $definition['definitionClass'] === static::class;
    }
            
    
    /**
     * Creates the definition object if possible
     * 
     * This static method checks if the given definition can be handled
     * by calling {@see static::supportsDefinition}. 
     * 
     * If the definition is supported, an instance is created and returned.
     * Otherwise it returns null
     * 
     * @param mixed $definition
     * @return null|\Amylian\Container\Basic\Definition\AbstractDefinition
     */
    static function createIfPossible(array $definition = []): ?AbstractDefinition
    {
        if (static::supportsDefinition($definition)) {
            return new static ($definition);
        } else {
            return null;
        }
    }
    
    /**
     * Actually resolves the instance
     * This is called by resolve if it no ready made instance is available, yet
     * @return object
     */
    abstract protected function doResolve(\Psr\Container\ContainerInterface $container): ?object;
    
    /**
     * Returns the definition as configuration array
     * @return array
     */
    public function getConfigurationArray(): array
    {
        return [
            'definitionClass' => get_class($this),
            'shared' => $this->shared];
    }
    
    
    public function setShared($shared) {
        $this->_shared = $shared;
    }
    
    public function getShared() {
        return $this->_shared;
    }
    
    protected function getInstance()
    {
        return $this->_shared ?  $this->_instance : null;
    }
    
    protected function setInstance($sharedInstance)
    {
        $this->_instance = $this->_shared ? $sharedInstance : null;
    }
    
    public function resolve(\Psr\Container\ContainerInterface $container)
    {
        $result = $this->getInstance();
        if ($result === null) {
          $result = $this->doResolve($container);
          if ($this->shared === true) {
              $this->setInstance($result);
          }
        }
        return $result;
    }
    
    
}
