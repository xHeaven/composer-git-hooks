# composer-git-hooks

[![Software License][badge-license]](LICENSE)
[![Packagist][badge-packagist]][link-packagist]
[![Download][badge-downloads]][link-packagist]

> Manage git hooks easily in your composer configuration. This command line tool makes it easy to implement a consistent
> project-wide usage of git hooks. Specifying hooks in the composer file makes them available for every member of the
> project team. This provides a consistent environment and behavior for everyone which is great. It is also possible to
> use to manage git hooks globally for every repository on your computer. That way you have a reliable set of hooks
> crafted by yourself for every project you choose to work on.

## Install

Add a `hooks` section to the `extra` section of your `composer.json` and add the hooks there.

```json
{
    "extra": {
        "hooks": {
            "pre-commit": [
                "echo committing as $(git config user.name)",
                "php-cs-fixer fix ."
            ],
            "commit-msg": "grep -q '[A-Z]+-[0-9]+.*' $1",
            "pre-push": [
                "php-cs-fixer fix --dry-run .",
                "phpunit"
            ],
            "post-merge": "composer install",
            "...": "..."
        }
    }
}
```

Then install with

```sh
composer require --dev xheaven/composer-git-hooks
```

This installs the `cghooks` binary to your `vendor/bin` folder. If this folder is not in your path, you will need to
preface every command with `vendor/bin/`.

Note: hooks declared in the `scripts` or `hooks` root sections of `composer.json` are no longer supported in v3.

### Global support

You can also install it globally. This feels much more natural when `cghooks` is used with the newly added support for
managing global git hooks.

```sh
composer global require --dev xheaven/composer-git-hooks
```

All commands have global support (besides testing the hooks. Still requires being in the directory with
the `composer.json` file).

### Optional Configuration

#### Stop on failure

When a hook is a sequence of commands, it can be useful to stop the execution when a command fails.

Specify the impacted hooks in the `stop-on-failure` config section.

```json
{
    "extra": {
        "hooks": {
            "config": {
                "stop-on-failure": [
                    "pre-push"
                ]
            },
            "pre-push": [
                "php-cs-fixer fix --dry-run --stop-on-violation .",
                "phpunit"
            ]
        }
    }
}
```

Always be sure to run the [update command](#updating-hooks) after changing the `stop-on-failure` config section.

#### Custom hooks

Custom hooks can be added to the `custom-hooks` array of the `config section.

```json
{
    "extra": {
        "hooks": {
            "config": {
                "custom-hooks": [
                    "pre-flow-feature-start"
                ]
            },
            "pre-flow-feature-start": [
                "echo 'Starting a new feature...'"
            ]
        }
    }
}
```

Always be sure to run the [update command](#updating-hooks) after changing the `custom-hooks` config section.
Note: `config` is not valid custom hook value.

#### Shortcut

Add a `cghooks` script to the `scripts` section of your `composer.json` file. That way, commands can be run
with `composer cghooks ${command}`. This is ideal if you would rather not edit your system path.

```json
{
    "scripts": {
        "cghooks": "vendor/bin/cghooks",
        "...": "..."
    }
}
```

#### Composer Events

Add the following events to your `composer.json` file. The `cghooks` commands will be run every time the events occur.
Go to [Composer Command Events][link-composer-events] for more details about composer's event system.

```json
{
    "scripts": {
        "post-install-cmd": "cghooks add --ignore-lock",
        "post-update-cmd": "cghooks update",
        "...": "..."
    }
}
```

## Usage

All the following commands have to be run either in the same folder as your `composer.json` file or by specifying
the `--git-dir` option to point to a folder with a `composer.json` file.

### Adding Hooks

After installation is complete, run `cghooks add`
to add all the valid git hooks that have been specified in the composer config.

| Option        | Description                      | Command                     |
|---------------|----------------------------------|-----------------------------|
| `no-lock`     | Do not create a lock file        | `cghooks add --no-lock`     |
| `ignore-lock` | Add the lock file to .gitignore  | `cghooks add --ignore-lock` |
| `force-win`   | Force windows bash compatibility | `cghooks add --force-win`   |

The `lock` file contains a list of all added hooks.

If the `--global` flag is used, the hooks will be added globally, and the global git config will also be modified. If no
directory is provided, there is a fallback to the current `core.hooksPath` in the global config. If that value is not
set, it defaults to `$COMPOSER_HOME` (this specific fallback only happens for the `add` command). It will fail with an
error if there is still no path after the fallbacks.

### Updating Hooks

The update command which is run with `cghooks update` basically ignores the lock file and tries to add hooks from the
composer file. This is similar to what the `--force` option for the `add` command did. This command is useful if the
hooks in the `composer.json` file have changed since the first time the hooks were added.

This works similarly when used with `--global` except that there is no fallback to `$COMPOSER_HOME` if no directory is
provided.

### Removing Hooks

Hooks can be easily removed with `cghooks remove`. This will remove all the hooks that were specified in the composer
config.

Hooks can also be removed by passing them as arguments. The command `cghooks remove pre-commit post-commit` which will
remove the `pre-commit` and `post-commit` hooks.

| Option  | Description                                 | Command                  |
|---------|---------------------------------------------|--------------------------|
| `force` | Delete hooks without checking the lock file | `cghooks remove --force` |

**CAREFUL**: If the lock file was tampered with or the force option was used, hooks that already existed before using
this package, but were specified in the composer scripts config will be removed as well. That is, if you had a
previous `pre-commit` hook, but your current composer config also has a `pre-commit` hook, this option will cause the
command to remove your initial hook.

This also does not have a fallback to `$COMPOSER_HOME` if no directory is provided when used with `--global`.

### Listing hooks

Hooks can be listed with the `cghooks list-hooks` command. This basically checks composer config and list the hooks that
actually have files.

#### Common Options

The following options are common to all commands.

| Option     | Description                         | Command                                         |
|------------|-------------------------------------|-------------------------------------------------|
| `git-dir`  | Path to git directory               | `cghooks ${command} --git-dir='/path/to/.git'`  |
| `lock-dir` | Path to lock file directory         | `cghooks ${command} --lock-dir='/path/to/lock'` |
| `global`   | Runs the specified command globally | `cghooks ${command} --global`                   |

Each command also has a flag `-v` to control verbosity for more detailed logs. Currently, only one level is supported.

### Testing Hooks

Hooks can be tested with `cghooks ${hook}` before adding them. Example `cghooks pre-commit` runs the `pre-commit` hook.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Ezinwa Okpoechi][link-author]
- [Mark Magyar][link-maintainer]

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[badge-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg

[badge-packagist]: https://img.shields.io/packagist/v/xheaven/composer-git-hooks.svg?style=flat-square

[badge-downloads]: https://img.shields.io/packagist/dt/xheaven/composer-git-hooks.svg?style=flat-square

[link-author]: https://github.com/BrainMaestro

[link-maintainer]: https://github.com/xHeaven

[link-composer-events]: https://getcomposer.org/doc/articles/scripts.md#command-events

[link-packagist]: https://packagist.org/packages/xheaven/composer-git-hooks
