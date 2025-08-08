<table>
    <thead>
        <tr>
            @if($floorFlag)
            <th>Floor</th>
            @endif
            <th>Date</th>
            <th>Day</th>
            <th>Session</th>
            <th>Check-In Count</th>
            <th>Check-Out Count</th>
            <th>Min Count</th>
            <th>Max Count</th>
            <th>Min Time(Minutes)</th>
            <th>Max Time(Minutes)</th>
            <th>Avg Time(Minutes)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $date => $sessions)
        @foreach ($sessions as $sessionData)
        <tr>
            @if($floorFlag)
            <td>{{ getFloorname($floorId) }}</td>
            @endif
            <td>{{ $sessionData['date'] ?? '-' }}</td>
            <td>{{ $sessionData['day'] ?? '-' }}</td>
            <td>{{ ucfirst($sessionData['session'] ?? '-') }}</td>
            <td>{{ $sessionData['check_in_count'] ?? '0' }}</td>
            <td>{{ $sessionData['check_out_count'] ?? '0' }}</td>
            <td>{{ $sessionData['min_count'] ?? '0' }}</td>
            <td>{{ $sessionData['max_count'] ?? '0' }}</td>
            <td>{{ $sessionData['min_time'] ?? '0' }}</td>
            <td>{{ $sessionData['max_time'] ?? '0' }}</td>
            <td>{{ $sessionData['avg_time'] ?? '0' }}</td>
        </tr>
        @endforeach
        @endforeach
    </tbody>
</table>