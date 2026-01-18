# Meeting Current Week API

Retrieve a 7-day week view (Mon–Sun) of meetings for the authenticated user's organizations.

- Method: POST
- URL: /api/meeting/current-week
- Auth: Bearer token (Sanctum)

## Request

All fields are optional. If omitted, the API returns the current week.

- date: string (YYYY-MM-DD)
- year: integer (1970–2100)
- month: integer (1–12)
- day: integer (1–31)

Notes:
- Provide either date, or year/month[/day]. If none are provided, the server uses today's date.
- The week is computed as Monday through Sunday containing the provided date.

### Examples

#### 1) Current week
```bash
curl -X POST http://localhost:8000/api/meeting/current-week \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json" \
  -d '{}'
```

#### 2) Week containing a specific date
```bash
curl -X POST http://localhost:8000/api/meeting/current-week \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{"date":"2025-11-04"}'
```

#### 3) Week using Y/M/D parts
```bash
curl -X POST http://localhost:8000/api/meeting/current-week \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{"year":2025,"month":11,"day":4}'
```

## Response

The service returns only days that actually have meetings (days with zero items are omitted). If no meetings exist in the week, `days` will be an empty array.

```json
{
  "data": {
    "year": 2025,
    "week_start": "2025-11-03",
    "week_end": "2025-11-09",
    "days": [
      {
        "date": "2025-11-04",
        "weekday": 2,
        "count": 1,
        "meetings": [
          {
            "id": 5,
            "meeting_title": "Design Workshop",
            "status": "Scheduled",
            "time": "10:00",
            "duration": 60,
            "meeting_type": "Online"
          }
        ]
      }
    ]
  },
  "message": "Week retrieved successfully."
}
```

Field notes:
- year: Year of `week_start`.
- week_start / week_end: Monday/Sunday of the computed week.
- days[].weekday: ISO weekday (1=Mon ... 7=Sun).
- days[].count: number of meetings for that date.
- days[].meetings: lightweight meeting info for that date.

## JavaScript (Fetch)
```javascript
const token = localStorage.getItem('auth_token');
const res = await fetch('http://localhost:8000/api/meeting/current-week', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({ date: '2025-11-04' }) // or {}, or {year:2025,month:11,day:4}
});
const data = await res.json();
console.log(data);
```

## Axios
```javascript
import axios from 'axios';

const token = localStorage.getItem('auth_token');
const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: { Authorization: `Bearer ${token}` }
});

const { data } = await api.post('/meeting/current-week', { date: '2025-11-04' });
console.log(data);
```

## UI Tips
- Render a 7-column grid for Mon–Sun using `week_start` to `week_end`.
- Since the API returns only days with meetings, merge the returned `days` into your full UI grid.
- Use `days[i].count` to show a subtle dot/badge; enumerate `meetings` for tooltips or day detail view.
