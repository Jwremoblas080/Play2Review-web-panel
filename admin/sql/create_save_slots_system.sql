-- =====================================================
-- SAVE SLOTS SYSTEM FOR PLAY2REVIEW
-- =====================================================
-- This creates a multiple save slot system where players
-- can manually save and load their game progress
-- =====================================================

-- 1. CREATE SAVE SLOTS TABLE
CREATE TABLE IF NOT EXISTS `save_slots` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `slot_number` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1-5 save slots per user',
  `slot_name` VARCHAR(100) DEFAULT NULL COMMENT 'Custom name for the save slot',
  `is_active` TINYINT(1) DEFAULT 0 COMMENT 'Currently loaded slot',
  
  -- Game Progress Data
  `lives` INT(11) DEFAULT 3,
  `feathers` INT(11) DEFAULT 0,
  `potion` INT(11) DEFAULT 0,
  `selected_character` VARCHAR(50) DEFAULT 'Akio',
  `volume` FLOAT DEFAULT 1.0,
  
  -- English Category Levels
  `english_grammar_level` INT(11) DEFAULT 0,
  `english_vocabulary_level` INT(11) DEFAULT 0,
  `english_reading_level` INT(11) DEFAULT 0,
  `english_literature_level` INT(11) DEFAULT 0,
  `english_writing_level` INT(11) DEFAULT 0,
  
  -- Math Category Levels
  `math_algebra_level` INT(11) DEFAULT 0,
  `math_geometry_level` INT(11) DEFAULT 0,
  `math_statistics_level` INT(11) DEFAULT 0,
  `math_probability_level` INT(11) DEFAULT 0,
  `math_functions_level` INT(11) DEFAULT 0,
  `math_wordproblems_level` INT(11) DEFAULT 0,
  
  -- Science Category Levels
  `science_biology_level` INT(11) DEFAULT 0,
  `science_chemistry_level` INT(11) DEFAULT 0,
  `science_physics_level` INT(11) DEFAULT 0,
  `science_earthscience_level` INT(11) DEFAULT 0,
  `science_investigation_level` INT(11) DEFAULT 0,
  
  -- Filipino Category Levels
  `filipino_gramatika_level` INT(11) DEFAULT 0,
  `filipino_panitikan_level` INT(11) DEFAULT 0,
  `filipino_paguunawa_level` INT(11) DEFAULT 0,
  `filipino_talasalitaan_level` INT(11) DEFAULT 0,
  `filipino_wika_level` INT(11) DEFAULT 0,
  
  -- AP (Araling Panlipunan) Category Levels
  `ap_ekonomiks_level` INT(11) DEFAULT 0,
  `ap_kasaysayan_level` INT(11) DEFAULT 0,
  `ap_kontemporaryo_level` INT(11) DEFAULT 0,
  `ap_heograpiya_level` INT(11) DEFAULT 0,
  `ap_pamahalaan_level` INT(11) DEFAULT 0,
  
  -- Metadata
  `last_subject_played` VARCHAR(50) DEFAULT NULL,
  `last_category_played` VARCHAR(100) DEFAULT NULL,
  `total_play_time_minutes` INT(11) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_slot` (`user_id`, `slot_number`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_active_slot` (`user_id`, `is_active`),
  CONSTRAINT `fk_save_slots_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. CREATE SAVE SLOT ACTIVITY LOG
CREATE TABLE IF NOT EXISTS `save_slot_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `save_slot_id` INT(11) NOT NULL,
  `action_type` ENUM('CREATE', 'SAVE', 'LOAD', 'DELETE', 'RENAME') NOT NULL,
  `action_description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_save_slot_id` (`save_slot_id`),
  CONSTRAINT `fk_slot_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_slot_logs_slot` FOREIGN KEY (`save_slot_id`) REFERENCES `save_slots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. ADD COLUMN TO USERS TABLE TO TRACK CURRENT ACTIVE SLOT
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `current_save_slot_id` INT(11) DEFAULT NULL COMMENT 'Currently active save slot',
ADD KEY `idx_current_slot` (`current_save_slot_id`);

-- 4. CREATE TRIGGER TO ENSURE ONLY ONE ACTIVE SLOT PER USER
DELIMITER $$

CREATE TRIGGER `before_save_slot_activate` BEFORE UPDATE ON `save_slots`
FOR EACH ROW
BEGIN
  IF NEW.is_active = 1 AND OLD.is_active = 0 THEN
    -- Deactivate all other slots for this user
    UPDATE save_slots 
    SET is_active = 0 
    WHERE user_id = NEW.user_id AND id != NEW.id;
  END IF;
END$$

DELIMITER ;

-- 5. CREATE STORED PROCEDURE TO CREATE DEFAULT SAVE SLOT FOR NEW USERS
DELIMITER $$

CREATE PROCEDURE `create_default_save_slot`(IN p_user_id INT)
BEGIN
  -- Check if user already has a save slot
  IF NOT EXISTS (SELECT 1 FROM save_slots WHERE user_id = p_user_id) THEN
    -- Create default slot 1
    INSERT INTO save_slots (
      user_id, slot_number, slot_name, is_active,
      lives, feathers, potion, selected_character, volume
    ) VALUES (
      p_user_id, 1, 'Main Save', 1,
      3, 0, 0, 'Akio', 1.0
    );
    
    -- Update user's current_save_slot_id
    UPDATE users 
    SET current_save_slot_id = LAST_INSERT_ID() 
    WHERE id = p_user_id;
  END IF;
END$$

DELIMITER ;

-- 6. MIGRATE EXISTING USER DATA TO SAVE SLOTS
-- This will create a default save slot for each existing user with their current progress
INSERT INTO save_slots (
  user_id, slot_number, slot_name, is_active,
  lives, feathers, potion, selected_character, volume,
  english_grammar_level, english_vocabulary_level, english_reading_level, english_literature_level, english_writing_level,
  math_algebra_level, math_geometry_level, math_statistics_level, math_probability_level, math_functions_level, math_wordproblems_level,
  science_biology_level, science_chemistry_level, science_physics_level, science_earthscience_level, science_investigation_level,
  filipino_gramatika_level, filipino_panitikan_level, filipino_paguunawa_level, filipino_talasalitaan_level, filipino_wika_level,
  ap_ekonomiks_level, ap_kasaysayan_level, ap_kontemporaryo_level, ap_heograpiya_level, ap_pamahalaan_level
)
SELECT 
  id as user_id, 1 as slot_number, 'Main Save' as slot_name, 1 as is_active,
  COALESCE(lives, 3), COALESCE(feathers, 0), COALESCE(potion, 0), 
  COALESCE(selected_character, 'Akio'), COALESCE(volume, 1.0),
  COALESCE(english_grammar_level, 0), COALESCE(english_vocabulary_level, 0), 
  COALESCE(english_reading_level, 0), COALESCE(english_literature_level, 0), 
  COALESCE(english_writing_level, 0),
  COALESCE(math_algebra_level, 0), COALESCE(math_geometry_level, 0), 
  COALESCE(math_statistics_level, 0), COALESCE(math_probability_level, 0), 
  COALESCE(math_functions_level, 0), COALESCE(math_wordproblems_level, 0),
  COALESCE(science_biology_level, 0), COALESCE(science_chemistry_level, 0), 
  COALESCE(science_physics_level, 0), COALESCE(science_earthscience_level, 0), 
  COALESCE(science_investigation_level, 0),
  COALESCE(filipino_gramatika_level, 0), COALESCE(filipino_panitikan_level, 0), 
  COALESCE(filipino_paguunawa_level, 0), COALESCE(filipino_talasalitaan_level, 0), 
  COALESCE(filipino_wika_level, 0),
  COALESCE(ap_ekonomiks_level, 0), COALESCE(ap_kasaysayan_level, 0), 
  COALESCE(ap_kontemporaryo_level, 0), COALESCE(ap_heograpiya_level, 0), 
  COALESCE(ap_pamahalaan_level, 0)
FROM users
WHERE NOT EXISTS (SELECT 1 FROM save_slots WHERE save_slots.user_id = users.id);

-- Update users table with their default save slot ID
UPDATE users u
INNER JOIN save_slots s ON u.id = s.user_id AND s.slot_number = 1
SET u.current_save_slot_id = s.id
WHERE u.current_save_slot_id IS NULL;

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

-- Check save slots created
-- SELECT u.username, s.slot_number, s.slot_name, s.is_active, s.created_at
-- FROM users u
-- LEFT JOIN save_slots s ON u.id = s.user_id
-- ORDER BY u.id, s.slot_number;

-- Check active slots per user
-- SELECT user_id, COUNT(*) as active_slots
-- FROM save_slots
-- WHERE is_active = 1
-- GROUP BY user_id
-- HAVING active_slots > 1;
