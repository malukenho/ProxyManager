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

namespace ProxyManagerTest;

use PHPUnit_Framework_TestCase;
use ProxyManager\Autoloader\AutoloaderInterface;
use ProxyManager\Configuration;
use ProxyManager\GeneratorStrategy\GeneratorStrategyInterface;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Inflector\ClassNameInflectorInterface;
use ProxyManager\Signature\ClassSignatureGeneratorInterface;
use ProxyManager\Signature\SignatureCheckerInterface;
use ProxyManager\Signature\SignatureGeneratorInterface;

/**
 * Tests for {@see \ProxyManager\Configuration}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class ConfigurationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ProxyManager\Configuration
     */
    protected $configuration;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->configuration = new Configuration();
    }

    /**
     * @covers \ProxyManager\Configuration::getProxiesNamespace
     * @covers \ProxyManager\Configuration::setProxiesNamespace
     */
    public function testGetSetProxiesNamespace()
    {
        $this->assertSame(
            'ProxyManagerGeneratedProxy',
            $this->configuration->getProxiesNamespace(),
            'Default setting check for BC'
        );

        $this->configuration->setProxiesNamespace('foo');
        $this->assertSame('foo', $this->configuration->getProxiesNamespace());
    }

    /**
     * @covers \ProxyManager\Configuration::getClassNameInflector
     * @covers \ProxyManager\Configuration::setClassNameInflector
     */
    public function testSetGetClassNameInflector()
    {
        $this->assertInstanceOf(ClassNameInflectorInterface::class, $this->configuration->getClassNameInflector());

        /* @var $inflector \ProxyManager\Inflector\ClassNameInflectorInterface */
        $inflector = $this->getMock(ClassNameInflectorInterface::class);

        $this->configuration->setClassNameInflector($inflector);
        $this->assertSame($inflector, $this->configuration->getClassNameInflector());
    }

    /**
     * @covers \ProxyManager\Configuration::getGeneratorStrategy
     */
    public function testDefaultGeneratorStrategyNeedToBeAInstanceOfEvaluatingGeneratorStrategy()
    {
        $this->assertInstanceOf(EvaluatingGeneratorStrategy::class, $this->configuration->getGeneratorStrategy());
    }

    /**
     * @covers \ProxyManager\Configuration::getGeneratorStrategy
     * @covers \ProxyManager\Configuration::setGeneratorStrategy
     */
    public function testSetGetGeneratorStrategy()
    {

        $this->assertInstanceOf(GeneratorStrategyInterface::class, $this->configuration->getGeneratorStrategy());

        /* @var $strategy \ProxyManager\GeneratorStrategy\GeneratorStrategyInterface */
        $strategy = $this->getMock(GeneratorStrategyInterface::class);

        $this->configuration->setGeneratorStrategy($strategy);
        $this->assertSame($strategy, $this->configuration->getGeneratorStrategy());
    }

    /**
     * @covers \ProxyManager\Configuration::getProxiesTargetDir
     * @covers \ProxyManager\Configuration::setProxiesTargetDir
     */
    public function testSetGetProxiesTargetDir()
    {
        $this->assertTrue(is_dir($this->configuration->getProxiesTargetDir()));

        $this->configuration->setProxiesTargetDir(__DIR__);
        $this->assertSame(__DIR__, $this->configuration->getProxiesTargetDir());
    }

    /**
     * @covers \ProxyManager\Configuration::getProxyAutoloader
     * @covers \ProxyManager\Configuration::setProxyAutoloader
     */
    public function testSetGetProxyAutoloader()
    {
        $this->assertInstanceOf(AutoloaderInterface::class, $this->configuration->getProxyAutoloader());

        /* @var $autoloader \ProxyManager\Autoloader\AutoloaderInterface */
        $autoloader = $this->getMock(AutoloaderInterface::class);

        $this->configuration->setProxyAutoloader($autoloader);
        $this->assertSame($autoloader, $this->configuration->getProxyAutoloader());
    }

    /**
     * @covers \ProxyManager\Configuration::getSignatureGenerator
     * @covers \ProxyManager\Configuration::setSignatureGenerator
     */
    public function testSetGetSignatureGenerator()
    {
        $this->assertInstanceOf(SignatureGeneratorInterface::class, $this->configuration->getSignatureGenerator());

        /* @var $signatureGenerator \ProxyManager\Signature\SignatureGeneratorInterface */
        $signatureGenerator = $this->getMock(SignatureGeneratorInterface::class);

        $this->configuration->setSignatureGenerator($signatureGenerator);
        $this->assertSame($signatureGenerator, $this->configuration->getSignatureGenerator());
    }

    /**
     * @covers \ProxyManager\Configuration::getSignatureChecker
     * @covers \ProxyManager\Configuration::setSignatureChecker
     */
    public function testSetGetSignatureChecker()
    {
        $this->assertInstanceOf(SignatureCheckerInterface::class, $this->configuration->getSignatureChecker());

        /* @var $signatureChecker \ProxyManager\Signature\SignatureCheckerInterface */
        $signatureChecker = $this->getMock(SignatureCheckerInterface::class);

        $this->configuration->setSignatureChecker($signatureChecker);
        $this->assertSame($signatureChecker, $this->configuration->getSignatureChecker());
    }

    /**
     * @covers \ProxyManager\Configuration::getClassSignatureGenerator
     * @covers \ProxyManager\Configuration::setClassSignatureGenerator
     */
    public function testSetGetClassSignatureGenerator()
    {
        $this->assertInstanceOf(
            ClassSignatureGeneratorInterface::class,
            $this->configuration->getClassSignatureGenerator()
        );

        /* @var $classSignatureGenerator \ProxyManager\Signature\ClassSignatureGeneratorInterface */
        $classSignatureGenerator = $this->getMock(ClassSignatureGeneratorInterface::class);

        $this->configuration->setClassSignatureGenerator($classSignatureGenerator);
        $this->assertSame($classSignatureGenerator, $this->configuration->getClassSignatureGenerator());
    }
}
