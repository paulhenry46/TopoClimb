# Android App API Integration Guide - Route Logging

This guide provides specific examples for integrating the TopoClimb route logging API into an Android application.

## Authentication

Before making any authenticated requests, you need an API token. Users can generate tokens from the TopoClimb web interface.

### Setting up the API Token

```kotlin
// Store the API token securely (e.g., in SharedPreferences or DataStore)
val apiToken = "user_generated_token_from_web_interface"
val baseUrl = "https://your-topoclimb-instance.com/api/v1"
```

## Creating a Route Log

### Endpoint

**POST** `/api/v1/routes/{route}/logs`

### Required Headers

```
Authorization: Bearer YOUR_API_TOKEN
Content-Type: application/json
```

### Request Body Parameters

| Parameter | Type | Required | Description | Valid Values |
|-----------|------|----------|-------------|--------------|
| `grade` | Integer | Yes | Difficulty grade value (300-950) | 300-950 |
| `type` | String | Yes | Type of ascent | `work`, `flash`, `view` |
| `way` | String | Yes | Climbing style | `top-rope`, `lead`, `bouldering` |
| `comment` | String | No | Comments about the climb (max 1000 chars) | Any string |
| `video_url` | String | No | URL to a video of the climb (max 255 chars) | Valid URL |

### Example Implementation with Retrofit

#### 1. Define the API Service

```kotlin
import retrofit2.Response
import retrofit2.http.*

data class LogRequest(
    val grade: Int,
    val type: String,
    val way: String,
    val comment: String? = null,
    val video_url: String? = null
)

data class LogResponse(
    val data: Log
)

data class Log(
    val id: Int,
    val route_id: Int,
    val comments: String?,
    val type: String,
    val way: String,
    val grade: Int,
    val created_at: String,
    val is_verified: Boolean,
    val user_name: String,
    val user_pp_url: String?
)

interface TopoClimbApiService {
    @POST("routes/{routeId}/logs")
    suspend fun createRouteLog(
        @Path("routeId") routeId: Int,
        @Header("Authorization") token: String,
        @Body logRequest: LogRequest
    ): Response<LogResponse>
    
    @GET("routes/{routeId}/logs")
    suspend fun getRouteLogs(
        @Path("routeId") routeId: Int
    ): Response<LogsListResponse>
}

data class LogsListResponse(
    val data: List<Log>
)
```

#### 2. Create a Log for a Route

```kotlin
import kotlinx.coroutines.launch
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope

class RouteViewModel : ViewModel() {
    private val apiService: TopoClimbApiService = // ... initialize Retrofit service
    
    fun logRoute(
        routeId: Int,
        grade: Int,
        type: String, // "work", "flash", or "view"
        way: String,  // "top-rope", "lead", or "bouldering"
        comment: String? = null,
        videoUrl: String? = null,
        apiToken: String
    ) {
        viewModelScope.launch {
            try {
                val request = LogRequest(
                    grade = grade,
                    type = type,
                    way = way,
                    comment = comment,
                    video_url = videoUrl
                )
                
                val response = apiService.createRouteLog(
                    routeId = routeId,
                    token = "Bearer $apiToken",
                    logRequest = request
                )
                
                if (response.isSuccessful) {
                    val log = response.body()?.data
                    // Handle success
                    println("Log created successfully: ${log?.id}")
                } else {
                    // Handle error
                    when (response.code()) {
                        401 -> println("Unauthorized - invalid or missing token")
                        404 -> println("Route not found")
                        422 -> println("Validation error - check your input")
                        else -> println("Error: ${response.code()}")
                    }
                }
            } catch (e: Exception) {
                // Handle network or other exceptions
                println("Error creating log: ${e.message}")
            }
        }
    }
    
    fun getLogsForRoute(routeId: Int) {
        viewModelScope.launch {
            try {
                val response = apiService.getRouteLogs(routeId)
                
                if (response.isSuccessful) {
                    val logs = response.body()?.data
                    // Handle success - display logs
                    logs?.forEach { log ->
                        println("Log: ${log.user_name} - ${log.type} - Grade: ${log.grade}")
                    }
                } else {
                    // Handle error
                    println("Error: ${response.code()}")
                }
            } catch (e: Exception) {
                println("Error fetching logs: ${e.message}")
            }
        }
    }
}
```

#### 3. Example Usage in a Composable or Activity

```kotlin
// In a Composable
@Composable
fun RouteLogScreen(routeId: Int, viewModel: RouteViewModel = viewModel()) {
    var selectedGrade by remember { mutableStateOf(650) }
    var selectedType by remember { mutableStateOf("flash") }
    var selectedWay by remember { mutableStateOf("bouldering") }
    var comment by remember { mutableStateOf("") }
    var videoUrl by remember { mutableStateOf("") }
    
    // Get API token from secure storage
    val apiToken = // ... retrieve from secure storage
    
    Column(modifier = Modifier.padding(16.dp)) {
        // Grade selector
        Text("Grade")
        Slider(
            value = selectedGrade.toFloat(),
            onValueChange = { selectedGrade = it.toInt() },
            valueRange = 300f..950f,
            steps = 64
        )
        
        // Type selector
        Text("Type")
        Row {
            RadioButton(
                selected = selectedType == "work",
                onClick = { selectedType = "work" }
            )
            Text("Work")
            
            RadioButton(
                selected = selectedType == "flash",
                onClick = { selectedType = "flash" }
            )
            Text("Flash")
            
            RadioButton(
                selected = selectedType == "view",
                onClick = { selectedType = "view" }
            )
            Text("View")
        }
        
        // Way selector
        Text("Climbing Style")
        Row {
            RadioButton(
                selected = selectedWay == "bouldering",
                onClick = { selectedWay = "bouldering" }
            )
            Text("Bouldering")
            
            RadioButton(
                selected = selectedWay == "lead",
                onClick = { selectedWay = "lead" }
            )
            Text("Lead")
            
            RadioButton(
                selected = selectedWay == "top-rope",
                onClick = { selectedWay = "top-rope" }
            )
            Text("Top-Rope")
        }
        
        // Comment field
        OutlinedTextField(
            value = comment,
            onValueChange = { comment = it },
            label = { Text("Comment (optional)") },
            maxLines = 4,
            modifier = Modifier.fillMaxWidth()
        )
        
        // Video URL field
        OutlinedTextField(
            value = videoUrl,
            onValueChange = { videoUrl = it },
            label = { Text("Video URL (optional)") },
            modifier = Modifier.fillMaxWidth()
        )
        
        // Submit button
        Button(
            onClick = {
                viewModel.logRoute(
                    routeId = routeId,
                    grade = selectedGrade,
                    type = selectedType,
                    way = selectedWay,
                    comment = comment.takeIf { it.isNotBlank() },
                    videoUrl = videoUrl.takeIf { it.isNotBlank() },
                    apiToken = apiToken
                )
            },
            modifier = Modifier.fillMaxWidth()
        ) {
            Text("Log Route")
        }
    }
}
```

### Example with OkHttp (if not using Retrofit)

```kotlin
import okhttp3.*
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.RequestBody.Companion.toRequestBody
import org.json.JSONObject
import java.io.IOException

class RouteLogApi(private val baseUrl: String) {
    private val client = OkHttpClient()
    private val JSON = "application/json; charset=utf-8".toMediaType()
    
    fun createRouteLog(
        routeId: Int,
        grade: Int,
        type: String,
        way: String,
        comment: String? = null,
        videoUrl: String? = null,
        apiToken: String,
        callback: (Boolean, String?) -> Unit
    ) {
        val json = JSONObject().apply {
            put("grade", grade)
            put("type", type)
            put("way", way)
            comment?.let { put("comment", it) }
            videoUrl?.let { put("video_url", it) }
        }
        
        val body = json.toString().toRequestBody(JSON)
        val request = Request.Builder()
            .url("$baseUrl/routes/$routeId/logs")
            .header("Authorization", "Bearer $apiToken")
            .post(body)
            .build()
        
        client.newCall(request).enqueue(object : Callback {
            override fun onFailure(call: Call, e: IOException) {
                callback(false, e.message)
            }
            
            override fun onResponse(call: Call, response: Response) {
                if (response.isSuccessful) {
                    callback(true, response.body?.string())
                } else {
                    callback(false, "Error: ${response.code}")
                }
            }
        })
    }
}
```

## Response Examples

### Success Response (201 Created)

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
    "user_pp_url": "https://example.com/user.jpg"
  }
}
```

### Error Responses

#### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

#### 422 Validation Error
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

#### 404 Not Found
```json
{
  "message": "Route not found"
}
```

## Best Practices

1. **Store API tokens securely**: Use Android's EncryptedSharedPreferences or a secure key-value store
2. **Handle errors gracefully**: Always check response codes and show user-friendly error messages
3. **Validate input client-side**: Validate grade ranges and enum values before sending requests
4. **Use coroutines**: For better async handling and cancellation support
5. **Cache route information**: Store route details locally to avoid repeated API calls
6. **Test with different scenarios**: Test with invalid tokens, missing fields, and network failures

## Testing

### Test Cases to Implement

1. Successful log creation with all fields
2. Successful log creation with only required fields
3. Handling 401 unauthorized error
4. Handling 422 validation error
5. Handling network timeout
6. Retrieving logs for a route

## Additional Resources

- Full API Documentation: [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- OpenAPI Specification: [openapi.yaml](openapi.yaml)
