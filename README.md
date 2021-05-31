# Update Notifications Plugin

The **Update Notifications** Plugin is an extension for [Grav CMS](http://github.com/getgrav/grav). Get notified of plugin, theme and core updates by email.

## Installation

Installing the Update Notifications plugin can be done in one of three ways: The GPM (Grav Package Manager) installation method lets you quickly install the plugin with a simple terminal command, the manual method lets you do so via a zip file, and the admin method lets you do so via the Admin Plugin.

### GPM Installation (Preferred)

To install the plugin via the [GPM](http://learn.getgrav.org/advanced/grav-gpm), through your system's terminal (also called the command line), navigate to the root of your Grav-installation, and enter:

    bin/gpm install update-notifications

This will install the Update Notifications plugin into your `/user/plugins`-directory within Grav. Its files can be found under `/your/site/grav/user/plugins/update-notifications`.

### Manual Installation

To install the plugin manually, download the zip-version of this repository and unzip it under `/your/site/grav/user/plugins`. Then rename the folder to `update-notifications`. You can find these files on [GitHub](https://github.com/the-dancing-code/grav-plugin-update-notifications) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/update-notifications

> NOTE: This plugin is a modular component for Grav which may require other plugins to operate, please see its [blueprints.yaml-file on GitHub](https://github.com/the-dancing-code/grav-plugin-update-notifications/blob/master/blueprints.yaml).

### Admin Plugin

If you use the Admin Plugin, you can install the plugin directly by browsing the `Plugins`-menu and clicking on the `Add` button.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/update-notifications/update-notifications.yaml` to `user/config/plugins/update-notifications.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
core_updates_enabled: true
plugin_updates_enabled: true
theme_updates_enabled: true
from:
to:
frequency: '0 0 * * *'
```

| Option                   | Default               | Values    | Description                             |
| ------------------------ | --------------------- | --------- | --------------------------------------- |
| `core_updates_enabled`   | `true`                | `boolean` | Enable notifications for core updates   |
| `plugin_updates_enabled` | `true`                | `boolean` | Enable notifications for plugin updates |
| `theme_updates_enabled`  | `true`                | `boolean` | Enable notifications for theme updates  |
| `from`                   | `grav@yourdomain.com` | `string`  | The "from" email address                |
| `to`                     | `null`                | `string`  | The "to" email address                  |
| `frequency`              | `'0 0 * * *'` (daily) | `string`  | Notifications frequency                 |

Note that if you use the Admin Plugin, a file with your configuration named update-notifications.yaml will be saved in the `user/config/plugins/`-folder once the configuration is saved in the Admin.
