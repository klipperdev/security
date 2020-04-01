<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Security\Sharing;

use Klipper\Component\DoctrineExtra\Util\ClassUtils;
use Klipper\Component\Security\Event\SharingDisabledEvent;
use Klipper\Component\Security\Event\SharingEnabledEvent;
use Klipper\Component\Security\Exception\AlreadyConfigurationAliasExistingException;
use Klipper\Component\Security\Exception\SharingIdentityConfigNotFoundException;
use Klipper\Component\Security\Exception\SharingSubjectConfigNotFoundException;
use Klipper\Component\Security\Identity\SubjectIdentityInterface;
use Klipper\Component\Security\SharingVisibilities;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Abstract sharing manager.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractSharingManager implements SharingManagerInterface
{
    /**
     * @var SharingProviderInterface
     */
    protected $provider;

    /**
     * @var null|SharingFactoryInterface
     */
    protected $factory;

    /**
     * @var null|EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var array
     */
    protected $subjectConfigs = [];

    /**
     * @var array
     */
    protected $identityConfigs = [];

    /**
     * @var array
     */
    protected $identityAliases = [];

    /**
     * @var bool
     */
    protected $identityRoleable = false;

    /**
     * @var bool
     */
    protected $identityPermissible = false;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var array
     */
    protected $cacheSubjectVisibilities = [];

    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * Constructor.
     *
     * @param SharingProviderInterface     $provider The sharing provider
     * @param null|SharingFactoryInterface $factory  The sharing factory
     */
    public function __construct(SharingProviderInterface $provider, ?SharingFactoryInterface $factory = null)
    {
        $this->provider = $provider;
        $this->factory = $factory;

        $this->provider->setSharingManager($this);
    }

    /**
     * Set the event dispatcher.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        if (null !== $this->dispatcher) {
            $event = $this->enabled ? new SharingEnabledEvent() : new SharingDisabledEvent();
            $this->dispatcher->dispatch($event);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addSubjectConfig(SharingSubjectConfigInterface $config): self
    {
        $this->subjectConfigs[$config->getType()] = $config;
        unset($this->cacheSubjectVisibilities[$config->getType()]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasSubjectConfig(string $class): bool
    {
        $this->init();

        return isset($this->subjectConfigs[ClassUtils::getRealClass($class)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectConfig(string $class): SharingSubjectConfigInterface
    {
        $this->init();
        $class = ClassUtils::getRealClass($class);

        if (!$this->hasSubjectConfig($class)) {
            throw new SharingSubjectConfigNotFoundException($class);
        }

        return $this->subjectConfigs[$class];
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectConfigs(): array
    {
        $this->init();

        return array_values($this->subjectConfigs);
    }

    /**
     * {@inheritdoc}
     */
    public function hasSharingVisibility(SubjectIdentityInterface $subject): bool
    {
        $this->init();

        return SharingVisibilities::TYPE_NONE !== $this->getSharingVisibility($subject);
    }

    /**
     * {@inheritdoc}
     */
    public function getSharingVisibility(SubjectIdentityInterface $subject): string
    {
        $this->init();
        $type = $subject->getType();

        if (!\array_key_exists($type, $this->cacheSubjectVisibilities)) {
            $sharingVisibility = SharingVisibilities::TYPE_NONE;

            if ($this->hasSubjectConfig($type)) {
                $config = $this->getSubjectConfig($type);
                $sharingVisibility = $config->getVisibility();
            }

            $this->cacheSubjectVisibilities[$type] = $sharingVisibility;
        }

        return $this->cacheSubjectVisibilities[$type];
    }

    /**
     * {@inheritdoc}
     */
    public function addIdentityConfig(SharingIdentityConfigInterface $config): self
    {
        if (isset($this->identityAliases[$config->getAlias()])) {
            throw new AlreadyConfigurationAliasExistingException($config->getAlias(), $config->getType());
        }

        $this->identityConfigs[$config->getType()] = $config;
        $this->identityAliases[$config->getAlias()] = $config->getType();

        if ($config->isRoleable()) {
            $this->identityRoleable = true;
        }

        if ($config->isPermissible()) {
            $this->identityPermissible = true;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasIdentityConfig(string $class): bool
    {
        $this->init();

        return isset($this->identityConfigs[ClassUtils::getRealClass($class)])
            || isset($this->identityAliases[$class]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityConfig(string $class): SharingIdentityConfigInterface
    {
        $this->init();
        $class = $this->identityAliases[$class] ?? ClassUtils::getRealClass($class);

        if (!$this->hasIdentityConfig($class)) {
            throw new SharingIdentityConfigNotFoundException($class);
        }

        return $this->identityConfigs[$class];
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityConfigs(): array
    {
        $this->init();

        return array_values($this->identityConfigs);
    }

    /**
     * {@inheritdoc}
     */
    public function hasIdentityRoleable(): bool
    {
        $this->init();

        return $this->identityRoleable;
    }

    /**
     * {@inheritdoc}
     */
    public function hasIdentityPermissible(): bool
    {
        $this->init();

        return $this->identityPermissible;
    }

    /**
     * Initialize the configurations.
     */
    protected function init(): void
    {
        if (!$this->initialized) {
            $this->initialized = true;

            if (null !== $this->factory) {
                foreach ($this->factory->createSubjectConfigurations() as $config) {
                    $this->addSubjectConfig($config);
                }

                foreach ($this->factory->createIdentityConfigurations() as $config) {
                    $this->addIdentityConfig($config);
                }
            }
        }
    }
}
