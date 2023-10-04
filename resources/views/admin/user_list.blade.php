@extends('.admin.index')

@section('title', 'Admin Page')

@section('content')
    <div class="container">
        <h1 class="mb-4">Foydalanuvchilar</h1>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>T/R</th>
                    <th>Ism Familiya Sharif</th>
                    <th>Telefon</th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ (($users->currentPage() - 1) * $users->perPage() + ($loop->index + 1)) }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->phone }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.show', ['admin' => $user->id]) }}">
                                <input class="btn btn-success" type="submit" value="Ko'rish"/>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination justify-content-center mt-3">
        {{ $users->links() }}
    </div>

@endsection
