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
    "grading_system": {
      "free": false,
      "hint": "System is Fontainebleau scale : https://fr.wikipedia.org/wiki/Cotation_en_escalade#Cotation_fran%C3%A7aise_2",
      "points": {
        "3a": 300,
        "3a+": 310,
        "3b": 320,
        "4a": 400,
        "5a": 500,
        "6a": 600,
        "7a": 700,
        "8a": 800,
        "9a": 900
      }
    },
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

**Note:** 
- `default_cotation`: Boolean indicating if the site uses the default grading system (true) or a custom one (false)
- `grading_system`: Object containing the grading system details:
  - `free`: Boolean indicating if grades can be entered freely
  - `hint`: String providing guidance on the grading system
  - `points`: Object mapping grade labels to numerical values (300-950)

#### Areas

- **GET** `/api/v1/sites/{site}/areas` - List all areas for a site
- **GET** `/api/v1/areas/{area}` - Get a specific area
- **GET** `/api/v1/areas/{area}/routes` - List all routes for an area

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

#### Route Logs

- **POST** `/api/v1/routes/{route}/logs` - Create a new log for a route (authenticated)

**POST Example Request:**
```json
{
  "grade": 650,
  "type": "flash",
  "way": "bouldering",
  "comment": "Amazing route! Really enjoyed it.",
  "video_url": "https://example.com/my-climb.mp4"
}
```

**Parameters:**
- `grade` (required, integer): The difficulty grade value (300-950). Must match the site's grading system values.
- `type` (required, string): Type of ascent. Options: `work` (working on it), `flash` (first try success), `view` (just viewing/attempted)
- `way` (required, string): Climbing style. Options: `top-rope`, `lead`, `bouldering`
- `comment` (optional, string): Comments about the climb (max 1000 characters)
- `video_url` (optional, string): URL to a video of the climb (max 255 characters, must be a valid URL)

**POST Example Response (201 Created):**
```json
{
  "data": {
    "id": 123,
    "route_id": 1,
    "comments": "Amazing route! Really enjoyed it.",
    "type": "flash",
    "way": "bouldering",
    "grade": 650,
    "created_at": "2025-01-15T14:30:00.000000Z",
    "is_verified": false,
    "user_name": "John Doe",
    "user_pp_url": "https://..."
  }
}
```

**Error Response (422 Validation Error):**
```json
{
  "message": "The grade field is required. (and 2 more errors)",
  "errors": {
    "grade": ["The grade field is required."],
    "type": ["The type field is required."],
    "way": ["The way field is required."]
  }
}
```

**Error Response (401 Unauthorized):**
```json
{
  "message": "Unauthenticated."
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

- Contest participation
- Team joining/leaving
- Category enrollment
- Social features (favorites, comments)
- Advanced filtering and search

## Support

For API support and questions, please contact the TopoClimb team or open an issue on GitHub.
