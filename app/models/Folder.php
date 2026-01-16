<?php
/**
 * Enhanced Folder Model
 * Handles folder-related database operations with advanced features
 */

class Folder {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $sql = 'INSERT INTO folders (name, parent_id, created_by, description, color) VALUES (?, ?, ?, ?, ?)';
        $params = [
            $data['name'],
            $data['parent_id'] ?? null,
            $data['created_by'],
            $data['description'] ?? null,
            $data['color'] ?? '#6B7280'
        ];
        
        try {
            return $this->db->insert($sql, $params);
        } catch(Exception $e) {
            error_log("Folder creation error: " . $e->getMessage());
            return false;
        }
    }

    public function findById($id) {
        $sql = 'SELECT f.*, u.full_name as created_by_name 
                FROM folders f 
                LEFT JOIN users u ON f.created_by = u.id 
                WHERE f.id = ? AND f.is_archived = 0 
                LIMIT 1';
        return $this->db->fetch($sql, [$id]);
    }

    public function getSubFolders($parentId = null) {
        if ($parentId) {
            $sql = 'SELECT f.*, u.full_name as created_by_name,
                    (SELECT COUNT(*) FROM document_files df WHERE df.folder_id = f.id AND df.is_deleted = 0) as file_count
                    FROM folders f 
                    LEFT JOIN users u ON f.created_by = u.id
                    WHERE f.parent_id = ? AND f.is_archived = 0 
                    ORDER BY f.is_system_folder DESC, f.name ASC';
            return $this->db->fetchAll($sql, [$parentId]);
        } else {
            $sql = 'SELECT f.*, u.full_name as created_by_name,
                    (SELECT COUNT(*) FROM document_files df WHERE df.folder_id = f.id AND df.is_deleted = 0) as file_count
                    FROM folders f 
                    LEFT JOIN users u ON f.created_by = u.id
                    WHERE f.parent_id IS NULL AND f.is_archived = 0 
                    ORDER BY f.is_system_folder DESC, f.name ASC';
            return $this->db->fetchAll($sql, []);
        }
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
            return $this->db->execute($sql, $params) > 0;
        } catch(Exception $e) {
            error_log("Folder update error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        // Soft delete (archive)
        $sql = 'UPDATE folders SET is_archived = 1, archived_at = NOW() WHERE id = ?';
        try {
            return $this->db->execute($sql, [$id]) > 0;
        } catch(Exception $e) {
            error_log("Folder delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Archive a folder
     */
    public function archive($id, $archivedBy = null) {
        $sql = 'UPDATE folders SET is_archived = 1, archived_at = NOW()';
        $params = [$id];
        
        if ($archivedBy) {
            $sql .= ', archived_by = ? WHERE id = ?';
            $params = [$archivedBy, $id];
        } else {
            $sql .= ' WHERE id = ?';
        }
        
        try {
            return $this->db->execute($sql, $params) > 0;
        } catch(Exception $e) {
            error_log("Folder archive error: " . $e->getMessage());
            return false;
        }
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
