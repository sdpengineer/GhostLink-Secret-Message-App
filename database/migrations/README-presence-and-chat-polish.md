# Dashboard polish deployment

This update adds server-backed typing state, unread/read receipts, reactions, view-once messages, and clear-chat handling.

For an existing database, back it up and apply `database/migrations/2026-11-09-presence-and-chat-polish.sql` once before deploying. It adds the `message_reads`, `message_reactions`, and `chat_typing` tables plus view-once/attachment metadata columns on `messages`.

The migration is for the database state that predates these tables/columns. Do not run it a second time without checking the schema, since the `ALTER TABLE` and index creation are intentionally not repeatable. View-once is best-effort server deletion and cannot prevent screenshots or copies made before the viewer closes the content.
