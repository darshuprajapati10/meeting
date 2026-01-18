# Meeting Current Month API

Return a month-view grid of meetings for the authenticated user's organizations.

- Method: POST
- URL: /api/meeting/current-month
- Auth: Bearer token (Sanctum)

## Request

All fields are optional. If omitted, the API uses the current year and month.

- year: integer (1970–2100)
- month: integer (1–12)

### Examples

#### 1) Current month
```bash
curl -X POST http://localhost:8000/api/meeting/current-month \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json" \
  -d '{}'
```

#### 2) Specific month
```bash
curl -X POST http://localhost:8000/api/meeting/current-month \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{"year":2025,"month":11}'
```

## Response

Only days that have meetings are returned in `days`. If the month has no meetings, `days` will be an empty array.

```json
{
  "data": {
    "year": 2025,
    "month": 11,
    "start": "2025-11-01",
    "end": "2025-11-30",
    "days": [
      {
        "date": "2025-11-18",
        "weekday": 2,
        "count": 2,
        "meetings": [
          {
            "id": 10,
            "meeting_title": "Sprint Planning",
            "status": "Scheduled",
            "time": "09:30",
            "duration": 60,
            "meeting_type": "Online"
          }
        ]
      }
    ]
  },
  "message": "Month retrieved successfully."
}
```

Field notes:
- year / month: The year and month for the calendar.
- start / end: The first/last date of the month.
- days[].weekday: ISO weekday (1=Mon ... 7=Sun).
- days[].count: number of meetings on that date.
- days[].meetings: lightweight meeting info for that date.

## JavaScript (Fetch)
```javascript
const token = localStorage.getItem('auth_token');
const res = await fetch('http://localhost:8000/api/meeting/current-month', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({ year: 2025, month: 11 }) // or {}
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

const { data } = await api.post('/meeting/current-month', { year: 2025, month: 11 });
console.log(data);
```

## UI Tips
- Generate the full month grid client-side using `start` → `end`, and merge the returned `days` (which are non-empty only) for counts and badges.
- Use `days[].meetings` list to show per-day chips or tooltips.
