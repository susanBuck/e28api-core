@extends('master')

@section('body')

<h1 data-test='api-heading'>API</h1>

<!--
SESSION_COOKIE: {{ config('session.cookie') }}
SESSION_DOMAIN: {{ config('session.domain') }}
SESSION_SECURE_COOKIE: {{ config('session.secure') ? 'TRUE' : 'FALSE' }}
CORS_ALLOWED_ORIGINS: @foreach(config('cors.allowed_origins') as $config) {{ $config }} @endforeach
SANCTUM_STATEFUL_DOMAINS: @foreach(config('sanctum.stateful') as $config) {{ $config }} @endforeach 
-->

<section>
    <h2>Routes</h2>

    <h3>Authorization</h3>
    <table class='table table-light table-striped table-bordered table-sm'>
        <thead class='thead-light'>
            <tr>
                <th>Name</th>
                <th>HTTP Method</th>
                <th>URL</th>
                <th>Usage & Params</th>
            </tr>
        </thead>

        <tr>
            <td>login</td>
            <td><code>POST</code></code></td>
            <td><code>/login</code></td>
            <td>Log in a user (expects params: <code>email</code>, <code>password</code>); includes a <em>Set-Cookie</em> HTTP response header if successful.</em></td>
        </tr>
        <tr>
            <td>auth</td>
            <td><code>POST</code></code></td>
            <td><code>/auth</code></td>
            <td>Check a visitor’s authentication status.</td>
        </tr>
        <tr>
            <td>logout</td>
            <td><code>POST</code></td>
            <td><code>/logout</code></td>
            <td>Log out a user.</td>
        </tr>
        <tr>
            <td>register</td>
            <td><code>POST</code></td>
            <td><code>/register</code></td>
            <td>Register a new user (expects params: <code>name</code>, <code>email</code>, <code>password</code>).</td>
        </tr>
    </table>


    <h3>Testing</h3>
    <table class='table table-light table-striped table-bordered table-sm'>
        <thead class='thead-light'>
            <tr>
                <th>Name</th>
                <th>HTTP Method</th>
                <th>URL</th>
                <th>Usage & Params</th>
            </tr>
        </thead>

        <tr>
            <td>refresh</td>
            <td><code>GET</code></code></td>
            <td><code>/refresh</code></td>
            <td>Clears any existing data for resources and re-runs seeds, giving application a “fresh start” for testing purposes.</em></td>
        </tr>

        <tr>
            <td>login-as</td>
            <td><code>POST</code></code></td>
            <td><code>/login-as/{user_Id}</code></td>
            <td>Log in a user by id. Utility for testing purposes, e.g. <code>cy.visit('/login-as/' + user.id);</code>. Only works for requests coming from a .loc domain.</td>
        </tr>

    </table>

    @foreach($resources as $resourceName => $resource)
    <h3>Resource: <code>{{ $resourceName }}</code></h3>
    <p>

        <div class='permission-level'>
            Permission level:
            {{ $resource->permission_level }}
            <em>({{ $permission_levels[$resource->permission_level] }})</em>
        </div>
    </p>

    <table class='table table-light table-striped table-bordered table-sm'>
        <thead class='thead-light'>
            <tr>
                <th>Name</th>
                <th>HTTP Method</th>
                <th>URL</th>
                <th>Usage & Params</th>
            </tr>
        </thead>
        <tr>
            <td>index</td>
            <td><code>GET</code></td>
            <td><code>/{{ $resourceName }}</code></td>
            <td>Show all <em>{{ Str::plural($resourceName) }}</em>.</td>
        </tr>
        <tr>
            <td>show</td>
            <td><code>GET</code></td>
            <td><code>/{{ $resourceName }}/{id}</code></td>
            <td>Show an individual <em>{{ $resourceName }}</em>.</td>
        </tr>
        <tr>
            <td>query</td>
            <td><code>GET</code></td>
            <td><code>/{{ $resourceName }}/query?field=value&field=value</code></td>
            <td>Query a <em>{{ $resourceName }}</em> by the given field params.</td>
        </tr>
        <tr>
            <td>store</td>
            <td><code>POST</code></td>
            <td><code>/{{ $resourceName }}</code></td>
            <td>Store a new <em>{{ $resourceName }}</em>.</td>
        </tr>
        <tr>
            <td>update</td>
            <td><code>PUT</code></td>
            <td><code>/{{ $resourceName }}/{id}</code></td>
            <td>Update an existing <em>{{ $resourceName }}</em></td>
        </tr>
        <tr>
            <td>destroy</td>
            <td><code>DELETE</code></td>
            <td><code>/{{ $resourceName }}/{id}</code></td>
            <td>Delete an existing <em>{{ $resourceName }}</em></td>
        </tr>


        </tbody>
    </table>
    @endforeach


</section>

<section>
    <h2>Database</h2>
    <small>
        Note: Because this API is designed for demonstration purposes, this database info is publicly viewable. In a

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
                    <td>{{ $key == 'password' ? '***' : $value }}</td>
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
