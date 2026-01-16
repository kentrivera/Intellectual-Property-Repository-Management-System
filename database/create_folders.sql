-- Insert system folders
INSERT IGNORE INTO folders (name, parent_id, created_by, is_system_folder, color, description, path, level) VALUES
('Patents', NULL, 1, 1, '#3B82F6', 'Patent documents and related files', '/Patents', 0),
('Trademarks', NULL, 1, 1, '#10B981', 'Trademark registrations and applications', '/Trademarks', 0),
('Copyrights', NULL, 1, 1, '#8B5CF6', 'Copyright protected materials', '/Copyrights', 0),
('Industrial Designs', NULL, 1, 1, '#F59E0B', 'Industrial design registrations', '/Industrial Designs', 0),
('Archived', NULL, 1, 1, '#6B7280', 'Archived documents', '/Archived', 0),
('Recent', NULL, 1, 1, '#6366F1', 'Recently added documents', '/Recent', 0);