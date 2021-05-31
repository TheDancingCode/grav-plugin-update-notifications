<?php

namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\GPM\GPM;
use Grav\Common\Grav;
use Grav\Common\Plugin;
use Grav\Common\Uri;
use RocketTheme\Toolbox\File\YamlFile;

/**
 * Class UpdateNotificationsPlugin
 * @package Grav\Plugin
 */
class UpdateNotificationsPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onSchedulerInitialized' => ['onSchedulerInitialized', 0]
        ];
    }

    /**
     * Composer autoload
     *
     * @return ClassLoader
     */
    public function autoload(): ClassLoader
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    public function onSchedulerInitialized($event)
    {

        $frequency = $this->config->get('plugins.update-notifications.frequency');
        $scheduler = $event['scheduler'];
        $job = $scheduler->addFunction('Grav\Plugin\UpdateNotificationsPlugin::run', [], 'update-notifications');
        $job->at($frequency);
        $job->output('/logs/update-notifications');
        $job->backlink('/plugins/update-notifications');
    }

    public static function run()
    {
        $updates = self::getNewUpdates();
        if ($updates['core'] || $updates['plugins'] || $updates['themes']) {
            self::sendNotifications($updates);
            self::saveSentNotifications($updates);
        }
    }

    private static function getNewUpdates()
    {
        $updates = self::getUpdates();
        $notified = self::getSentNotifications();

        foreach ($updates as $type => $packages) {
            $updates[$type] = array_filter($updates[$type], function ($data, $slug) use ($notified, $type) {
                if (array_key_exists($slug, $notified[$type]) && $data['available'] === $notified[$type][$slug]) {
                    return false;
                }
                return true;
            }, ARRAY_FILTER_USE_BOTH);
        }

        return $updates;
    }

    private static function getUpdates()
    {
        $config = Grav::instance()['config']->get('plugins.update-notifications');
        $gpm = new GPM();
        $updates = [
            'core' => [],
            'plugins' => [],
            'themes' => []
        ];

        if ($config['core_updates_enabled']) {
            $updates['core'] = self::getCoreUpdates($gpm);
        }
        if ($config['plugin_updates_enabled']) {
            $updates['plugins'] = self::getPluginUpdates($gpm);
        }
        if ($config['theme_updates_enabled']) {
            $updates['themes'] = self::getThemeUpdates($gpm);
        }

        return $updates;
    }

    private static function getCoreUpdates($gpm)
    {
        if ($gpm->grav->isUpdatable()) {
            return [
                'grav' => [
                    'name' => 'Grav',
                    'version' => GRAV_VERSION,
                    'available' => $gpm->grav->getVersion()
                ]
            ];
        }

        return [];
    }

    private static function getPluginUpdates($gpm)
    {
        return array_map([__CLASS__, 'getRelevantPackageData'], $gpm->getUpdatablePlugins());
    }

    private static function getThemeUpdates($gpm)
    {
        return array_map([__CLASS__, 'getRelevantPackageData'], $gpm->getUpdatableThemes());
    }

    private static function getRelevantPackageData($package)
    {
        $data = $package->getData();
        return [
            'name' => $data->value('name'),
            'version' => $data->value('version'),
            'available' => $data->value('available')
        ];
    }

    private static function getSentNotifications()
    {
        $notified = [
            'core' => [],
            'plugins' => [],
            'themes' => []
        ];

        $filename = DATA_DIR . 'update-notifications/notified.yaml';
        $file = YamlFile::instance($filename);

        if ($file->exists()) {
            $notified = array_merge($notified,  $file->content());
        }

        return $notified;
    }

    private static function saveSentNotifications($updates)
    {
        $notified = self::getSentNotifications();

        foreach ($updates as $type => $packages) {
            foreach ($packages as $slug => $data) {
                $notified[$type][$slug]  = $data['available'];
            }
        }

        $filename = DATA_DIR . 'update-notifications/notified.yaml';
        $file = YamlFile::instance($filename);
        $file->save($notified);
    }

    private static function sendNotifications($updates)
    {
        $grav = Grav::instance();

        $config = $grav['config']->get('plugins.update-notifications');

        $to = $config['to'];
        $to = is_array($to) ? $to : array_map('trim', explode(',', $to));
        $from = $config['from'];

        $subject = "Updates available for {$grav['base_url_absolute']}";
        $content = "The following updates are available for your Grav site:\n";

        if ($updates['core']) {
            foreach ($updates['core'] as $slug => $data) {
                $content .= "\n- \"{$data['name']}\" can be updated from v{$data['version']} to v{$data['available']}.";
            }
        }

        if ($updates['plugins']) {
            foreach ($updates['plugins'] as $slug => $data) {
                $content .= "\n- Plugin \"{$data['name']}\" can be updated from v{$data['version']} to v{$data['available']}.";
            }
        }

        if ($updates['themes']) {
            foreach ($updates['themes'] as $slug => $data) {
                $content .= "\n- Theme \"{$data['name']}\" can be updated from v{$data['version']} to v{$data['available']}.";
            }
        }

        $admin_url = $grav['uri']->rootUrl(true) . '/admin';

        $content .= "\n\nVisit {$admin_url} to update.";

        $message = $grav['Email']->message($subject, $content, 'text/plain')
            ->setFrom($from)
            ->setTo($to);

        $grav['Email']->send($message);
    }

    public static function getFromAddress()
    {
        $host = (new Uri())->host();
        return 'grav@' . str_replace('www.', '', $host);
    }
}
