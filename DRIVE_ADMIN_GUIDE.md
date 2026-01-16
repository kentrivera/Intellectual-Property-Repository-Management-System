# Google Drive-Style Admin IP Records Page

The admin/ip-records.php has been updated with a Google Drive folder-based interface.

## Key Features:

### 1. **Breadcrumb Navigation**
- Shows current location: IP Repository > All Records
- Click "IP Repository" to return to root folder view

### 2. **Clean Toolbar**
- Google-style search bar (gray → white on hover)
- "New" button to create records
- Grid/List view toggle icons
- Sort, Filter, and Export buttons

### 3. **Folder Structure**
Six main folders organized by type:
- **Patents** (blue folder)
- **Trademarks** (green folder)  
- **Copyrights** (purple folder)
- **Industrial Designs** (orange folder)
- **Archived** (gray folder)
- **Recent** (indigo folder with clock icon)

Each folder shows item count from database stats.

### 4. **File Cards (Grid View)**
- Compact 6-column layout
- File icon with type badge in corner
- Hover shows three-dot menu
- Clean white cards with borders
- Filename, date, and status badge

### 5. **List View (Table)**
- Checkbox in first column for bulk actions
- File icon + name column
- Owner, Modified, Status columns
- Action buttons (Edit + More menu)
- Row hover highlighting

### 6. **Context Menus**
All accessible via three-dot buttons:

**Folder Menu:**
- Open
- Share
- Details

**File Menu:**
- View Details
- Edit Record
- Archive
- Share
- Copy Link
- File Information

**Sort Menu:**
- Name
- Last modified  
- Owner
- Type

**Filter Menu:**
- Type dropdown
- Status dropdown
- Apply button

### 7. **Empty States**
- Dashed border container
- Large folder icon
- "This folder is empty" message
- "Create New Record" button

## JavaScript Functions:

```javascript
toggleView(view)          // Switch between grid/list
navigateToFolder(folder)  // Go to root/back
openFolder(folderType)    // Open specific folder
showFolderMenu(event, id) // Show folder context menu
showFileMenu(event, id)   // Show file context menu
showSortMenu()            // Show sort options
showFilterMenu()          // Show filter options
createRecord()            // Create new IP record
viewRecord(id)            // View record details
editRecord(id)            // Edit record
archiveRecord(id)         // Archive record
exportRecords()           // Export data
```

## Admin vs Staff Differences:

**Admin** has:
- Blue accent color (#2563eb)
- "New" button to create records
- Edit and Archive actions
- Export functionality
- Archived folder

**Staff** has:
- Indigo accent color (#6366f1)
- Read-only access
- "Request Download" action instead of Edit
- Shared with Me folder instead of Archived

## Implementation Notes:

The page uses the existing admin sidebar and header components. All file operations should connect to your existing backend controllers for IP records management.

Stats are pulled from `$stats` array with keys:
- `patent_count`
- `trademark_count`  
- `copyright_count`
- `design_count`
