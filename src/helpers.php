<?php

if (!function_exists('create_hooks_dir')) {
    /**
     * Create hook directory if not exists.
     *
     * @param string $dir
     * @param int $mode
     * @param bool $recursive
     *
     * @return void
     */
    function create_hooks_dir($dir, $mode = 0700, $recursive = true)
    {
        if (!is_dir("{$dir}/hooks")) {
            mkdir("{$dir}/hooks", $mode, $recursive);
        }
    }
}

if (!function_exists('is_windows')) {
    /**
     * Determine whether the current environment is Windows based.
     *
     * @return bool
     */
    function is_windows()
    {
        return stripos(PHP_OS_FAMILY, 'WIN') === 0;
    }
}

if (!function_exists('global_hook_dir')) {
    /**
     * Gets the global directory set for git hooks
     */
    function global_hook_dir()
    {
        return trim((string)shell_exec('git config --global core.hooksPath'));
    }
}

if (!function_exists('git_dir')) {
    /**
     * Resolve absolute git dir which will serve as the default git dir
     * if one is not provided by the user.
     * @return string|false
     */
    function git_dir()
    {
        // Don't show command error if git is not initialized
        $errorToDevNull = is_windows() ? '' : ' 2>/dev/null';

        $gitDir = trim((string)shell_exec('git rev-parse --git-common-dir' . $errorToDevNull));
        if ($gitDir === '' || $gitDir === '--git-common-dir') {
            // the version of git does not support `--git-common-dir`
            // we fallback to `--git-dir` which and lose worktree support
            $gitDir = trim((string)shell_exec('git rev-parse --git-dir' . $errorToDevNull));
        }

        return $gitDir === '' ? false : realpath($gitDir);
    }
}
