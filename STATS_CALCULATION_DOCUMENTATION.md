# Statistics Calculation Documentation

This document provides a comprehensive explanation of how each statistic in the `user_stats` table is calculated, including the criteria, formulas, and data sources used.

## Table of Contents
1. [Technical Analysis Metrics](#technical-analysis-metrics)
2. [Behavioral Analysis Metrics](#behavioral-analysis-metrics)
3. [Progression Analysis Metrics](#progression-analysis-metrics)
4. [Training Load Analysis Metrics](#training-load-analysis-metrics)
5. [Data Sources](#data-sources)
6. [Calculation Frequency](#calculation-frequency)

---

## Technical Analysis Metrics

### 1. Consistency Variance (`consistency_variance`)

**Purpose**: Measures how consistent a climber is in their climbing level. Lower values indicate more stable performance.

**Calculation**:
```
variance = average of (each_grade - mean_grade)²
```

**Criteria**:
- Uses only **public logs** (work, flash, view)
- Excludes tentative (attempt) logs
- Requires at least 2 logged routes
- Grade is the numerical difficulty value (e.g., 500, 600, 700)

**Example**:
- Grades: [500, 520, 510, 500, 530]
- Mean: 512
- Variance: ((500-512)² + (520-512)² + (510-512)² + (500-512)² + (530-512)²) / 5 = 128

**Interpretation**:
- Low variance (0-100): Very consistent climber
- Medium variance (100-300): Moderate consistency
- High variance (>300): Wide range of difficulty levels

---

### 2. Flash/Work Ratio (`flash_work_ratio`)

**Purpose**: Indicates climbing style - explosive (high flash rate) vs methodical (high work rate).

**Calculation**:
```
flash_work_ratio = count(flash_logs) / count(work_logs)
```

**Criteria**:
- Uses only **public logs**
- If work_count = 0 and flash_count > 0: ratio = 999.99 (maximum value)
- If both counts = 0: no value calculated

**Example**:
- Flash logs: 10
- Work logs: 5
- Ratio: 10/5 = 2.0

**Interpretation**:
- Ratio < 0.5: Methodical climber, usually works routes before success
- Ratio 0.5-1.5: Balanced approach
- Ratio > 1.5: Explosive climber, often succeeds on first try

---

### 3. Risk Profile Abandonment Rate (`risk_profile_abandonment_rate`)

**Purpose**: Percentage of attempted routes that were never completed.

**Calculation**:
```
abandonment_rate = (count(abandoned_routes) / count(attempted_routes)) × 100
```

**Criteria**:
- Attempted routes: Routes with at least one **tentative log**
- Abandoned routes: Attempted routes with **no successful public log**
- Completed routes: Attempted routes with at least one public log (work/flash/view)

**Example**:
- Attempted 20 unique routes (via tentative logs)
- Completed 15 of them (have public logs)
- Abandoned: 5
- Rate: (5/20) × 100 = 25%

**Interpretation**:
- Low rate (<20%): Persistent, completes most projects
- Medium rate (20-40%): Selective, gives up on some routes
- High rate (>40%): Either very ambitious or easily discouraged

---

### 4. Average Difficulty of Abandoned Routes (`avg_difficulty_abandoned`)

**Purpose**: Shows the difficulty level at which a climber tends to give up.

**Calculation**:
```
avg_difficulty_abandoned = average(grades of all tentative logs on abandoned routes)
```

**Criteria**:
- Only includes routes that have tentative logs but no public logs
- Uses the grade from the tentative logs
- Requires at least one abandoned route

**Example**:
- Abandoned route 1: tentative logs with grades [650, 650, 660]
- Abandoned route 2: tentative logs with grades [700, 710]
- Average: (650+650+660+700+710) / 5 = 674

**Interpretation**:
- Compare with climber's usual grade to see their limit
- Higher than usual: pushing boundaries but not succeeding
- Similar to usual: projects at current level

---

### 5. Long Routes Count (`long_routes_count`)

**Purpose**: Number of endurance-focused routes completed.

**Calculation**:
```
long_routes_count = count of public logs where route has endurance-related tags
```

**Criteria**:
- Uses only **public logs**
- Route must have at least one of these tags (case-insensitive):
  - "continuity"
  - "endurance"
  - "resistance"
- Tags are checked via route.tags relationship

**Example**:
- Climber has 50 public logs
- 15 of these are on routes tagged with "endurance" or "resistance"
- Long routes count: 15

---

### 6. Short Routes Count (`short_routes_count`)

**Purpose**: Number of power/boulder-focused routes completed.

**Calculation**:
```
short_routes_count = total_public_logs - long_routes_count
```

**Criteria**:
- All public logs that don't qualify as "long routes"
- Typically boulder problems or short sport routes

**Example**:
- Total public logs: 50
- Long routes: 15
- Short routes: 50 - 15 = 35

**Interpretation (Long vs Short Ratio)**:
- More long routes: Endurance specialist
- More short routes: Power/boulder specialist
- Balanced: All-around climber

---

### 7. Average Time Between Attempts (`avg_time_between_attempts`)

**Purpose**: Shows how much time passes between repeated attempts on the same route.

**Calculation**:
```
For each route with multiple logs:
  time_diffs = [time between consecutive logs]
avg_time_between_attempts = average of all time_diffs (in hours)
```

**Criteria**:
- Uses **all logs** (public and tentative)
- Only counts routes with 2+ logs
- Logs are sorted by creation time
- Time measured in hours

**Example**:
- Route A: attempted on Day 1, Day 3, Day 5 (48h, 48h between attempts)
- Route B: attempted on Day 1, Day 8 (168h between attempts)
- Average: (48 + 48 + 168) / 3 = 88 hours

**Interpretation**:
- < 24 hours: Same-session or next-day attempts
- 24-72 hours: Regular training schedule
- > 168 hours (1 week): Long-term projects

---

### 8. Movement Preferences (`movement_preferences`)

**Purpose**: Identifies preferred movement types based on route tags.

**Calculation**:
```
For each public log:
  Count each tag on the route
Sort tags by frequency
Return top 10 tags with their counts
```

**Criteria**:
- Uses only **public logs**
- Tags come from route.tags relationship
- Stored as JSON: {"coordination": 15, "balance": 12, "resistance": 10, ...}

**Example Tags**:
- Technique: coordination, balance, footwork
- Physical: resistance, power, compression
- Style: crimpy, slopey, dynamic

**Example**:
```json
{
  "coordination": 25,
  "resistance": 20,
  "balance": 18,
  "compression": 15,
  "power": 12
}
```

**Interpretation**:
- Top tags show movement strengths or preferences
- Use for targeted training recommendations

---

## Behavioral Analysis Metrics

### 9. Preferred Climbing Hour (`preferred_climbing_hour`)

**Purpose**: Identifies the most common time of day for climbing.

**Calculation**:
```
For each log:
  Extract hour from created_at timestamp
Count frequency of each hour
preferred_hour = hour with highest count
```

**Criteria**:
- Uses **all logs** (public and tentative)
- Hour is in 24-hour format (0-23)
- Stored as string: "14:00", "18:00", etc.

**Example**:
- 30 logs at 18:00
- 20 logs at 19:00
- 15 logs at 17:00
- Preferred hour: "18:00"

**Interpretation**:
- Morning (6-11): Early bird climber
- Afternoon (12-17): Flexible schedule
- Evening (18-22): After-work climber

---

### 10. Average Session Duration (`avg_session_duration`)

**Purpose**: How long climbing sessions typically last.

**Calculation**:
```
Sessions = group logs by date
For each session with 2+ logs:
  duration = time_of_last_log - time_of_first_log (in hours)
avg_session_duration = average of all durations
```

**Criteria**:
- Uses **all logs**
- Logs on the same calendar day = same session
- Only counts sessions with 2+ logs
- Duration measured in hours

**Example**:
- Session 1: First log at 18:00, last at 20:30 → 2.5 hours
- Session 2: First log at 17:00, last at 19:00 → 2.0 hours
- Session 3: First log at 18:30, last at 21:00 → 2.5 hours
- Average: (2.5 + 2.0 + 2.5) / 3 = 2.33 hours

**Interpretation**:
- < 1.5 hours: Quick sessions
- 1.5-3 hours: Standard sessions
- > 3 hours: Marathon sessions

---

### 11. Average Routes Per Session (`avg_routes_per_session`)

**Purpose**: Typical volume (number of routes) per session.

**Calculation**:
```
Sessions = group logs by date
For each session:
  count = number of logs in that session
avg_routes_per_session = average of all counts
```

**Criteria**:
- Uses **all logs**
- Logs on the same date = same session
- Each log counts as one route (even if same route logged multiple times)

**Example**:
- Session 1: 8 logs
- Session 2: 12 logs
- Session 3: 6 logs
- Average: (8 + 12 + 6) / 3 = 8.67

**Interpretation**:
- < 5: Quality over quantity
- 5-15: Balanced approach
- > 15: High volume training

---

### 12. Exploration Ratio (`exploration_ratio`)

**Purpose**: Percentage of climbing on new routes vs repeating routes.

**Calculation**:
```
unique_routes = count of distinct route_ids in all logs
total_logs = count of all logs
exploration_ratio = (unique_routes / total_logs) × 100
```

**Criteria**:
- Uses **all logs**
- Higher percentage = more variety
- Lower percentage = more repetition on same routes

**Example**:
- Total logs: 100
- Unique routes: 70
- Ratio: (70/100) × 100 = 70%

**Interpretation**:
- > 80%: Explorer - constantly trying new routes
- 50-80%: Balanced - mix of new and repeated
- < 50%: Specialist - works same routes repeatedly

---

### 13. Sector Fidelity (`sector_fidelity`)

**Purpose**: Most frequently climbed sectors/areas.

**Calculation**:
```
For each log:
  sector_name = log.route.line.sector.name
Count frequency of each sector
Return top 5 sectors with their counts
```

**Criteria**:
- Uses **all logs**
- Stored as JSON: {"Sector A": 45, "Sector B": 30, ...}
- Limited to top 5 sectors

**Example**:
```json
{
  "Main Wall": 45,
  "Boulder Cave": 30,
  "Overhangs": 25,
  "Slabs": 15,
  "Competition Wall": 10
}
```

**Interpretation**:
- Shows favorite climbing areas
- Useful for gym owners to see popular sectors
- Helps identify comfort zones

---

### 14. Average Attempts Before Success (`avg_attempts_before_success`)

**Purpose**: Shows tenacity - how many attempts typically needed to send.

**Calculation**:
```
For each route with a successful log (work/flash/view):
  attempts = count of logs (any type) before the success log
  (only count if attempts > 0)
avg_attempts_before_success = average of all attempt counts
```

**Criteria**:
- Success = first public log (work/flash/view) for that route
- Attempts = any logs (including tentative) created before success
- Only counts routes where attempts > 0
- Flash/view logs would have 0 prior attempts (not counted)

**Example**:
- Route A: 3 tentative logs, then 1 work log → 3 attempts
- Route B: 1 tentative log, then 1 work log → 1 attempt
- Route C: 1 flash log → 0 attempts (not counted)
- Average: (3 + 1) / 2 = 2.0

**Interpretation**:
- < 2: Usually gets it quickly
- 2-5: Normal project work
- > 5: Very persistent, works hard projects

---

### 15. Project Count (`project_count`)

**Purpose**: Number of routes worked across multiple sessions.

**Calculation**:
```
For each unique route in all logs:
  dates = unique dates when route was logged
  if count(dates) > 1:
    project_count += 1
```

**Criteria**:
- Uses **all logs**
- Multi-session = route logged on 2+ different calendar dates
- Doesn't matter if route was eventually completed

**Example**:
- Route A: logged on 2023-01-05, 2023-01-12, 2023-01-15 → Project (3 sessions)
- Route B: logged on 2023-01-05 (3 times same day) → Not a project
- Route C: logged on 2023-01-05, 2023-01-08 → Project (2 sessions)
- Project count: 2

**Interpretation**:
- Low count: Prefers onsight/flash style
- High count: Enjoys working long-term projects

---

## Progression Analysis Metrics

### 16. Progression Rate (`progression_rate`)

**Purpose**: How quickly climbing difficulty is improving.

**Calculation**:
```
first_log = oldest public log
last_log = newest public log
months_diff = difference in months between first and last
grade_diff = last_log.grade - first_log.grade
progression_rate = grade_diff / months_diff
```

**Criteria**:
- Uses only **public logs**
- Requires at least 2 public logs
- Measured as grade points per month
- Can be negative (regression)

**Example**:
- First log: Grade 500 on January 1
- Last log: Grade 620 on July 1 (6 months later)
- Progression: (620 - 500) / 6 = 20 points/month

**Interpretation**:
- Positive: Improving
- Zero: Stable/plateau
- Negative: Regressing (injury, break, etc.)
- Typical rate: 10-30 points/month for active climbers

---

### 17. Plateau Detection (`plateau_detected`, `plateau_weeks`)

**Purpose**: Identifies if climber is stuck at current level.

**Calculation**:
```
recent_logs = public logs from last 8 weeks
if count(recent_logs) > 5:
  first_grade = recent_logs.first().grade
  last_grade = recent_logs.last().grade
  grade_diff = abs(last_grade - first_grade)
  
  if grade_diff < 10:
    plateau_detected = true
    plateau_weeks = 8
  else:
    plateau_detected = false
    plateau_weeks = 0
```

**Criteria**:
- Uses only **public logs**
- Requires minimum 5 logs in last 8 weeks
- Plateau threshold: less than 10 grade points progression
- Fixed window: 8 weeks

**Example**:
- Week 1-8: Grades [600, 610, 600, 605, 600, 610, 605]
- First: 600, Last: 605, Diff: 5
- Grade diff < 10 → Plateau detected

**Interpretation**:
- True: Time to change training approach
- False: Still progressing normally

---

### 18. Progression by Style (`progression_by_style`)

**Purpose**: Shows improvement rate for different climbing styles.

**Calculation**:
```
For each style (slab, overhang, vertical):
  style_logs = public logs on routes with matching tags
  if count(style_logs) > 1:
    first_log = oldest style log
    last_log = newest style log
    months = difference in months
    grade_diff = last - first
    progression_by_style[style] = grade_diff / months
```

**Criteria**:
- Uses only **public logs**
- Style determined by route tags:
  - Slab: tags containing "slab" or "dalle"
  - Overhang: tags containing "overhang" or "devers"
  - Vertical: tags containing "vertical"
- Tags are case-insensitive
- Requires at least 2 logs per style
- Stored as JSON

**Example**:
```json
{
  "slab": 25.5,
  "overhang": 15.0,
  "vertical": -5.0
}
```

**Interpretation**:
- Positive values: Improving in that style
- Negative values: Regressing or avoiding that style
- Highest value: Strongest improvement area

---

### 19. Progression by Sector (`progression_by_sector`)

**Purpose**: Shows improvement rate in different sectors/areas.

**Calculation**:
```
For each sector with 2+ public logs:
  sector_logs = logs in that sector, sorted by date
  first_log = oldest
  last_log = newest
  months = difference in months
  grade_diff = last - first
  progression_by_sector[sector] = grade_diff / months

Return top 5 sectors sorted by progression rate
```

**Criteria**:
- Uses only **public logs**
- Sector from: route.line.sector.name
- Skips "Unknown" sectors
- Requires at least 2 logs per sector
- Limited to top 5 sectors
- Stored as JSON

**Example**:
```json
{
  "Boulder Cave": 30.0,
  "Main Wall": 20.0,
  "Competition Wall": 15.0,
  "Slabs": 5.0,
  "Overhangs": -10.0
}
```

**Interpretation**:
- Shows which areas are improving fastest
- Negative values: areas being avoided or struggling with
- Use to identify strengths and weaknesses by location

---

## Training Load Analysis Metrics

### 20. Weekly Volume (`weekly_volume`)

**Purpose**: Total climbing load in the past week.

**Calculation**:
```
weekly_logs = all logs from last 7 days
weekly_volume = sum of all grades in weekly_logs
```

**Criteria**:
- Uses **all logs** (public and tentative)
- Rolling 7-day window
- Sum of numerical grades (e.g., 500 + 600 + 550 = 1650)

**Example**:
- Monday: 3 logs at grades [500, 520, 510]
- Wednesday: 2 logs at grades [600, 620]
- Friday: 4 logs at grades [550, 560, 540, 530]
- Volume: 500+520+510+600+620+550+560+540+530 = 4930

**Interpretation**:
- Low volume (<2000): Light week
- Medium volume (2000-5000): Moderate training
- High volume (>5000): Heavy training week

---

### 21. Weekly Intensity (`weekly_intensity`)

**Purpose**: Average difficulty level in the past week.

**Calculation**:
```
weekly_logs = all logs from last 7 days
weekly_intensity = average of all grades in weekly_logs
```

**Criteria**:
- Uses **all logs**
- Rolling 7-day window
- Average of numerical grades

**Example**:
- 9 logs with grades: [500, 520, 510, 600, 620, 550, 560, 540, 530]
- Intensity: sum(grades) / 9 = 4930 / 9 = 547.78

**Interpretation**:
- Shows if climbing harder or easier routes
- Compare to historical intensity to see trends
- High intensity + high volume = risk of overtraining

---

### 22. Acute Load (`acute_load`)

**Purpose**: Training load in the last 7 days (short-term stress).

**Calculation**:
```
acute_logs = all logs from last 7 days
acute_load = sum of all grades in acute_logs
```

**Criteria**:
- Uses **all logs**
- Exactly same as weekly_volume
- Used for acute/chronic ratio calculation

**Example**:
- Same as weekly_volume: 4930

---

### 23. Chronic Load (`chronic_load`)

**Purpose**: Average weekly training load over the last 4 weeks (long-term fitness).

**Calculation**:
```
chronic_logs = all logs from last 28 days
chronic_volume = sum of all grades in chronic_logs
chronic_load = chronic_volume / 4  (average per week)
```

**Criteria**:
- Uses **all logs**
- Rolling 28-day window
- Divided by 4 to get average weekly load

**Example**:
- Week 1: volume 4000
- Week 2: volume 4500
- Week 3: volume 5000
- Week 4: volume 4930
- Total: 18430
- Chronic load: 18430 / 4 = 4607.5

**Interpretation**:
- Shows baseline fitness level
- Higher chronic load = better base fitness
- Compare to acute load for training balance

---

### 24. Acute/Chronic Ratio (`acute_chronic_ratio`)

**Purpose**: Measures training balance to prevent overtraining.

**Calculation**:
```
if chronic_load > 0:
  acute_chronic_ratio = acute_load / chronic_load
else:
  no value calculated
```

**Criteria**:
- Based on sports science research
- Requires chronic_load > 0
- Ratio interpreted for injury risk

**Example**:
- Acute load: 4930
- Chronic load: 4607.5
- Ratio: 4930 / 4607.5 = 1.07

**Interpretation** (based on sports science):
- **< 0.8**: Undertraining - may be detraining
- **0.8-1.3**: Sweet spot - optimal training load
- **1.3-1.5**: Caution zone - approaching overtraining
- **> 1.5**: High injury risk - overtraining likely

---

### 25. Overtraining Detection (`overtraining_detected`)

**Purpose**: Automatic flag when acute/chronic ratio is too high.

**Calculation**:
```
if acute_chronic_ratio > 1.5:
  overtraining_detected = true
else:
  overtraining_detected = false
```

**Criteria**:
- Threshold: 1.5 (based on sports science research)
- Boolean flag for easy monitoring

**Example**:
- Ratio 1.7 → overtraining_detected = true
- Ratio 1.2 → overtraining_detected = false

**Interpretation**:
- True: Take rest day(s), reduce volume
- False: Training load is manageable

---

### 26. Average Recovery Time (`avg_recovery_time`)

**Purpose**: How much time between climbing sessions.

**Calculation**:
```
session_dates = unique dates when any log was created, sorted
recovery_times = []
for i in 1 to count(session_dates):
  time_diff = session_dates[i] - session_dates[i-1] (in hours)
  recovery_times.append(time_diff)

avg_recovery_time = average of recovery_times
```

**Criteria**:
- Uses **all logs**
- Session = unique calendar date with logs
- Time measured in hours between session dates

**Example**:
- Session dates: Jan 1, Jan 3, Jan 5, Jan 6, Jan 9
- Gaps: 48h, 48h, 24h, 72h
- Average: (48+48+24+72) / 4 = 48 hours

**Interpretation**:
- < 24 hours: Daily climber (high frequency)
- 24-72 hours: Every 2-3 days (standard)
- > 72 hours: Weekly climber (lower frequency)

---

### 27. Average Time Between Performances (`avg_time_between_performances`)

**Purpose**: How often climber achieves peak performance.

**Calculation**:
```
top_grade = maximum grade in all logs
threshold = top_grade × 0.9  (top 10%)
performance_logs = logs with grade >= threshold, sorted by date

For each consecutive pair of performance_logs:
  time_diff = difference in hours
  
avg_time_between_performances = average of all time_diffs
```

**Criteria**:
- Uses **all logs**
- Performance = top 10% hardest climbs
- Requires at least 2 performance logs
- Time measured in hours

**Example**:
- Hardest grade: 700
- Threshold: 700 × 0.9 = 630
- Performance logs: Jan 5 (650), Jan 15 (640), Feb 1 (700), Feb 10 (635)
- Time gaps: 240h, 408h, 216h
- Average: (240+408+216) / 3 = 288 hours (12 days)

**Interpretation**:
- Shows peak performance frequency
- Lower is better (more frequent peaks)
- Can indicate peaking cycles

---

## Data Sources

### Log Types Used in Calculations

1. **Public Logs Only** (type: 'work', 'flash', 'view'):
   - Used for public-facing stats
   - Contest calculations
   - Progression metrics
   - Achievement metrics

2. **All Logs** (public + tentative):
   - Used for personal analytics
   - Training load metrics
   - Behavioral patterns
   - Attempt tracking

### Relationships Used

- `log.route`: Route being climbed
- `log.route.tags`: Movement types and characteristics
- `log.route.line.sector`: Physical location/area
- `log.user`: Climber who created the log
- `log.created_at`: Timestamp for time-based calculations

---

## Calculation Frequency

### Automated Updates
- **Schedule**: Nightly at 2:00 AM
- **Command**: `php artisan stats:calculate`
- **Process**: Runs for all users with logs
- **Duration**: Processes in chunks of 100 users

### Manual Triggers
```bash
# All users
php artisan stats:calculate

# Specific user
php artisan stats:calculate --user_id=123
```

### Calculation Order
1. Load all user logs (with relationships)
2. Split into public vs all logs
3. Calculate technical metrics
4. Calculate behavioral metrics
5. Calculate progression metrics
6. Calculate training load metrics
7. Save to user_stats table
8. Update last_calculated_at timestamp

---

## Important Notes

### Route Counting
- **Unique routes**: Multiple logs of the same route count as ONE route in stats
- **Total logs**: All logs count individually for volume/frequency metrics
- **Example**: 3 sends of the same route = 1 unique route, 3 total logs

### Grade System
- Grades are stored as integers (300-950)
- Correspond to climbing grades (3a through 9c+)
- Higher numbers = harder difficulty
- 10 points typically = one plus grade (e.g., 500→510 = 5a→5a+)

### Null Handling
- If insufficient data: stat remains NULL
- Prevents misleading zeros
- Check for NULL before displaying to users

### Performance Considerations
- Tags eagerly loaded to avoid N+1 queries
- Null-safe operators prevent errors
- Chunks user processing for memory efficiency
- Calculations run off-peak hours (2 AM)

---

## Changelog

- **2026-01-03**: Initial documentation created
- **Version**: 1.0
