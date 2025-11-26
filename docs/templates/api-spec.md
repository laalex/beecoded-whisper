# API: [Resource Name]

**Base Path**: `/api/[resource]`
**Version**: v1

## Endpoints

### List [Resources]
```
GET /api/[resources]
```

**Query Parameters**:
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| page | int | 1 | Page number |
| limit | int | 20 | Items per page |

**Response**: `200 OK`
```json
{
  "data": [],
  "meta": {
    "current_page": 1,
    "total": 100
  }
}
```

### Get [Resource]
```
GET /api/[resources]/{id}
```

**Response**: `200 OK`
```json
{
  "data": {
    "id": "uuid",
    "created_at": "2024-01-01T00:00:00Z"
  }
}
```

### Create [Resource]
```
POST /api/[resources]
```

**Request Body**:
```json
{
  "field": "value"
}
```

**Response**: `201 Created`

### Update [Resource]
```
PUT /api/[resources]/{id}
```

**Response**: `200 OK`

### Delete [Resource]
```
DELETE /api/[resources]/{id}
```

**Response**: `204 No Content`

## Error Responses

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Description",
    "details": {}
  }
}
```
