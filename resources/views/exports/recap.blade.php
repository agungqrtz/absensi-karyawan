<!DOCTYPE html>
<html>
<head>
    <title>Rekap Absensi</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #dddddd; text-align: left; padding: 8px; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rekapitulasi Absensi Karyawan</h1>
        <p>Periode: {{ $monthName }} {{ $year }}</p>
    </div>

    @if($recap->isEmpty())
        <p>Tidak ada data untuk periode ini.</p>
    @else
        <h3>Ringkasan Absensi</h3>
        <table>
            <thead>
                <tr>
                    <th>Nama Karyawan</th>
                    <th>Hadir</th>
                    <th>Izin</th>
                    <th>Sakit</th>
                    <th>Alpha</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recap as $name => $statuses)
                    <tr>
                        <td>{{ $name }}</td>
                        <td>{{ $statuses->get('Hadir', 0) }}</td>
                        <td>{{ $statuses->get('Izin', 0) }}</td>
                        <td>{{ $statuses->get('Sakit', 0) }}</td>
                        <td>{{ $statuses->get('Alpha', 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <br><br>

        <h3>Data Detail</h3>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama Karyawan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detail as $item)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item->date)->isoFormat('dddd, D MMMM YYYY') }}</td>
                        <td>{{ $item->employee->name }}</td>
                        <td>{{ $item->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
