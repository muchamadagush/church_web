@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Data Jadwal Pertukaran Doa</h1>

    <div class="table-responsive">
        <table class="table">
            <thead class="bg-light">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Jan</th>
                    <th>Feb</th>
                    <th>Mar</th>
                    <th>Apr</th>
                    <th>Mei</th>
                    <th>Jun</th>
                    <th>Jul</th>
                    <th>Agu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($schedules as $index => $schedule)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $schedule->name }}</td>
                    <td>{!! $schedule->jan ? '✓' : '' !!}</td>
                    <td>{!! $schedule->feb ? '✓' : '' !!}</td>
                    <td>{!! $schedule->mar ? '✓' : '' !!}</td>
                    <td>{!! $schedule->apr ? '✓' : '' !!}</td>
                    <td>{!! $schedule->may ? '✓' : '' !!}</td>
                    <td>{!! $schedule->jun ? '✓' : '' !!}</td>
                    <td>{!! $schedule->jul ? '✓' : '' !!}</td>
                    <td>{!! $schedule->aug ? '✓' : '' !!}</td>
                    <td>
                        <button class="btn btn-primary btn-sm">Edit</button>
                        <button class="btn btn-danger btn-sm">Hapus</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<style>
.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 8px;
    text-align: left;
    border: 1px solid #ddd;
}

.table thead th {
    background-color: #f5f5f5;
}

.btn {
    padding: 4px 8px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    margin-right: 4px;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}
</style>
@endsection
