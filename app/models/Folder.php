<?php
/**
 * Enhanced Folder Model
 * Handles folder-related database operations with advanced features
 */

class Folder {
    private $db;
    private static $folderColumns = null;
    private $lastError = null;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    private function getFolderColumns() {
        if (self::$folderColumns !== null) {
            return self::$folderColumns;
        }

        try {
            $cols = $this->db->fetchAll('SHOW COLUMNS FROM folders', []);
            $names = [];
            foreach ($cols as $c) {
                if (!empty($c['Field'])) {
                    $names[$c['Field']] = true;
                }
            }
            self::$folderColumns = $names;
            return self::$folderColumns;
        } catch (Exception $e) {
            // If introspection fails, fall back to the minimal known schema.
            self::$folderColumns = [
                'name' => true,
                'parent_id' => true,
                'created_by' => true,
                'is_archived' => true,
                'created_at' => true,
                'updated_at' => true
            ];
            return self::$folderColumns;
        }
    }

    private function folderColumnExists($name) {
        $cols = $this->getFolderColumns();
        return isset($cols[$name]);
    }

    public function create($data) {
        $this->lastError = null;
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '') {
            $this->lastError = 'Folder name is required';
            return false;
        }

        $parentId = $data['parent_id'] ?? null;
        $createdBy = $data['created_by'] ?? null;
        if (!$createdBy) {
            $this->lastError = 'Missing created_by';
            return false;
        }

        $columns = [];
        $placeholders = [];
        $params = [];

        // Always-supported (base schema)
        $columns[] = 'name';
        $placeholders[] = '?';
        $params[] = $name;

        if ($this->folderColumnExists('parent_id')) {
            $columns[] = 'parent_id';
            $placeholders[] = '?';
            $params[] = $parentId ?: null;
        }

        $columns[] = 'created_by';
        $placeholders[] = '?';
        $params[] = (int)$createdBy;

        // Optional enhanced columns
        if ($this->folderColumnExists('description')) {
            $columns[] = 'description';
            $placeholders[] = '?';
            $params[] = $data['description'] ?? null;
        }

        if ($this->folderColumnExists('color')) {
            $columns[] = 'color';
            $placeholders[] = '?';
            $params[] = $data['color'] ?? '#6B7280';
        }

        if ($this->folderColumnExists('is_system_folder')) {
            $columns[] = 'is_system_folder';
            $placeholders[] = '?';
            $params[] = (int)($data['is_system_folder'] ?? 0);
        }

        // Compute path + level if schema supports it
        $parentPath = null;
        $parentLevel = 0;
        if ($parentId) {
            $metaCols = [];
            if ($this->folderColumnExists('path')) $metaCols[] = 'path';
            if ($this->folderColumnExists('level')) $metaCols[] = 'level';

            if (!empty($metaCols)) {
                $sqlMeta = 'SELECT ' . implode(', ', $metaCols) . ' FROM folders WHERE id = ? LIMIT 1';
                $parent = $this->db->fetch($sqlMeta, [(int)$parentId]);
                if ($parent) {
                    if (isset($parent['path'])) $parentPath = $parent['path'];
                    if (isset($parent['level'])) $parentLevel = (int)$parent['level'];
                }
            }
        }

        if ($this->folderColumnExists('level')) {
            $columns[] = 'level';
            $placeholders[] = '?';
            $params[] = $parentId ? ($parentLevel + 1) : 0;
        }

        if ($this->folderColumnExists('path')) {
            $safeName = str_replace(['\r', '\n'], '', $name);
            $base = $parentPath ? rtrim((string)$parentPath, '/') : '';
            $computed = ($base === '' ? '' : $base) . '/' . $safeName;
            if ($computed === '/') $computed = '/';

            $columns[] = 'path';
            $placeholders[] = '?';
            $params[] = $computed;
        }

        $sql = 'INSERT INTO folders (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';

        try {
            return $this->db->insert($sql, $params);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log('Folder creation error: ' . $e->getMessage());

            // Last-resort fallback: minimal insert (older schemas)
            try {
                return $this->db->insert(
                    'INSERT INTO folders (name, parent_id, created_by) VALUES (?, ?, ?)',
                    [$name, $parentId ?: null, (int)$createdBy]
                );
            } catch (Exception $e2) {
                $this->lastError = $e2->getMessage();
                error_log('Folder creation fallback error: ' . $e2->getMessage());
                return false;
            }
        }
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function findById($id) {
        return $this->findByIdWithArchived($id, false);
    }

    public function findByIdWithArchived($id, $includeArchived = false) {
        $where = $includeArchived ? 'WHERE f.id = ?' : 'WHERE f.id = ? AND f.is_archived = 0';
        $sql = 'SELECT f.*, u.full_name as created_by_name 
                FROM folders f 
                LEFT JOIN users u ON f.created_by = u.id 
                ' . $where . ' 
                LIMIT 1';
        return $this->db->fetch($sql, [$id]);
    }

    public function getSubFolders($parentId = null, $includeArchived = false) {
        $archivedClause = $includeArchived ? '' : ' AND f.is_archived = 0';

        if ($parentId) {
            $sql = 'SELECT f.*, u.full_name as created_by_name,
                    (SELECT COUNT(*) FROM document_files df WHERE df.folder_id = f.id AND df.is_deleted = 0) as file_count
                    ,(SELECT COUNT(*) FROM folders sf WHERE sf.parent_id = f.id AND sf.is_archived = 0) as subfolder_count
                    FROM folders f 
                    LEFT JOIN users u ON f.created_by = u.id
                    WHERE f.parent_id = ?' . $archivedClause . ' 
                    ORDER BY f.is_system_folder DESC, f.name ASC';
            return $this->db->fetchAll($sql, [$parentId]);
        }

        $sql = 'SELECT f.*, u.full_name as created_by_name,
                (SELECT COUNT(*) FROM document_files df WHERE df.folder_id = f.id AND df.is_deleted = 0) as file_count
                ,(SELECT COUNT(*) FROM folders sf WHERE sf.parent_id = f.id AND sf.is_archived = 0) as subfolder_count
                FROM folders f 
                LEFT JOIN users u ON f.created_by = u.id
                WHERE f.parent_id IS NULL' . $archivedClause . ' 
                ORDER BY f.is_system_folder DESC, f.name ASC';
        return $this->db->fetchAll($sql, []);
    }

    public function getAllFolders($includeArchived = false) {
        $whereClause = $includeArchived ? '' : 'WHERE is_archived = 0';
        $sql = "SELECT f.*, u.full_name as created_by_name,
                (SELECT COUNT(*) FROM document_files df WHERE df.folder_id = f.id AND df.is_deleted = 0) as file_count,
                (SELECT COUNT(*) FROM folders sf WHERE sf.parent_id = f.id AND sf.is_archived = 0) as subfolder_count
                FROM folders f 
                LEFT JOIN users u ON f.created_by = u.id
                $whereClause 
                ORDER BY f.is_system_folder DESC, f.path ASC";
        return $this->db->fetchAll($sql, []);
    }

    public function update($id, $data) {
        // If the schema supports path/level, keep them consistent on rename/move.
        $shouldUpdatePath = ($this->folderColumnExists('path') || $this->folderColumnExists('level'))
            && (array_key_exists('name', $data) || array_key_exists('parent_id', $data));

        $oldPath = null;
        if ($shouldUpdatePath && $this->folderColumnExists('path')) {
            $existing = $this->db->fetch('SELECT path FROM folders WHERE id = ? LIMIT 1', [$id]);
            $oldPath = $existing['path'] ?? null;
        }

        if ($shouldUpdatePath) {
            // Determine target parent id / name
            $current = $this->db->fetch('SELECT name, parent_id FROM folders WHERE id = ? LIMIT 1', [$id]);
            if ($current) {
                $newName = array_key_exists('name', $data) ? (string)$data['name'] : (string)$current['name'];
                $newParentId = array_key_exists('parent_id', $data) ? $data['parent_id'] : $current['parent_id'];

                $parentPath = '';
                $parentLevel = 0;
                if (!empty($newParentId)) {
                    $metaCols = [];
                    if ($this->folderColumnExists('path')) $metaCols[] = 'path';
                    if ($this->folderColumnExists('level')) $metaCols[] = 'level';

                    if (!empty($metaCols)) {
                        $parent = $this->db->fetch(
                            'SELECT ' . implode(', ', $metaCols) . ' FROM folders WHERE id = ? LIMIT 1',
                            [(int)$newParentId]
                        );
                        if ($parent) {
                            if (isset($parent['path'])) $parentPath = (string)$parent['path'];
                            if (isset($parent['level'])) $parentLevel = (int)$parent['level'];
                        }
                    }
                }

                if ($this->folderColumnExists('level')) {
                    $data['level'] = !empty($newParentId) ? ($parentLevel + 1) : 0;
                }

                if ($this->folderColumnExists('path')) {
                    $base = $parentPath ? rtrim($parentPath, '/') : '';
                    $data['path'] = ($base === '' ? '' : $base) . '/' . $newName;
                }
            }
        }

        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $id; // Add id at the end for WHERE clause
        
        $fieldsStr = implode(', ', $fields);
        $sql = "UPDATE folders SET $fieldsStr WHERE id = ?";
        
        try {
            $ok = $this->db->execute($sql, $params) > 0;

            // If path changed, update descendants (mimics the previous trigger behavior).
            if ($ok && $shouldUpdatePath && $this->folderColumnExists('path') && $oldPath) {
                $new = $this->db->fetch('SELECT path FROM folders WHERE id = ? LIMIT 1', [$id]);
                $newPath = $new['path'] ?? null;
                if ($newPath && $newPath !== $oldPath) {
                    $this->db->execute(
                        'UPDATE folders SET path = REPLACE(path, ?, ?) WHERE path LIKE CONCAT(?, "%") AND id != ?',
                        [$oldPath, $newPath, $oldPath, $id]
                    );
                }
            }

            return $ok;
        } catch(Exception $e) {
            error_log("Folder update error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        // Soft delete (archive)
        return $this->archive($id, null);
    }

    /**
     * Archive a folder
     */
    public function archive($id, $archivedBy = null) {
        $sets = ['is_archived = 1'];
        $params = [];

        if ($this->folderColumnExists('archived_at')) {
            $sets[] = 'archived_at = NOW()';
        }

        if ($archivedBy && $this->folderColumnExists('archived_by')) {
            $sets[] = 'archived_by = ?';
            $params[] = (int)$archivedBy;
        }

        $params[] = (int)$id;
        $sql = 'UPDATE folders SET ' . implode(', ', $sets) . ' WHERE id = ?';

        try {
            return $this->db->execute($sql, $params) > 0;
        } catch(Exception $e) {
            error_log("Folder archive error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get archived folders (trash)
     */
    public function getTrashed() {
        $select = ['f.*', 'u.full_name as created_by_name'];
        $joins = ['LEFT JOIN users u ON f.created_by = u.id'];

        if ($this->folderColumnExists('archived_by')) {
            $select[] = 'au.full_name as archived_by_name';
            $joins[] = 'LEFT JOIN users au ON f.archived_by = au.id';
        }

        $orderBy = $this->folderColumnExists('archived_at') ? 'f.archived_at DESC' : 'f.updated_at DESC';

        $sql = 'SELECT ' . implode(', ', $select) . '
                FROM folders f
                ' . implode("\n", $joins) . '
                WHERE f.is_archived = 1
                ORDER BY ' . $orderBy;

        return $this->db->fetchAll($sql, []);
    }

    /**
     * Restore archived folder
     */
    public function restore($id) {
        $sets = ['is_archived = 0'];
        if ($this->folderColumnExists('archived_at')) {
            $sets[] = 'archived_at = NULL';
        }
        if ($this->folderColumnExists('archived_by')) {
            $sets[] = 'archived_by = NULL';
        }
        $sql = 'UPDATE folders SET ' . implode(', ', $sets) . ' WHERE id = ?';
        return $this->db->execute($sql, [(int)$id]) > 0;
    }

    /**
     * Permanently delete a folder tree and (optionally) its files.
     * If DocumentFile model is provided, it will hard-delete physical files + DB rows.
     */
    public function permanentDeleteRecursive($folderId, $documentFileModel = null) {
        $folderId = (int)$folderId;
        if ($folderId <= 0) return false;

        // Gather descendant folder IDs (including archived)
        $folderIds = [];
        $stack = [$folderId];
        while (!empty($stack)) {
            $current = array_pop($stack);
            if (isset($folderIds[$current])) continue;
            $folderIds[$current] = true;

            $children = $this->getSubFolders($current, true);
            foreach ($children as $child) {
                $cid = (int)($child['id'] ?? 0);
                if ($cid > 0 && !isset($folderIds[$cid])) {
                    $stack[] = $cid;
                }
            }
        }

        $idList = array_keys($folderIds);
        if (empty($idList)) return false;

        // Delete files in these folders (all, not just deleted) to avoid orphans
        if ($documentFileModel && method_exists($documentFileModel, 'getFilesByFolderIds') && method_exists($documentFileModel, 'permanentDelete')) {
            $files = $documentFileModel->getFilesByFolderIds($idList);
            foreach ($files as $file) {
                $documentFileModel->permanentDelete((int)$file['id']);
            }
        }

        // Delete folders bottom-up (reverse order helps if no cascade)
        $idList = array_reverse($idList);
        foreach ($idList as $fid) {
            $this->db->execute('DELETE FROM folders WHERE id = ?', [(int)$fid]);
        }

        return true;
    }

    /**
     * Create system folders during installation
     */
    public function createSystemFolders($userId = 1) {
        $systemFolders = [
            [
                'name' => 'Patents',
                'description' => 'Patent documents and applications',
                'color' => '#3B82F6',
                'icon' => 'lightbulb',
                'is_system_folder' => 1,
                'path' => '/Patents'
            ],
            [
                'name' => 'Trademarks', 
                'description' => 'Trademark registrations and applications',
                'color' => '#10B981',
                'icon' => 'trademark',
                'is_system_folder' => 1,
                'path' => '/Trademarks'
            ],
            [
                'name' => 'Copyrights',
                'description' => 'Copyright documents and registrations',
                'color' => '#8B5CF6',
                'icon' => 'copyright',
                'is_system_folder' => 1,
                'path' => '/Copyrights'
            ],
            [
                'name' => 'Industrial Designs',
                'description' => 'Industrial design patents and applications',
                'color' => '#F59E0B',
                'icon' => 'palette',
                'is_system_folder' => 1,
                'path' => '/Industrial Designs'
            ],
            [
                'name' => 'Archived',
                'description' => 'Archived intellectual property records',
                'color' => '#6B7280',
                'icon' => 'archive',
                'is_system_folder' => 1,
                'path' => '/Archived'
            ],
            [
                'name' => 'Recent',
                'description' => 'Recently accessed documents',
                'color' => '#6366F1',
                'icon' => 'clock',
                'is_system_folder' => 1,
                'path' => '/Recent'
            ]
        ];

        foreach ($systemFolders as $folder) {
            $sql = 'INSERT INTO folders (name, description, color, icon, is_system_folder, path, created_by, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())';
            
            try {
                $this->db->execute($sql, [
                    $folder['name'],
                    $folder['description'],
                    $folder['color'],
                    $folder['icon'],
                    $folder['is_system_folder'],
                    $folder['path'],
                    $userId
                ]);
            } catch (Exception $e) {
                error_log("Error creating system folder {$folder['name']}: " . $e->getMessage());
            }
        }
        
        return true;
    }
    
    public function getTree($parentId = null, $level = 0) {
        $folders = $this->getSubFolders($parentId);
        $tree = [];
        foreach ($folders as $folder) {
            $folder['level'] = $level;
            $folder['children'] = $this->getTree($folder['id'], $level + 1);
            $tree[] = $folder;
        }
        return $tree;
    }

    public function getFolderPath($folderId) {
        $sql = 'SELECT path FROM folders WHERE id = ? AND is_archived = 0';
        $result = $this->db->fetch($sql, [$folderId]);
        return $result ? $result['path'] : null;
    }

    public function getFolderBreadcrumbs($folderId) {
        if (!$folderId) return [];
        
        $sql = 'SELECT id, name, parent_id FROM folders WHERE id = ? AND is_archived = 0';
        $folder = $this->db->fetch($sql, [$folderId]);
        
        if (!$folder) return [];
        
        $breadcrumbs = [$folder];
        
        // Get parent folders recursively
        while ($folder['parent_id']) {
            $sql = 'SELECT id, name, parent_id FROM folders WHERE id = ? AND is_archived = 0';
            $folder = $this->db->fetch($sql, [$folder['parent_id']]);
            if ($folder) {
                array_unshift($breadcrumbs, $folder);
            } else {
                break;
            }
        }
        
        return $breadcrumbs;
    }

    public function getFolderStatistics($folderId = null) {
        $whereClause = '';
        $params = [];
        
        if ($folderId !== null) {
            $whereClause = 'WHERE df.folder_id = ?';
            $params[] = $folderId;
        }
        
        $sql = "SELECT 
                    COUNT(df.id) as total_files,
                    SUM(df.file_size) as total_size,
                    COUNT(CASE WHEN df.is_image = 1 THEN 1 END) as image_count,
                    COUNT(CASE WHEN df.is_document = 1 THEN 1 END) as document_count,
                    COUNT(CASE WHEN df.is_video = 1 THEN 1 END) as video_count,
                    COUNT(CASE WHEN df.is_audio = 1 THEN 1 END) as audio_count
                FROM document_files df 
                $whereClause AND df.is_deleted = 0";
                
        $stats = $this->db->fetch($sql, $params);
        
        // Get subfolder count if specific folder
        if ($folderId !== null) {
            $subfolderSql = 'SELECT COUNT(*) as subfolder_count FROM folders WHERE parent_id = ? AND is_archived = 0';
            $subfolderResult = $this->db->fetch($subfolderSql, [$folderId]);
            $stats['subfolder_count'] = $subfolderResult['subfolder_count'] ?? 0;
        }
        
        return $stats;
    }

    public function searchFolders($query) {
        $searchTerm = '%' . $query . '%';
        $sql = 'SELECT f.*, u.full_name as created_by_name,
                (SELECT COUNT(*) FROM document_files df WHERE df.folder_id = f.id AND df.is_deleted = 0) as file_count
                FROM folders f 
                LEFT JOIN users u ON f.created_by = u.id
                WHERE (f.name LIKE ? OR f.description LIKE ?) AND f.is_archived = 0
                ORDER BY f.name ASC';
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm]);
    }

    public function moveFolder($folderId, $newParentId, $userId) {
        try {
            // Prevent moving folder into its own subfolder
            if ($this->isDescendant($folderId, $newParentId)) {
                return false;
            }
            
            $sql = 'UPDATE folders SET parent_id = ? WHERE id = ? AND created_by = ?';
            return $this->db->execute($sql, [$newParentId, $folderId, $userId]) > 0;
        } catch (Exception $e) {
            error_log("Folder move error: " . $e->getMessage());
            return false;
        }
    }

    private function isDescendant($ancestorId, $descendantId) {
        if (!$descendantId) return false;
        
        $sql = 'SELECT parent_id FROM folders WHERE id = ?';
        $folder = $this->db->fetch($sql, [$descendantId]);
        
        if (!$folder) return false;
        if ($folder['parent_id'] == $ancestorId) return true;
        
        return $this->isDescendant($ancestorId, $folder['parent_id']);
    }

    public function getFoldersByType() {
        $sql = 'SELECT 
                    SUM(CASE WHEN f.name = "Patents" THEN stats.file_count ELSE 0 END) as patent_count,
                    SUM(CASE WHEN f.name = "Trademarks" THEN stats.file_count ELSE 0 END) as trademark_count,
                    SUM(CASE WHEN f.name = "Copyrights" THEN stats.file_count ELSE 0 END) as copyright_count,
                    SUM(CASE WHEN f.name = "Industrial Designs" THEN stats.file_count ELSE 0 END) as design_count,
                    SUM(CASE WHEN f.name = "Archived" THEN stats.file_count ELSE 0 END) as archived_count
                FROM folders f
                LEFT JOIN (
                    SELECT folder_id, COUNT(*) as file_count 
                    FROM document_files 
                    WHERE is_deleted = 0 
                    GROUP BY folder_id
                ) stats ON f.id = stats.folder_id
                WHERE f.is_system_folder = 1 AND f.is_archived = 0';
                
        return $this->db->fetch($sql, []);
    }

    public function canUserAccess($folderId, $userId, $permission = 'read') {
        // Admin can access everything
        $userSql = 'SELECT role FROM users WHERE id = ?';
        $user = $this->db->fetch($userSql, [$userId]);
        if ($user && $user['role'] === 'admin') {
            return true;
        }

        // Check if user created the folder
        $folderSql = 'SELECT created_by FROM folders WHERE id = ?';
        $folder = $this->db->fetch($folderSql, [$folderId]);
        if ($folder && $folder['created_by'] == $userId) {
            return true;
        }

        // Check folder permissions
        $permissionSql = 'SELECT permission_type FROM folder_permissions WHERE folder_id = ? AND user_id = ?';
        $userPermission = $this->db->fetch($permissionSql, [$folderId, $userId]);
        
        if (!$userPermission) {
            return false; // No permission found
        }

        // Check permission level
        $permissionLevels = ['read' => 1, 'write' => 2, 'admin' => 3];
        $requiredLevel = $permissionLevels[$permission] ?? 1;
        $userLevel = $permissionLevels[$userPermission['permission_type']] ?? 0;
        
        return $userLevel >= $requiredLevel;
    }
}
