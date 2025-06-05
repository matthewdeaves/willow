# Git Hooks Auto-Update System

This repository includes a self-updating git hook system that ensures developers always have the latest git hooks and development aliases.

## How It Works

### Pre-Push Hook Self-Update
The `hooks/pre-push` file includes an auto-update mechanism that:

1. **Checks for Updates**: Before running code quality checks, it compares itself with the source version in `hooks/pre-push`
2. **Auto-Updates**: If differences are found, it automatically runs `./setup_dev_aliases.sh --quiet` to update
3. **Sources Latest Aliases**: Ensures the latest development aliases are available
4. **Continues Execution**: Proceeds with code quality checks using the updated hook

### Manual Installation/Update
To manually install or update hooks and aliases:

```bash
# Install/update hooks and aliases
./setup_dev_aliases.sh

# Install/update in quiet mode (no output)
./setup_dev_aliases.sh --quiet

# Apply changes to current shell session
source ~/.bashrc  # or ~/.zshrc depending on your shell
```

### Setup Script Features

The `setup_dev_aliases.sh` script:
- **Detects Shell**: Automatically identifies bash or zsh
- **Installs Aliases**: Adds development aliases to your shell RC file
- **Updates Git Hooks**: Installs/updates the pre-push hook
- **Backup Protection**: Backs up existing hooks before updating
- **Quiet Mode**: Supports `--quiet` flag for automatic updates
- **Idempotent**: Safe to run multiple times

### Development Aliases

The system includes helpful aliases for development:
- `cake_shell` - Execute CakePHP console commands
- `willowcms_exec` - Execute commands in the WillowCMS container
- `phpunit` - Run PHPUnit tests
- `phpcs_sniff` - Check coding standards
- `phpcs_fix` - Auto-fix coding standard violations
- `cake_migrate` - Run database migrations
- And many more...

### Benefits

1. **Always Current**: Developers automatically get the latest git hooks
2. **Consistent Environment**: Everyone uses the same development tools
3. **Zero Maintenance**: Updates happen transparently during git operations
4. **Fail-Safe**: If updates fail, the original hook continues working

### Troubleshooting

If you encounter issues:

1. **Manual Update**: Run `./setup_dev_aliases.sh` manually
2. **Check Permissions**: Ensure the setup script is executable: `chmod +x setup_dev_aliases.sh`
3. **Verify Git Repo**: Ensure you're in the root of the git repository
4. **Shell Issues**: If aliases don't work, run `source ~/.bashrc` (or `~/.zshrc`)

### Technical Details

- The hook uses `cmp -s` to efficiently compare files
- Updates are atomic - either fully succeed or leave the original intact
- The system works across different operating systems (Linux, macOS, Windows with WSL)
- Quiet mode ensures automatic updates don't interfere with normal git operations