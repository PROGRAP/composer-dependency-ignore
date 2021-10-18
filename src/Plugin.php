<?php declare(strict_types=1);

namespace Pro\Composer\DependencyIgnore;

use Exception;
use ReflectionProperty;

use Composer\Composer;
use Composer\DependencyResolver\Request;

use Composer\IO\IOInterface;
use Composer\Package\Link;
use Composer\Package\PackageInterface;

use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PrePoolCreateEvent;

use Composer\Semver\Constraint\ConstraintInterface;

class Plugin implements PluginInterface, EventSubscriberInterface {

    protected Composer $composer;
    protected IOInterface $io;

    /**
     * @var string[]
     */
    protected array $rootDependencies;

    function activate(Composer $composer, IOInterface $io): void {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    function deactivate(Composer $composer, IOInterface $io): void {}

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    function uninstall(Composer $composer, IOInterface $io): void {}

    function onFilterDependencies(PrePoolCreateEvent $event): void {
        $rootPackage = $this->composer->getPackage();
        $extras = $rootPackage->getExtra();
        $ignoreList = $extras['ignore'];

        $requestRequires = [];

        foreach ($event->getRequest()->getRequires() as $key => $value) {
            if (in_array($key, $ignoreList)) {
                continue;
            }

            $requestRequires[$key] = $value;
        }


        $rf = new ReflectionProperty(Request::class, 'requires');
        $rf->setAccessible(true);
        $rf->setValue($event->getRequest(), $requestRequires);

        $requires = [];

        foreach ($rootPackage->getRequires() as $key => $package) {
            if (in_array($package->getTarget(), $ignoreList)) {
                continue;
            }

            $requires[$key] = $package;
        }

        $rootPackage->setRequires($requires);
    }

    /**
     * @return array<string, string>
     */
    static function getSubscribedEvents(): array {
        return [
            PluginEvents::PRE_POOL_CREATE => 'onFilterDependencies',
        ];
    }
}
