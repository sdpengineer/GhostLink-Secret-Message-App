-- SDP Link database bootstrap for a new MariaDB/MySQL installation.
-- Create the database/user first, then import this file into that database.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(80) NOT NULL,
    phone VARCHAR(32) NOT NULL,
    dob VARCHAR(16) NOT NULL,
    password VARCHAR(255) NOT NULL,
    api_key CHAR(64) NOT NULL,
    bio VARCHAR(255) NOT NULL DEFAULT '',
    last_activity DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_phone (phone),
    UNIQUE KEY uq_users_api_key (api_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    sender_id BIGINT UNSIGNED NOT NULL,
    receiver_id BIGINT UNSIGNED NOT NULL,
    message MEDIUMTEXT NOT NULL,
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    view_once TINYINT(1) NOT NULL DEFAULT 0,
    viewed_at DATETIME NULL,
    attachment_path VARCHAR(255) NULL,
    PRIMARY KEY (id),
    KEY idx_message_pair_time (sender_id, receiver_id, timestamp),
    KEY idx_message_receiver_time (receiver_id, sender_id, timestamp),
    KEY idx_messages_view_once (receiver_id, view_once, viewed_at),
    CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_settings (
    user_id BIGINT UNSIGNED NOT NULL,
    theme VARCHAR(24) NOT NULL DEFAULT 'glass',
    density ENUM('comfortable','compact') NOT NULL DEFAULT 'comfortable',
    font_size ENUM('normal','large') NOT NULL DEFAULT 'normal',
    motion_enabled TINYINT(1) NOT NULL DEFAULT 1,
    sound_enabled TINYINT(1) NOT NULL DEFAULT 1,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    CONSTRAINT fk_user_settings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS message_reactions (
    message_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    reaction VARCHAR(16) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (message_id, user_id, reaction),
    KEY idx_reactions_user (user_id),
    CONSTRAINT fk_reaction_message FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    CONSTRAINT fk_reaction_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS message_reads (
    message_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    read_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (message_id, user_id),
    KEY idx_reads_user_time (user_id, read_at),
    CONSTRAINT fk_reads_message FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    CONSTRAINT fk_reads_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_typing (
    user_id BIGINT UNSIGNED NOT NULL,
    peer_id BIGINT UNSIGNED NOT NULL,
    is_typing TINYINT(1) NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (user_id, peer_id),
    KEY idx_typing_peer (peer_id, updated_at),
    CONSTRAINT fk_typing_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_typing_peer FOREIGN KEY (peer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
