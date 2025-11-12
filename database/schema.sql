-- Database schema for MLBB Academy
CREATE TABLE IF NOT EXISTS hero_roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(50) NOT NULL,
    name_fa VARCHAR(50) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS heroes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(100) NOT NULL,
    name_fa VARCHAR(100) NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    specialty VARCHAR(100) NULL,
    hp INT UNSIGNED NULL,
    hp_regen DECIMAL(10,2) NULL,
    mana INT UNSIGNED NULL,
    mana_regen DECIMAL(10,2) NULL,
    physical_attack INT NULL,
    magic_power INT NULL,
    physical_defense INT NULL,
    magic_defense INT NULL,
    attack_speed DECIMAL(10,2) NULL,
    movement_speed INT NULL,
    difficulty ENUM('Easy','Medium','Hard','Expert') DEFAULT 'Medium',
    description TEXT NULL,
    tips TEXT NULL,
    image_url VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_heroes_roles FOREIGN KEY (role_id) REFERENCES hero_roles(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hero_skills (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_id INT UNSIGNED NOT NULL,
    skill_type ENUM('Passive','Skill1','Skill2','Skill3','Ultimate') NOT NULL,
    name_en VARCHAR(150) NOT NULL,
    name_fa VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    cooldown DECIMAL(10,2) NULL,
    mana_cost DECIMAL(10,2) NULL,
    damage_type ENUM('Physical','Magic','True','Hybrid') NULL,
    tips TEXT NULL,
    image_url VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_skills_hero FOREIGN KEY (hero_id) REFERENCES heroes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hero_combos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_id INT UNSIGNED NOT NULL,
    combo_name VARCHAR(150) NOT NULL,
    combo_name_fa VARCHAR(150) NOT NULL,
    combo_steps TEXT NOT NULL,
    combo_description TEXT NOT NULL,
    combo_description_fa TEXT NOT NULL,
    difficulty ENUM('Easy','Medium','Hard','Expert') DEFAULT 'Medium',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_combos_hero FOREIGN KEY (hero_id) REFERENCES heroes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hero_combo_steps (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    combo_id INT UNSIGNED NOT NULL,
    skill_id INT UNSIGNED NOT NULL,
    custom_label VARCHAR(255) NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_combo_steps_combo FOREIGN KEY (combo_id) REFERENCES hero_combos(id) ON DELETE CASCADE,
    CONSTRAINT fk_combo_steps_skill FOREIGN KEY (skill_id) REFERENCES hero_skills(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS item_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(60) NOT NULL,
    name_en VARCHAR(120) NOT NULL,
    name_fa VARCHAR(120) NOT NULL,
    description_en TEXT NULL,
    description_fa TEXT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_item_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    slug VARCHAR(120) NOT NULL,
    name_en VARCHAR(150) NOT NULL,
    name_fa VARCHAR(150) NOT NULL,
    tier VARCHAR(50) NULL,
    gold_cost INT UNSIGNED NULL,
    stats_en TEXT NULL,
    stats_fa TEXT NULL,
    passive_en TEXT NULL,
    passive_fa TEXT NULL,
    active_en TEXT NULL,
    active_fa TEXT NULL,
    description_en TEXT NULL,
    description_fa TEXT NULL,
    image_url VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_items_category FOREIGN KEY (category_id) REFERENCES item_categories(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY uq_items_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hero_tips (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_id INT UNSIGNED NOT NULL,
    tip_type ENUM('General','Combat','Strategy','Advanced') DEFAULT 'General',
    tip_text TEXT NOT NULL,
    tip_text_en TEXT NULL,
    priority TINYINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_tips_hero FOREIGN KEY (hero_id) REFERENCES heroes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hero_counter_groups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_id INT UNSIGNED NOT NULL,
    group_type ENUM('hero','item') NOT NULL DEFAULT 'hero',
    title_en VARCHAR(150) NOT NULL,
    title_fa VARCHAR(150) NOT NULL,
    description_en TEXT NULL,
    description_fa TEXT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_counter_groups_hero FOREIGN KEY (hero_id) REFERENCES heroes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hero_counter_entities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id INT UNSIGNED NOT NULL,
    target_hero_id INT UNSIGNED NULL,
    target_item_id INT UNSIGNED NULL,
    name_en VARCHAR(150) NULL,
    name_fa VARCHAR(150) NULL,
    image_url VARCHAR(255) NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_counter_entities_group FOREIGN KEY (group_id) REFERENCES hero_counter_groups(id) ON DELETE CASCADE,
    CONSTRAINT fk_counter_entities_target_hero FOREIGN KEY (target_hero_id) REFERENCES heroes(id) ON DELETE SET NULL,
    CONSTRAINT fk_counter_entities_target_item FOREIGN KEY (target_item_id) REFERENCES items(id) ON DELETE SET NULL,
    KEY idx_counter_entities_target_hero (target_hero_id),
    KEY idx_counter_entities_target_item (target_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hero_strengths (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_id INT UNSIGNED NOT NULL,
    strength_text TEXT NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_strengths_hero FOREIGN KEY (hero_id) REFERENCES heroes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hero_weaknesses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_id INT UNSIGNED NOT NULL,
    weakness_text TEXT NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_weaknesses_hero FOREIGN KEY (hero_id) REFERENCES heroes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
