@extends('master')

@section('body')

<h1 data-test='api-heading'>API</h1>

<section id='configs'>
    <h2>Security Configs</h2>

    <h3>Allowed origins</h3>

    <p>What URLs can make requests to this API:</p>
    <ul>
        @if($allowedOrigins[0] == '*')
        <li><code>*</code> (Any URL)</li>

        @else

        @foreach($allowedOrigins as $origin)
        <li><code>{{ $origin }}</code></li>
        @endforeach

        @endif
    </ul>
    <small>Configure via <em>CORS_ALLOWED_ORIGINS</em> in <em>/e28-api/core/.env</em></small>


    <h3>Stateful domains</h3>
    <p>What domains and/or subdomains will receive stateful API authentication cookies in response to succesful login requests:</p>
    <ul>
        @if($statefulDomains[0] == '')
        <code>None specified</code>
        @else
        @foreach($statefulDomains as $domain)
        <li><code>{{ $domain }}</code></li>
        @endforeach
        @endif
    </ul>
    <small>Configure via <em>SANCTUM_STATEFUL_DOMAINS</em> in <em>/e28-api/core/.env</em></small>


    <h3>Session domain</h3>
    <p>Authentication cookies will be valid under this root domain: <code>{{ $sessionDomain }}</code></p>

    <small>Configure via <em>SESSION_DOMAIN</em> in <em>/e28-api/core/.env</em></small>


    <h3>Session secure cookie</h3>
    <p>Authentication cookies will only be sent back if the browser has a HTTPS connection: <code>{{ $httpsCookie ? 'True' : 'False' }}</code></p>

    <small>Configure via <em>SESSION_SECURE_COOKIE</em> in <em>/e28-api/core/.env</em></small>

</section>

<section>
    <h2>Routes</h2>

    <small>🔒 = Auth-required</small>

    <table class='table table-light table-striped table-bordered table-sm'>
        <thead class='thead-dark'>
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

        @foreach($resources as $resourceName => $fields)
        <tr>
            <td>index</td>
            <td><code>GET</code></td>
            <td>{{ property_exists($fields, 'user_id') ? '🔒' : ' ' }} <code>/{{ $resourceName }}</code></td>
            <td>Show all <em>{{ Str::plural($resourceName) }}</em>.
                @if(property_exists($fields, 'user_id'))
                Will only return <em>{{ Str::plural($resourceName) }}</em> belonging to the currently authenticated user.
                @endif
            </td>
        </tr>
        <tr>
            <td>show</td>
            <td><code>GET</code></td>
            <td>{{ property_exists($fields, 'user_id') ? '🔒' : ' ' }} <code>/{{ $resourceName }}/{id}</code></td>
            <td>Show an individual <em>{{ $resourceName }}</em>.
                @if(property_exists($fields, 'user_id'))
                Only works if the requested <em>{{ $resourceName }}</em> belongs to the currently authenticated user.
                @endif
            </td>
        </tr>
        <tr>
            <td>store</td>
            <td><code>POST</code></td>
            <td>{{ property_exists($fields, 'user_id') ? '🔒' : ' ' }} <code>/{{ $resourceName }}</code></td>
            <td>Store a new <em>{{ $resourceName }}</em>.
                @if(property_exists($fields, 'user_id'))
                By default, the new <em>{{ $resourceName }}</em> will be associated with the currently authenticated user.
                @endif
            </td>
        </tr>
        <tr>
            <td>update</td>
            <td><code>PUT</code></td>
            <td>{{ property_exists($fields, 'user_id') ? '🔒' : ' ' }} <code>/{{ $resourceName }}/{id}</code></td>
            <td>Update an existing <em>{{ $resourceName }}</em>.
                @if(property_exists($fields, 'user_id'))
                Only works if the <em>{{ $resourceName }}</em> being updated belongs to the currently authenticated user.


                @endif
            </td>

        </tr>
        <tr>
            <td>destroy</td>
            <td><code>DELETE</code></td>
            <td>{{ property_exists($fields, 'user_id') ? '🔒' : ' ' }} <code>/{{ $resourceName }}/{id}</code></td>

            <td>Delete an existing <em>{{ $resourceName }}</em>.
                @if(property_exists($fields, 'user_id'))
                Only works if the <em>{{ $resourceName }}</em> being deleted belongs to the currently authenticated user.
                @endif
            </td>
        </tr>
        <tr>
            <td>query</td>
            <td><code>GET</code></td>
            <td>{{ property_exists($fields, 'user_id') ? '🔒' : ' ' }} <code>/{{ $resourceName }}/query?field=value&field=value</code></td>
            <td>Query a <em>{{ $resourceName }}</em> by the given field params.
                @if(property_exists($fields, 'user_id'))
                Will only return <em>{{ Str::plural($resourceName) }}</em> belonging the currently authenticated user.


                @endif
            </td>
        </tr>

        @endforeach
        </tbody>
    </table>

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
