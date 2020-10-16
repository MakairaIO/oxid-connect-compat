<?php

namespace Makaira\ConnectCompat;

use Composer\Script\Event;

class Composer
{
    public static function postUpdate(Event $event)
    {
        $packages = $event->getComposer()->getRepositoryManager()->getLocalRepository()->getPackages();
        $installationManager = $event->getComposer()->getInstallationManager();

        $foundOxid6 = false;
        $connectDir = null;

        foreach ($packages as $package) {
            if ($package->getName() === 'oxid-esales/oxideshop-metapackage-ce') {
                if (\version_compare($package->getVersion(), '6.0.0', '>=')) {
                    $foundOxid6 = true;
                }
            }

            if ($package->getName() === 'makaira/oxid-connect') {
                $installationManager = $event->getComposer()->getInstallationManager();
                $connectDir = $installationManager->getInstallPath($package);
            }
        }

        if (!$foundOxid6 && null !== $connectDir) {
            $root = __DIR__ . '/../../../../../../';
            $modules = null;
            foreach (['modules', 'web/modules', 'source/modules'] as $part) {
                if (\is_dir($root . $part)) {
                    $modules = $root . $part;
                    break;
                }
            }

            if (!$modules) {
                echo "Modules directory not found.";
                return;
            }

            $target = realpath($modules) . '/makaira/connect';
            echo "Mirror $connectDir\nto     $target\n";

            $fs = new \Symfony\Component\Filesystem\Filesystem();
            $fs->mirror($connectDir, $target, null, [
                'override' => true,
                'delete' => true
            ]);

            $vendorFile = realpath($modules) . '/makaira/vendormetadata.php';
            if (!$fs->exists($vendorFile)) {
                $fs->touch($vendorFile);
            }
        }
    }
}
