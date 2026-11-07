# Chat polish database migration

For an existing database, back it up and apply `2026-11-07-view-once-messages.sql` first if the `messages.view_once`, `messages.viewed_at`, and `messages.attachment_path` columns are not present. Then apply `2026-11-09-presence-and-chat-polish.sql` to add any missing read/reaction tables and the typing-state table. The latter migration is safe to rerun because it uses `CREATE TABLE IF NOT EXISTS` and does not attempt to add columns already managed by the earlier migration.

New installations should use the current `database/schema.sql`, which includes view-once columns and the typing table, and may run the presence migration if they need migration-created auxiliary tables.

View-once content is deleted server-side after closing (and stale viewed messages are swept on subsequent engine requests); it cannot prevent screenshots or copies made before deletion.
