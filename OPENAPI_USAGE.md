# OpenAPI Specification Usage Guide

## Overview

TopoClimb now includes a comprehensive OpenAPI 3.0 specification that documents all API endpoints. This specification file (`openapi.yaml`) can be used for:

- **API Documentation**: Generate beautiful, interactive API documentation
- **Client Generation**: Automatically generate client SDKs in multiple programming languages
- **API Testing**: Test and validate API endpoints using various tools
- **Contract Testing**: Ensure API implementation matches the specification
- **Integration**: Integrate with API management platforms

## Quick Start

### Viewing the API Documentation

#### Option 1: Swagger UI (Online)
1. Go to [Swagger Editor](https://editor.swagger.io/)
2. Click **File** → **Import File**
3. Select the `openapi.yaml` file from this repository
4. The interactive documentation will be displayed on the right side

#### Option 2: Swagger UI (Local with Docker)
```bash
docker run -p 8080:8080 -e SWAGGER_JSON=/openapi.yaml -v $(pwd)/openapi.yaml:/openapi.yaml swaggerapi/swagger-ui
```
Then open http://localhost:8080 in your browser

#### Option 3: Redoc (Online)
1. Go to [Redoc](https://redocly.github.io/redoc/)
2. Paste the raw URL of the `openapi.yaml` file from GitHub

### Validating the Specification

To validate that the OpenAPI specification is correct:

```bash
npm install
npx swagger-cli validate openapi.yaml
```

If the specification is valid, you'll see:
```
openapi.yaml is valid
```

## Generating Client SDKs

You can use [OpenAPI Generator](https://openapi-generator.tech/) to generate client libraries in various programming languages.

### JavaScript/TypeScript Client

```bash
npx @openapitools/openapi-generator-cli generate \
  -i openapi.yaml \
  -g typescript-axios \
  -o ./clients/typescript
```

### Python Client

```bash
npx @openapitools/openapi-generator-cli generate \
  -i openapi.yaml \
  -g python \
  -o ./clients/python
```

### Java Client

```bash
npx @openapitools/openapi-generator-cli generate \
  -i openapi.yaml \
  -g java \
  -o ./clients/java
```

### Other Languages

OpenAPI Generator supports 50+ languages and frameworks. See the [full list](https://openapi-generator.tech/docs/generators).

## Testing the API

### Using Postman

1. Open Postman
2. Click **Import** → **File** → **Upload Files**
3. Select `openapi.yaml`
4. Postman will create a collection with all API endpoints
5. You can now test each endpoint directly from Postman

### Using Insomnia

1. Open Insomnia
2. Click **Create** → **Import From** → **File**
3. Select `openapi.yaml`
4. All endpoints will be available in a new workspace

### Using cURL

Example requests from the specification:

```bash
# List all sites (public endpoint)
curl https://your-site.com/api/v1/sites

# Get authenticated user profile (requires token)
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://your-site.com/api/v1/user

# Update user profile
curl -X PUT \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"name": "John Doe", "birth_date": "1990-01-01"}' \
     https://your-site.com/api/v1/user
```

## API Documentation Highlights

### Authentication

The API uses **Laravel Sanctum** for authentication:
- **Public endpoints**: No authentication required
- **Authenticated endpoints**: Require Bearer token in Authorization header

To obtain a token:
1. Log in to your TopoClimb account
2. Navigate to API Tokens section
3. Create a new token
4. Use it in the `Authorization: Bearer YOUR_TOKEN` header

### Endpoints Overview

#### Public Endpoints (No Authentication)
- **Sites**: `GET /api/v1/sites`, `GET /api/v1/sites/{site}`
- **Areas**: `GET /api/v1/sites/{site}/areas`, `GET /api/v1/areas/{area}`
- **Sectors**: `GET /api/v1/areas/{area}/sectors`, `GET /api/v1/sectors/{sector}`
- **Lines**: `GET /api/v1/sectors/{sector}/lines`, `GET /api/v1/lines/{line}`
- **Routes**: `GET /api/v1/lines/{line}/routes`, `GET /api/v1/routes/{route}`
- **Contests**: `GET /api/v1/sites/{site}/contests`, `GET /api/v1/contests/{contest}`
- **Teams**: `GET /api/v1/contests/{contest}/teams`, `GET /api/v1/teams/{team}`
- **Tags**: `GET /api/v1/tags`, `GET /api/v1/tags/{tag}`

#### Authenticated Endpoints (Require API Token)
- **User Profile**: `GET /api/v1/user`, `PUT /api/v1/user`

### Response Format

All API responses follow a consistent structure:

```json
{
  "data": {
    "id": 1,
    "name": "Example",
    ...
  }
}
```

For collections:

```json
{
  "data": [
    {
      "id": 1,
      "name": "Example 1",
      ...
    },
    {
      "id": 2,
      "name": "Example 2",
      ...
    }
  ]
}
```

### Error Responses

Standard HTTP status codes are used:
- `200`: Success
- `401`: Unauthorized (missing or invalid token)
- `404`: Resource not found
- `422`: Validation error

Error response example:
```json
{
  "message": "Unauthenticated."
}
```

## Integration with API Management Platforms

### AWS API Gateway

You can import the OpenAPI specification directly into AWS API Gateway:
1. Open AWS API Gateway console
2. Click **Create API** → **REST API** → **Import**
3. Upload `openapi.yaml`
4. Configure your gateway settings

### Azure API Management

1. Open Azure API Management instance
2. Click **APIs** → **Add API** → **OpenAPI**
3. Upload `openapi.yaml`
4. Configure policies and settings

### Kong API Gateway

```bash
deck gateway dump --output-file kong.yaml
deck file openapi2kong -s openapi.yaml -o kong-config.yaml
deck gateway sync kong-config.yaml
```

## Best Practices

1. **Always validate** the OpenAPI spec after making changes:
   ```bash
   npx swagger-cli validate openapi.yaml
   ```

2. **Keep in sync**: When adding new endpoints to the API, update the OpenAPI spec accordingly

3. **Version control**: The spec is committed to git, so all changes are tracked

4. **Generate docs**: Regenerate client libraries and documentation after spec updates

5. **Test against spec**: Use contract testing tools to ensure implementation matches specification

## Tools and Resources

### Recommended Tools
- **[Swagger Editor](https://editor.swagger.io/)**: Online editor and validator
- **[Redoc](https://redocly.github.io/redoc/)**: Beautiful API documentation generator
- **[Postman](https://www.postman.com/)**: API testing and collaboration
- **[Insomnia](https://insomnia.rest/)**: REST client
- **[OpenAPI Generator](https://openapi-generator.tech/)**: Client SDK generator

### Learning Resources
- [OpenAPI Specification](https://swagger.io/specification/)
- [OpenAPI Guide](https://swagger.io/docs/specification/about/)
- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)

## Maintenance

### Updating the Specification

When you add new API endpoints:

1. Update the `openapi.yaml` file with:
   - New path definitions
   - Request/response schemas
   - Example data

2. Validate the changes:
   ```bash
   npx swagger-cli validate openapi.yaml
   ```

3. Test the new endpoints match the spec

4. Commit the updated spec to version control

### Version Management

The API is currently at version `v1`. When making breaking changes:

1. Consider creating a new version (`v2`)
2. Update the `openapi.yaml` version number
3. Document migration guides for clients

## Support

For questions or issues related to the OpenAPI specification:
- Open an issue on GitHub
- Check existing API documentation in `API_DOCUMENTATION.md`
- Review the implementation in `routes/api.php` and `app/Http/Controllers/Api/`
