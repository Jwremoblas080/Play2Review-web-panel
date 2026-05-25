-- Add archive feature columns to educators table
-- Run this SQL script to enable the archive functionality

ALTER TABLE educators 
ADD COLUMN IF NOT EXISTS is_archived TINYINT(1) DEFAULT 0 COMMENT 'Soft delete flag: 0=active, 1=archived',
ADD COLUMN IF NOT EXISTS archived_at DATETIME NULL COMMENT 'Timestamp when archived',
ADD COLUMN IF NOT EXISTS archived_by INT NULL COMMENT 'Admin ID who archived this record';

-- Add index for better query performance
CREATE INDEX IF NOT EXISTS idx_is_archived ON educators(is_archived);
CREATE INDEX IF NOT EXISTS idx_archived_at ON educators(archived_at);

-- Update existing records to ensure they are not archived
UPDATE educators SET is_archived = 0 WHERE is_archived IS NULL;
