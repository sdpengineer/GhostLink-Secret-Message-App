-- Apply to existing databases before deploying the upgraded dashboard/engine.
-- New installs should use database/schema.sql plus this migration if these tables are absent.
CREATE TABLE IF NOT EXISTS message_reads (
  message_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  read_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (message_id,user_id),
  KEY idx_reads_user_time (user_id,read_at),
  CONSTRAINT fk_reads_message FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
  CONSTRAINT fk_reads_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS message_reactions (
  message_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  reaction VARCHAR(16) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (message_id,user_id,reaction),
  KEY idx_reactions_user (user_id),
  CONSTRAINT fk_reaction_message FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
  CONSTRAINT fk_reaction_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS chat_typing (
  user_id BIGINT UNSIGNED NOT NULL,
  peer_id BIGINT UNSIGNED NOT NULL,
  is_typing TINYINT(1) NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (user_id,peer_id),
  KEY idx_typing_peer (peer_id,updated_at),
  CONSTRAINT fk_typing_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_typing_peer FOREIGN KEY (peer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE messages
  ADD COLUMN view_once TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN viewed_at DATETIME NULL,
  ADD COLUMN attachment_path VARCHAR(255) NULL;
CREATE INDEX idx_messages_view_once ON messages(receiver_id,view_once,viewed_at);
