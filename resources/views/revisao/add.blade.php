@extends('layouts.model')

@section('content')
    @include('revisao._form', ['route' => '/revisao/store', 'type' => 'POST'])
@endsection
