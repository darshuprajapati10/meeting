# Meeting Current Day API

Return all meetings for a specific day, ordered by time.

- Method: POST
- URL: /api/meeting/current-day
- Auth: Bearer token (Sanctum)

## Request

All fields are optional. If omitted, the API uses today's date.

- date: string (YYYY-MM-DD)
- year: integer (1970–2100)
- month: integer (1–12)
- day: integer (1–31)

### Examples

#### 1) Current day (today)
```bash
curl -X POST http://localhost:8000/api/meeting/current-day \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json" \
  -d '{}'
```

#### 2) Specific date
```bash
curl -X POST http://localhost:8000/api/meeting/current-day \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{"date":"2025-11-04"}'
```

#### 3) Using Y/M/D parts
```bash
curl -X POST http://localhost:8000/api/meeting/current-day \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{"year":2025,"month":11,"day":4}'
```

## Response

Meetings are ordered by time. If no meetings exist for the day, `meetings` will be an empty array.

```json
{
  "data": {
    "date": "2025-11-04",
    "weekday": 2,
    "year": 2025,
    "month": 11,
    "day": 4,
    "count": 1,
    "meetings": [
      {
        "id": 5,
        "meeting_title": "Design Workshop",
        "status": "Scheduled",
        "time": "15:00",
        "duration": 120,
        "meeting_type": "Online",
        "custom_location": null,
        "agenda_notes": "Discuss design system updates"
      }
    ]
  },
  "message": "Day retrieved successfully."
}
```

Field notes:
- date: The date in YYYY-MM-DD format.
- weekday: ISO weekday (1=Mon ... 7=Sun).
- year / month / day: Numeric components of the date.
- count: Total number of meetings for this day.
- meetings[].time: Time in HH:MM format (24-hour).
- meetings[].duration: Duration in minutes.
- meetings[].custom_location: Location string if provided, otherwise null.
- meetings[].agenda_notes: Agenda/notes if provided, otherwise null.

## JavaScript (Fetch)
```javascript
const token = localStorage.getItem('auth_token');
const res = await fetch('http://localhost:8000/api/meeting/current-day', {
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

const { data } = await api.post('/meeting/current-day', { date: '2025-11-04' });
console.log(data);
```

## React Example
```javascript
import { useState, useEffect } from 'react';

function DayView({ date }) {
  const [meetings, setMeetings] = useState([]);
  const token = localStorage.getItem('auth_token');

  useEffect(() => {
    fetch('http://localhost:8000/api/meeting/current-day', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ date })
    })
      .then(res => res.json())
      .then(data => setMeetings(data.data.meetings));
  }, [date, token]);

  return (
    <div>
      {meetings.map(meeting => (
        <div key={meeting.id} className="meeting-card">
          <h3>{meeting.meeting_title}</h3>
          <p>{meeting.time} - {meeting.duration} minutes</p>
          <p>Type: {meeting.meeting_type}</p>
          {meeting.custom_location && <p>Location: {meeting.custom_location}</p>}
        </div>
      ))}
    </div>
  );
}
```

## UI Tips
- Display meetings in chronological order (already sorted by time).
- Format time as 12-hour (e.g., "3:00 PM") for display.
- Show duration as hours/minutes (e.g., "2 hours" for 120 minutes).
- Use `count` to show a summary badge or header.
- If `meetings` is empty, show a "No meetings scheduled" message.

