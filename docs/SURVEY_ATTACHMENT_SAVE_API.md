# Survey Attachment Save API Documentation

## Overview
This API endpoint allows you to upload or update survey attachments (files). The same endpoint handles both operations - if an `id` is provided, it updates the existing attachment; otherwise, it creates a new one. Files are stored in the public storage directory and accessible via URL.

---

## Endpoint Details

**Method:** `POST`  
**URL:** `/api/survey/attachment/save`  
**Authentication:** Required (Bearer Token via Laravel Sanctum)

---

## Headers

```
Authorization: Bearer <your-auth-token>
Content-Type: multipart/form-data
Accept: application/json
```

---

## Request Body

### Creating a New Attachment

When creating an attachment, **do not** include the `id` field:

**Form Data:**
- `file` (required): The file to upload (max 10MB)
  - Supported types: Any file type
  - Max size: 10MB (10240 KB)

**Example using cURL:**
```bash
curl -X POST http://localhost:8000/api/survey/attachment/save \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/document.pdf"
```

**Example using Postman:**
- Method: POST
- URL: `http://localhost:8000/api/survey/attachment/save`
- Headers:
  - `Authorization: Bearer YOUR_TOKEN`
- Body: form-data
  - Key: `file` (Type: File)
  - Value: Select your file

### Updating an Existing Attachment

When updating an attachment, **include** the `id` field:

**Form Data:**
- `id` (required): The attachment ID to update
- `file` (optional): New file to replace the existing one
  - If not provided, only metadata will be updated (if applicable)
  - If provided, the old file will be deleted and replaced with the new one

**Example using Postman:**
- Method: POST
- URL: `http://localhost:8000/api/survey/attachment/save`
- Headers:
  - `Authorization: Bearer YOUR_TOKEN`
- Body: form-data
  - Key: `id` (Type: Text)
  - Value: `1`
  - Key: `file` (Type: File, Optional)
  - Value: Select new file (optional)

---

## Field Specifications

| Field | Type | Required | Description | Constraints |
|-------|------|----------|-------------|-------------|
| `id` | integer | No | Attachment ID (only for updates) | Must exist in survey_attachments table |
| `file` | file | Yes (create) / No (update) | The file to upload | Max 10MB, any file type |

---

## Response Format

### Success Response (201 - Created)

```json
{
  "id": 1,
  "name": "cloths-shirt.jpeg",
  "size": 8092,
  "type": "image/jpeg",
  "url": "http://localhost:8000/storage/attachments/WSxMcuQuva4zv85y4Ou1vmkhnztEzvLWCbeHaLtt.jpg"
}
```

### Success Response (200 - Updated)

```json
{
  "id": 1,
  "name": "updated-document.pdf",
  "size": 2048000,
  "type": "application/pdf",
  "url": "http://localhost:8000/storage/attachments/new-file-path.pdf"
}
```

### Error Response (422 - Validation Failed)

```json
{
  "message": "The file field is required.",
  "errors": {
    "file": [
      "The file field is required."
    ]
  }
}
```

### Error Response (404 - Not Found)

```json
{
  "message": "Attachment not found or you do not have permission to update it."
}
```

### Error Response (500 - Server Error)

```json
{
  "message": "Error saving attachment: <error details>"
}
```

---

## Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Unique attachment identifier |
| `name` | string | Original file name |
| `size` | integer | File size in bytes |
| `type` | string | MIME type of the file (e.g., "image/jpeg", "application/pdf") |
| `url` | string | Public URL to access the file |

---

## Important Notes

1. **File Storage:**
   - Files are stored in `storage/app/public/attachments/`
   - Public URL is generated automatically
   - Old files are automatically deleted when updating

2. **File Size Limit:**
   - Maximum file size: 10MB (10240 KB)
   - Larger files will be rejected with validation error

3. **File Types:**
   - All file types are supported
   - MIME type is automatically detected

4. **Access Control:**
   - Users can only upload/update their own attachments
   - Attachments are linked to the user's organization

5. **Update Behavior:**
   - If `id` is provided and file is not provided: No changes (if no other fields to update)
   - If `id` is provided and file is provided: Old file is deleted, new file is uploaded

---

## Example Use Cases

### 1. Upload a New Image

**Request:**
```
POST /api/survey/attachment/save
Content-Type: multipart/form-data

file: [image.jpg]
```

**Response:**
```json
{
  "id": 1,
  "name": "image.jpg",
  "size": 245760,
  "type": "image/jpeg",
  "url": "http://localhost:8000/storage/attachments/abc123.jpg"
}
```

### 2. Upload a PDF Document

**Request:**
```
POST /api/survey/upload-attachment
Content-Type: multipart/form-data

file: [document.pdf]
```

**Response:**
```json
{
  "id": 2,
  "name": "document.pdf",
  "size": 1024000,
  "type": "application/pdf",
  "url": "http://localhost:8000/storage/attachments/xyz789.pdf"
}
```

### 3. Update Existing Attachment with New File

**Request:**
```
POST /api/survey/upload-attachment
Content-Type: multipart/form-data

id: 1
file: [new-image.jpg]
```

**Response:**
```json
{
  "id": 1,
  "name": "new-image.jpg",
  "size": 512000,
  "type": "image/jpeg",
  "url": "http://localhost:8000/storage/attachments/new-abc123.jpg"
}
```

---

## Error Handling

### Common Errors

1. **File Required (422)**
   - **Cause:** Creating attachment without file
   - **Solution:** Include `file` field in request

2. **File Too Large (422)**
   - **Cause:** File size exceeds 10MB
   - **Solution:** Compress or reduce file size

3. **Invalid File (422)**
   - **Cause:** Invalid file format or corrupted file
   - **Solution:** Ensure file is valid and not corrupted

4. **Attachment Not Found (404)**
   - **Cause:** Trying to update non-existent attachment or attachment belongs to another user
   - **Solution:** Check attachment ID and ensure you have permission

5. **Organization Not Found (404)**
   - **Cause:** User doesn't have an organization
   - **Solution:** Create an organization first

---

## Testing with Postman

1. **Create New Attachment:**
   - Method: `POST`
   - URL: `http://localhost:8000/api/survey/attachment/save`
   - Authorization: Bearer Token
   - Body → form-data:
     - Key: `file` (Type: File)
     - Value: Select file

2. **Update Existing Attachment:**
   - Method: `POST`
   - URL: `http://localhost:8000/api/survey/attachment/save`
   - Authorization: Bearer Token
   - Body → form-data:
     - Key: `id` (Type: Text)
     - Value: `1`
     - Key: `file` (Type: File, Optional)
     - Value: Select new file (optional)

---

## Related APIs

- `POST /api/survey/attachment/index` - List all attachments
- `POST /api/survey/attachment/show` - Get single attachment
- `POST /api/survey/attachment/delete` - Delete attachment

