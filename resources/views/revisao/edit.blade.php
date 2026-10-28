@extends('layouts.model')

@section('content')
    @include('revisao._form', ['route' => '/revisao/edit/'.$revisao->id, 'type' => 'PATCH'])
@endsection
