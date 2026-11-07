-- Run once on existing GhostLink databases before deploying the updated dashboard.
-- The messages, message_reads, and message_reactions tables are created by database/schema.sql.
ALTER TABLE messages
    ADD COLUMN view_once TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN viewed_at DATETIME NULL,
    ADD COLUMN attachment_path VARCHAR(255) NULL;

CREATE INDEX idx_messages_view_once ON messages (receiver_id, view_once, viewed_at);
