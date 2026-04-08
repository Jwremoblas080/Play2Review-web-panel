-- Migration: Add category column to quizes table
-- Date: 2026-03-02
-- Purpose: Enable subject-based category tracking for DepEd-aligned quiz management

-- Add category column if it doesn't exist
ALTER TABLE `quizes` 
ADD COLUMN IF NOT EXISTS `category` VARCHAR(255) NULL AFTER `subject_name`;

-- Add index for better query performance
ALTER TABLE `quizes` 
ADD INDEX IF NOT EXISTS `idx_category` (`category`);

-- Add composite index for subject + category queries
ALTER TABLE `quizes` 
ADD INDEX IF NOT EXISTS `idx_subject_category` (`subject_name`, `category`);

-- Update existing records to have NULL category (will be filled manually or via admin panel)
UPDATE `quizes` SET `category` = NULL WHERE `category` IS NULL OR `category` = '';

-- Verify the changes
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT
FROM 
    INFORMATION_SCHEMA.COLUMNS
WHERE 
    TABLE_SCHEMA = 'play2review_db' 
    AND TABLE_NAME = 'quizes' 
    AND COLUMN_NAME = 'category';
