# User Stats API Endpoint - Sample Response

## Endpoint
```
GET /api/v1/user/user-stats
```

## Authentication
Requires authentication via Laravel Sanctum token in the `Authorization` header:
```
Authorization: Bearer {token}
```

## Success Response (200 OK)

When user stats exist, the endpoint returns a structured JSON response with all user statistics organized into four main categories:

```json
{
  "data": {
    "id": 1,
    "user_id": 42,
    "technical_analysis": {
      "consistency_variance": 12.5,
      "flash_work_ratio": 0.35,
      "risk_profile_abandonment_rate": 15.2,
      "avg_difficulty_abandoned": 650.0,
      "long_routes_count": 45,
      "short_routes_count": 120,
      "avg_time_between_attempts": 48.5,
      "movement_preferences": {
        "crimpy": 0.4,
        "slopers": 0.3,
        "pockets": 0.2,
        "jugs": 0.1
      }
    },
    "behavioral_analysis": {
      "preferred_climbing_hour": "18:00",
      "avg_session_duration": 2.5,
      "avg_routes_per_session": 8.3,
      "exploration_ratio": 65.5,
      "sector_fidelity": {
        "sector_1": 45,
        "sector_2": 30,
        "sector_3": 25
      },
      "avg_attempts_before_success": 2.8,
      "project_count": 5
    },
    "progression_analysis": {
      "progression_rate": 1.2,
      "plateau_detected": false,
      "plateau_weeks": 0,
      "progression_by_style": {
        "slab": 0.8,
        "vertical": 1.0,
        "overhang": 1.5
      },
      "progression_by_sector": {
        "sector_1": 1.1,
        "sector_2": 0.9,
        "sector_3": 1.3
      }
    },
    "training_load_analysis": {
      "weekly_volume": 120.5,
      "weekly_intensity": 75.3,
      "acute_load": 95.2,
      "chronic_load": 88.7,
      "acute_chronic_ratio": 1.07,
      "overtraining_detected": false,
      "avg_recovery_time": 72.0,
      "avg_time_between_performances": 168.5
    },
    "last_calculated_at": "2026-01-04T12:00:00.000000Z",
    "created_at": "2026-01-03T10:00:00.000000Z",
    "updated_at": "2026-01-04T12:00:00.000000Z"
  }
}
```

## Field Descriptions

### Technical Analysis
- **consistency_variance** (decimal): Variance of difficulty levels. Lower values indicate more stable performance.
- **flash_work_ratio** (decimal): Ratio of flash ascents to worked routes. Higher values indicate more explosive climbing style.
- **risk_profile_abandonment_rate** (decimal): Percentage of attempted routes that were never completed.
- **avg_difficulty_abandoned** (decimal): Average difficulty level at which routes are abandoned.
- **long_routes_count** (integer): Number of endurance-focused routes completed.
- **short_routes_count** (integer): Number of power/boulder-focused routes completed.
- **avg_time_between_attempts** (decimal): Average time (in hours) between repeated attempts on the same route.
- **movement_preferences** (object): Preferred movement types based on route tags.

### Behavioral Analysis
- **preferred_climbing_hour** (string): Most common time of day for climbing.
- **avg_session_duration** (decimal): Average duration (in hours) of climbing sessions.
- **avg_routes_per_session** (decimal): Typical number of routes climbed per session.
- **exploration_ratio** (decimal): Percentage of climbing on new routes vs repeating routes.
- **sector_fidelity** (object): Most frequently climbed sectors/areas.
- **avg_attempts_before_success** (decimal): Average number of attempts needed before successfully sending a route.
- **project_count** (integer): Number of routes worked across multiple sessions.

### Progression Analysis
- **progression_rate** (decimal): Grade progression per month. Positive values indicate improvement.
- **plateau_detected** (boolean): Whether stagnation is detected.
- **plateau_weeks** (integer): Number of weeks in plateau if detected.
- **progression_by_style** (object): Progression rate for different climbing styles.
- **progression_by_sector** (object): Progression rate in different sectors/areas.

### Training Load Analysis
- **weekly_volume** (decimal): Total climbing load in the past week.
- **weekly_intensity** (decimal): Average difficulty level in the past week.
- **acute_load** (decimal): Training load in the last 7 days (short-term stress).
- **chronic_load** (decimal): Average weekly training load over the last 4 weeks.
- **acute_chronic_ratio** (decimal): Ratio to prevent overtraining. Sweet spot: 0.8-1.3, >1.5 indicates high injury risk.
- **overtraining_detected** (boolean): Automatic flag when acute/chronic ratio exceeds 1.5.
- **avg_recovery_time** (decimal): Average time (in hours) between climbing sessions.
- **avg_time_between_performances** (decimal): Average time (in hours) between peak performances.

## Error Responses

### 404 Not Found
When the user has no stats calculated yet:

```json
{
  "message": "Statistics not yet calculated. They will be available after the nightly update.",
  "data": null
}
```

### 401 Unauthorized
When the request is not authenticated:

```json
{
  "message": "Unauthenticated."
}
```

## Notes

- Statistics are calculated through a nightly batch process
- All decimal values are nullable and may be `null` if not enough data is available
- JSON fields (movement_preferences, sector_fidelity, progression_by_style, progression_by_sector) may be `null` or empty objects
- Timestamps are in ISO 8601 format with microseconds
- The endpoint only returns stats for the authenticated user (no ability to view other users' stats via this endpoint)
