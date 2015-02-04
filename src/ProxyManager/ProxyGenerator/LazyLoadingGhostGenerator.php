<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace ProxyManager\ProxyGenerator;

use ProxyManager\Generator\Util\ClassGeneratorUtils;
use ProxyManager\Proxy\GhostObjectInterface;
use ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator\StaticProxyConstructor;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\CallInitializer;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\Factory;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\GetProxyInitializer;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\InitializeProxy;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\IsProxyInitialized;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\LazyLoadingMethodInterceptor;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\SetProxyInitializer;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializerProperty;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PropertiesMap;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesDefaults;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use ReflectionClass;
use ReflectionMethod;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Generator for proxies implementing {@see \ProxyManager\Proxy\GhostObjectInterface}
 *
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class LazyLoadingGhostGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass);

        $interfaces          = [GhostObjectInterface::class];
        $publicProperties    = new PublicPropertiesMap($originalClass);
        $privateProperties   = new PrivatePropertiesMap($originalClass);
        $protectedProperties = new ProtectedPropertiesMap($originalClass);

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($initializer = new InitializerProperty());
        $classGenerator->addPropertyFromGenerator($initializationTracker = new InitializationTracker());
        $classGenerator->addPropertyFromGenerator($publicProperties);
        $classGenerator->addPropertyFromGenerator($privateProperties);
        $classGenerator->addPropertyFromGenerator($protectedProperties);

        $init = new CallInitializer(
            $initializer,
            $initializationTracker,
            Properties::fromReflectionClass($originalClass)
        );

        $factoryMethod = new Factory(
            $originalClass,
            $initializer,
            $init,
            $publicProperties,
            $protectedProperties,
            $privateProperties
        );

        array_map(
            function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator) {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                array_map(
                    function (ReflectionMethod $method) use ($initializer, $init) {
                        return LazyLoadingMethodInterceptor::generateMethod(
                            new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                            $initializer,
                            $init
                        );
                    },
                    ProxiedMethodsFilter::getAbstractProxiedMethods($originalClass)
                ),
                [
                    $init,
                    new StaticProxyConstructor(
                        $initializer,
                        Properties::fromReflectionClass($originalClass)
                    ),
                    $factoryMethod->magicGet(),
                    $factoryMethod->magicSet(),
                    $factoryMethod->magicIsset(),
                    $factoryMethod->magicUnset(),
                    $factoryMethod->magicClone(),
                    $factoryMethod->magicSleep(),
                    new SetProxyInitializer($initializer),
                    new GetProxyInitializer($initializer),
                    new InitializeProxy($initializer, $init),
                    new IsProxyInitialized($initializer),
                ]
            )
        );
    }
}
