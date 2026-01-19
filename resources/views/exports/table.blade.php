<table border="1" cellspacing="0" cellpadding="6">
    <thead>
        <tr>
            @foreach($columns as $label)
                <th><strong>{{ $label }}</strong></th>
            @endforeach
        </tr>
    </thead>

    <tbody>
        @foreach($rows as $row)
            <tr>
                @foreach($fields as $field)
                    <td>{{ data_get($row, $field, '-') }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
