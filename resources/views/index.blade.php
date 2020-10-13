@extends('master')

@section('body')
<section>
    <h2>API</h2>

    <strong>Allowed origins</strong>
    (<small>Configure via <code>/core/.env</code></small>)

    <ul>
        @foreach($allowedOrigins as $origin)
        <li><code>{{ $origin }}</code></li>
        @endforeach
    </ul>




    <table class='table table-light table-striped table-bordered table-sm'>
        <thead class='thead-dark'>
            <tr>
                <th>HTTP Method</th>
                <th>URL</th>
                <th>Usage & Params</th>
            </tr>
        </thead>
        <tr>
            <td><code>POST</code></code></td>
            <td><code>/login</code></td>
            <td>Log in a user (<code>email</code>, <code>password</code>)</td>
        </tr>
        <tr>
            <td><code>POST</code></td>
            <td><code>/logout</code></td>
            <td>Log out a user</td>
        </tr>
        <tr>
            <td><code>POST</code></td>
            <td><code>/register</code></td>
            <td>Register a new user (<code>name</code>, <code>email</code>, <code>password</code>)</td>
        </tr>
        <tr>
            <td><code>POST</code></td>
            <td><code>/auth</code></td>
            <td>Authorize and get details of currently logged in user</td>
        </tr>

        <tbody>
            @foreach($resources as $resourceName => $fields)
            <tr>

                <td><code>GET</code></td>
                <td><code>/{{ $resourceName }}</code></td>
                <td>Show all {{ $resourceName }}</td>
            </tr>
            <tr>
                <td><code>GET</code></td>
                <td><code>/{{ $resourceName }}/{id}</code></td>
                <td>Show an individual {{ $resourceName }}</td>
            </tr>
            <tr>
                <td><code>POST</code></td>
                <td><code>/{{ $resourceName }}</code></td>
                <td>Store a new {{ $resourceName }}</td>
            </tr>
            <tr>
                <td><code>PUT</code></td>
                <td><code>/{{ $resourceName }}/{id}</code></td>
                <td>Update an existing {{ $resourceName }}</td>
            </tr>
            <tr>
                <td><code>DELETE</code></td>
                <td><code>/{{ $resourceName }}/{id}</code></td>
                <td>Delete an existing {{ $resourceName }}</td>
            </tr>
            <tr>
                <td><code>GET</code></td>
                <td><code>/{{ $resourceName }}/query?key=value&key=value</code></td>
                <td>Query a {{ $resourceName }}</td>
            </tr>

            @endforeach
        </tbody>
    </table>
</section>

<section>
    <h2>Database</h2>
    <small>
        Note: Because this API is designed for demonstration purposes, this database info is publically viewable. In a
        real-world application, this information should be restricted.
    </small>

    @if($database)
    @foreach($database as $table => $data)
    <div class='db-table table-responsive'>
        <h5>{{ $table }}</h5>

        @if($data == null)
        <small class='text-muted'>No data</small>
        @else

        <table class='table table-light table-sm table-striped table-bordered'>
            <thead class="thead-dark">
                <tr>
                    @foreach($data[0] as $key => $value)
                    <th scope="col">{{ $key }}</th>
                    @endforeach
                </tr>
            </thead>
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
