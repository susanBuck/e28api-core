@extends('master')

@section('body')

<section>
    <h2>API</h2>

    <ul>

        <li>POST <code>/api/login</code></li>
        <li>POST <code>/api/register</code></li>
        <li>GET <code>/api/user</code> Details for logged in user</li>

        @foreach($resources as $resourceName => $fields)
        <li>
            <strong>{{ $resourceName}}</strong>:
            <ul>
                <li>Index: GET <code><a href='/api/{{ $resourceName }}'>/api/{{ $resourceName }}</a></code></li>
                <li>Show: GET <code>/api/{{ $resourceName }}/{id}</code></a>
                <li>Store: POST <code>/api/{{ $resourceName }}</code></a>
                <li>Update: PUT <code>/api/{{ $resourceName }}/{id}</code></a>
                <li>Destroy: DELETE <code>/api/{{ $resourceName }}/{id}</code></a>
                <li>Query: GET <code>/api/{{ $resourceName }}/query?key=value&key=value</code></a>
            </ul>
        </li>
        @endforeach

        </li>
    </ul>
</section>

<section>
    <h2>Database</h2>
    @if($database)
    @foreach($database as $table => $data)
    <div class='db-table'>
        <h5>{{ $table }}</h5>

        @if($data == null)
        <small class='text-muted'>No data</small>
        @else

        <table class='table table-dark table-sm'>

            {{-- Table header with column names --}}
            <thead>
                <tr>
                    @foreach($data[0] as $key => $value)
                    <th scope="col">{{ $key }}</th>
                    @endforeach
                </tr>
            </thead>

            {{-- Rows of data --}}
            <tbody>
                @foreach($data as $row)
                <tr>
                    @foreach($row as $key => $value)
                    <td>{{ $value }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>

        </table>

        @endif
    </div>

    @endforeach
    @endif
</section>


@endsection