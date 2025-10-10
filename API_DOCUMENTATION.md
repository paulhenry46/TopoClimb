# TopoClimb API Documentation

## Overview

The TopoClimb API provides access to climbing site data, routes, contests, and user information. The API is designed for building mobile applications and third-party integrations.

## Base URL

All API endpoints are prefixed with `/api/v1`

## Authentication

The API uses Laravel Sanctum for authentication. To access protected endpoints, you need to include an API token in the request header.

### Getting an API Token

1. Log in to your TopoClimb account via the web interface
2. Navigate to the API Tokens section
3. Create a new API token
4. Copy the token (it will only be shown once)

### Using the Token

Include the token in the `Authorization` header:

```
Authorization: Bearer YOUR_API_TOKEN
```

## Available Endpoints

### Public Endpoints (No Authentication Required)

#### Sites

- **GET** `/api/v1/sites` - List all climbing sites
- **GET** `/api/v1/sites/{site}` - Get a specific site

**Example Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Example Climbing Site",
    "slug": "example-site",
    "address": "123 Climbing St",
    "description": "A great climbing location",
    "profile_picture": "https://...",
    "banner": "https://...",
    "default_cotation": true,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

#### Areas

- **GET** `/api/v1/sites/{site}/areas` - List all areas for a site
- **GET** `/api/v1/areas/{area}` - Get a specific area

**Example Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Boulder Area",
    "slug": "boulder-area",
    "type": "bouldering",
    "site_id": 1,
    "banner": "https://...",
    "svg_schema": "https://.../users.svg",
    "edited_svg_schema": "https://.../admin.svg",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

**Note:** The `svg_schema` field contains the SVG schema for users, while `edited_svg_schema` contains the admin version with editing capabilities.

#### Sectors

- **GET** `/api/v1/areas/{area}/sectors` - List all sectors for an area
- **GET** `/api/v1/sectors/{sector}` - Get a specific sector

**Example Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Sector A",
    "slug": "sector-a",
    "local_id": 1,
    "area_id": 1,
    "common_edited_lines": "https://.../common_paths.svg",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

**Note:** The `common_edited_lines` field contains the SVG with all route paths for the sector.

#### Lines

- **GET** `/api/v1/sectors/{sector}/lines` - List all lines for a sector
- **GET** `/api/v1/lines/{line}` - Get a specific line

**Example Response:**
```json
{
  "data": {
    "id": 1,
    "local_id": 1,
    "sector_id": 1,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

#### Routes

- **GET** `/api/v1/lines/{line}/routes` - List all routes for a line
- **GET** `/api/v1/routes/{route}` - Get a specific route

**Example Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Red Route",
    "slug": "red-route",
    "local_id": 1,
    "line_id": 1,
    "grade": 15,
    "color": "#FF0000",
    "comment": "Great route for beginners",
    "picture": "https://.../route-1",
    "filtered_picture": "https://.../route-filtered-1",
    "circle": "https://.../route-1.svg",
    "path_line": "https://.../route-1.svg",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

**Note:** 
- `picture`: Photo of the route
- `filtered_picture`: Filtered/processed photo of the route
- `circle`: SVG circle marker for the route
- `path_line`: SVG path line showing the route path

#### Contests

- **GET** `/api/v1/sites/{site}/contests` - List all contests for a site
- **GET** `/api/v1/contests/{contest}` - Get a specific contest

**Example Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Summer Contest 2025",
    "description": "Annual summer climbing contest",
    "start_date": "2025-06-01T00:00:00.000000Z",
    "end_date": "2025-06-30T23:59:59.000000Z",
    "mode": "official",
    "site_id": 1,
    "use_dynamic_points": true,
    "team_mode": true,
    "team_points_mode": "unique",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

#### Teams

- **GET** `/api/v1/contests/{contest}/teams` - List all teams for a contest
- **GET** `/api/v1/teams/{team}` - Get a specific team

**Example Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Team Awesome",
    "contest_id": 1,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

#### Tags

- **GET** `/api/v1/tags` - List all tags
- **GET** `/api/v1/tags/{tag}` - Get a specific tag

**Example Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Beginner Friendly",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

### Authenticated Endpoints (Require API Token)

#### User Profile

- **GET** `/api/v1/user` - Get authenticated user's profile
- **PUT** `/api/v1/user` - Update authenticated user's profile

**GET Example Response:**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "birth_date": "1990-01-01",
    "gender": "male",
    "profile_photo_url": "https://...",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

**PUT Example Request:**
```json
{
  "name": "John Doe",
  "birth_date": "1990-01-01",
  "gender": "male"
}
```

## API Mode Features

The API is designed with the following principles:

1. **Read-Only Access for Most Resources**: Sites, areas, sectors, lines, routes, contests, teams, and tags are read-only. This prevents unauthorized modifications to climbing site data.

2. **User Profile Management**: Authenticated users can view and update their own profile information.

3. **No Admin Features**: The API does not expose admin features such as:
   - Creating, updating, or deleting sites
   - Creating, updating, or deleting areas, sectors, lines, or routes
   - Managing contests, teams, or categories
   - User management

4. **Versioned API**: All endpoints are versioned (v1) to allow for future changes without breaking existing integrations.

## Error Responses

The API uses standard HTTP status codes:

- `200` - Success
- `401` - Unauthorized (missing or invalid API token)
- `404` - Resource not found
- `422` - Validation error

**Example Error Response:**
```json
{
  "message": "Unauthenticated."
}
```

## Rate Limiting

API requests are subject to rate limiting to ensure fair usage. The specific limits will be communicated through response headers.

## Future Enhancements

Planned features for future API versions:

- Route climbing logs (view and create)
- Contest participation
- Team joining/leaving
- Category enrollment
- Social features (favorites, comments)
- Advanced filtering and search

## Support

For API support and questions, please contact the TopoClimb team or open an issue on GitHub.
