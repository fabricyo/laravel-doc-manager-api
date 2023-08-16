<html>
<body>
    <h2>{{ $doc->name }}</h2>
    <h4>Type {{ $doc->document_type->name }}</h4>
    <p>Created at {{ $doc->created_at->toDayDateTimeString() }} </p>
    <p>Last update at {{ $doc->updated_at->toDayDateTimeString() }} </p>
    @foreach($doc->data as $col)
        <p><span style="font-weight: bolder">{{$col->name }}</span>: {{$col->content}}</p>
    @endforeach
</body>
</html>

