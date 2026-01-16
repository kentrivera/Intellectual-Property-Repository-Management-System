-- Add folder trash metadata columns (optional but recommended)
-- This matches app/models/Folder.php which may use archived_at / archived_by.

ALTER TABLE folders
  ADD COLUMN archived_at TIMESTAMP NULL AFTER is_archived,
  ADD COLUMN archived_by INT NULL AFTER archived_at;

-- Foreign key is optional; only add if you want strict integrity.
-- If this fails due to existing constraints or different engine/settings, you can skip it.
ALTER TABLE folders
  ADD CONSTRAINT fk_folders_archived_by
  FOREIGN KEY (archived_by) REFERENCES users(id)
  ON DELETE SET NULL ON UPDATE CASCADE;
