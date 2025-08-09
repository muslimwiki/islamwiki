# GitIntegration Extension

Provides Git repository integration for content versioning, backups, and scholarly review workflows.

## Features

- Auto-commit article changes and deletions
- Configure Git user on login
- Create backups and review branches via hooks
- Optional auto-push to remote

## Configuration (extension.json)

Key options:
- `enabled` (bool): turn integration on/off
- `repositoryPath` (string): relative path under storage (e.g., `storage/git/content`)
- `remoteUrl` (string): optional remote
- `branch` (string): branch to push (default `main`)
- `autoPush` (bool): automatically push after commits
- `commitMessageTemplate` (string): e.g., "Wiki update: {title} by {user}"

## Hooks

- `ArticleSave` → commits/updates the file
- `ArticleDelete` → removes and commits deletion
- `UserLogin` → configures Git user.name and user.email
- `ContentBackup` → creates a backup copy and a backup commit
- `ReviewRequest` → creates a `review/{article_id}/{user_id}` branch and commits pending changes

## Behavior

- Files are stored by slug: `{slug}.md`
- Commit message template replaces `{title}` and `{user}`
- Auto-push runs `git push origin {branch}` if a remote is configured and `autoPush` is true

## Notes

- Ensure the configured `repositoryPath` is writable by the app.
- Initial repo is auto-initialized if missing.
